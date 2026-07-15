<?php

namespace App\Console\Commands;

use App\Exports\Thirds\ThirdTemplateExporter;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Extrae los arrendatarios del sistema viejo (Siinmob) por scraping autenticado
 * y genera un Excel en el formato de ThirdTemplateExporter, listo para pasar
 * por "Validar archivo" / "Importar Excel" en el módulo de Terceros.
 *
 * Mismo patrón que ImportSiinmobPropietarios, apuntando a /inmueble/arrendatario/.
 *
 * Uso: php artisan siinmob:import-arrendatarios {usuario} {password} [--salida=ruta.xlsx] [--limite=N]
 */
class ImportSiinmobArrendatarios extends Command
{
    protected $signature = 'siinmob:import-arrendatarios
                            {usuario : Usuario de Siinmob}
                            {password : Contraseña de Siinmob}
                            {--salida=storage/app/siinmob_arrendatarios.xlsx : Ruta del Excel de salida}
                            {--limite= : Límite de arrendatarios a procesar (para pruebas)}';

    protected $description = 'Extrae los arrendatarios del sistema Siinmob y genera el Excel de carga masiva de Terceros';

    private const BASE = 'https://serviarrendar.siinmob.com.co';

    private const TIPO_NUIP_MAP = [
        '1' => 'CC', '2' => 'CE', '3' => 'Pasaporte', '4' => 'TI', '5' => 'CC', '6' => 'NIT',
    ];

    public function handle(): int
    {
        $usuario  = $this->argument('usuario');
        $password = $this->argument('password');
        $limite   = $this->option('limite') ? (int) $this->option('limite') : null;
        $salida   = base_path($this->option('salida'));

        $jar    = new CookieJar();
        $guzzle = new Client([
            'base_uri'        => self::BASE,
            'cookies'         => $jar,
            'allow_redirects' => true,
            'timeout'         => 30,
            'headers'         => [
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36',
            ],
        ]);

        $this->info('Iniciando sesión en Siinmob...');
        $loginPage = (string) $guzzle->get('/sistema/login/entrar/')->getBody();

        if (! preg_match('/id="rsa32_key"[^>]*value="([^"]+)"/', $loginPage, $rm)) {
            $this->error('No se encontró el token de login (rsa32_key).');
            return self::FAILURE;
        }

        $loginResp = (string) $guzzle->post('/sistema/login/entrar/', [
            'headers'     => ['Referer' => self::BASE . '/sistema/login/entrar/'],
            'form_params' => [
                'login' => $usuario, 'password' => $password, 'mode' => 'auth', 'rsa32_key' => $rm[1],
            ],
        ])->getBody();

        if (Str::contains($loginResp, 'son incorrectos')) {
            $this->error('Usuario o contraseña incorrectos.');
            return self::FAILURE;
        }
        $this->info('Sesión iniciada correctamente.');

        // ── 1. Recolectar links de todos los arrendatarios ──
        $links    = [];
        $listPage = (string) $guzzle->get('/inmueble/arrendatario/listar/', [
            'headers' => ['Referer' => self::BASE . '/'],
        ])->getBody();

        preg_match('/Total registros encontrados: <b>(\d+)<\/b>/', $listPage, $m);
        $totalRegistros = isset($m[1]) ? (int) $m[1] : null;
        $this->info('Total de arrendatarios en Siinmob: ' . ($totalRegistros ?? '?'));

        $pagina = 1;
        while (true) {
            $html = $pagina === 1
                ? $listPage
                : (string) $guzzle->get("/inmueble/arrendatario/listar/order.nombre.asc/pag.{$pagina}/", [
                    'headers' => ['Referer' => self::BASE . '/inmueble/arrendatario/listar/'],
                ])->getBody();

            preg_match_all('#/inmueble/arrendatario/ver/[0-9]+\.[a-f0-9]+/#', $html, $lm);
            $encontrados = count($lm[0]);
            if ($encontrados === 0) break;

            foreach ($lm[0] as $link) $links[] = $link;
            $this->line("  Página {$pagina}: {$encontrados} arrendatarios (acumulado: " . count(array_unique($links)) . ')');

            if ($totalRegistros !== null && count(array_unique($links)) >= $totalRegistros) break;
            $pagina++;
            if ($pagina > 100) { $this->warn('Límite de seguridad de 100 páginas alcanzado.'); break; }
            usleep(300_000);
        }

        $links = array_values(array_unique($links));
        if ($limite) $links = array_slice($links, 0, $limite);
        $this->info('Total de fichas a procesar: ' . count($links));

        // ── 2. Visitar el detalle de cada arrendatario ──
        $filas = [];
        $bar = $this->output->createProgressBar(count($links));
        $bar->start();

        foreach ($links as $link) {
            try {
                $html = (string) $guzzle->get($link . 'show/', [
                    'headers' => ['Referer' => self::BASE . '/inmueble/arrendatario/listar/'],
                ])->getBody();

                $fila = $this->parseDetalle($html);
                if ($fila) $filas[] = $fila;
            } catch (\Throwable $e) {
                $this->newLine();
                $this->warn("Error en {$link}: " . $e->getMessage());
            }
            usleep(200_000);
            $bar->advance();
        }
        $bar->finish();
        $this->newLine(2);

        $this->info('Arrendatarios válidos extraídos: ' . count($filas));

        $this->escribirExcel($filas, $salida);
        $this->info("Excel generado en: {$salida}");

        return self::SUCCESS;
    }

