<?php

namespace App\Services\FE;

use App\Contracts\FE\FEResponse;
use App\Contracts\FE\FacturacionElectronicaContract;
use App\Models\Company;
use App\Models\ElectronicInvoice;
use App\Models\RentBill;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FacturatechDriver implements FacturacionElectronicaContract
{
    private string $baseUrl;
    private array  $cfg;

    public function __construct()
    {
        $this->cfg     = config('fe.facturatech');
        $this->baseUrl = rtrim($this->cfg['base_url'], '/');
    }

    private function http(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::timeout($this->cfg['timeout'])
            ->withHeaders(['Authorization' => 'Bearer ' . $this->cfg['api_key']])
            ->acceptJson()
            ->asJson();
    }

    private function buildPayload(RentBill $bill, Company $company): array
    {
        $arrendatario = $bill->arrendatario;
        $aplicaRete   = $arrendatario?->tipo_persona === 'juridica';
        $retePct      = (float)($company->tarifa_retefuente_arrendamiento ?? 3.5);

        $lineas = [];

        if ((float)$bill->canon_base > 0) {
            $linea = [
                'descripcion'       => 'Canon de arrendamiento',
                'codigo'            => 'CANON',
                'cantidad'          => 1,
                'valorUnitario'     => (float)$bill->canon_base,
                'descuento'         => (float)($bill->descuentos ?? 0),
                'impuestos'         => [],
                'retenciones'       => [],
            ];

            if ($aplicaRete) {
                $linea['retenciones'][] = [
                    'tipo'  => 'RTEFTE',
                    'base'  => (float)$bill->canon_base,
                    'tarifa'=> $retePct,
                    'valor' => round((float)$bill->canon_base * ($retePct / 100), 2),
                ];
            }

            $lineas[] = $linea;
        }

        if ((float)$bill->cuota_administracion > 0) {
            $lineas[] = [
                'descripcion'   => 'Cuota de administración',
                'codigo'        => 'ADM',
                'cantidad'      => 1,
                'valorUnitario' => (float)$bill->cuota_administracion,
                'descuento'     => 0,
                'impuestos'     => [],
                'retenciones'   => [],
            ];
        }

        if ((float)$bill->otros_cobros > 0) {
            $lineas[] = [
                'descripcion'   => $bill->descripcion_otros_cobros ?? 'Otros cobros',
                'codigo'        => 'OTROS',
                'cantidad'      => 1,
                'valorUnitario' => (float)$bill->otros_cobros,
                'descuento'     => 0,
                'impuestos'     => [],
                'retenciones'   => [],
            ];
        }

        return [
            'nit'              => preg_replace('/[^0-9]/', '', $this->cfg['nit']),
            'tipoDocumento'    => 'FV',
            'prefijo'          => $company->prefijo_factura ?? 'FE',
            'consecutivo'      => $company->consecutivo_actual,
            'fecha'            => now()->format('Y-m-d'),
            'fechaVencimiento' => $bill->fecha_limite_pago?->format('Y-m-d') ?? now()->addDays(5)->format('Y-m-d'),
            'medioPago'        => 'transferencia',
            'notas'            => $company->fe_nota_pie ?? '',

            'receptor' => [
                'tipoDocumento'    => $arrendatario?->tipo_documento ?? 'CC',
                'numeroDocumento'  => preg_replace('/[^0-9]/', '', $arrendatario?->numero_documento ?? ''),
                'dv'               => $arrendatario?->digito_verificacion ?? '',
                'razonSocial'      => $arrendatario?->nombre_completo ?? '',
                'tipoPersona'      => $arrendatario?->tipo_persona === 'juridica' ? 'J' : 'N',
                'direccion'        => $arrendatario?->direccion ?? '',
                'ciudad'           => $arrendatario?->ciudad ?? 'Bogotá',
                'pais'             => 'CO',
                'telefono'         => $arrendatario?->telefono ?? '',
                'email'            => $arrendatario?->email ?? '',
            ],

            'lineas' => $lineas,
        ];
    }

    public function emitir(RentBill $bill, array $opciones = []): FEResponse
    {
        try {
            $company = Company::first();
            $payload = $this->buildPayload($bill, $company);

            $response = $this->http()
                ->post("{$this->baseUrl}/api/facturas/emitir", $payload);

            $data = $response->json();

            if (!$response->successful()) {
                Log::error('Facturatech emitir error', ['status' => $response->status(), 'body' => $data]);
                return FEResponse::error('Error Facturatech ' . $response->status() . ': ' . ($data['mensaje'] ?? json_encode($data)));
            }

            $exitoso = ($data['exitoso'] ?? $data['success'] ?? false);

            if (!$exitoso) {
                return FEResponse::rechazada(
                    $data['mensajeDIAN'] ?? $data['mensaje'] ?? 'Rechazada',
                    $data['codigoDIAN'] ?? '',
                    $data,
                );
            }

            return FEResponse::exito(
                estado:      'aceptada',
                cufe:        $data['cufe'] ?? null,
                qrData:      $data['qr'] ?? null,
                xmlUrl:      $data['urlXML'] ?? $data['xml_url'] ?? null,
                pdfUrl:      $data['urlPDF'] ?? $data['pdf_url'] ?? null,
                mensajeDian: $data['mensajeDIAN'] ?? null,
                codigoDian:  $data['codigoDIAN'] ?? null,
                raw:         $data,
            );

        } catch (\Throwable $e) {
            Log::error('Facturatech emitir exception', ['msg' => $e->getMessage()]);
            return FEResponse::error($e->getMessage());
        }
    }

    public function anular(ElectronicInvoice $fe, string $razon): FEResponse
    {
        try {
            $response = $this->http()
                ->post("{$this->baseUrl}/api/notas-credito/emitir", [
                    'cufe'    => $fe->cufe,
                    'concepto'=> '2',
                    'razon'   => $razon,
                ]);

            $data = $response->json();

            if (!$response->successful() || !($data['exitoso'] ?? false)) {
                return FEResponse::error('Error anulación Facturatech: ' . ($data['mensaje'] ?? $response->body()), $data ?? []);
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
                ->get("{$this->baseUrl}/api/facturas/{$fe->cufe}/estado");

            $data = $response->json();

            return FEResponse::exito(
                estado:      ($data['aceptada'] ?? false) ? 'aceptada' : $fe->estado,
                cufe:        $fe->cufe,
                mensajeDian: $data['mensajeDIAN'] ?? null,
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
