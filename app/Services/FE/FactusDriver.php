<?php

namespace App\Services\FE;

use App\Contracts\FE\FEResponse;
use App\Contracts\FE\FacturacionElectronicaContract;
use App\Models\Company;
use App\Models\ElectronicInvoice;
use App\Models\RentBill;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FactusDriver implements FacturacionElectronicaContract
{
    private string $baseUrl;
    private array  $cfg;

    public function __construct()
    {
        $this->cfg     = config('fe.factus');
        $this->baseUrl = rtrim($this->cfg['base_url'], '/');
    }

    // ── Auth OAuth2 ──────────────────────────────────────────────────────────

    private function token(): string
    {
        return Cache::remember('factus_access_token', 3300, function () {
            $response = Http::timeout($this->cfg['timeout'])
                ->asForm()
                ->post("{$this->baseUrl}/oauth/token", [
                    'grant_type'    => 'password',
                    'client_id'     => $this->cfg['client_id'],
                    'client_secret' => $this->cfg['client_secret'],
                    'username'      => $this->cfg['username'],
                    'password'      => $this->cfg['password'],
                ]);

            if (!$response->successful()) {
                throw new \RuntimeException('Factus OAuth error: ' . $response->body());
            }

            return $response->json('access_token');
        });
    }

    private function http(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::timeout($this->cfg['timeout'])
            ->withToken($this->token())
            ->acceptJson()
            ->asJson();
    }

    // ── Payload DIAN UBL 2.1 para Factus ────────────────────────────────────

    private function buildPayload(RentBill $bill, Company $company): array
    {
        $arrendatario = $bill->arrendatario;
        $contrato     = $bill->rentalContract;
        $consecutivo  = $company->consecutivo_actual;

        $items = $this->buildItems($bill, $company);

        return [
            'numbering_range_id' => null, // Factus lo resuelve por prefijo+consecutivo
            'reference_code'     => $bill->numero,
            'observation'        => "Arrendamiento período {$bill->periodo_inicio?->format('d/m/Y')} al {$bill->periodo_fin?->format('d/m/Y')}",
            'payment_method_code'=> '10', // Efectivo/transferencia
            'payment_due_date'   => $bill->fecha_limite_pago?->format('Y-m-d') ?? now()->addDays(5)->format('Y-m-d'),
            'note'               => $company->fe_nota_pie ?? '',

            // Receptor
            'names'              => $arrendatario?->nombre_completo ?? '',
            'identification'     => preg_replace('/[^0-9]/', '', $arrendatario?->numero_documento ?? ''),
            'dv'                 => $arrendatario?->digito_verificacion ?? null,
            'company'            => $arrendatario?->tipo_persona === 'juridica' ? ($arrendatario?->razon_social ?? '') : null,
            'trade_name'         => null,
            'identification_document_id' => $this->mapTipoDocumento($arrendatario?->tipo_documento ?? 'CC'),
            'municipality_id'    => $arrendatario?->ciudad_codigo_dane ?? '11001', // Bogotá por defecto
            'address'            => $arrendatario?->direccion ?? '',
            'phone'              => $arrendatario?->telefono ?? '',
            'email'              => $arrendatario?->email ?? '',

            // Ítems
            'items' => $items,

            // Tributos globales (retención si aplica)
            'withheld_taxes' => $this->buildRetenciones($bill, $company),
        ];
    }

    private function buildItems(RentBill $bill, Company $company): array
    {
        $items = [];

        // Ítem 1: Canon de arrendamiento
        if ($bill->canon_base > 0) {
            $items[] = [
                'code_reference'       => 'CANON',
                'name'                 => 'Canon de arrendamiento',
                'quantity'             => 1,
                'discount_rate'        => 0,
                'price'                => (float) $bill->canon_base,
                'tax_rate'             => '0.00',  // Sin IVA sobre canon (arrendamiento residencial)
                'unit_measure_id'      => 70,       // Servicio
                'standard_code_id'     => 1,        // Estándar DIAN
                'is_excluded_vat'      => 1,
                'tribute_id'           => 22,       // ZY = No aplica IVA
                'withholding_taxes'    => [],
            ];
        }

        // Ítem 2: Cuota administración (si aplica)
        if ((float)$bill->cuota_administracion > 0) {
            $items[] = [
                'code_reference'   => 'ADM',
                'name'             => 'Cuota de administración',
                'quantity'         => 1,
                'discount_rate'    => 0,
                'price'            => (float) $bill->cuota_administracion,
                'tax_rate'         => '0.00',
                'unit_measure_id'  => 70,
                'standard_code_id' => 1,
                'is_excluded_vat'  => 1,
                'tribute_id'       => 22,
                'withholding_taxes'=> [],
            ];
        }

        // Ítem 3: Descuentos (valor negativo como descuento)
        if ((float)$bill->descuentos > 0) {
            $items[0]['discount_rate'] = round(
                ((float)$bill->descuentos / (float)$bill->canon_base) * 100, 2
            );
        }

        // Ítem 4: Otros cobros
        if ((float)$bill->otros_cobros > 0) {
            $items[] = [
                'code_reference'   => 'OTROS',
                'name'             => $bill->descripcion_otros_cobros ?? 'Otros cobros',
                'quantity'         => 1,
                'discount_rate'    => 0,
                'price'            => (float) $bill->otros_cobros,
                'tax_rate'         => '0.00',
                'unit_measure_id'  => 70,
                'standard_code_id' => 1,
                'is_excluded_vat'  => 1,
                'tribute_id'       => 22,
                'withholding_taxes'=> [],
            ];
        }

        return $items;
    }

    private function buildRetenciones(RentBill $bill, Company $company): array
    {
        $retenciones = [];
        $arrendatario = $bill->arrendatario;

        // Retención en la fuente (si arrendatario jurídico)
        if ($arrendatario?->tipo_persona === 'juridica') {
            $retePct = (float)($company->tarifa_retefuente_arrendamiento ?? 3.5);
            $base    = (float)$bill->canon_base;
            $retenciones[] = [
                'code'     => '01', // Retención en la fuente
                'name'     => "Retención en la fuente {$retePct}%",
                'rate'     => $retePct,
                'base'     => $base,
                'value'    => round($base * ($retePct / 100), 2),
            ];
        }

        // Reteica (si aplica)
        if ($company->tarifa_reteica && $company->tarifa_reteica > 0) {
            $base = (float)$bill->canon_base;
            $retenciones[] = [
                'code'  => '05', // Reteica
                'name'  => "ReteICA {$company->tarifa_reteica}‰",
                'rate'  => (float)$company->tarifa_reteica,
                'base'  => $base,
                'value' => round($base * ((float)$company->tarifa_reteica / 1000), 2),
            ];
        }

        return $retenciones;
    }

    private function mapTipoDocumento(string $tipo): int
    {
        return match(strtoupper($tipo)) {
            'NIT'        => 6,
            'CC'         => 13,
            'CE'         => 22,
            'PASAPORTE'  => 91,
            'TI'         => 12,
            default      => 13,
        };
    }

    // ── Métodos públicos ─────────────────────────────────────────────────────

    public function emitir(RentBill $bill, array $opciones = []): FEResponse
    {
        try {
            $company = Company::first();
            $payload = $this->buildPayload($bill, $company);

            $response = $this->http()
                ->post("{$this->baseUrl}/v1/bills/validate", $payload);

            $data = $response->json();

            if (!$response->successful()) {
                Log::error('Factus emitir error', ['status' => $response->status(), 'body' => $data]);
                return FEResponse::error('Error HTTP ' . $response->status() . ': ' . ($data['message'] ?? $response->body()), $data ?? []);
            }

            // Factus retorna data.bill con el resultado
            $bill_data = $data['data']['bill'] ?? $data['data'] ?? $data;

            $estado     = match($bill_data['status'] ?? '') {
                'Aceptado', 'accepted'           => 'aceptada',
                'Con notificación', 'notified'   => 'aceptada_con_notificacion',
                'Rechazado', 'rejected'          => 'rechazada',
                default                          => 'enviada',
            };

            if ($estado === 'rechazada') {
                return FEResponse::rechazada(
                    $bill_data['reject_message'] ?? 'Rechazada por la DIAN',
                    $bill_data['dian_response_code'] ?? '',
                    $data,
                );
            }

            return FEResponse::exito(
                estado:      $estado,
                cufe:        $bill_data['cufe'] ?? null,
                qrData:      $bill_data['qr'] ?? null,
                xmlUrl:      $bill_data['xml'] ?? null,
                pdfUrl:      $bill_data['pdf'] ?? null,
                attachedUrl: $bill_data['attached_document'] ?? null,
                mensajeDian: $bill_data['dian_response'] ?? null,
                codigoDian:  $bill_data['dian_response_code'] ?? null,
                raw:         $data,
            );

        } catch (\Throwable $e) {
            Log::error('Factus emitir exception', ['msg' => $e->getMessage()]);
            return FEResponse::error($e->getMessage());
        }
    }

    public function anular(ElectronicInvoice $fe, string $razon): FEResponse
    {
        try {
            $response = $this->http()
                ->post("{$this->baseUrl}/v1/credit-notes", [
                    'bill_id'          => $fe->cufe,
                    'correction_reason'=> '2', // Anulación de factura
                    'note'             => $razon,
                ]);

            $data = $response->json();

            if (!$response->successful()) {
                return FEResponse::error('Error anulación: ' . ($data['message'] ?? $response->body()), $data ?? []);
            }

            $cn = $data['data']['credit_note'] ?? $data['data'] ?? [];

            return FEResponse::exito(
                estado:          'anulada',
                cufe:            $fe->cufe,
                mensajeDian:     'Nota crédito emitida',
                raw:             $data,
            );

        } catch (\Throwable $e) {
            Log::error('Factus anular exception', ['msg' => $e->getMessage()]);
            return FEResponse::error($e->getMessage());
        }
    }

    public function consultarEstado(ElectronicInvoice $fe): FEResponse
    {
        try {
            $response = $this->http()
                ->get("{$this->baseUrl}/v1/bills/show/{$fe->cufe}");

            $data      = $response->json();
            $bill_data = $data['data']['bill'] ?? $data['data'] ?? [];

            $estado = match($bill_data['status'] ?? '') {
                'Aceptado', 'accepted'           => 'aceptada',
                'Con notificación', 'notified'   => 'aceptada_con_notificacion',
                'Rechazado', 'rejected'          => 'rechazada',
                default                          => $fe->estado,
            };

            return FEResponse::exito(
                estado:      $estado,
                cufe:        $fe->cufe,
                mensajeDian: $bill_data['dian_response'] ?? null,
                raw:         $data,
            );

        } catch (\Throwable $e) {
            return FEResponse::error($e->getMessage());
        }
    }

    public function descargarPdf(ElectronicInvoice $fe): ?string
    {
        try {
            if (!$fe->pdf_url) return null;
            $response = $this->http()->get($fe->pdf_url);
            return $response->successful() ? $response->body() : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
