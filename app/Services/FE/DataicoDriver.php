<?php

namespace App\Services\FE;

use App\Contracts\FE\FEResponse;
use App\Contracts\FE\FacturacionElectronicaContract;
use App\Models\Company;
use App\Models\ElectronicInvoice;
use App\Models\RentBill;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DataicoDriver implements FacturacionElectronicaContract
{
    private string $baseUrl;
    private array  $cfg;

    public function __construct()
    {
        $this->cfg     = config('fe.dataico');
        $this->baseUrl = rtrim($this->cfg['base_url'], '/');
    }

    private function http(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::timeout($this->cfg['timeout'])
            ->withHeaders([
                'api-key'    => $this->cfg['api_key'],
                'account-id' => $this->cfg['account_id'],
            ])
            ->acceptJson()
            ->asJson();
    }

    private function buildPayload(RentBill $bill, Company $company): array
    {
        $arrendatario = $bill->arrendatario;
        $retePct = (float)($company->tarifa_retefuente_arrendamiento ?? 3.5);
        $aplicaRete = $arrendatario?->tipo_persona === 'juridica';

        $items = [];

        if ((float)$bill->canon_base > 0) {
            $items[] = [
                'description'   => 'Canon de arrendamiento',
                'code'          => 'CANON',
                'type'          => 'IP',  // Ítem producto/servicio
                'quantity'      => 1,
                'unit'          => 'SRV',
                'unit_value'    => (float)$bill->canon_base,
                'discount'      => (float)($bill->descuentos ?? 0),
                'taxes'         => [],   // Sin IVA (arrendamiento exento)
                'retentions'    => $aplicaRete ? [[
                    'type'  => 'RENTA',
                    'rate'  => $retePct,
                    'value' => round((float)$bill->canon_base * ($retePct / 100), 2),
                ]] : [],
            ];
        }

        if ((float)$bill->cuota_administracion > 0) {
            $items[] = [
                'description' => 'Cuota de administración',
                'code'        => 'ADM',
                'type'        => 'IP',
                'quantity'    => 1,
                'unit'        => 'SRV',
                'unit_value'  => (float)$bill->cuota_administracion,
                'discount'    => 0,
                'taxes'       => [],
                'retentions'  => [],
            ];
        }

        if ((float)$bill->otros_cobros > 0) {
            $items[] = [
                'description' => $bill->descripcion_otros_cobros ?? 'Otros cobros',
                'code'        => 'OTROS',
                'type'        => 'IP',
                'quantity'    => 1,
                'unit'        => 'SRV',
                'unit_value'  => (float)$bill->otros_cobros,
                'discount'    => 0,
                'taxes'       => [],
                'retentions'  => [],
            ];
        }

        return [
            'account_id'       => $this->cfg['account_id'],
            'number'           => $company->consecutivo_actual,
            'type_document'    => 'FV',  // Factura de venta
            'date'             => now()->format('Y-m-d'),
            'date_due'         => $bill->fecha_limite_pago?->format('Y-m-d') ?? now()->addDays(5)->format('Y-m-d'),
            'currency'         => 'COP',
            'notes'            => $company->fe_nota_pie ?? '',
            'payment_means'    => 'transferencia',
            'payment_means_type' => 'credito',

            // Emisor (ya configurado en la cuenta Dataico)
            'sender' => [
                'name'           => $company->razon_social,
                'nit'            => preg_replace('/[^0-9]/', '', $company->nit),
                'dv'             => $company->digito_verificacion,
                'address'        => $company->direccion ?? '',
                'city'           => $company->ciudad ?? 'Bogotá',
                'department'     => $company->departamento ?? 'Cundinamarca',
                'country'        => 'CO',
                'phone'          => $company->telefono ?? '',
                'email'          => $company->email ?? '',
            ],

            // Receptor
            'receiver' => [
                'name'           => $arrendatario?->nombre_completo ?? '',
                'identification' => preg_replace('/[^0-9]/', '', $arrendatario?->numero_documento ?? ''),
                'dv'             => $arrendatario?->digito_verificacion ?? null,
                'type_document'  => $arrendatario?->tipo_documento ?? 'CC',
                'type_person'    => $arrendatario?->tipo_persona === 'juridica' ? 'juridica' : 'natural',
                'address'        => $arrendatario?->direccion ?? '',
                'city'           => $arrendatario?->ciudad ?? 'Bogotá',
                'country'        => 'CO',
                'phone'          => $arrendatario?->telefono ?? '',
                'email'          => $arrendatario?->email ?? '',
            ],

            'items' => $items,
        ];
    }

    public function emitir(RentBill $bill, array $opciones = []): FEResponse
    {
        try {
            $company = Company::first();
            $payload = $this->buildPayload($bill, $company);

            $response = $this->http()
                ->post("{$this->baseUrl}/api/v2/invoices", $payload);

            $data = $response->json();

            if (!$response->successful()) {
                Log::error('Dataico emitir error', ['status' => $response->status(), 'body' => $data]);
                return FEResponse::error('Error Dataico ' . $response->status() . ': ' . ($data['message'] ?? json_encode($data)));
            }

            $status = strtolower($data['status'] ?? '');

            if (in_array($status, ['rejected', 'rechazado'])) {
                return FEResponse::rechazada(
                    $data['validation_errors'] ? implode(' | ', $data['validation_errors']) : 'Rechazada por DIAN',
                    $data['dian_error_code'] ?? '',
                    $data,
                );
            }

            return FEResponse::exito(
                estado:      match($status) {
                    'accepted', 'aceptado'   => 'aceptada',
                    'pending', 'pendiente'   => 'enviada',
                    default                  => 'enviada',
                },
                cufe:        $data['cufe'] ?? $data['cude'] ?? null,
                qrData:      $data['qr_code'] ?? null,
                xmlUrl:      $data['xml_url'] ?? null,
                pdfUrl:      $data['pdf_url'] ?? null,
                mensajeDian: $data['dian_message'] ?? null,
                codigoDian:  $data['dian_code'] ?? null,
                raw:         $data,
            );

        } catch (\Throwable $e) {
            Log::error('Dataico emitir exception', ['msg' => $e->getMessage()]);
            return FEResponse::error($e->getMessage());
        }
    }

    public function anular(ElectronicInvoice $fe, string $razon): FEResponse
    {
        try {
            $response = $this->http()
                ->post("{$this->baseUrl}/api/v2/credit-notes", [
                    'invoice_cufe'      => $fe->cufe,
                    'correction_concept'=> '2',
                    'note'              => $razon,
                ]);

            $data = $response->json();

            if (!$response->successful()) {
                return FEResponse::error('Error anulación Dataico: ' . ($data['message'] ?? $response->body()), $data ?? []);
            }

            return FEResponse::exito(estado: 'anulada', cufe: $fe->cufe, raw: $data);

        } catch (\Throwable $e) {
            return FEResponse::error($e->getMessage());
        }
    }

    public function consultarEstado(ElectronicInvoice $fe): FEResponse
    {
        try {
            $response = $this->http()
                ->get("{$this->baseUrl}/api/v2/invoices/{$fe->cufe}");

            $data   = $response->json();
            $status = strtolower($data['status'] ?? '');

            return FEResponse::exito(
                estado: match($status) {
                    'accepted', 'aceptado'   => 'aceptada',
                    'rejected', 'rechazado'  => 'rechazada',
                    default                  => $fe->estado,
                },
                cufe:        $fe->cufe,
                mensajeDian: $data['dian_message'] ?? null,
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
            $response = Http::timeout(30)->get($fe->pdf_url);
            return $response->successful() ? $response->body() : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
