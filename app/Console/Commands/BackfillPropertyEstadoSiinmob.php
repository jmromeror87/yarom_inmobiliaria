<?php

namespace App\Console\Commands;

use App\Models\Property;
use Illuminate\Console\Command;

/**
 * Corrige Property.estado usando el estado REAL consultado en vivo en Siinmob
 * (ver database/data/property_estados_siinmob.json), ya que el importador
 * original (siinmob:import-inmuebles) nunca leyó ese dato y dejó todos los
 * inmuebles en 'en_captacion' sin importar su situación contractual real.
 *
 * Uso: php artisan properties:backfill-estado-siinmob [--dry-run]
 */
class BackfillPropertyEstadoSiinmob extends Command
{
    protected $signature = 'properties:backfill-estado-siinmob {--dry-run : Solo mostrar los cambios sin aplicarlos}';

    protected $description = 'Corrige el estado de los inmuebles migrados usando el estado real verificado en Siinmob';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $ruta   = database_path('data/property_estados_siinmob.json');

        if (! file_exists($ruta)) {
            $this->error("No se encontró {$ruta}");
            return self::FAILURE;
        }

        $filas = json_decode(file_get_contents($ruta), true);
        $this->info('Registros a procesar: ' . count($filas));

        $actualizados = 0;
        $sinCambio    = 0;
        $noEncontrados = [];

        foreach ($filas as $fila) {
            $property = Property::where('codigo', $fila['codigo'])->first();

            if (! $property) {
                $noEncontrados[] = $fila['codigo'];
                continue;
            }

            if ($property->estado === $fila['estado_propuesto']) {
                $sinCambio++;
                continue;
            }

            if (! $dryRun) {
                $property->update(['estado' => $fila['estado_propuesto']]);
            }
            $actualizados++;
        }

        $this->newLine();
        $this->info(($dryRun ? '[DRY-RUN] ' : '') . "Actualizados: {$actualizados}");
        $this->info("Ya estaban correctos: {$sinCambio}");
        if ($noEncontrados) {
            $this->warn('Códigos no encontrados en BD: ' . implode(', ', $noEncontrados));
        }

        return self::SUCCESS;
    }
}
