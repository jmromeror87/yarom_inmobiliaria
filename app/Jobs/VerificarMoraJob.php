<?php
namespace App\Jobs;

use App\Models\RentBill;
use App\Models\Company;
use App\Helpers\WhatsApp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class VerificarMoraJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $company = Company::first();

        // Facturas pendientes/parciales vencidas
        RentBill::whereIn('estado', ['pendiente','parcial'])
            ->where('fecha_limite_pago', '<', now()->toDateString())
            ->with(['rentalContract','arrendatario'])
            ->get()
            ->each(function (RentBill $bill) use ($company) {
                $diasMora = now()->diffInDays($bill->fecha_limite_pago);
                $mora     = round($bill->saldo_pendiente * ($bill->tasa_mora_diaria / 100) * $diasMora, 2);

                $bill->update([
                    'estado'            => 'en_mora',
                    'dias_mora'         => $diasMora,
                    'mora_acumulada'    => $mora,
                    'fecha_inicio_mora' => $bill->fecha_inicio_mora ?? $bill->fecha_limite_pago,
                ]);

                // Enviar aviso de mora por WhatsApp (solo si no se ha enviado)
                if (!$bill->wap_mora_enviado && $bill->arrendatario?->celular) {
                    $nombre   = $bill->arrendatario->nombre_completo;
                    $saldo    = '$' . number_format($bill->saldo_pendiente, 0, ',', '.');
                    $moraFmt  = '$' . number_format($mora, 0, ',', '.');
                    $total    = '$' . number_format($bill->saldo_pendiente + $mora, 0, ',', '.');

                    $mensaje = "⚠️ AVISO DE MORA — Serviarrendar S.A.S\n\n" .
                        "Estimad@ {$nombre},\n\n" .
                        "Su factura {$bill->numero} se encuentra en mora.\n\n" .
                        "💰 Saldo pendiente: {$saldo}\n" .
                        "📈 Mora acumulada ({$diasMora} días): {$moraFmt}\n" .
                        "💵 *Total a pagar: {$total}*\n\n" .
                        "Le solicitamos realizar el pago a la mayor brevedad para evitar mayores recargos.\n\n" .
                        "Serviarrendar S.A.S\n☎️ " . ($company?->celular ?? '3186934710');

                    $enviado = WhatsApp::enviar($bill->arrendatario->celular, $mensaje);
                    if ($enviado) {
                        $bill->update(['wap_mora_enviado' => true, 'wap_mora_enviado_at' => now()]);
                    }
                }
            });
    }
}
