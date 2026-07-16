<?php

namespace App\Console\Commands;

use App\Exports\RentalContracts\RentalContractTemplateExporter;
use App\Models\Third;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Extrae los contratos de arriendo del sistema viejo (Siinmob) y genera el
 * Excel en el formato de RentalContractTemplateExporter, incluyendo datos
 * del codeudor cuando existen.
 *
 * Por cada inmueble visita su ficha /ver/ (dirección), su ficha
 * /arrendamiento/ (contrato vigente: arrendatario, fechas, canon) y el
 * formulario de edición de ese contrato (fechas exactas, codeudor).
 *
 * Uso: php artisan siinmob:import-contratos-arriendo {usuario} {password} [--salida=ruta.xlsx] [--limite=N]
 */
class ImportSiinmobContratosArriendo extends Command
{
    protected $signature = 'siinmob:import-contratos-arriendo
                            {usuario : Usuario de Siinmob}
                            {password : Contraseña de Siinmob}
                            {--salida=storage/app/siinmob_contratos_arriendo.xlsx : Ruta del Excel de salida}
                            {--limite= : Límite de inmuebles a procesar (para pruebas)}';

    protected $description = 'Extrae los contratos de arriendo del sistema Siinmob y genera el Excel de carga masiva';

    private const BASE = 'https://serviarrendar.siinmob.com.co';

    /** @var array<string,string> nombre completo (normalizado) => numero_documento */
    private array $arrendatariosPorNombre = [];

    public function handle(): int
    {
        $usuario  = $this->argument('usuario');
        $password = $this->argument('password');
        $limite   = $this->option('limite') ? (int) $this->option('limite') : null;
        $salida   = base_path($this->option('salida'));

        $this->cargarArrendatariosLocales();

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

        // ── 1. Recolectar links de todos los inmuebles ──
        $links    = [];
        $listPage = (string) $guzzle->get('/inmueble/principal/listar/', [
            'headers' => ['Referer' => self::BASE . '/'],
        ])->getBody();

        preg_match('/Total registros encontrados: <b>(\d+)<\/b>/', $listPage, $m);
        $totalRegistros = isset($m[1]) ? (int) $m[1] : null;
        $this->info('Total de inmuebles en Siinmob: ' . ($totalRegistros ?? '?'));

        $pagina = 1;
        while (true) {
            $html = $pagina === 1
                ? $listPage
                : (string) $guzzle->get("/inmueble/principal/listar/todos/order.inmueble.asc/pag.{$pagina}/", [
                    'headers' => ['Referer' => self::BASE . '/inmueble/principal/listar/'],
                ])->getBody();

            preg_match_all('#/inmueble/principal/ver/[0-9]+\.[a-f0-9]+/#', $html, $lm);
            $encontrados = count($lm[0]);
            if ($encontrados === 0) break;

            foreach ($lm[0] as $link) $links[] = $link;
            $this->line("  Página {$pagina}: {$encontrados} inmuebles (acumulado: " . count(array_unique($links)) . ')');

            if ($totalRegistros !== null && count(array_unique($links)) >= $totalRegistros) break;
            $pagina++;
            if ($pagina > 100) { $this->warn('Límite de seguridad de 100 páginas alcanzado.'); break; }
            usleep(300_000);
        }

        $links = array_values(array_unique($links));
        if ($limite) $links = array_slice($links, 0, $limite);
        $this->info('Total de fichas a procesar: ' . count($links));

        // ── 2. Visitar cada inmueble: dirección + contrato de arriendo ──
        $filas = [];
        $bar = $this->output->createProgressBar(count($links));
        $bar->start();

        foreach ($links as $link) {
            try {
                $htmlVer = (string) $guzzle->get($link, [
                    'headers' => ['Referer' => self::BASE . '/inmueble/principal/listar/'],
                ])->getBody();

                $direccion = $this->inputValue($htmlVer, 'inmueble[direccion]');

                if (preg_match('#href="(/inmueble/principal/arrendamiento/[0-9]+\.[a-f0-9]+/)"#', $htmlVer, $am)) {
                    $htmlArr = (string) $guzzle->get($am[1], [
                        'headers' => ['Referer' => self::BASE . $link],
                    ])->getBody();

                    $contrato = $this->parseContratoActivo($htmlArr);

                    if ($contrato && preg_match('#href="(/inmueble/arrendamiento/editar/[0-9]+\.[a-f0-9]+/[0-9]+\.[a-f0-9]+/[0-9]+\.[a-f0-9]+/)"#', $htmlArr, $em)) {
                        $htmlEditar = (string) $guzzle->get($em[1], [
                            'headers' => ['Referer' => self::BASE . $am[1]],
                        ])->getBody();

                        $this->enriquecerConEditar($contrato, $htmlEditar);
                    }

                    if ($contrato && $direccion !== '') {
                        $contrato['inmueble_direccion'] = $direccion;
                        $filas[] = $contrato;
                    }
                }
            } catch (\Throwable $e) {
                $this->newLine();
                $this->warn("Error en {$link}: " . $e->getMessage());
            }
            usleep(200_000);
            $bar->advance();
        }
        $bar->finish();
        $this->newLine(2);

        $this->info('Contratos de arriendo válidos extraídos: ' . count($filas));
        $sinArrendatario = count(array_filter($filas, fn ($f) => $f['arrendatario_documento'] === ''));
        if ($sinArrendatario > 0) {
            $this->warn("{$sinArrendatario} contratos sin arrendatario resuelto (nombre no coincide con ningún Tercero ya migrado).");
        }

        $this->escribirExcel($filas, $salida);
        $this->info("Excel generado en: {$salida}");

        return self::SUCCESS;
    }

