<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Recibo {{ $payment->numero }}</title>
<style>
    @page { margin: 8pt 10pt; }
    * { box-sizing: border-box; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 6pt; color: #000; margin: 0; }

    .top table { width: 100%; border-collapse: collapse; }
    .razon { font-size: 8pt; font-weight: bold; color: #000; }
    .datos-empresa { font-size: 5.4pt; color: #000; line-height: 1.35; }
    .doctitle { text-align: right; font-size: 7pt; font-weight: bold; color: #000; }
    .docnum { text-align: right; font-size: 5.8pt; font-weight: bold; color: #000; }
    .top-sep { border-bottom: 1pt solid #000; margin-top: 3pt; }

    table.datos { width: 100%; border-collapse: collapse; margin-top: 4pt; border: 0.9pt solid #000; }
    table.datos td { border: 0.9pt solid #000; padding: 1.5pt 4pt; font-size: 6.2pt; font-weight: bold; vertical-align: top; line-height: 1.15; color: #000; }
    table.datos td.lbl { width: 24%; white-space: nowrap; font-weight: bold; }
    table.datos td.val { font-weight: bold; width: 26%; }

    .suma-row td { padding: 2pt 4pt; }
    .suma-row .lbl { width: 24%; }
    .suma-row .txt { font-weight: bold; }

    table.concepto { width: 100%; border-collapse: collapse; margin-top: -0.9pt; border: 0.9pt solid #000; border-top: none; }
    table.concepto th { border: 0.9pt solid #000; padding: 1.5pt 4pt; text-align: left; font-size: 6pt; font-weight: bold; color: #000; }
    table.concepto th.val, table.concepto td.val { text-align: right; }
    table.concepto td { border: 0.9pt solid #000; padding: 1.5pt 4pt; font-size: 6.2pt; font-weight: bold; line-height: 1.15; color: #000; }
    table.concepto td.desc-sub { padding-left: 10pt; font-size: 5pt; font-weight: normal; color: #000; }
    table.concepto tr.neto td { font-weight: bold; }
    table.concepto td .cuenta-cod { font-weight: bold; }

    .notas { border: 0.9pt solid #000; border-top: none; padding: 1.5pt 4pt; font-size: 5.4pt; font-weight: bold; min-height: 10pt; line-height: 1.15; color: #000; }

    table.firmas { width: 100%; border-collapse: collapse; margin-top: -0.9pt; }
    table.firmas td { border: 0.9pt solid #000; border-top: none; padding: 6pt 4pt 2pt 4pt; text-align: center; font-size: 5.4pt; font-weight: bold; width: 50%; color: #000; }

    .pie { margin-top: 5pt; text-align: center; }
    .pie-linea { border-top: 0.75pt solid #000; margin-bottom: 3pt; }
    .pie-texto { font-size: 4.6pt; color: #000; line-height: 1.4; }
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
            <td style="width:58%;">
                <div class="razon">{{ mb_strtoupper($company?->razon_social ?? 'SERVIARRENDAR SAS', 'UTF-8') }}</div>
                <div class="datos-empresa">
                    NIT: {{ $company?->nit_completo ?? $company?->nit }}<br>
                    Tel: {{ $company?->telefono ?? $company?->celular ?? '—' }} &nbsp;·&nbsp; {{ $company?->email ?? '—' }}<br>
                    {{ mb_strtoupper($company?->direccion ?? '—', 'UTF-8') }}
                </div>
            </td>
            <td style="width:42%;">
                <div class="doctitle">RECIBO: {{ $payment->numero }}</div>
                <div class="docnum">FECHA: {{ \Carbon\Carbon::parse($payment->fecha_pago)->format('Y-m-d') }}</div>
            </td>
        </tr>
    </table>
    <div class="top-sep"></div>
</div>

<table class="datos">
    <tr>
        <td class="lbl">RECIBIDO DE</td>
        <td class="val" colspan="3">{{ mb_strtoupper($payment->arrendatario?->nombre_completo ?? '', 'UTF-8') }}</td>
    </tr>
    <tr>
        <td class="lbl">C.C.</td>
        <td class="val">{{ $payment->arrendatario?->numero_documento }}</td>
        <td class="lbl">CONTRATO</td>
        <td class="val">{{ $bill?->rentalContract?->numero_contrato }}</td>
    </tr>
    <tr>
        <td class="lbl">DIRECCION</td>
        <td class="val" colspan="3">{{ mb_strtoupper($bill?->property?->direccion ?? '', 'UTF-8') }}</td>
    </tr>
    <tr>
        <td class="lbl">INM</td>
        <td class="val">{{ $bill?->property?->codigo }}</td>
        <td class="lbl">FACTURA</td>
        <td class="val">{{ $bill?->numero }}</td>
    </tr>
    <tr>
        <td class="lbl">CIUDAD</td>
        <td class="val">{{ $bill?->property?->municipio?->nombre ?? '—' }}</td>
        <td class="lbl">BANCO</td>
        <td class="val">{{ $payment->bank?->nombre ?? '—' }}</td>
    </tr>
    <tr>
        <td class="lbl">FORMA PAGO</td>
        <td class="val">{{ ucfirst($payment->forma_pago) }}</td>
        <td class="lbl">REF</td>
        <td class="val">{{ $payment->referencia_pago ?: '—' }}</td>
    </tr>
    <tr class="suma-row">
        <td class="lbl">LA SUMA DE</td>
        <td class="txt" colspan="3">{{ \App\Helpers\NumeroALetras::convertir((float) $payment->total_pagado) }}</td>
    </tr>
</table>

<table class="concepto">
    <thead>
        <tr><th>POR CONCEPTO DE</th><th class="val" style="width:26%;">VALOR</th></tr>
    </thead>
    <tbody>
        @forelse($lineasContables ?? [] as $linea)
        <tr>
            <td>
                <span class="cuenta-cod">{{ $linea->account?->codigo }}</span> {{ mb_strtoupper($linea->account?->nombre ?? '', 'UTF-8') }}
                @if($linea->descripcion)<span class="desc-sub"><br>{{ mb_strtoupper($linea->descripcion, 'UTF-8') }}</span>@endif
            </td>
            <td class="val">{{ $money($linea->credito) }}</td>
        </tr>
        @empty
        @if($payment->valor_canon > 0)
        <tr>
            <td>
                CANON DE ARRIENDO
                @if($periodoTexto)<span class="desc-sub"><br>PERIODO: {{ $periodoTexto }}</span>@endif
            </td>
            <td class="val">{{ $money($payment->valor_canon) }}</td>
        </tr>
        @endif
        @if($payment->valor_administracion > 0)
        <tr><td>CUOTA DE ADMINISTRACIÓN</td><td class="val">{{ $money($payment->valor_administracion) }}</td></tr>
        @endif
        @if($payment->valor_mora > 0)
        <tr><td>RECARGOS APLICADOS</td><td class="val">{{ $money($payment->valor_mora) }}</td></tr>
        @endif
        @if($payment->otros_valores > 0)
        <tr><td>OTROS VALORES</td><td class="val">{{ $money($payment->otros_valores) }}</td></tr>
        @endif
        @endforelse
        <tr class="neto"><td>NETO</td><td class="val">{{ $money($payment->total_pagado) }}</td></tr>
    </tbody>
</table>

<div class="notas">
    SE REGISTRA EL PAGO {{ $periodoTexto ? 'DEL PERIODO ' . $periodoTexto : '' }}.
    {{ $payment->notas }}
</div>

<table class="firmas">
    <tr>
        <td>ELABORA<br>{{ $payment->registradoPor?->name }}</td>
        <td>FIRMA Y SELLO — {{ mb_strtoupper($company?->razon_social ?? 'SERVIARRENDAR SAS', 'UTF-8') }}</td>
    </tr>
</table>

<div class="pie">
    <div class="pie-linea"></div>
    <div class="pie-texto">
        Documento generado electrónicamente por ServiArrendar ERP v1.0.0 &bull; Desarrollado por YarOM Technology &bull;<br>
        www.serviarrendar.com &bull; &copy; {{ \Carbon\Carbon::parse($payment->fecha_pago)->format('Y') }} Todos los derechos reservados.
    </div>
</div>

</body>
</html>
