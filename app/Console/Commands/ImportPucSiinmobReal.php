<?php

namespace App\Console\Commands;

use App\Models\AccountingAccount;
use Illuminate\Console\Command;

/**
 * Carga en accounting_accounts el PUC real y completo extraído del sistema
 * Siinmob (Balance de Comprobación + Estado de Resultados, nivel auxiliar),
 * es decir, solo las cuentas que la empresa realmente usa — no el catálogo
 * genérico completo de +1000 cuentas del PUC colombiano.
 *
 * Fuente: storage/app/cuentas_puc_siinmob.json (generado por scraping).
 *
 * Uso: php artisan puc:import-siinmob-real [--archivo=ruta.json]
 */
class ImportPucSiinmobReal extends Command
{
    protected $signature = 'puc:import-siinmob-real {--archivo=database/data/cuentas_puc_siinmob.json}';

    protected $description = 'Importa el PUC real (con auxiliares) extraído de Siinmob a accounting_accounts';

    public function handle(): int
    {
        $ruta = base_path($this->option('archivo'));

        if (! file_exists($ruta)) {
            $this->error("No se encontró el archivo: {$ruta}");
            return self::FAILURE;
        }

        $cuentas = json_decode(file_get_contents($ruta), true);
        if (! is_array($cuentas)) {
            $this->error('El archivo JSON no es válido.');
            return self::FAILURE;
        }

        $this->info('Total de cuentas a procesar: ' . count($cuentas));

        // Ordenar por nivel para crear primero los padres
        usort($cuentas, fn ($a, $b) => $a['nivel'] <=> $b['nivel']);

        $idsPorCodigo = [];
        $creadas = 0;
        $actualizadas = 0;

        foreach ($cuentas as $c) {
            $parentCodigo = $this->codigoPadre($c['codigo']);
            $parentId     = $parentCodigo ? ($idsPorCodigo[$parentCodigo] ?? null) : null;

            $cuenta = AccountingAccount::updateOrCreate(
                ['codigo' => $c['codigo']],
                [
                    'nombre'                => $c['nombre'],
                    'nivel'                 => $c['nivel'],
                    'parent_id'             => $parentId,
                    'clase'                 => $c['clase'],
                    'naturaleza'            => $c['naturaleza'],
                    'acepta_movimiento'     => $c['acepta_movimiento'],
                    'requiere_tercero'      => $c['requiere_tercero'],
                    'requiere_centro_costo' => false,
                    'estado'                => 'activo',
                ]
            );

            $cuenta->wasRecentlyCreated ? $creadas++ : $actualizadas++;
            $idsPorCodigo[$c['codigo']] = $cuenta->id;
        }

        $this->info("Cuentas creadas: {$creadas}");
        $this->info("Cuentas actualizadas: {$actualizadas}");

        return self::SUCCESS;
    }

    private function codigoPadre(string $codigo): ?string
    {
        $n = strlen($codigo);
        return match (true) {
            $n <= 1 => null,
            $n === 2 => substr($codigo, 0, 1),
            $n === 4 => substr($codigo, 0, 2),
            $n === 6 => substr($codigo, 0, 4),
            $n === 8 => substr($codigo, 0, 6),
            default  => null,
        };
    }
}
