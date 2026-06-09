<?php

namespace App\Services;

use App\Contracts\FE\FEResponse;
use App\Contracts\FE\FacturacionElectronicaContract;
use App\Models\Company;
use App\Models\ElectronicInvoice;
use App\Models\RentBill;
use App\Services\FE\DataicoDriver;
use App\Services\FE\FacturatechDriver;
use App\Services\FE\FactusDriver;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FacturacionElectronicaService
{
    // ── Resolución del driver activo ─────────────────────────────────────────

    public static function driver(?string $nombre = null): FacturacionElectronicaContract
    {
        $driver = $nombre ?? config('fe.driver', 'factus');

        return match($driver) {
            'dataico'      => new DataicoDriver(),
            'facturatech'  => new FacturatechDriver(),
            default        => new FactusDriver(),
        };
    }

    // ── Emisión principal ────────────────────────────────────────────────────

    public static function emitir(RentBill $bill): ?ElectronicInvoice
    {
        $company = Company::first();

        // Solo emitir si FE está activa y la factura lo requiere
        if (!$company?->factura_electronica_activa) return null;
        if ($bill->tipo_documento !== 'factura_electronica') return null;

        // Evitar doble emisión
        $existente = ElectronicInvoice::where('rent_bill_id', $bill->id)
            ->whereNotIn('estado', ['error', 'rechazada'])
            ->first();
        if ($existente) return $existente;

        $operador = $company->fe_operador ?? config('fe.driver', 'factus');
        $ambiente = $company->fe_ambiente ?? config('fe.ambiente', 'habilitacion');

        // Crear registro pendiente
        $fe = ElectronicInvoice::create([
            'rent_bill_id'  => $bill->id,
            'operador'      => $operador,
            'ambiente'      => $ambiente,
            'estado'        => 'pendiente',
            'consecutivo'   => $company->consecutivo_actual,
            'prefijo'       => $company->prefijo_factura,
            'numero_factura_dian' => ($company->prefijo_factura ?? '') . $company->consecutivo_actual,
            'emitido_por'   => Auth::id(),
            'emitido_en'    => now(),
        ]);

        try {
            $response = static::driver($operador)->emitir($bill);
            static::aplicarRespuesta($fe, $response, $company, $bill);

        } catch (\Throwable $e) {
            Log::error("FE emitir exception bill#{$bill->id}", ['msg' => $e->getMessage()]);
            $fe->update([
                'estado'         => 'error',
                'ultimo_error'   => $e->getMessage(),
                'intentos'       => $fe->intentos + 1,
                'proximo_reintento' => now()->addMinutes(5),
            ]);
        }

        return $fe->fresh();
    }

    // ── Reintento manual o automático ────────────────────────────────────────

    public static function reintentar(ElectronicInvoice $fe): ElectronicInvoice
    {
        $bill    = $fe->rentBill;
        $company = Company::first();

        try {
            $response = static::driver($fe->operador)->emitir($bill);
            static::aplicarRespuesta($fe, $response, $company, $bill);

        } catch (\Throwable $e) {
            $intento = $fe->intentos + 1;
            $delays  = config('fe.reintentos.delay_minutos', [5, 30, 120]);
            $delay   = $delays[min($intento, count($delays) - 1)];

            $fe->update([
                'estado'            => 'error',
                'ultimo_error'      => $e->getMessage(),
                'intentos'          => $intento,
                'proximo_reintento' => now()->addMinutes($delay),
            ]);
        }

        return $fe->fresh();
    }

    // ── Anulación ────────────────────────────────────────────────────────────

    public static function anular(ElectronicInvoice $fe, string $razon): ElectronicInvoice
    {
        try {
            $response = static::driver($fe->operador)->anular($fe, $razon);

            $fe->update([
                'estado'             => $response->exitoso ? 'anulada' : 'error',
                'razon_anulacion'    => $razon,
                'cufe_nota_credito'  => $response->cufeCreditNote,
                'mensaje_dian'       => $response->mensajeDian,
                'ultimo_error'       => $response->exitoso ? null : $response->error,
                'respuesta_operador' => $response->raw ?: null,
                'anulado_por'        => Auth::id(),
                'anulado_en'         => now(),
            ]);

        } catch (\Throwable $e) {
            $fe->update(['estado' => 'error', 'ultimo_error' => $e->getMessage()]);
        }

        return $fe->fresh();
    }

    // ── Consultar estado en operador ─────────────────────────────────────────

    public static function consultarEstado(ElectronicInvoice $fe): ElectronicInvoice
    {
        try {
            $response = static::driver($fe->operador)->consultarEstado($fe);

            if ($response->exitoso) {
                $fe->update([
                    'estado'       => $response->estado,
                    'mensaje_dian' => $response->mensajeDian ?? $fe->mensaje_dian,
                ]);
            }

        } catch (\Throwable $e) {
            Log::warning("FE consultarEstado fe#{$fe->id}: " . $e->getMessage());
        }

        return $fe->fresh();
    }

    // ── Helper privado ───────────────────────────────────────────────────────

    private static function aplicarRespuesta(
        ElectronicInvoice $fe,
        FEResponse        $response,
        Company           $company,
        RentBill          $bill,
    ): void {
        $fe->update([
            'estado'             => $response->estado,
            'cufe'               => $response->cufe,
            'qr_data'            => $response->qrData,
            'xml_url'            => $response->xmlUrl,
            'pdf_url'            => $response->pdfUrl,
            'attached_document_url' => $response->attachedUrl,
            'mensaje_dian'       => $response->mensajeDian,
            'codigo_dian'        => $response->codigoDian,
            'respuesta_operador' => $response->raw ?: null,
            'ultimo_error'       => $response->exitoso ? null : ($response->error ?? $response->mensajeDian),
            'intentos'           => $fe->intentos + 1,
            'proximo_reintento'  => $response->exitoso ? null : now()->addMinutes(30),
            'aceptada_en'        => in_array($response->estado, ['aceptada','aceptada_con_notificacion']) ? now() : null,
        ]);

        if ($response->exitoso && $response->cufe) {
            // Sincronizar CUFE en RentBill
            $bill->update([
                'cufe'        => $response->cufe,
                'numero_dian' => $fe->numero_factura_dian,
            ]);

            // Avanzar consecutivo en Company (dentro de transacción)
            DB::transaction(function () use ($company) {
                $company->refresh()->lockForUpdate();
                $company->increment('consecutivo_actual');
            });
        }

        if (!$response->exitoso) {
            $intento = $fe->intentos;
            $delays  = config('fe.reintentos.delay_minutos', [5, 30, 120]);
            $delay   = $delays[min($intento, count($delays) - 1)];
            $fe->update(['proximo_reintento' => now()->addMinutes($delay)]);
        }
    }
}