    private function cargarArrendatariosLocales(): void
    {
        Third::where('es_arrendatario', true)->get(['nombre_completo', 'numero_documento'])->each(function ($t) {
            if ($t->nombre_completo) {
                $this->arrendatariosPorNombre[$this->normalizarNombre($t->nombre_completo)] = $t->numero_documento;
            }
        });
        $this->info('Arrendatarios cargados localmente para resolver por nombre: ' . count($this->arrendatariosPorNombre));
    }

    private function normalizarNombre(string $nombre): string
    {
        return trim(preg_replace('/\s+/', ' ', Str::upper(trim($nombre))));
    }

    private function inputValue(string $html, string $name): string
    {
        $pattern = '/name="' . preg_quote($name, '/') . '"[^>]*value="([^"]*)"/';
        return preg_match($pattern, $html, $m) ? trim(html_entity_decode($m[1])) : '';
    }

    /**
     * Parsea la primera fila (contrato vigente) de la tabla
     * "Historial de arrendatarios" de la ficha de arrendamiento del inmueble.
     */
    private function parseContratoActivo(string $html): ?array
    {
        if (! preg_match('/<tbody>(.*?)<\/tbody>/s', $html, $tb)) return null;
        if (! preg_match('/<tr>(.*?)<\/tr>/s', $tb[1], $trm)) return null;

        preg_match_all('/<td[^>]*>(.*?)<\/td>/s', $trm[1], $tds);
        $celdas = array_map(fn ($c) => trim(strip_tags($c)), $tds[1] ?? []);

        // NUM | CONTRATO | INQUILINO | TELEFONO(S) | F.INICIO | F.FINAL | V.ARR | ARR. | ACCIONES
        if (count($celdas) < 7) return null;

        $arrendatarioNombre = $celdas[2] ?? '';
        $canon              = preg_replace('/[^\d]/', '', $celdas[6] ?? '');

        $arrendatarioDocumento = '';
        if ($arrendatarioNombre !== '') {
            $clave = $this->normalizarNombre($arrendatarioNombre);
            $arrendatarioDocumento = $this->arrendatariosPorNombre[$clave] ?? '';
        }

        return [
            'arrendatario_documento' => $arrendatarioDocumento,
            'fecha_inicio'           => $celdas[4] ?? now()->format('Y-m-d'),
            'fecha_fin'              => $celdas[5] ?? now()->addYear()->format('Y-m-d'),
            'canon_mensual'          => $canon,
            'deposito'               => '',
            'duracion_meses'         => '12',
            'tipo_garantia'          => 'ninguna',
            'codeudor_documento'     => '',
            'codeudor_nombre'        => '',
            'codeudor_apellido'      => '',
            'codeudor_celular'       => '',
            'codeudor_direccion'     => '',
            'estado'                 => 'activo',
            'notas'                  => 'Migrado desde Siinmob.',
        ];
    }

    private function enriquecerConEditar(array &$contrato, string $htmlEditar): void
    {
        $fechaReal   = $this->inputValue($htmlEditar, 'contrato[fecha_real]');
        $fechaFinal  = $this->inputValue($htmlEditar, 'contrato[fecha_final]');
        $vigencia    = $this->inputValue($htmlEditar, 'contrato[vigencia_real]');
        $valorArr    = $this->inputValue($htmlEditar, 'contrato[valor_arriendo]');

        if ($fechaReal !== '') $contrato['fecha_inicio'] = $fechaReal;
        if ($fechaFinal !== '') $contrato['fecha_fin'] = $fechaFinal;
        if ($vigencia !== '') $contrato['duracion_meses'] = $vigencia;
        if ($valorArr !== '') $contrato['canon_mensual'] = preg_replace('/[^\d]/', '', $valorArr);

        $codNombre   = $this->inputValue($htmlEditar, 'codeudor[nombre]');
        $codApellido = $this->inputValue($htmlEditar, 'codeudor[apellido]');
        $codNuip     = $this->inputValue($htmlEditar, 'codeudor[nuip]');

        // El sistema viejo usa "0" como valor vacío en estos campos
        if ($codNuip !== '' && $codNuip !== '0' && $codNombre !== '' && $codNombre !== '0') {
            $codDireccion = $this->inputValue($htmlEditar, 'codeudor[direccion]');
            $codCelular   = $this->inputValue($htmlEditar, 'codeudor[celular]');

            $contrato['tipo_garantia']      = 'codeudor';
            $contrato['codeudor_documento'] = $codNuip;
            $contrato['codeudor_nombre']    = $codNombre;
            $contrato['codeudor_apellido']  = $codApellido;
            $contrato['codeudor_celular']   = $codCelular !== '0' ? $codCelular : '';
            $contrato['codeudor_direccion'] = $codDireccion !== '0' ? $codDireccion : '';
        }
    }

    private function escribirExcel(array $filas, string $rutaSalida): void
    {
        $exporter    = new RentalContractTemplateExporter();
        $spreadsheet = $exporter->build();
        $sheet       = $spreadsheet->getSheetByName('Contratos');

        $columnKeys = array_keys(RentalContractTemplateExporter::COLUMNS);
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
