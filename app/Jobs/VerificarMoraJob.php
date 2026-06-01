<?php
namespace App\Jobs;

use App\Jobs\Concerns\LogsExecution;
use App\Models\Company;
use App\Models\RentBill;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class VerificarMoraJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, LogsExecution;

    public int $tries = 3;

    public function handle(): void
    {
        $this->iniciarLog('Verificar Mora');
        $company  = Company::first();
        $wap      = app(WhatsAppService::class);
        $hoy      = now()->toDateString();
        $empresa  = $company?->razon_social ?? 'Serviarrendar S.A.S';
        $celEmpresa = $company?->celular ?? '';

        $bills = RentBill::whereIn('estado', ['pendiente', 'parcial', 'en_mora', 'vencida'])
            ->where('fecha_limite_pago', '<', $hoy)
            ->with(['arrendatario', 'property', 'rentalContract'])
            ->get();

        $actualizadas = 0;

        foreach ($bills as $bill) {
            $diasMora = (int) now()->startOfDay()->diffInDays($bill->fecha_limite_pago->startOfDay());

            // Si el contrato indica mora solo sobre canon (admin la cobra el edificio),
            // usar canon_base como base de cálculo en lugar del saldo_pendiente completo.
            $baseParaMora = $bill->saldo_pendiente;
            if ($bill->rentalContract?->mora_solo_sobre_canon && $bill->canon_base > 0) {
                $proporcionCanon = $bill->canon_base / max($bill->total_factura, 1);
                $baseParaMora    = round($bill->saldo_pendiente * $proporcionCanon, 2);
            }

            $mora = round($baseParaMora * ($bill->tasa_mora_diaria / 100) * $diasMora, 2);

            $bill->update([
                'estado'            => 'en_mora',
                'dias_mora'         => $diasMora,
                'mora_acumulada'    => $mora,
                'fecha_inicio_mora' => $bill->fecha_inicio_mora ?? $bill->fecha_limite_pago,
            ]);

            // Aviso WhatsApp solo la primera vez
            if (!$bill->wap_mora_enviado && $bill->arrendatario?->celular) {
                try {
                    $token    = $bill->generatePaymentToken();
                    $urlPago  = route('payment.show', ['token' => $token]);
                    $nombre   = $bill->arrendatario->nombre_completo;
                    $saldoFmt = '$' . number_format($bill->saldo_pendiente, 0, ',', '.');
                    $moraFmt  = '$' . number_format($mora, 0, ',', '.');
                    $totalFmt = '$' . number_format($bill->saldo_pendiente + $mora, 0, ',', '.');

                    $msg = "⚠️ *AVISO DE MORA*\n\n"
                        . "Estimad@ {$nombre},\n\n"
                        . "Su factura *{$bill->numero}* lleva *{$diasMora} día(s) en mora*.\n\n"
                        . "💰 Saldo pendiente: {$saldoFmt} COP\n"
                        . "📈 Mora acumulada: {$moraFmt} COP\n"
                        . "💵 *Total a pagar: {$totalFmt} COP*\n\n"
                        . "Le solicitamos regularizar su pago a la mayor brevedad.\n\n"
                        . "🔗 *Pagar en línea:*\n{$urlPago}\n\n"
                        . "— {$empresa}"
                        . ($celEmpresa ? "\n☎️ {$celEmpresa}" : '');

                    $resultado = $wap->enviar($bill->arrendatario->celular, $msg);
                    if ($resultado['ok'] ?? false) {
                        $bill->update(['wap_mora_enviado' => true, 'wap_mora_enviado_at' => now()]);
                    }
                } catch (\Throwable $e) {
                    Log::warning("WhatsApp mora falló para {$bill->numero}: " . $e->getMessage());
                }
            }

            $actualizadas++;
            Log::info("Mora actualizada: {$bill->numero} — {$diasMora} días — mora: {$mora}");
        }

        Log::info("VerificarMoraJob completado — {$actualizadas} facturas en mora procesadas");

        $this->finalizarLog($actualizadas, [
            'facturas_en_mora' => $actualizadas,
            'fecha' => now()->toDateString(),
        ]);
    }
}
