<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8">
<style>
    @page { margin:1.5cm 2cm 1.8cm; }
    body { font-family:'DejaVu Sans',sans-serif; font-size:9.5pt; color:#000; line-height:1.5; }
    .footer-fijo { position:fixed; bottom:-1.3cm; left:0; right:0; text-align:center; font-size:7pt; color:#555; border-top:0.5pt solid #ccc; padding-top:3pt; }
    .head { background:#0A192F; padding:14pt 16pt; }
    .head-logo { color:#fff; font-size:14pt; font-weight:bold; }
    .head-logo span { color:#E24B4A; }
    .head-sub { color:#94a3b8; font-size:7.5pt; margin-top:2pt; }
    .head-num-label { color:#64748b; font-size:7.5pt; text-align:right; }
    .head-num-val { color:#fff; font-size:16pt; font-weight:bold; text-align:right; }
    .body { padding:12pt 16pt; }
    .info-grid { width:100%; margin-bottom:10pt; border-collapse:collapse; }
    .info-grid td { vertical-align:top; width:50%; padding:0 4pt 0 0; }
    .info-block { border:0.5pt solid #e2e8f0; border-radius:3pt; padding:8pt; }
    .block-title { font-size:7.5pt; font-weight:bold; text-transform:uppercase; color:#94a3b8; letter-spacing:0.05em; margin-bottom:5pt; padding-bottom:4pt; border-bottom:0.5pt solid #e2e8f0; }
    .info-row { width:100%; font-size:8.5pt; margin-bottom:2pt; display:table; }
    .info-lbl { color:#64748b; display:table-cell; width:45%; }
    .info-val { font-weight:bold; color:#000; display:table-cell; width:55%; text-align:right; }
    .items-table { width:100%; border-collapse:collapse; margin-bottom:10pt; }
    .items-table th { background:#f1f5f9; padding:5pt 6pt; text-align:left; font-size:8pt; font-weight:bold; color:#475569; border:0.5pt solid #cbd5e1; text-transform:uppercase; }
    .items-table td { padding:7pt 6pt; border:0.5pt solid #e2e8f0; font-size:8.5pt; }
    .items-table .r { text-align:right; }
    .totales { width:100%; margin-bottom:10pt; border-collapse:collapse; border:0.5pt solid #e2e8f0; }
    .totales td { padding:5pt 10pt; font-size:8.5pt; border-bottom:0.5pt solid #f1f5f9; }
    .totales .lbl { color:#64748b; }
    .totales .val { text-align:right; font-weight:bold; }
    .totales .desc { color:#dc2626; }
    .total-final { background:#0A192F; }
    .total-final td { border:none; padding:10pt; }
    .total-final .lbl { color:#94a3b8; font-size:10pt; font-weight:bold; }
    .total-final .val { color:#fff; font-size:14pt; font-weight:bold; text-align:right; }
    .giro-box { background:#f0fdf4; border:0.5pt solid #bbf7d0; border-radius:3pt; padding:8pt 10pt; margin-bottom:10pt; font-size:8.5pt; }
    .pie-legal { background:#f8fafc; border:0.5pt solid #e2e8f0; border-radius:3pt; padding:8pt 10pt; font-size:7pt; color:#64748b; line-height:1.5; }
</style>
</head>
<body>

<div class="footer-fijo">
    YarOM ERP — Liquidación generada el {{ now()->format('d/m/Y H:i') }} — Documento confidencial
</div>

{{-- Encabezado --}}
<div class="head">
    <table style="width:100%">
        <tr>
            <td>
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" style="height:36pt;margin-bottom:4pt;"><br>
                @endif
                <div class="head-logo">YAROM <span>INMO</span>BILIARIA</div>
                <div class="head-sub">{{ $company?->razon_social ?? 'Serviarrendar S.A.S' }} · NIT: {{ $company?->nit ?? '' }}</div>
                <div class="head-sub">{{ $company?->direccion ?? '' }} · {{ $company?->telefono ?? '' }}</div>
            </td>
            <td style="text-align:right;">
                <div class="head-num-label">LIQUIDACIÓN PROPIETARIO</div>
                <div class="head-num-val">{{ $liquidation->numero }}</div>
                <div class="head-num-label">{{ $liquidation->periodoLabel }}</div>
            </td>
        </tr>
    </table>
</div>

<div class="body">

    {{-- Partes --}}
    <table class="info-grid">
        <tr>
            <td>
                <div class="info-block">
                    <div class="block-title">Propietario</div>
                    <div class="info-row">
                        <span class="info-lbl">Nombre:</span>
                        <span class="info-val">{{ $liquidation->propietario?->nombre_completo }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-lbl">Documento:</span>
                        <span class="info-val">{{ $liquidation->propietario?->tipo_documento }} {{ $liquidation->propietario?->numero_documento }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-lbl">Celular:</span>
                        <span class="info-val">{{ $liquidation->propietario?->celular }}</span>
                    </div>
                </div>
            </td>
            <td>
                <div class="info-block">
                    <div class="block-title">Inmueble</div>
                    <div class="info-row">
                        <span class="info-lbl">Código:</span>
                        <span class="info-val">{{ $liquidation->property?->codigo }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-lbl">Dirección:</span>
                        <span class="info-val">{{ $liquidation->property?->direccion }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-lbl">Arrendatario:</span>
                        <span class="info-val">{{ $liquidation->rentalContract?->arrendatario?->nombre_completo }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-lbl">Contrato:</span>
                        <span class="info-val">{{ $liquidation->rentalContract?->numero_contrato }}</span>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    {{-- Detalle liquidación --}}
    <table class="items-table">
        <thead>
            <tr>
                <th style="width:60%">Concepto</th>
                <th class="r" style="width:20%">Valor</th>
                <th class="r" style="width:20%">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Canon de arrendamiento cobrado — {{ $liquidation->periodoLabel }}</td>
                <td class="r">${{ number_format($liquidation->canon_cobrado, 0, ',', '.') }}</td>
                <td class="r">${{ number_format($liquidation->canon_cobrado, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    {{-- Descuentos --}}
    <table class="totales">
        <tr>
            <td class="lbl">(-) Comisión de administración ({{ $liquidation->comision_porcentaje }}%)</td>
            <td class="val desc">-${{ number_format($liquidation->comision_valor, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="lbl">(-) IVA sobre comisión (19%)</td>
            <td class="val desc">-${{ number_format($liquidation->iva_comision, 0, ',', '.') }}</td>
        </tr>
        @if($liquidation->retefuente_valor > 0)
        <tr>
            <td class="lbl">(-) Retención en la fuente arrendamientos</td>
            <td class="val desc">-${{ number_format($liquidation->retefuente_valor, 0, ',', '.') }}</td>
        </tr>
        @endif
        @if($liquidation->otros_descuentos > 0)
        <tr>
            <td class="lbl">(-) Otros descuentos: {{ $liquidation->descripcion_descuentos }}</td>
            <td class="val desc">-${{ number_format($liquidation->otros_descuentos, 0, ',', '.') }}</td>
        </tr>
        @endif
        <tr class="total-final">
            <td class="lbl">TOTAL A GIRAR AL PROPIETARIO</td>
            <td class="val">${{ number_format($liquidation->total_giro, 0, ',', '.') }} COP</td>
        </tr>
    </table>

    {{-- Información del giro --}}
    @if($liquidation->estado === 'pagada' && $liquidation->fecha_giro)
    <div class="giro-box">
        <strong style="color:#166534;">✓ Giro realizado</strong><br>
        Fecha: <strong>{{ $liquidation->fecha_giro->format('d/m/Y') }}</strong> &nbsp;|&nbsp;
        Forma: <strong>{{ ucfirst($liquidation->forma_giro) }}</strong>
        @if($liquidation->referencia_giro)
        &nbsp;|&nbsp; Ref: <strong>{{ $liquidation->referencia_giro }}</strong>
        @endif
    </div>
    @endif

    {{-- Pie legal --}}
    <div class="pie-legal">
        Este documento es un comprobante de liquidación de arrendamiento generado por el sistema YarOM ERP.
        Los valores reflejan los cánones cobrados, las comisiones de administración y las deducciones legales correspondientes.
        {{ $company?->razon_social }} — Matrícula Arrendador N° {{ $company?->matricula_arrendador ?? '002' }}.
    </div>

</div>
</body>
</html>
