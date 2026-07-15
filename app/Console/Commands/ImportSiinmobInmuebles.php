<?php

namespace App\Console\Commands;

use App\Exports\Properties\PropertyTemplateExporter;
use App\Models\Third;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Extrae los inmuebles del sistema viejo (Siinmob) y genera el Excel en el
 * formato de PropertyTemplateExporter. El propietario se resuelve por
 * coincidencia de nombre contra los Terceros ya migrados (numero_documento).
 *
 * Uso: php artisan siinmob:import-inmuebles {usuario} {password} [--salida=ruta.xlsx] [--limite=N]
 */
class ImportSiinmobInmuebles extends Command
{
    protected $signature = 'siinmob:import-inmuebles
                            {usuario : Usuario de Siinmob}
                            {password : Contraseña de Siinmob}
                            {--salida=storage/app/siinmob_inmuebles.xlsx : Ruta del Excel de salida}
                            {--limite= : Límite de inmuebles a procesar (para pruebas)}';

    protected $description = 'Extrae los inmuebles del sistema Siinmob y genera el Excel de carga masiva de Inmuebles';

    private const BASE = 'https://serviarrendar.siinmob.com.co';

    private const TIPO_INMUEBLE_MAP = [
        'apartamento'       => 'Apartamento',
        'casa'              => 'Casa',
        'local comercial'   => 'Local Comercial',
        'oficina'           => 'Oficina',
        'bodega'            => 'Bodega',
        'lote'              => 'Lote',
        'finca'             => 'Finca',
        'consultorio'       => 'Consultorio',
        'parqueadero'       => 'Parqueadero',
    ];

    /** @var array<string,string> nombre completo (upper) => numero_documento */
    private array $propietariosPorNombre = [];

