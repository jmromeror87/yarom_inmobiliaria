<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8">
<style>
    @page { margin:1.5cm 2cm 1.8cm; }
    body { font-family:'DejaVu Sans',sans-serif; font-size:9.5pt; color:#000; line-height:1.5; }
    .footer-fijo { position:fixed; bottom:-1.3cm; left:0; right:0; text-align:center; font-size:7pt; color:#555; border-top:0.5pt solid #ccc; padding-top:3pt; }

    /* Encabezado oscuro */
    .head { background:#0A192F; padding:14pt 16pt; margin-bottom:0; }
    .head-grid { width:100%; }
    .head-logo { color:#fff; font-size:14pt; font-weight:bold; }
    .head-logo span { color:#E24B4A; }
    .head-sub { color:#94a3b8; font-size:7.5pt; margin-top:2pt; }
    .head-num-label { color:#64748b; font-size:7.5pt; text-align:right; }
    .head-num-val { color:#fff; font-size:16pt; font-weight:bold; text-align:right; }
    .head-num-tipo { color:#94a3b8; font-size:7.5pt; text-align:right; }

    /* Barra DIAN */
    .dian-bar { background:#0d2340; padding:5pt 16pt; border-bottom:0.5pt solid #1e3a5f; }
    .dian-bar table { margin:0; }
    .dian-bar td { border:none; padding:0 4pt; font-size:7.5pt; }
    .dian-label { color:#64748b; }
    .dian-res { color:#378ADD; font-weight:bold; }

    /* Body */
    .body { padding:12pt 16pt; }

    /* Grilla cliente */
    .info-grid { width:100%; margin-bottom:10pt; border-collapse:collapse; }
    .info-grid td { vertical-align:top; width:50%; padding:0 4pt 0 0; }
    .info-block { border:0.5pt solid #e2e8f0; border-radius:3pt; padding:8pt; }
    .block-title { font-size:7.5pt; font-weight:bold; text-transform:uppercase; color:#94a3b8; letter-spacing:0.05em; margin-bottom:5pt; padding-bottom:4pt; border-bottom:0.5pt solid #e2e8f0; }
    .info-row { display:flex; width:100%; font-size:8.5pt; margin-bottom:2pt; }
    .info-lbl { color:#64748b; width:40%; }
    .info-val { font-weight:bold; color:#000; width:60%; text-align:right; }

    /* Tabla ítems */
    .items-table { width:100%; border-collapse:collapse; margin-bottom:10pt; }
    .items-table th { background:#f1f5f9; padding:5pt 6pt; text-align:left; font-size:8pt; font-weight:bold; color:#475569; border:0.5pt solid #cbd5e1; text-transform:uppercase; letter-spacing:0.04em; }
    .items-table th.right,.items-table td.right { text-align:right; }
    .items-table td { padding:7pt 6pt; border:0.5pt solid #e2e8f0; font-size:8.5pt; vertical-align:top; }
    .items-table .desc-main { font-weight:bold; }
    .items-table .desc-sub { color:#64748b; font-size:7.5pt; margin-top:2pt; }
    .unspsc { color:#94a3b8; font-size:7pt; }

    /* Totales */
    .totales { width:100%; margin-bottom:10pt; border-collapse:collapse; border:0.5pt solid #e2e8f0; border-radius:3pt; }
    .totales td { padding:5pt 10pt; font-size:8.5pt; border-bottom:0.5pt solid #f1f5f9; }
    .totales .lbl { color:#64748b; }
    .totales .val { text-align:right; font-weight:bold; }
    .totales .mora { color:#dc2626; }
    .totales .rte { color:#d97706; }
    .totales .desc { color:#15803d; }
    .total-final { background:#0A192F; }
    .total-final td { border:none; padding:10pt; }
    .total-final .lbl { color:#94a3b8; font-size:10pt; font-weight:bold; }
    .total-final .val { color:#fff; font-size:14pt; font-weight:bold; text-align:right; }

    /* Pagos */
    .pagos-table { width:100%; border-collapse:collapse; margin-bottom:10pt; }
    .pagos-table th { background:#f0fdf4; padding:4pt 6pt; font-size:7.5pt; font-weight:bold; color:#15803d; border:0.5pt solid #bbf7d0; }
    .pagos-table td { padding:5pt 6pt; font-size:8pt; border:0.5pt solid #e2e8f0; }

    /* Datos pago */
    .datos-pago { background:#eff6ff; border:0.5pt solid #bfdbfe; border-radius:3pt; padding:8pt 10pt; margin-bottom:10pt; font-size:8.5pt; }
    .datos-pago strong { color:#1e40af; }

    /* CUFE */
    .cufe-box { background:#f8fafc; border:0.5pt solid #e2e8f0; border-radius:3pt; padding:6pt 10pt; margin-bottom:10pt; }
    .cufe-label { font-size:7pt; font-weight:bold; text-transform:uppercase; color:#94a3b8; margin-bottom:3pt; }
    .cufe-val { font-size:7pt; color:#64748b; word-break:break-all; }

    /* Pie */
    .pie-legal { background:#f8fafc; border:0.5pt solid #e2e8f0; border-radius:3pt; padding:8pt 10pt; font-size:7pt; color:#64748b; line-height:1.5; }
    .pie-grid { width:100%; }
    .pie-txt { width:75%; vertical-align:top; }
    .pie-plat { width:25%; text-align:right; vertical-align:top; color:#94a3b8; }
</style>
</head>
<body>

<div class="footer-fijo">
    {{ $company?->razon_social }} · NIT {{ $company?->nit_completo }} | {{ $company?->direccion }}, {{ $company?->municipio?->nombre }} N/S | {{ $company?->email }} · {{ $company?->celular }}
</div>

@php
    $arr      = $bill->arrendatario;
    $mesAnio  = \Carbon\Carbon::create($bill->anio, $bill->mes, 1)->translatedFormat('F Y');
    $rtefonte = round($bill->canon_base * 0.035, 2);
    $neto     = $bill->total_factura + $bill->mora_acumulada - $rtefonte;
@endphp

{{-- ENCABEZADO OSCURO --}}
<div class="head">
    <table class="head-grid" cellpadding="0" cellspacing="0">
        <tr>
            <td style="width:55%;vertical-align:top;">
                @if($logoBase64)
                <img src="{{ $logoBase64 }}" style="max-height:32pt;max-width:120pt;display:block;margin-bottom:6pt;">
                @else
                <div class="head-logo">YAROM<span>INMOBILIARIA</span></div>
                @endif
                <div class="head-sub">{{ $company?->razon_social ?? 'Inmobiliaria Serviarrendar S.A.S' }}</div>
                <div class="head-sub">NIT {{ $company?->nit_completo ?? '807.005.762-8' }} · {{ $company?->direccion }}, {{ $company?->municipio?->nombre ?? 'Ocaña' }} N/S</div>
                <div class="head-sub">{{ $company?->email }} · {{ $company?->celular }}</div>
            </td>
            <td style="width:45%;vertical-align:top;">
                <div class="head-num-label">{{ $bill->tipo_documento === 'factura_electronica' ? 'Factura de venta electrónica' : 'Documento equivalente de arrendamiento' }}</div>
                <div class="head-num-val">{{ $bill->numero_dian ?? $bill->numero }}</div>
                <div class="head-num-tipo">{{ ucfirst($mesAnio) }}</div>
                <div style="text-align:right;margin-top:4pt;">
                    <span style="background:#1e3a5f;color:#94a3b8;padding:2pt 8pt;border-radius:20pt;font-size:7.5pt;font-weight:bold;">
                        {{ strtoupper($bill->estado) }}
                    </span>
                </div>
            </td>
        </tr>
    </table>
</div>

{{-- BARRA DIAN --}}
<div class="dian-bar">
    <table cellpadding="0" cellspacing="0" style="width:100%;">
        <tr>
            <td class="dian-label">Autorización DIAN:</td>
            <td class="dian-res">Res. {{ $company?->resolucion_facturacion ?? '18760000001' }} · Rango {{ $company?->prefijo_factura ?? 'FEFE' }}{{ str_pad($company?->consecutivo_desde ?? 1001, 4, '0', STR_PAD_LEFT) }}–{{ $company?->prefijo_factura ?? 'FEFE' }}{{ str_pad($company?->consecutivo_hasta ?? 2000, 4, '0', STR_PAD_LEFT) }}</td>
            <td class="dian-label" style="text-align:right;">Emisión: {{ $bill->created_at?->format('d/m/Y H:i') }}</td>
        </tr>
    </table>
</div>

<div class="body">

{{-- GRILLA ARRENDATARIO / CONDICIONES --}}
<table class="info-grid" cellpadding="4" cellspacing="0">
    <tr>
        <td style="padding-right:6pt;">
            <div class="info-block">
                <div class="block-title">Adquirente / Arrendatario</div>
                <table style="width:100%;border:none;margin:0;" cellpadding="1">
                    <tr><td class="info-lbl" style="border:none;">Nombre</td><td class="info-val" style="border:none;">{{ mb_strtoupper($arr?->nombre_completo ?? '', 'UTF-8') }}</td></tr>
                    <tr><td class="info-lbl" style="border:none;">{{ $arr?->tipo_documento ?? 'CC' }}</td><td class="info-val" style="border:none;">{{ number_format((float)($arr?->numero_documento ?? 0), 0, ',', '.') }}</td></tr>
                    @if($arr?->celular)<tr><td class="info-lbl" style="border:none;">Celular</td><td class="info-val" style="border:none;">{{ $arr->celular }}</td></tr>@endif
                    @if($arr?->email)<tr><td class="info-lbl" style="border:none;">Correo</td><td class="info-val" style="border:none;">{{ $arr->email }}</td></tr>@endif
                </table>
            </div>
        </td>
        <td>
            <div class="info-block">
                <div class="block-title">Condiciones de pago</div>
                <table style="width:100%;border:none;margin:0;" cellpadding="1">
                    <tr><td class="info-lbl" style="border:none;">Contrato</td><td class="info-val" style="border:none;">{{ $bill->rentalContract?->numero_contrato }}</td></tr>
                    <tr><td class="info-lbl" style="border:none;">Período</td><td class="info-val" style="border:none;">{{ ucfirst($mesAnio) }}</td></tr>
                    <tr><td class="info-lbl" style="border:none;">Fecha límite</td><td class="info-val" style="border:none;">{{ $bill->fecha_limite_pago?->format('d/m/Y') }}</td></tr>
                    <tr><td class="info-lbl" style="border:none;">Días gracia</td><td class="info-val" style="border:none;">{{ $bill->dias_gracia }} días</td></tr>
                    <tr><td class="info-lbl" style="border:none;">Forma pago</td><td class="info-val" style="border:none;">Transferencia / Consignación</td></tr>
                </table>
            </div>
        </td>
    </tr>
</table>

{{-- TABLA ÍTEMS --}}
<table class="items-table">
    <thead>
        <tr>
            <th style="width:8%;">Cód.</th>
            <th style="width:54%;">Descripción del servicio</th>
            <th style="width:7%;" class="right">Cant.</th>
            <th style="width:15%;" class="right">Vr. unitario</th>
            <th style="width:16%;" class="right">Total</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><div style="font-weight:bold;">001</div><div class="unspsc">UNSPSC<br>70330000-3</div></td>
            <td>
                <div class="desc-main">Canon de arrendamiento — {{ ucfirst($mesAnio) }}</div>
                <div class="desc-sub">{{ $bill->property?->tipo?->nombre }} · {{ $bill->property?->codigo }} — {{ $bill->property?->direccion }}</div>
                <div class="desc-sub">Contrato: {{ $bill->rentalContract?->numero_contrato }}</div>
            </td>
            <td class="right">1</td>
            <td class="right">${{ number_format($bill->canon_base, 0, ',', '.') }}</td>
            <td class="right">${{ number_format($bill->canon_base, 0, ',', '.') }}</td>
        </tr>
        @if($bill->cuota_administracion > 0)
        <tr>
            <td><div style="font-weight:bold;">002</div><div class="unspsc">UNSPSC<br>72100000</div></td>
            <td><div class="desc-main">Cuota de administración — {{ ucfirst($mesAnio) }}</div><div class="desc-sub">{{ $bill->property?->conjunto_edificio ?? 'Conjunto residencial' }}</div></td>
            <td class="right">1</td>
            <td class="right">${{ number_format($bill->cuota_administracion, 0, ',', '.') }}</td>
            <td class="right">${{ number_format($bill->cuota_administracion, 0, ',', '.') }}</td>
        </tr>
        @endif
        @if($bill->otros_cobros > 0)
        <tr>
            <td><div style="font-weight:bold;">003</div></td>
            <td><div class="desc-main">{{ $bill->descripcion_otros_cobros ?? 'Otros cobros' }}</div></td>
            <td class="right">1</td>
            <td class="right">${{ number_format($bill->otros_cobros, 0, ',', '.') }}</td>
            <td class="right">${{ number_format($bill->otros_cobros, 0, ',', '.') }}</td>
        </tr>
        @endif
    </tbody>
</table>

{{-- TOTALES --}}
<table class="totales" cellpadding="0" cellspacing="0">
    <tr><td class="lbl">Subtotal</td><td class="val">${{ number_format($bill->total_factura, 0, ',', '.') }}</td></tr>
    <tr><td class="lbl">IVA (0% — Arrendamiento vivienda)</td><td class="val">$0</td></tr>
    <tr class="rte"><td class="lbl rte">ReteFuente arrendamiento 3.5% (Cód. 06)</td><td class="val rte">-${{ number_format($rtefonte, 0, ',', '.') }}</td></tr>
    @if($bill->mora_acumulada > 0)
    <tr class="mora"><td class="lbl mora">Intereses de mora ({{ $bill->dias_mora }} días · {{ $bill->tasa_mora_diaria }}% diario)</td><td class="val mora">+${{ number_format($bill->mora_acumulada, 0, ',', '.') }}</td></tr>
    @endif
    @if($bill->descuentos > 0)
    <tr class="desc"><td class="lbl desc">Descuentos</td><td class="val desc">-${{ number_format($bill->descuentos, 0, ',', '.') }}</td></tr>
    @endif
    @if($bill->total_pagado > 0)
    <tr><td class="lbl" style="color:#15803d;">Total pagado</td><td class="val" style="color:#15803d;">-${{ number_format($bill->total_pagado, 0, ',', '.') }}</td></tr>
    @endif
    <tr class="total-final"><td class="lbl">Neto a pagar</td><td class="val">${{ number_format($neto, 0, ',', '.') }} COP</td></tr>
</table>

{{-- DATOS PARA PAGO --}}
<div class="datos-pago">
    <strong>Datos para consignación o transferencia:</strong><br>
    Banco: {{ $company?->banco ?? 'Bancolombia' }} &nbsp;·&nbsp;
    Cta. ahorros: {{ $company?->numero_cuenta ?? 'N/A' }} &nbsp;·&nbsp;
    Titular: {{ $company?->razon_social ?? 'Inmobiliaria Serviarrendar S.A.S' }}<br>
    Referencia de pago: <strong>{{ $bill->numero }} — {{ mb_strtoupper($arr?->nombre_completo ?? '', 'UTF-8') }}</strong>
</div>

{{-- PAGOS REGISTRADOS --}}
@if($bill->payments->isNotEmpty())
<table class="pagos-table">
    <thead>
        <tr><th>N° Pago</th><th>Fecha</th><th>Forma de pago</th><th>Banco / Referencia</th><th style="text-align:right;">Valor</th></tr>
    </thead>
    <tbody>
        @foreach($bill->payments as $p)
        <tr>
            <td>{{ $p->numero }}</td>
            <td>{{ $p->fecha_pago?->format('d/m/Y') }}</td>
            <td>{{ ucfirst(str_replace('_',' ',$p->forma_pago)) }}</td>
            <td>{{ $p->banco_origen ?? '—' }}{{ $p->referencia_pago ? ' · ' . $p->referencia_pago : '' }}</td>
            <td style="text-align:right;font-weight:bold;color:#15803d;">${{ number_format($p->total_pagado, 0, ',', '.') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

{{-- CUFE --}}
@if($bill->cufe)
<div class="cufe-box">
    <div class="cufe-label">CUFE — Código único de factura electrónica</div>
    <div class="cufe-val">{{ $bill->cufe }}</div>
</div>
@endif

{{-- PIE LEGAL --}}
<div class="pie-legal">
    <table class="pie-grid" cellpadding="0" cellspacing="0">
        <tr>
            <td class="pie-txt">
                Factura electrónica de venta generada automáticamente de acuerdo al art. 774 del C.C.
                Una vez aceptada, el adquirente declara haber recibido el servicio a satisfacción.
                Representación gráfica de la Factura de Venta Electrónica.
                Documento generado el {{ now()->format('d/m/Y H:i') }}.
            </td>
            <td class="pie-plat">
                Plataforma: ServiCore ERP<br>
                NIT {{ $company?->nit_completo ?? '807.005.762-8' }}<br>
                Software: Yarom Inmobiliaria
            </td>
        </tr>
    </table>
</div>

</div>
</body>
</html>
