<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>{{ $entry->tipo }} {{ $entry->numero }}</title>
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
    $money = fn ($v) => number_format((float) $v, 0, ',', '.');
    $esIngreso = $entry->tipo === 'CI' || $entry->tipo === 'CR';
    $tituloDoc = $esIngreso ? 'RECIBO DE INGRESO' : 'COMPROBANTE DE EGRESO';

    // La cuenta de disponible (caja/banco) del comprobante determina la forma de pago;
    // las demás líneas son el "concepto" (a qué se aplicó el ingreso o egreso).
    $lineaDisponible = $entry->lines->first(fn ($l) => str_starts_with($l->account?->codigo ?? '', '11'));
    $lineasConcepto  = $entry->lines->reject(fn ($l) => $l->id === $lineaDisponible?->id)->sortBy('orden')->values();
    $monto = (float) max($entry->total_debitos, $entry->total_creditos);
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
                <div class="doctitle">{{ $tituloDoc }}: {{ $entry->numero }}</div>
                <div class="docnum">FECHA: {{ $entry->fecha->format('Y-m-d') }}</div>
            </td>
        </tr>
    </table>
    <div class="top-sep"></div>
</div>

<table class="datos">
    <tr>
        <td class="lbl">{{ $esIngreso ? 'RECIBIDO DE' : 'PAGADO A' }}</td>
        <td class="val" colspan="3">{{ mb_strtoupper($entry->third?->nombre_completo ?? 'VARIOS', 'UTF-8') }}</td>
    </tr>
    <tr>
        <td class="lbl">C.C. / NIT</td>
        <td class="val">{{ $entry->third?->numero_documento ?? '—' }}</td>
        <td class="lbl">REFERENCIA</td>
        <td class="val">{{ $entry->referencia ?: '—' }}</td>
    </tr>
    <tr>
        <td class="lbl">CUENTA</td>
        <td class="val">{{ $lineaDisponible?->account?->codigo }} — {{ mb_strtoupper($lineaDisponible?->account?->nombre ?? '', 'UTF-8') }}</td>
        <td class="lbl">PERIODO</td>
        <td class="val">{{ $entry->period?->nombre }}</td>
    </tr>
    <tr class="suma-row">
        <td class="lbl">LA SUMA DE</td>
        <td class="txt" colspan="3">{{ \App\Helpers\NumeroALetras::convertir($monto) }}</td>
    </tr>
</table>

<table class="concepto">
    <thead>
        <tr><th>POR CONCEPTO DE</th><th class="val" style="width:26%;">VALOR</th></tr>
    </thead>
    <tbody>
        @foreach($lineasConcepto as $linea)
        <tr>
            <td>
                <span class="cuenta-cod">{{ $linea->account?->codigo }}</span> {{ mb_strtoupper($linea->account?->nombre ?? '', 'UTF-8') }}
                @if($linea->descripcion)<span class="desc-sub"><br>{{ mb_strtoupper($linea->descripcion, 'UTF-8') }}</span>@endif
            </td>
            <td class="val">{{ $money(max($linea->debito, $linea->credito)) }}</td>
        </tr>
        @endforeach
        <tr class="neto"><td>NETO</td><td class="val">{{ $money($monto) }}</td></tr>
    </tbody>
</table>

<div class="notas">
    {{ mb_strtoupper($entry->descripcion ?? '', 'UTF-8') }}
</div>

<table class="firmas">
    <tr>
        <td>ELABORA<br>{{ $entry->creadoPor?->name }}</td>
        <td>FIRMA Y SELLO — {{ mb_strtoupper($company?->razon_social ?? 'SERVIARRENDAR SAS', 'UTF-8') }}</td>
    </tr>
</table>

<div class="pie">
    <div class="pie-linea"></div>
    <div class="pie-texto">
        Documento generado electrónicamente por ServiArrendar ERP v1.0.0 &bull; Desarrollado por YarOM Technology &bull;<br>
        www.serviarrendar.com &bull; &copy; {{ $entry->fecha->format('Y') }} Todos los derechos reservados.
    </div>
</div>

</body>
</html>
