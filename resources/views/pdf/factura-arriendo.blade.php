<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8">
<style>
    @page { margin:1cm 1.4cm 1.6cm; }
    * { box-sizing:border-box; }
    body { font-family:'DejaVu Sans',sans-serif; font-size:7.6pt; color:#111827; line-height:1.4; }
    .footer-fijo { position:fixed; bottom:-1.3cm; left:0; right:0; text-align:center; font-size:6.2pt; color:#94a3b8; border-top:0.5pt solid #e2e8f0; padding-top:4pt; }

    /* ── Encabezado ── */
    .head table { width:100%; border-collapse:collapse; }
    .head td { vertical-align:top; padding:0; }
    .head-logo img { max-height:40pt; max-width:130pt; }
    .head-logo-fallback { color:#0A192F; font-size:14pt; font-weight:bold; }
    .head-logo-fallback span { color:#E24B4A; }
    .head-center { text-align:center; padding-top:4pt; }
    .head-company { font-size:9.5pt; font-weight:bold; color:#0A192F; }
    .head-tagline { font-size:6.4pt; color:#64748b; font-style:italic; margin-top:1pt; }
    .head-right { text-align:right; font-size:6.2pt; color:#374151; line-height:1.35; }
    .head-right b { color:#0A192F; }

    hr.sep { border:none; border-top:1pt solid #0A192F; margin:8pt 0 0 0; }

    /* ── Barra de identificación del documento ── */
    .id-bar { width:100%; border-collapse:collapse; margin-top:6pt; table-layout:fixed; }
    .id-bar td { vertical-align:middle; }
    .id-bar .dep-fecha { background:#0A192F; color:#fff; border-radius:4pt 0 0 4pt; padding:6pt 10pt; }
    .id-bar .dep-fecha .k { font-size:5.8pt; text-transform:uppercase; letter-spacing:.05em; color:#94a3b8; }
    .id-bar .dep-fecha .v { font-size:7.4pt; font-weight:bold; margin-top:1pt; }
    .id-bar .titulo { text-align:center; padding:6pt 10pt; }
    .id-bar .titulo .t1 { font-size:9.5pt; font-weight:bold; color:#E24B4A; letter-spacing:.02em; }
    .id-bar .numero { border:1.25pt solid #0A192F; border-radius:4pt; text-align:center; padding:6pt 12pt; }
    .id-bar .numero .k { font-size:5.8pt; text-transform:uppercase; letter-spacing:.05em; color:#64748b; }
    .id-bar .numero .v { font-size:11pt; font-weight:bold; color:#0A192F; }

    /* ── Sello de estado de pago ── */
    .sello-estado { text-align:center; margin-top:1.5pt; }
    .sello-estado span { display:inline-block; padding:0.5pt 6pt; border-radius:2pt; font-size:4.6pt; font-weight:bold; letter-spacing:.02em; text-transform:uppercase; }
    .sello-pagada { background:#dcfce7; color:#15803d; border:1pt solid #15803d; }
    .sello-pendiente { background:#fef3c7; color:#b45309; border:1pt solid #b45309; }
    .sello-mora { background:#fee2e2; color:#b91c1c; border:1pt solid #b91c1c; }
    .sello-anulada { background:#e5e7eb; color:#374151; border:1pt solid #374151; }

    /* ── Bloque de datos del adquirente / documento ── */
    .datos-box { border:0.75pt solid #0A192F; border-radius:4pt; margin-top:6pt; padding:6pt 12pt; }
    .datos-box table { width:100%; border-collapse:collapse; }
    .datos-box td { vertical-align:top; padding:0.8pt 6pt 0.8pt 0; font-size:7.1pt; }
    .datos-box .k { color:#64748b; display:inline-block; min-width:80pt; }
    .datos-box .v { font-weight:bold; color:#0f172a; }

    /* ── Tabla ítems ── */
    .items-table { width:100%; border-collapse:collapse; margin-top:7pt; }
    .items-table th { background:#0A192F; color:#fff; padding:4pt 5pt; text-align:left; font-size:6.2pt; font-weight:bold; text-transform:uppercase; letter-spacing:.03em; border:0.5pt solid #0A192F; }
    .items-table th.right,.items-table td.right { text-align:right; }
    .items-table th.center,.items-table td.center { text-align:center; }
    .items-table td { padding:3.5pt 5pt; border:0.5pt solid #e2e8f0; font-size:7.1pt; vertical-align:top; }
    .items-table .desc-main { font-weight:bold; }
    .items-table .desc-sub { color:#64748b; font-size:6.4pt; margin-top:1.5pt; }

    /* ── Notas / SON + Totales ── */
    .foot-grid { width:100%; border-collapse:collapse; margin-top:6pt; }
    .foot-grid td { vertical-align:top; padding:0; }
    .foot-left { width:62%; padding-right:10pt; font-size:7pt; }
    .foot-left .lbl { font-size:6.2pt; font-weight:bold; text-transform:uppercase; color:#64748b; letter-spacing:.05em; }
    .totales { width:100%; border-collapse:collapse; border:0.75pt solid #0A192F; border-radius:3pt; }
    .totales td { padding:3pt 9pt; font-size:7.1pt; border-bottom:0.5pt solid #e2e8f0; }
    .totales .lbl { color:#64748b; }
    .totales .val { text-align:right; font-weight:bold; }
    .totales .mora { color:#dc2626; }
    .totales .rte { color:#b45309; }
    .totales .desc { color:#15803d; }
    .total-final td { background:#0A192F; border-bottom:none; }
    .total-final .lbl { color:#94a3b8; font-size:8.3pt; font-weight:bold; }
    .total-final .val { color:#fff; font-size:11pt; font-weight:bold; }

    /* ── Pagos ── */
    .pagos-table { width:100%; border-collapse:collapse; margin-top:10pt; }
    .pagos-table th { background:#f0fdf4; padding:3pt 6pt; font-size:6.4pt; font-weight:bold; color:#15803d; border:0.5pt solid #bbf7d0; }
    .pagos-table td { padding:3.5pt 6pt; font-size:7pt; border:0.5pt solid #e2e8f0; }

    /* ── Datos de pago ── */
    .datos-pago { background:#eff6ff; border:0.5pt solid #bfdbfe; border-radius:3pt; padding:4pt 10pt; margin-top:6pt; font-size:7.1pt; }
    .datos-pago strong { color:#1e40af; }

    /* ── CUFE ── */
    .cufe-box { border-top:0.5pt solid #e2e8f0; margin-top:6pt; padding-top:4pt; }
    .cufe-label { font-size:6pt; font-weight:bold; text-transform:uppercase; color:#94a3b8; }
    .cufe-val { font-size:6.2pt; color:#64748b; word-break:break-all; font-family:'DejaVu Sans Mono',monospace; margin-top:1pt; }

    /* ── Pie legal ── */
    .pie-legal { margin-top:5pt; font-size:6pt; color:#64748b; line-height:1.35; }
    .pie-legal b { color:#374151; }
</style>
</head>
<body>

<div class="footer-fijo">
    {{ $company?->razon_social }} · NIT {{ $company?->nit_completo }} · Software: YarOM ERP
</div>

@php
    $arr        = $bill->arrendatario;
    $mesAnio    = \Carbon\Carbon::create($bill->anio, $bill->mes, 1)->translatedFormat('F Y');
    // La retefuente solo la practica un arrendatario persona jurídica (agente
    // retenedor obligado por ley) — igual que en ContabilidadService::generarParaFactura.
    // Antes esta plantilla la restaba siempre, sin importar el tipo de persona.
    $aplicaRete = $arr?->tipo_persona === 'juridica';
    $rtefonte   = $aplicaRete ? round($bill->canon_base * 0.035, 2) : 0;
    $neto       = $bill->total_factura + $bill->mora_acumulada - $rtefonte;
    $emision    = $bill->created_at ?? now();
    $tituloDoc  = $bill->tipo_documento === 'factura_electronica' ? 'FACTURA ELECTRÓNICA DE VENTA' : 'DOCUMENTO EQUIVALENTE DE ARRENDAMIENTO';
    $departamento = $company?->municipio?->departamento?->nombre ?? 'Norte de Santander';
    $regimenTxt = $company?->responsable_iva ? 'Responsable del impuesto sobre las ventas IVA' : 'No responsable de IVA';

    $totalLineas = 1 + ($bill->cuota_administracion > 0 ? 1 : 0) + ($bill->otros_cobros > 0 ? 1 : 0);

    $selloTexto = match ($bill->estado) {
        'pagada'  => 'PAGADA',
        'anulada' => 'ANULADA',
        'en_mora' => 'PENDIENTE — EN MORA',
        'parcial' => 'PAGO PARCIAL — SALDO PENDIENTE',
        default   => 'PENDIENTE DE PAGO',
    };
    $selloClase = match ($bill->estado) {
        'pagada'  => 'sello-pagada',
        'anulada' => 'sello-anulada',
        'en_mora' => 'sello-mora',
        default   => 'sello-pendiente',
    };
@endphp

{{-- ENCABEZADO --}}
<div class="head">
    <table>
        <tr>
            <td class="head-logo" style="width:26%;">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}">
                @else
                    <div class="head-logo-fallback">YAROM<span>INMOBILIARIA</span></div>
                @endif
            </td>
            <td class="head-center" style="width:38%;">
            </td>
            <td class="head-right" style="width:36%;">
                @if($company?->nombre_comercial)<b>{{ $company->nombre_comercial }}</b><br>@endif
                NIT: {{ $company?->nit_completo ?? $company?->nit }}<br>
                Régimen: {{ $regimenTxt }}<br>
                @if($company?->resolucion_facturacion)
                Resolución DIAN N° {{ $company->resolucion_facturacion }}<br>
                Fecha {{ $company?->fecha_resolucion?->format('d/m/Y') }} - {{ $company?->fecha_vencimiento_resolucion?->format('d/m/Y') }}<br>
                Autorización del {{ $company?->prefijo_factura ?? 'FEFE' }}{{ str_pad($company?->consecutivo_desde ?? 1001, 4, '0', STR_PAD_LEFT) }} al {{ $company?->prefijo_factura ?? 'FEFE' }}{{ str_pad($company?->consecutivo_hasta ?? 2000, 4, '0', STR_PAD_LEFT) }}<br>
                @endif
                Email: {{ $company?->email }}<br>
                Teléfono: {{ $company?->celular }}
            </td>
        </tr>
    </table>
</div>
<hr class="sep">

{{-- BARRA DE IDENTIFICACIÓN --}}
<table class="id-bar">
    <tr>
        <td class="dep-fecha" style="width:34%;">
            <table style="width:100%;">
                <tr>
                    <td style="width:58%;">
                        <div class="k">Departamento</div>
                        <div class="v" style="white-space:nowrap;">{{ $departamento }}</div>
                    </td>
                    <td style="width:42%;">
                        <div class="k">Fecha</div>
                        <div class="v" style="white-space:nowrap;">{{ $emision->format('d/m/Y') }}</div>
                    </td>
                </tr>
            </table>
        </td>
        <td class="titulo" style="width:38%;">
            <div class="t1">{{ $tituloDoc }}</div>
        </td>
        <td style="width:28%;">
            <div class="numero">
                <div class="k">N°</div>
                <div class="v" style="white-space:nowrap;">{{ $bill->numero_dian ?? $bill->numero }}</div>
            </div>
            <div class="sello-estado"><span class="{{ $selloClase }}">{{ $selloTexto }}</span></div>
        </td>
    </tr>
</table>

{{-- DATOS DEL ADQUIRENTE / DOCUMENTO --}}
<div class="datos-box">
    <table>
        <tr>
            <td style="width:40%;">
                <span class="k">Nombre/Razón Social:</span><br>
                <span class="v">{{ mb_strtoupper($arr?->nombre_completo ?? '', 'UTF-8') }}</span>
            </td>
            <td style="width:30%;">
                <span class="k">NIT/CC:</span> <span class="v">{{ $arr?->numero_documento }}</span>
            </td>
            <td style="width:30%;">
                <span class="k">Fecha de firmado:</span><br>
                <span class="v">{{ $emision->format('d/m/Y H:i:s') }}</span>
            </td>
        </tr>
        <tr>
            <td>
                <span class="k">Dirección:</span> <span class="v">{{ $bill->property?->direccion }}</span>
            </td>
            <td>
                <span class="k">Departamento:</span><br>
                <span class="v">{{ $departamento }}</span>
            </td>
            <td>
                <span class="k">Medio de pago:</span><br>
                <span class="v">Transferencia / Consignación</span>
            </td>
        </tr>
        <tr>
            <td>
                @if($arr?->email)<span class="k">Email:</span> <span class="v">{{ $arr->email }}</span>@endif
            </td>
            <td>
                <span class="k">Forma de Pago:</span> <span class="v">Contado</span><br>
                <span class="k">Hora emisión:</span> <span class="v">{{ $emision->format('H:i:s') }}</span><br>
                <span class="k">Total de Líneas:</span> <span class="v">{{ $totalLineas }}</span>
            </td>
            <td>
                <span class="k">Moneda:</span><br>
                <span class="v">COP, Peso colombiano</span>
            </td>
        </tr>
    </table>
</div>

{{-- TABLA ÍTEMS --}}
<table class="items-table">
    <thead>
        <tr>
            <th style="width:6%;">#</th>
            <th style="width:9%;">Código</th>
            <th style="width:6%;" class="center">Cant.</th>
            <th style="width:41%;">Descripción</th>
            <th style="width:6%;" class="center">U.M</th>
            <th style="width:14%;" class="right">Vr. Unit.</th>
            <th style="width:18%;" class="right">Total</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="center">1</td>
            <td>CDA</td>
            <td class="center">1,00</td>
            <td>
                <div class="desc-main">CANON DE ARRENDAMIENTO</div>
                <div class="desc-sub">Canon de arrendamiento correspondiente {{ ucfirst($mesAnio) }} — {{ $bill->property?->codigo }} — {{ $bill->property?->direccion }}</div>
                <div class="desc-sub">Contrato: {{ $bill->rentalContract?->numero_contrato }}</div>
            </td>
            <td class="center">94</td>
            <td class="right">${{ number_format($bill->canon_base, 2, ',', '.') }}</td>
            <td class="right">${{ number_format($bill->canon_base, 2, ',', '.') }}</td>
        </tr>
        @if($bill->cuota_administracion > 0)
        <tr>
            <td class="center">2</td>
            <td>ADM</td>
            <td class="center">1,00</td>
            <td>
                <div class="desc-main">CUOTA DE ADMINISTRACIÓN</div>
                <div class="desc-sub">Cuota de administración — {{ ucfirst($mesAnio) }}</div>
            </td>
            <td class="center">94</td>
            <td class="right">${{ number_format($bill->cuota_administracion, 2, ',', '.') }}</td>
            <td class="right">${{ number_format($bill->cuota_administracion, 2, ',', '.') }}</td>
        </tr>
        @endif
        @if($bill->otros_cobros > 0)
        <tr>
            <td class="center">{{ $totalLineas }}</td>
            <td>OTR</td>
            <td class="center">1,00</td>
            <td><div class="desc-main">{{ mb_strtoupper($bill->descripcion_otros_cobros ?? 'OTROS COBROS', 'UTF-8') }}</div></td>
            <td class="center">94</td>
            <td class="right">${{ number_format($bill->otros_cobros, 2, ',', '.') }}</td>
            <td class="right">${{ number_format($bill->otros_cobros, 2, ',', '.') }}</td>
        </tr>
        @endif
    </tbody>
</table>

{{-- NOTAS/SON + TOTALES --}}
<table class="foot-grid">
    <tr>
        <td class="foot-left">
            <div class="lbl">Notas</div>
            <div style="margin-bottom:6pt;">{{ $bill->notas ?: '—' }}</div>
            <div class="lbl">Son</div>
            <div>{{ \App\Helpers\NumeroALetras::convertir((float) $neto) }}</div>
        </td>
        <td>
            <table class="totales">
                <tr><td class="lbl">Subtotal</td><td class="val">${{ number_format($bill->total_factura, 2, ',', '.') }}</td></tr>
                <tr><td class="lbl">IVA (0% — Arrendamiento vivienda)</td><td class="val">$0,00</td></tr>
                @if($aplicaRete)
                <tr><td class="lbl rte">ReteFuente arrendamiento 3.5% (Cód. 06)</td><td class="val rte">-${{ number_format($rtefonte, 2, ',', '.') }}</td></tr>
                @endif
                @if($bill->mora_acumulada > 0)
                <tr><td class="lbl mora">Intereses de mora ({{ $bill->dias_mora }} días)</td><td class="val mora">+${{ number_format($bill->mora_acumulada, 2, ',', '.') }}</td></tr>
                @endif
                @if($bill->descuentos > 0)
                <tr><td class="lbl desc">Descuentos</td><td class="val desc">-${{ number_format($bill->descuentos, 2, ',', '.') }}</td></tr>
                @endif
                @if($bill->total_pagado > 0)
                <tr><td class="lbl" style="color:#15803d;">Total pagado</td><td class="val" style="color:#15803d;">-${{ number_format($bill->total_pagado, 2, ',', '.') }}</td></tr>
                @endif
                <tr class="total-final"><td class="lbl">Total</td><td class="val">${{ number_format($neto, 2, ',', '.') }}</td></tr>
            </table>
        </td>
    </tr>
</table>

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
            <td>{{ $p->bank?->nombre ?? $p->banco_origen ?? '—' }}{{ $p->referencia_pago ? ' · ' . $p->referencia_pago : '' }}</td>
            <td style="text-align:right;font-weight:bold;color:#15803d;">${{ number_format($p->total_pagado, 0, ',', '.') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

{{-- DATOS PARA PAGO --}}
@php
    $bancosPago = \App\Models\Bank::where('is_active', true)->where('tipo_cuenta', '!=', 'caja')->orderBy('id')->get();
@endphp
<div class="datos-pago">
    <strong>Datos para consignación o transferencia:</strong><br>
    @foreach($bancosPago as $banco)
        {{ $banco->nombre }} {{ ucfirst($banco->tipo_cuenta) }}: {{ $banco->numero_cuenta }}@if(!$loop->last) &nbsp;·&nbsp; @endif
    @endforeach
    <br>
    Titular: {{ $company?->razon_social ?? 'Inmobiliaria Serviarrendar S.A.S' }}<br>
    Referencia de pago: <strong>{{ $bill->numero }} — {{ mb_strtoupper($arr?->nombre_completo ?? '', 'UTF-8') }}</strong>
</div>

{{-- CUFE --}}
@if($bill->cufe)
<div class="cufe-box">
    <div class="cufe-label">CUFE</div>
    <div class="cufe-val">{{ $bill->cufe }}</div>
</div>
@endif

{{-- PIE LEGAL --}}
<div class="pie-legal">
    Esta {{ $bill->tipo_documento === 'factura_electronica' ? 'factura' : 'documento' }} es un título valor de acuerdo al art. 774 del C.C. y una vez aceptado(a) declara haber recibido los bienes y servicios a satisfacción.<br>
    <b>Representación gráfica de la {{ $tituloDoc }}.</b><br>
    Software: YarOM ERP · Desarrollado por Ing. Jhoan Romero Rivera.
</div>

</body>
</html>
