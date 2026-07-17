<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Recibo {{ $payment->numero }}</title>
<style>
    @page { margin: 30pt 34pt; }
    * { box-sizing: border-box; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 10pt; color: #000; margin: 0; }

    .top table { width: 100%; border-collapse: collapse; }
    .razon { font-size: 12pt; font-weight: bold; }
    .nit { font-size: 10pt; }
    .doctitle { text-align: right; font-size: 11pt; font-weight: bold; }
    .docnum { text-align: right; font-size: 10pt; }

    table.datos { width: 100%; border-collapse: collapse; margin-top: 10pt; border: 1pt solid #000; }
    table.datos td { border: 1pt solid #000; padding: 5pt 8pt; font-size: 10pt; vertical-align: top; }
    table.datos td.lbl { width: 14%; white-space: nowrap; }
    table.datos td.val { font-weight: bold; }

    .suma-row td { padding: 6pt 8pt; }
    .suma-row .lbl { width: 14%; }
    .suma-row .txt { font-weight: bold; }
    .suma-row .num { text-align: right; font-weight: bold; width: 16%; }

    table.concepto { width: 100%; border-collapse: collapse; margin-top: -1pt; border: 1pt solid #000; border-top: none; }
    table.concepto th { border: 1pt solid #000; padding: 5pt 8pt; text-align: left; font-size: 9.5pt; font-weight: bold; }
    table.concepto th.val, table.concepto td.val { text-align: right; }
    table.concepto td { border: 1pt solid #000; padding: 5pt 8pt; font-size: 10pt; }
    table.concepto td.desc-sub { padding-left: 20pt; font-size: 9pt; color: #333; }
    table.concepto tr.neto td { font-weight: bold; }

    .notas { border: 1pt solid #000; border-top: none; padding: 6pt 8pt; font-size: 9.5pt; min-height: 34pt; }

    table.firmas { width: 100%; border-collapse: collapse; margin-top: -1pt; }
    table.firmas td { border: 1pt solid #000; border-top: none; padding: 22pt 8pt 6pt 8pt; text-align: center; font-size: 9pt; width: 50%; }
</style>
</head>
<body>

@php
    $bill = $payment->bill;
    $money = fn ($v) => number_format((float) $v, 0, ',', '.');

    $periodoTexto = null;
    if ($bill?->periodo_inicio && $bill?->periodo_fin) {
        $periodoTexto = \Carbon\Carbon::parse($bill->periodo_inicio)->format('Y-m-d') . ' al ' . \Carbon\Carbon::parse($bill->periodo_fin)->format('Y-m-d');
    } elseif ($bill?->mes && $bill?->anio) {
        $periodoTexto = ucfirst(\Carbon\Carbon::create($bill->anio, $bill->mes, 1)->translatedFormat('F Y'));
    }
@endphp

<div class="top">
    <table>
        <tr>
            <td style="width:60%;">
                <div class="razon">{{ $company?->razon_social ?? 'SERVIARRENDAR SAS' }}</div>
                <div class="nit">NIT: {{ $company?->nit_completo ?? $company?->nit }}</div>
            </td>
            <td style="width:40%;">
                <div class="doctitle">RECIBO DE PAGO: {{ $payment->numero }}</div>
                <div class="docnum">FECHA: {{ \Carbon\Carbon::parse($payment->fecha_pago)->format('Y-m-d') }}</div>
            </td>
        </tr>
    </table>
</div>

<table class="datos">
    <tr>
        <td class="lbl">RECIBIDO DE</td>
        <td class="val" style="width:36%;">{{ mb_strtoupper($payment->arrendatario?->nombre_completo ?? '', 'UTF-8') }}</td>
        <td class="lbl" style="width:10%;">C.C.</td>
        <td class="val" style="width:14%;">{{ $payment->arrendatario?->numero_documento }}</td>
        <td class="lbl" style="width:12%;">CONTRATO</td>
        <td class="val">{{ $bill?->rentalContract?->numero_contrato }}</td>
    </tr>
    <tr>
        <td class="lbl">DIRECCION</td>
        <td class="val" colspan="3">{{ mb_strtoupper($bill?->property?->direccion ?? '', 'UTF-8') }}</td>
        <td class="lbl">INM</td>
        <td class="val">{{ $bill?->property?->codigo }}</td>
    </tr>
    <tr>
        <td class="lbl">CIUDAD</td>
        <td class="val" colspan="3">{{ $bill?->property?->municipio?->nombre ?? '—' }}</td>
        <td class="lbl">FACTURA</td>
        <td class="val">{{ $bill?->numero }}</td>
    </tr>
    <tr>
        <td class="lbl">FORMA PAGO</td>
        <td class="val">{{ ucfirst($payment->forma_pago) }}</td>
        <td class="lbl">BANCO</td>
        <td class="val">{{ $payment->bank?->nombre ?? '—' }}</td>
        <td class="lbl">REF</td>
        <td class="val">{{ $payment->referencia_pago ?: '—' }}</td>
    </tr>
    <tr class="suma-row">
        <td class="lbl">LA SUMA DE</td>
        <td class="txt" colspan="4">{{ \App\Helpers\NumeroALetras::convertir((float) $payment->total_pagado) }}</td>
        <td class="num">{{ $money($payment->total_pagado) }}</td>
    </tr>
</table>

<table class="concepto">
    <thead>
        <tr><th colspan="5">POR CONCEPTO DE</th><th class="val">VALOR</th></tr>
    </thead>
    <tbody>
        @if($payment->valor_canon > 0)
        <tr>
            <td colspan="5">
                CANON DE ARRIENDO
                @if($periodoTexto)<span class="desc-sub"><br>PERIODO: {{ $periodoTexto }}</span>@endif
            </td>
            <td class="val">{{ $money($payment->valor_canon) }}</td>
        </tr>
        @endif
        @if($payment->valor_administracion > 0)
        <tr><td colspan="5">CUOTA DE ADMINISTRACIÓN</td><td class="val">{{ $money($payment->valor_administracion) }}</td></tr>
        @endif
        @if($payment->valor_mora > 0)
        <tr><td colspan="5">RECARGOS APLICADOS</td><td class="val">{{ $money($payment->valor_mora) }}</td></tr>
        @endif
        @if($payment->otros_valores > 0)
        <tr><td colspan="5">OTROS VALORES</td><td class="val">{{ $money($payment->otros_valores) }}</td></tr>
        @endif
        <tr class="neto"><td colspan="5">NETO</td><td class="val">{{ $money($payment->total_pagado) }}</td></tr>
    </tbody>
</table>

<div class="notas">
    SE REGISTRA EL PAGO {{ $periodoTexto ? 'DEL PERIODO ' . $periodoTexto : '' }}.
    {{ $payment->notas }}
</div>

<table class="firmas">
    <tr>
        <td>ELABORA<br>{{ $payment->registradoPor?->name }}</td>
        <td>FIRMA Y SELLO — {{ $company?->razon_social ?? 'SERVIARRENDAR SAS' }}</td>
    </tr>
</table>

</body>
</html>
