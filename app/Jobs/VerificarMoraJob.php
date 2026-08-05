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

    public function __construct(private bool $enviarWhatsapp = true)
    {
    }

    public function handle(): void
    {
        $this->iniciarLog('Verificar Mora');
        $company  = Company::first();
        $wap      = app(WhatsAppService::class);
        $hoy      = now()->toDateString();
        $empresa  = $company?->razon_social ?? 'Serviarrendar S.A.S';
        $celEmpresa = $company?->celular ?? '';

        // Solo entra en mora después de agotar los días de gracia.
        $bills = RentBill::whereIn('estado', ['pendiente', 'parcial', 'en_mora', 'vencida'])
            ->whereRaw('DATE_ADD(fecha_limite_pago, INTERVAL dias_gracia DAY) < ?', [$hoy])
            ->where('aplicar_mora', true)
            ->whereDoesntHave('rentalContract', fn ($q) => $q->where('en_revision', true))
            ->with(['arrendatario', 'property', 'rentalContract'])
            ->get();

        $actualizadas = 0;

        foreach ($bills as $bill) {
            // El período de gracia es solo el "colchón" antes de que la mora
            // empiece a aplicar (por eso el filtro de arriba exige que ya se
            // haya superado fecha_limite + dias_gracia). Pero una vez se
            // supera, la mora se cobra completa desde la fecha límite
            // ORIGINAL — no se descuentan los días de gracia del conteo.
            $diasMora = (int) $bill->fecha_limite_pago->copy()->startOfDay()->diffInDays(now()->startOfDay());

            // La mora se calcula sobre el CAPITAL (factura + saldo arrastrado - pagado),
            // nunca sobre saldo_pendiente: ese campo puede incluir mora de días
            // anteriores (no se resincroniza salvo al registrar un pago), y usarlo
            // como base generaría interés sobre interés silenciosamente.
            $capital = round(
                (float) $bill->total_factura + (float) $bill->saldo_anterior_arrastrado - (float) $bill->total_pagado,
                2
            );

            $baseParaMora = $capital;
            if ($bill->rentalContract?->mora_solo_sobre_canon && $bill->canon_base > 0) {
                $proporcionCanon = $bill->canon_base / max($bill->total_factura, 1);
                $baseParaMora    = round($capital * $proporcionCanon, 2);
            }

            $mora = round($baseParaMora * ($bill->tasa_mora_diaria / 100) * $diasMora, 2);

            $bill->update([
                'estado'            => 'en_mora',
                'dias_mora'         => $diasMora,
                'mora_acumulada'    => $mora,
                'saldo_pendiente'   => max(0, round($capital + $mora, 2)),
                'fecha_inicio_mora' => $bill->fecha_inicio_mora ?? $bill->fecha_limite_pago->toDateString(),
            ]);

            // Renovar token de pago si expiró (inquilino en mora debe poder pagar en línea)
            if (!$bill->payment_token || $bill->payment_token_expires_at?->isPast()) {
                $bill->update([
                    'payment_token'            => bin2hex(random_bytes(32)),
                    'payment_token_expires_at' => now()->addDays(30)->endOfDay(),
                ]);
            }

            // Aviso WhatsApp solo la primera vez
            if ($this->enviarWhatsapp && !$bill->wap_mora_enviado && $bill->arrendatario?->celular) {
                try {
                    $token    = $bill->generatePaymentToken();
                    $urlPago  = route('payment.show', ['token' => $token]);
                    $nombre   = $bill->arrendatario->nombre_completo;
                    // saldo_pendiente ya incluye la mora del día (capital + mora) —
                    // no sumar $mora de nuevo o se duplica en el "total a pagar".
                    $saldoFmt = '$' . number_format($capital, 0, ',', '.');
                    $moraFmt  = '$' . number_format($mora, 0, ',', '.');
                    $totalFmt = '$' . number_format($bill->saldo_pendiente, 0, ',', '.');

                    $msg = "⚠️ *AVISO DE MORA*\n\n"
                        . "Estimado(a) {$nombre},\n\n"
                        . "Su factura *{$bill->numero}* lleva *{$diasMora} día(s) en mora*.\n\n"
                        . "💰 Saldo de la factura: {$saldoFmt} COP\n"
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

            // Contabilizar mora e intereses del período
            try {
                \App\Services\ContabilidadService::generarParaMora($bill, $mora);
                \App\Services\ContabilidadService::generarProvisionCartera($bill);
            } catch (\Throwable $e) {
                Log::warning("Contabilidad mora {$bill->numero}: " . $e->getMessage());
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
