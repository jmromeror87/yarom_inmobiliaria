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
            // Cálculo canónico (RentBill::recalcularMoraDesdeHistorial): reproduce
            // el historial de pagos con "interés primero" — cada abono salda el
            // interés causado antes de tocar capital, y solo entonces el ancla de
            // cómputo se mueve a la fecha de ese abono. Nunca se cobra interés
            // sobre interés ni se recalcula sobre el capital nuevo los días ya
            // saldados por un abono anterior.
            $r = RentBill::recalcularMoraDesdeHistorial($bill);
            $capital  = $r['capital'];
            $mora     = $r['mora'];
            $diasMora = $r['dias_mora'];

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

        // ── Aviso WhatsApp consolidado por contrato ─────────────────────────
        // En vez de un mensaje por factura, se manda UNO por inquilino que
        // resume TODOS los meses que debe (incluido el mes actual aunque
        // todavía esté en período de gracia, para que vea el panorama
        // completo), con días, interés y total de cada mes, más el total
        // acumulado — igual a como lo mostraba el sistema anterior (Siinmob).
        if ($this->enviarWhatsapp) {
            $contratosAvisar = $bills->where('wap_mora_enviado', false)
                ->pluck('rental_contract_id')
                ->unique();

            foreach ($contratosAvisar as $contratoId) {
                $pendientes = RentBill::where('rental_contract_id', $contratoId)
                    ->whereIn('estado', ['pendiente', 'parcial', 'en_mora'])
                    ->where('saldo_pendiente', '>', 0)
                    ->where('fecha_limite_pago', '<', $hoy)
                    ->with('arrendatario')
                    ->orderBy('periodo_inicio')
                    ->get();

                if ($pendientes->isEmpty()) continue;

                $arrendatario = $pendientes->first()->arrendatario;
                if (!$arrendatario?->celular) continue;

                $lineasMeses  = '';
                $totalGeneral = 0.0;
                foreach ($pendientes as $p) {
                    $diasReales = (int) $p->fecha_limite_pago->copy()->startOfDay()->diffInDays(now()->startOfDay());
                    $mesNombre  = ucfirst($p->periodo_inicio->translatedFormat('F Y'));
                    $lineasMeses .= "\n📅 *{$mesNombre}* ({$p->numero})\n"
                        . "   Días de mora: {$diasReales}\n"
                        . "   Interés mora: $" . number_format($p->mora_acumulada, 0, ',', '.') . "\n"
                        . "   Total del mes: $" . number_format($p->saldo_pendiente, 0, ',', '.') . "\n";
                    $totalGeneral += (float) $p->saldo_pendiente;
                }

                $bill0 = $pendientes->first();
                $token = ($bill0->payment_token && !$bill0->payment_token_expires_at?->isPast())
                    ? $bill0->payment_token
                    : $bill0->generatePaymentToken();
                $urlPago = route('payment.show', ['token' => $token]);

                $msg = "⚠️ *AVISO DE MORA*\n\n"
                    . "Estimado(a) {$arrendatario->nombre_completo},\n\n"
                    . "Tiene *{$pendientes->count()} mes(es)* pendiente(s) de pago:\n"
                    . $lineasMeses
                    . "\n💵 *TOTAL ACUMULADO A PAGAR: $" . number_format($totalGeneral, 0, ',', '.') . " COP*\n\n"
                    . "Le solicitamos regularizar su pago a la mayor brevedad.\n\n"
                    . "🔗 *Pagar en línea:*\n{$urlPago}\n\n"
                    . "— {$empresa}"
                    . ($celEmpresa ? "\n☎️ {$celEmpresa}" : '');

                try {
                    $resultado = $wap->enviar($arrendatario->celular, $msg);
                    if ($resultado['ok'] ?? false) {
                        $idsANotificar = $bills->where('rental_contract_id', $contratoId)
                            ->where('wap_mora_enviado', false)
                            ->pluck('id');
                        RentBill::whereIn('id', $idsANotificar)
                            ->update(['wap_mora_enviado' => true, 'wap_mora_enviado_at' => now()]);
                    }
                } catch (\Throwable $e) {
                    Log::warning("WhatsApp mora consolidado falló para contrato {$contratoId}: " . $e->getMessage());
                }
            }
        }

        Log::info("VerificarMoraJob completado — {$actualizadas} facturas en mora procesadas");

        $this->finalizarLog($actualizadas, [
            'facturas_en_mora' => $actualizadas,
            'fecha' => now()->toDateString(),
        ]);
    }
}
