<?php

namespace App\Console\Commands;

use App\Models\Property;
use App\Models\RentalContract;
use App\Models\RentalContractStatusHistory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * El importador legacy (siinmob:import-contratos-arriendo) marcó TODOS los
 * contratos de arriendo como 'activo' sin importar su situación real. Tras
 * corregir Property.estado con el estado verificado en vivo en Siinmob
 * (properties:backfill-estado-siinmob), quedan contratos 'activo' sobre
 * inmuebles que en realidad están desocupados o inactivos.
 *
 * Este comando termina esos contratos. Usa una actualización directa (DB::table)
 * para NO disparar el observer de RentalContract::updating(), que sobreescribiría
 * el Property.estado ya corregido (lo pondría en 'disponible'/'en_captacion'
 * en vez de respetar 'inactivo', etc.).
 *
 * Uso: php artisan contratos:alinear-estado-real [--dry-run]
 */
class AlinearRentalContractsConEstadoReal extends Command
{
    protected $signature = 'contratos:alinear-estado-real {--dry-run : Solo mostrar los cambios sin aplicarlos}';

    protected $description = 'Termina los contratos de arriendo activos sobre inmuebles que ya no están arrendados según Siinmob';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $propiedadesNoArrendadas = Property::whereIn('estado', ['disponible', 'inactivo'])->pluck('id');

        $contratos = RentalContract::whereIn('property_id', $propiedadesNoArrendadas)
            ->where('estado', 'activo')
            ->get(['id', 'numero_contrato', 'property_id', 'estado', 'notas']);

        $this->info('Contratos de arriendo a terminar: ' . $contratos->count());

        if ($contratos->isEmpty()) {
            return self::SUCCESS;
        }

        $bills = DB::table('rent_bills')->whereIn('rental_contract_id', $contratos->pluck('id'))->count();
        if ($bills > 0) {
            $this->error("Hay {$bills} facturas ligadas a estos contratos. Abortando por seguridad — revisar manualmente.");
            return self::FAILURE;
        }

        if ($dryRun) {
            foreach ($contratos as $c) {
                $this->line("  [DRY-RUN] {$c->numero_contrato} (property {$c->property_id}): activo -> terminado");
            }
            return self::SUCCESS;
        }

        DB::transaction(function () use ($contratos) {
            foreach ($contratos as $c) {
                DB::table('rental_contracts')->where('id', $c->id)->update([
                    'estado'             => 'terminado',
                    'fecha_terminacion'  => now()->toDateString(),
                    'causal_terminacion' => 'otra',
                    'notas'              => trim(($c->notas ?? '') . ' / Terminado por alineación con estado real de Siinmob (auditoría 2026-07-16): el inmueble figura desocupado/inactivo en el sistema legacy.'),
                    'updated_at'         => now(),
                ]);

                RentalContractStatusHistory::create([
                    'rental_contract_id' => $c->id,
                    'changed_by'         => null,
                    'estado_anterior'    => 'activo',
                    'estado_nuevo'       => 'terminado',
                    'canal'              => 'backfill',
                    'razon_cambio'       => 'Alineación masiva con estado real verificado en Siinmob.',
                    'cambiado_en'        => now(),
                ]);
            }
        });

        $this->info("Terminados: {$contratos->count()}");

        return self::SUCCESS;
    }
}
