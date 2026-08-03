<?php

namespace App\Jobs;

use App\Jobs\Concerns\LogsExecution;
use App\Models\AdministrationContract;
use App\Models\OwnerLiquidation;
use App\Models\RentalContract;
use App\Models\RentBill;
use App\Models\User;
use App\Notifications\GiroPropietarioBloqueadoNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Política interna de la inmobiliaria: al propietario se le gira en su día
 * fijo del mes, exista o no exista pago del inquilino ese mes — así se
 * atraen más clientes propietarios. La única excepción es cuando el
 * inquilino acumula más de 3 meses de mora: ahí NO se gira automático y se
 * avisa para que alguien lo revise a mano.
 */
class GenerarLiquidacionesAutomaticasJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, LogsExecution;

    public int $tries = 3;

    const MESES_MORA_LIMITE = 3;

    public function handle(): void
    {
        $this->iniciarLog('Liquidaciones Automáticas por Día de Giro');

        $hoyDia = now()->day;
        $mes    = now()->month;
        $anio   = now()->year;

        $contratos = AdministrationContract::where('dia_giro', $hoyDia)
            ->where('estado', 'activo')
            ->with(['propietario', 'property'])
            ->get();

        $generadas = 0;
        $bloqueadas = 0;
        $omitidas = 0;

        $admins = User::role(['super_admin', 'admin'])->get();

        foreach ($contratos as $admContrato) {
            $rentalContrato = RentalContract::where('administration_contract_id', $admContrato->id)
                ->where('estado', 'activo')
                ->first();

            if (!$rentalContrato) { $omitidas++; continue; }

            $mesesEnMora = RentBill::where('rental_contract_id', $rentalContrato->id)
                ->whereNotIn('estado', ['pagada', 'anulada'])
                ->count();

            if ($mesesEnMora > self::MESES_MORA_LIMITE) {
                $bloqueadas++;
                foreach ($admins as $admin) {
                    $admin->notify(new GiroPropietarioBloqueadoNotification(
                        propietario: $admContrato->propietario?->nombre_completo ?? 'Propietario sin nombre',
                        inmueble: $admContrato->property?->codigo ?? $admContrato->property?->direccion ?? 'Inmueble sin código',
                        mesesEnMora: $mesesEnMora,
                    ));
                }
                Log::warning("Giro bloqueado por mora: contrato {$rentalContrato->numero_contrato} — {$mesesEnMora} meses en mora");
                continue;
            }

            $bill = RentBill::where('rental_contract_id', $rentalContrato->id)
                ->where('mes', $mes)->where('anio', $anio)
                ->first();

            if (!$bill) {
                $omitidas++;
                Log::info("Sin factura del mes para generar liquidación: contrato {$rentalContrato->numero_contrato}");
                continue;
            }

            $liq = OwnerLiquidation::generarDesdeFact($bill);
            if ($liq) $generadas++;
            else $omitidas++;
        }

        Log::info("GenerarLiquidacionesAutomaticasJob completado — generadas: {$generadas}, bloqueadas por mora: {$bloqueadas}, omitidas: {$omitidas}");

        $this->finalizarLog($generadas, [
            'generadas' => $generadas,
            'bloqueadas_por_mora' => $bloqueadas,
            'omitidas' => $omitidas,
            'fecha' => now()->toDateString(),
        ]);
    }
}