    public function handle(): int
    {
        $usuario  = $this->argument('usuario');
        $password = $this->argument('password');
        $limite   = $this->option('limite') ? (int) $this->option('limite') : null;
        $salida   = base_path($this->option('salida'));

        $this->cargarPropietariosLocales();

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

        // ── 2. Visitar cada inmueble: ficha básica + administración (propietario/canon) ──
        $filas = [];
        $bar = $this->output->createProgressBar(count($links));
        $bar->start();

        foreach ($links as $link) {
            try {
                $htmlVer = (string) $guzzle->get($link, [
                    'headers' => ['Referer' => self::BASE . '/inmueble/principal/listar/'],
                ])->getBody();

                $basico = $this->parseVer($htmlVer);

                if (preg_match('#href="(/inmueble/principal/administracion/[0-9]+\.[a-f0-9]+/)"#', $htmlVer, $am)) {
                    $htmlAdmin = (string) $guzzle->get($am[1], [
                        'headers' => ['Referer' => self::BASE . $link],
                    ])->getBody();
                    $admin = $this->parseAdministracion($htmlAdmin);
                } else {
                    $admin = null;
                }

                $fila = $this->combinar($basico, $admin);
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

        $this->info('Inmuebles válidos extraídos: ' . count($filas));
        $sinPropietario = count(array_filter($filas, fn ($f) => $f['propietario_documento'] === ''));
        if ($sinPropietario > 0) {
            $this->warn("{$sinPropietario} inmuebles sin propietario resuelto (nombre no coincide con ningún Tercero ya migrado).");
        }

        $this->escribirExcel($filas, $salida);
        $this->info("Excel generado en: {$salida}");

        return self::SUCCESS;
    }

    private array $nombresDepartamentos = [];

    private function cargarPropietariosLocales(): void
    {
        Third::propietarios()->get(['nombre_completo', 'numero_documento'])->each(function ($t) {
            if ($t->nombre_completo) {
                $clave = $this->normalizarNombre($t->nombre_completo);
                $this->propietariosPorNombre[$clave] = $t->numero_documento;
            }
        });
        $this->info('Propietarios cargados localmente para resolver por nombre: ' . count($this->propietariosPorNombre));

        $this->nombresDepartamentos = \App\Models\Departamento::pluck('nombre')
            ->map(fn ($n) => Str::upper($n))
            ->sortByDesc(fn ($n) => strlen($n))
            ->values()
            ->all();
    }

    private function normalizarNombre(string $nombre): string
    {
        return trim(preg_replace('/\s+/', ' ', Str::upper(trim($nombre))));
    }

    /** @return array{0:string,1:string} [municipio, departamento] */
    private function separarCiudadDepartamento(string $ciudad): array
    {
        $ciudad = trim($ciudad);
        foreach ($this->nombresDepartamentos as $depUpper) {
            if (Str::endsWith(Str::upper($ciudad), $depUpper)) {
                $municipio    = trim(substr($ciudad, 0, -strlen($depUpper)));
                $departamento = $depUpper;
                return [$municipio !== '' ? $municipio : $ciudad, Str::title(Str::lower($departamento))];
            }
        }
        return [$ciudad, ''];
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

    private function parseVer(string $html): array
    {
        return [
            'direccion'     => $this->inputValue($html, 'inmueble[direccion]'),
            'ciudad'        => $this->inputValue($html, 'ciudad'),
            'tipo_inmueble' => $this->selectedOption($html, 'inmueble[tipo_inmueble_id]'),
            'escritura'     => $this->inputValue($html, 'inmueble[escritura]'),
            'registro'      => $this->inputValue($html, 'inmueble[registro]'),
            'catastro'      => $this->inputValue($html, 'inmueble[catastro]'),
        ];
    }

    private function parseAdministracion(string $html): ?array
    {
        // Primera fila de la tabla "Historial de propietarios"
        if (! preg_match('/<tbody>(.*?)<\/tbody>/s', $html, $tb)) return null;
        if (! preg_match('/<tr>(.*?)<\/tr>/s', $tb[1], $trm)) return null;

        preg_match_all('/<td[^>]*>(.*?)<\/td>/s', $trm[1], $tds);
        $celdas = array_map(fn ($c) => trim(strip_tags($c)), $tds[1] ?? []);

        // NUM | CONTRATO | PROPIETARIO | TELEFONO(S) | DIRECCION | V.ARR | P.PRO | ARR. | ACCIONES
        if (count($celdas) < 7) return null;

        return [
            'propietario_nombre' => $celdas[2] ?? '',
            'canon_arriendo'     => preg_replace('/[^\d]/', '', $celdas[5] ?? ''),
        ];
    }

    private function combinar(array $basico, ?array $admin): ?array
    {
        if ($basico['direccion'] === '') return null;

        $propietarioDocumento = '';
        if ($admin && $admin['propietario_nombre'] !== '') {
            $clave = $this->normalizarNombre($admin['propietario_nombre']);
            $propietarioDocumento = $this->propietariosPorNombre[$clave] ?? '';
        }

        $tipoInmuebleNombre = self::TIPO_INMUEBLE_MAP[Str::lower($basico['tipo_inmueble'])] ?? $basico['tipo_inmueble'];

        [$municipio, $departamento] = $this->separarCiudadDepartamento($basico['ciudad']);

        $notas = trim(implode(' / ', array_filter([
            $basico['escritura'] ? "Escritura: {$basico['escritura']}" : '',
            $basico['registro'] ? "Registro: {$basico['registro']}" : '',
            $basico['catastro'] ? "Catastro: {$basico['catastro']}" : '',
        ])));

        return [
            'propietario_documento' => $propietarioDocumento,
            'tipo_inmueble'         => $tipoInmuebleNombre,
            'direccion'             => $basico['direccion'],
            'barrio'                => '',
            'municipio'             => $municipio,
            'departamento'          => $departamento,
            'estrato'               => '',
            'area_construida_m2'    => '',
            'area_privada_m2'       => '',
            'habitaciones'          => '',
            'banos'                 => '',
            'garajes'               => '',
            'canon_arriendo'        => $admin['canon_arriendo'] ?? '',
            'cuota_administracion'  => '',
            'disponible_arriendo'   => 'SI',
            'disponible_venta'      => 'NO',
            'estado'                => 'en_captacion',
            'notas_internas'        => $notas,
        ];
    }

    private function escribirExcel(array $filas, string $rutaSalida): void
    {
        $exporter    = new PropertyTemplateExporter();
        $spreadsheet = $exporter->build();
        $sheet       = $spreadsheet->getSheetByName('Inmuebles');

        $columnKeys = array_keys(PropertyTemplateExporter::COLUMNS);
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
