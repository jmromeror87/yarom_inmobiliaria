<?php
namespace App\Jobs;

use App\Jobs\Concerns\LogsExecution;
use App\Models\Company;
use App\Models\RentBill;
use App\Models\RentalContract;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerarFacturasMensuales implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, LogsExecution;

    public int $tries = 3;

    public function handle(): void
    {
        $this->iniciarLog('Generar Facturas Mensuales');
        $mes     = now()->month;
        $anio    = now()->year;
        $company = Company::first();
        $wap     = app(WhatsAppService::class);

        $diasGracia = $company?->dias_gracia_mora ?? 5;
        $tasaMora   = $company?->tasa_mora_mensual ?? 1.5441;
        $tasaDiaria = round($tasaMora / 30, 6);

        // fecha límite = día de corte mensual (ej. día 5 del mes)
        $diaCorte     = $company?->dia_corte_mensual ?? 5;
        $fechaLimite  = now()->startOfMonth()->addDays($diaCorte - 1)->toDateString();
        $periodoInicio = now()->startOfMonth()->toDateString();
        $periodoFin    = now()->endOfMonth()->toDateString();

        $contratos = RentalContract::where('estado', 'activo')
            ->with(['property', 'arrendatario'])
            ->get();


        $generadas = 0;

        $tarifaSeguroSura = (float)($company?->tarifa_seguro_sura ?? 2.50);
        $tarifaIva        = (float)($company?->tarifa_iva ?? 19);

        foreach ($contratos as $contrato) {
            $existe = RentBill::where('rental_contract_id', $contrato->id)
                ->where('mes', $mes)->where('anio', $anio)->exists();
            if ($existe) continue;

            $canonBase = (float)$contrato->canon_mensual;
            $admin     = (float)($contrato->cuota_administracion ?? 0);

            // ── Seguro SURA: se lee del inmueble ────────────────────────────
            $tieneSeguroSura = (bool)($contrato->property?->tiene_seguro_sura);
            $valorSeguroSura = 0;
            $ivaSeguroSura   = 0;
            $redondeoSeguro  = 0;

            if ($tieneSeguroSura) {
                $valorSeguroSura = round($canonBase * ($tarifaSeguroSura / 100), 2);
                $ivaSeguroSura   = round($valorSeguroSura * ($tarifaIva / 100), 2);
            }

            // Canon cobrado al inquilino: valor manual del inmueble (con seguro redondeado)
            // La diferencia sobre el exacto va al propietario
            $totalExacto    = $canonBase + $admin + $valorSeguroSura + $ivaSeguroSura;
            $canonInquilino = (float)($contrato->property?->canon_cobrado_inquilino ?? 0);
            if ($tieneSeguroSura && $canonInquilino > $totalExacto) {
                $total          = $canonInquilino;
                $redondeoSeguro = round($canonInquilino - $totalExacto, 2);
            } else {
                $total          = $totalExacto;
                $redondeoSeguro = 0;
            }

            $bill = RentBill::create([
                'rental_contract_id'   => $contrato->id,
                'property_id'          => $contrato->property_id,
                'arrendatario_id'      => $contrato->arrendatario_id,
                'mes'                  => $mes,
                'anio'                 => $anio,
                'periodo_inicio'       => $periodoInicio,
                'periodo_fin'          => $periodoFin,
                'canon_base'           => $canonBase,
                'cuota_administracion' => $admin,
                'valor_seguro_sura'    => $valorSeguroSura,
                'iva_seguro_sura'      => $ivaSeguroSura,
                'redondeo_seguro'      => $redondeoSeguro,
                'total_factura'        => $total,
                'saldo_pendiente'      => $total,
                'fecha_limite_pago'    => $fechaLimite,
                'dias_gracia'          => $diasGracia,
                'tasa_mora_diaria'     => $tasaDiaria,
                'estado'               => 'pendiente',
                'tipo_documento'       => 'documento_equivalente',
            ]);

            // Generar token de pago y enviar link por WhatsApp
            if ($contrato->arrendatario?->celular) {
                try {
                    $token    = $bill->generatePaymentToken();
                    $urlPago  = route('payment.show', ['token' => $token]);
                    $inmueble = ($contrato->property?->codigo ?? '') . ' — ' . ($contrato->property?->direccion ?? '');
                    $totalFmt = '$' . number_format($total, 0, ',', '.');
                    $fechaFmt = \Carbon\Carbon::parse($fechaLimite)->format('d/m/Y');
                    $nombre   = $contrato->arrendatario->nombre_completo;
                    $empresa  = $company?->razon_social ?? 'Serviarrendar S.A.S';

                    $seguroLinea = $tieneSeguroSura && $valorSeguroSura > 0
                        ? "🛡️ Seguro SURA: \$" . number_format($valorSeguroSura + $ivaSeguroSura + $redondeoSeguro, 0, ',', '.') . " COP\n"
                        : '';

                    $msg = "🏠 *Factura de Arrendamiento*\n\n"
                        . "Estimad@ {$nombre},\n\n"
                        . "📋 *{$bill->numero}*\n"
                        . "📅 Período: " . now()->translatedFormat('F Y') . "\n"
                        . "🏠 Inmueble: {$inmueble}\n\n"
                        . "💰 Canon: \$" . number_format($canonBase, 0, ',', '.') . " COP\n"
                        . ($admin > 0
                            ? "🏢 Administración: \$" . number_format($admin, 0, ',', '.') . " COP\n"
                            : '')
                        . $seguroLinea
                        . "💵 *Total: {$totalFmt} COP*\n\n"
                        . "📆 *Vence: {$fechaFmt}*\n\n"
                        . "🔗 *Pagar en línea (PSE · Nequi · Tarjeta):*\n{$urlPago}\n\n"
                        . "— {$empresa}";

                    $resultado = $wap->enviar($contrato->arrendatario->celular, $msg);
                    if ($resultado['ok'] ?? false) {
                        $bill->update(['wap_enviado' => true, 'wap_enviado_at' => now()]);
                    }
                } catch (\Throwable $e) {
                    Log::warning("WhatsApp falló para factura {$bill->numero}: " . $e->getMessage());
                }
            }

            $generadas++;
            Log::info("Factura generada: {$bill->numero} — Contrato {$contrato->numero_contrato}");
        }

        Log::info("GenerarFacturasMensuales completado — {$generadas} facturas nuevas de {$contratos->count()} contratos");

        $this->finalizarLog($generadas, [
            'contratos_activos' => $contratos->count(),
            'facturas_generadas' => $generadas,
            'mes' => now()->format('F Y'),
        ]);
    }
}
