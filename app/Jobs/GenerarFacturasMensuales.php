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

    public function __construct(
        private ?int $mesParam = null,
        private ?int $anioParam = null,
        private ?int $businessOriginId = null,
        private bool $enviarWhatsapp = true,
    ) {}

    public function handle(): void
    {
        $this->iniciarLog('Generar Facturas Mensuales');
        $mes     = $this->mesParam ?? now()->month;
        $anio    = $this->anioParam ?? now()->year;
        $company = Company::first();
        $wap     = app(WhatsAppService::class);

        $diasGracia = $company?->dias_gracia_mora ?? 5;
        $tasaMora   = $company?->tasa_mora_mensual ?? 1.5441;
        $tasaDiaria = round($tasaMora / 30, 6);

        $periodoBase = \Carbon\Carbon::create($anio, $mes, 1);

        // Día de corte global de la empresa (respaldo cuando el contrato no
        // tiene un día de pago propio pactado).
        $diaCorteGlobal = $company?->dia_corte_mensual ?? 5;

        $contratos = RentalContract::where('estado', 'activo')
            ->where('en_revision', false)
            ->when($this->businessOriginId, fn ($q) => $q->whereHas('property', fn ($p) => $p->where('business_origin_id', $this->businessOriginId)))
            ->with(['property', 'arrendatario'])
            ->get();


        $generadas = 0;

        foreach ($contratos as $contrato) {
            // Sin acta de entrega cerrada no hay desde cuándo cobrar —
            // no se factura hasta que el inquilino reciba el inmueble.
            if (! $contrato->fecha_entrega_efectiva) {
                Log::info("Contrato {$contrato->numero_contrato} sin entrega registrada — no se factura todavía.");
                continue;
            }

            $existe = RentBill::where('rental_contract_id', $contrato->id)
                ->where('mes', $mes)->where('anio', $anio)->exists();
            if ($existe) continue;

            $canonBase = (float)$contrato->canon_mensual;
            $admin     = (float)($contrato->cuota_administracion ?? 0);

            // El ciclo de facturación se ancla SIEMPRE a la última factura real
            // del contrato (nunca al calendario de forma independiente) — así
            // no importa en qué orden o lote se generen los meses, el período
            // nuevo siempre arranca justo donde terminó el anterior, sin huecos
            // ni solapamientos.
            $ultimaFactura = RentBill::where('rental_contract_id', $contrato->id)
                ->whereNotIn('estado', ['anulada'])
                ->orderByDesc('periodo_fin')
                ->first();

            if ($ultimaFactura) {
                $fechaLimiteCarbon = $ultimaFactura->periodo_fin->copy()->addDay();
            } else {
                // Primera factura del contrato: día de pago propio si está
                // pactado, si no el día de corte global de la empresa. Si la
                // entrega real ocurrió después del día de pago normal de este
                // período, el cobro arranca desde la fecha de entrega (no se
                // le cobra tiempo que todavía no tenía el inmueble).
                $diaPago     = $contrato->dia_pago ?: $diaCorteGlobal;
                $diaPago     = min($diaPago, $periodoBase->copy()->endOfMonth()->day); // por si el mes es más corto
                $fechaLimite = $periodoBase->copy()->startOfMonth()->addDays($diaPago - 1)->toDateString();

                if ($contrato->fecha_entrega_efectiva->toDateString() > $fechaLimite) {
                    $fechaLimite = $contrato->fecha_entrega_efectiva->toDateString();
                }
                $fechaLimiteCarbon = \Carbon\Carbon::parse($fechaLimite);
            }

            // Período de arriendo: mismo ciclo que Siinmob — el canon se paga
            // anticipado, así que el período va DESDE la fecha límite de pago
            // HACIA ADELANTE un mes (nunca el mes calendario, que no coincide
            // con el día de pago pactado en el contrato).
            $fechaLimite   = $fechaLimiteCarbon->toDateString();
            $periodoInicio = $fechaLimiteCarbon->copy()->toDateString();
            $periodoFin    = $fechaLimiteCarbon->copy()->addMonthNoOverflow()->subDay()->toDateString();

            // ── Seguro SURA: valores precalculados y guardados en el inmueble ──
            $tieneSeguroSura = (bool)($contrato->property?->tiene_seguro_sura);
            $valorSeguroSura = $tieneSeguroSura ? (float)($contrato->property?->valor_seguro_sura ?? 0) : 0;
            $ivaSeguroSura   = $tieneSeguroSura ? (float)($contrato->property?->iva_seguro_sura   ?? 0) : 0;
            $redondeoSeguro  = 0;

            // Canon cobrado al inquilino: guardado manualmente en el inmueble
            $totalExacto    = $canonBase + $admin + $valorSeguroSura + $ivaSeguroSura;
            $canonInquilino = (float)($contrato->property?->canon_cobrado_inquilino ?? 0);
            if ($tieneSeguroSura && $canonInquilino > $totalExacto) {
                $total          = $canonInquilino;
                $redondeoSeguro = round($canonInquilino - $totalExacto, 2);
            } else {
                $total          = $totalExacto;
                $redondeoSeguro = 0;
            }

            // Si el arrendatario es persona jurídica y agente retenedor,
            // retiene un % del canon al pagar — esa parte no se cobra en
            // efectivo, así que el saldo a cobrar ya sale neto de retención
            // (igual a como ya lo trataba el asiento contable de la
            // factura, que debitaba cartera solo por total - retención).
            $retencion = \App\Services\ContabilidadService::calcularRetencionArrendamiento($contrato, $canonBase);
            $totalACobrar = round($total - $retencion, 2);

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
                'retencion_practicada' => $retencion,
                'total_factura'        => $totalACobrar,
                'saldo_pendiente'      => $totalACobrar,
                'fecha_limite_pago'    => $fechaLimite,
                'dias_gracia'          => $diasGracia,
                'tasa_mora_diaria'     => $tasaDiaria,
                'estado'               => 'pendiente',
                'tipo_documento'       => 'documento_equivalente',
            ]);

            // Generar token de pago y enviar link por WhatsApp
            if ($this->enviarWhatsapp && $contrato->arrendatario?->celular) {
                try {
                    $token    = $bill->generatePaymentToken();
                    $urlPago  = route('payment.show', ['token' => $token]);
                    $inmueble = ($contrato->property?->codigo ?? '') . ' — ' . ($contrato->property?->direccion ?? '');
                    $totalFmt = '$' . number_format($totalACobrar, 0, ',', '.');
                    $fechaFmt = \Carbon\Carbon::parse($fechaLimite)->format('d/m/Y');
                    $nombre   = $contrato->arrendatario->nombre_completo;
                    $empresa  = $company?->razon_social ?? 'Serviarrendar S.A.S';

                    $seguroLinea = $tieneSeguroSura && $valorSeguroSura > 0
                        ? "🛡️ Seguro SURA: \$" . number_format($valorSeguroSura + $ivaSeguroSura + $redondeoSeguro, 0, ',', '.') . " COP\n"
                        : '';

                    $msg = "🏠 *Factura de Arrendamiento*\n\n"
                        . "Estimado(a) {$nombre},\n\n"
                        . "📋 *{$bill->numero}*\n"
                        . "📅 Período: arriendo del mes " . \Carbon\Carbon::parse($periodoInicio)->format('Y-m-d') . " al " . \Carbon\Carbon::parse($periodoFin)->format('Y-m-d') . "\n"
                        . "🏠 Inmueble: {$inmueble}\n\n"
                        . "💰 Canon: \$" . number_format($canonBase, 0, ',', '.') . " COP\n"
                        . ($admin > 0
                            ? "🏢 Administración: \$" . number_format($admin, 0, ',', '.') . " COP\n"
                            : '')
                        . $seguroLinea
                        . ($retencion > 0
                            ? "🧾 Retención en la fuente: -\$" . number_format($retencion, 0, ',', '.') . " COP\n"
                            : '')
                        . "💵 *Total a pagar: {$totalFmt} COP*\n\n"
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
            'mes' => $periodoBase->translatedFormat('F Y'),
        ]);
    }
}
