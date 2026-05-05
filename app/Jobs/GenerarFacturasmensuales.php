<?php
namespace App\Jobs;

use App\Models\RentalContract;
use App\Models\RentBill;
use App\Models\Company;
use App\Helpers\WhatsApp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerarFacturasMensuales implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $mes   = now()->month;
        $anio  = now()->year;
        $company = Company::first();

        $contratos = RentalContract::where('estado', 'activo')
            ->with(['property','arrendatario'])
            ->get();

        foreach ($contratos as $contrato) {
            // Evitar duplicados
            $existe = RentBill::where('rental_contract_id', $contrato->id)
                ->where('mes', $mes)->where('anio', $anio)->exists();
            if ($existe) continue;

            $diasGracia   = $company?->dias_gracia_mora ?? 5;
            $tasaMora     = $company?->tasa_mora_mensual ?? 1.5441;
            $tasaDiaria   = round($tasaMora / 30, 6);
            $periodoInicio = now()->startOfMonth()->toDateString();
            $periodoFin    = now()->endOfMonth()->toDateString();
            $fechaLimite   = now()->startOfMonth()->addDays($diasGracia)->toDateString();

            $total = $contrato->canon_mensual + $contrato->cuota_administracion;

            $bill = RentBill::create([
                'rental_contract_id'  => $contrato->id,
                'property_id'         => $contrato->property_id,
                'arrendatario_id'     => $contrato->arrendatario_id,
                'mes'                 => $mes,
                'anio'                => $anio,
                'periodo_inicio'      => $periodoInicio,
                'periodo_fin'         => $periodoFin,
                'canon_base'          => $contrato->canon_mensual,
                'cuota_administracion'=> $contrato->cuota_administracion,
                'total_factura'       => $total,
                'saldo_pendiente'     => $total,
                'fecha_limite_pago'   => $fechaLimite,
                'dias_gracia'         => $diasGracia,
                'tasa_mora_diaria'    => $tasaDiaria,
                'estado'              => 'pendiente',
                'tipo_documento'      => $contrato->arrendatario?->requiere_factura_electronica
                    ? 'factura_electronica' : 'documento_equivalente',
            ]);

            // Enviar WhatsApp al arrendatario
            if ($contrato->arrendatario?->celular) {
                $nombreArrend = $contrato->arrendatario->nombre_completo;
                $canonFmt     = '$' . number_format($contrato->canon_mensual, 0, ',', '.');
                $totalFmt     = '$' . number_format($total, 0, ',', '.');
                $fechaFmt     = \Carbon\Carbon::parse($fechaLimite)->format('d/m/Y');
                $inmueble     = $contrato->property?->codigo . ' — ' . $contrato->property?->direccion;

                $mensaje = "Estimad@ {$nombreArrend},\n\n" .
                    "📋 Factura de arrendamiento {$bill->numero}\n" .
                    "📅 Período: " . now()->translatedFormat('F Y') . "\n" .
                    "🏠 Inmueble: {$inmueble}\n\n" .
                    "💰 Canon: {$canonFmt}\n" .
                    ($contrato->cuota_administracion > 0 ? "🏢 Administración: $" . number_format($contrato->cuota_administracion, 0, ',', '.') . "\n" : '') .
                    "💵 *Total a pagar: {$totalFmt}*\n\n" .
                    "📆 Fecha límite de pago: {$fechaFmt}\n" .
                    "⚠️ Después de esta fecha aplica mora.\n\n" .
                    "Realice su pago a:\n" .
                    "🏦 " . ($company?->banco ?? 'Bancolombia') . "\n" .
                    "💳 Cuenta: " . ($company?->numero_cuenta ?? '') . "\n\n" .
                    "Serviarrendar S.A.S\n☎️ " . ($company?->celular ?? '3186934710');

                $enviado = WhatsApp::enviar($contrato->arrendatario->celular, $mensaje);
                if ($enviado) {
                    $bill->update(['wap_enviado' => true, 'wap_enviado_at' => now()]);
                }
            }

            Log::info("Factura generada: {$bill->numero} — Contrato {$contrato->numero_contrato}");
        }

        Log::info("GenerarFacturasMensuales completado — {$contratos->count()} contratos procesados");
    }
}