    private function inputValue(string $html, string $name): string
    {
        $pattern = '/name="' . preg_quote($name, '/') . '"[^>]*value="([^"]*)"/';
        return preg_match($pattern, $html, $m) ? trim(html_entity_decode($m[1])) : '';
    }

    private function selectedOption(string $html, string $name): string
    {
        $selectPattern = '/<select[^>]*name="' . preg_quote($name, '/') . '"[^>]*>(.*?)<\/select>/s';
        if (! preg_match($selectPattern, $html, $sm)) return '';
        if (preg_match('/<option value="([^"]*)"\s+selected/', $sm[1], $om)) return $om[1];
        return '';
    }

    private function parseDetalle(string $html): ?array
    {
        $nuip = $this->inputValue($html, 'persona[nuip]');
        if ($nuip === '' || $nuip === '0') {
            return null;
        }

        $tipoNuip = self::TIPO_NUIP_MAP[$this->selectedOption($html, 'persona[tipo_nuip_id]')] ?? 'CC';

        $nombreCompleto   = $this->inputValue($html, 'persona[nombre]');
        $apellidoCompleto = $this->inputValue($html, 'persona[apellido]');
        $partesNombre     = preg_split('/\s+/', trim($nombreCompleto), 2);
        $partesApellido   = preg_split('/\s+/', trim($apellidoCompleto), 2);

        $telefono  = $this->inputValue($html, 'persona[telefono]');
        $direccion = $this->inputValue($html, 'persona[direccion]');

        return [
            'es_propietario'      => 'NO',
            'es_arrendatario'     => 'SI',
            'es_fiador'           => 'NO',
            'es_cliente_compra'   => 'NO',
            'es_proveedor'        => 'NO',
            'tipo_persona'        => 'natural',
            'tipo_documento'      => $tipoNuip,
            'numero_documento'    => $nuip,
            'digito_verificacion' => '',
            'primer_nombre'       => $partesNombre[0] ?? '',
            'segundo_nombre'      => $partesNombre[1] ?? '',
            'primer_apellido'     => $partesApellido[0] ?? '',
            'segundo_apellido'    => $partesApellido[1] ?? '',
            'razon_social'        => '',
            'nombre_comercial'    => '',
            'email'                 => $this->inputValue($html, 'persona[email]'),
            'celular'               => $this->inputValue($html, 'persona[celular]'),
            'telefono_fijo'         => $telefono !== '0' ? $telefono : '',
            'direccion_residencia'  => $direccion !== '0' ? $direccion : '',
            'barrio_residencia'     => '',
            'municipio'             => $this->inputValue($html, 'ciudad'),
            'departamento'          => '',
            'nacionalidad'          => 'Colombiana',
            'banco'                 => '',
            'tipo_cuenta'           => '',
            'numero_cuenta'         => '',
            'titular_cuenta'        => '',
            'comision_pactada'      => '',
            'tipo_empleo'           => '',
            'empresa_donde_trabaja' => '',
            'cargo'                 => $this->inputValue($html, 'profesion'),
            'ingresos_mensuales'    => '',
            'otros_ingresos'        => '',
        ];
    }

    private function escribirExcel(array $filas, string $rutaSalida): void
    {
        $exporter    = new ThirdTemplateExporter();
        $spreadsheet = $exporter->build();
        $sheet       = $spreadsheet->getSheetByName('Terceros');

        $columnKeys = array_keys(ThirdTemplateExporter::COLUMNS);
        $sheet->removeRow(2, 1);
        $sheet->insertNewRowBefore(2, 1);

        $row = 2;
        foreach ($filas as $fila) {
            $col = 1;
            foreach ($columnKeys as $key) {
                $letter = Coordinate::stringFromColumnIndex($col);
                $sheet->setCellValue("{$letter}{$row}", $fila[$key] ?? '');
                $col++;
            }
            $row++;
        }

        $dir = dirname($rutaSalida);
        if (! is_dir($dir)) mkdir($dir, 0755, true);

        (new Xlsx($spreadsheet))->save($rutaSalida);
    }
}
