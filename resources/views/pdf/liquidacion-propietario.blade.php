<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Egreso {{ $liquidation->numero }}</title>
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
    table.datos td.val { font-weight: normal; width: 26%; }

    .suma-row td { padding: 2pt 4pt; }
    .suma-row .lbl { width: 24%; }
    .suma-row .txt { font-weight: normal; }

    table.concepto { width: 100%; border-collapse: collapse; margin-top: -0.9pt; border: 0.9pt solid #000; border-top: none; }
    table.concepto th { border: 0.9pt solid #000; padding: 1.5pt 4pt; text-align: left; font-size: 6pt; font-weight: bold; color: #000; }
    table.concepto th.val, table.concepto td.val { text-align: right; }
    table.concepto td { border: 0.9pt solid #000; padding: 1.5pt 4pt; font-size: 6.2pt; font-weight: normal; line-height: 1.15; color: #000; }
    table.concepto td.desc-sub { padding-left: 10pt; font-size: 5pt; font-weight: normal; color: #000; }
    table.concepto tr.neto td { font-weight: bold; }
    table.concepto tr.deduccion td { font-weight: normal; }

    .notas { border: 0.9pt solid #000; border-top: none; padding: 1.5pt 4pt; font-size: 5.4pt; font-weight: normal; min-height: 10pt; line-height: 1.15; color: #000; }

    table.firmas { width: 100%; border-collapse: collapse; margin-top: -0.9pt; }
    table.firmas td { border: 0.9pt solid #000; border-top: none; padding: 10pt 4pt 4pt 4pt; text-align: center; font-size: 5.4pt; font-weight: bold; width: 50%; color: #000; vertical-align: bottom; }
    table.firmas .quien { font-size: 4.6pt; font-weight: normal; color: #333; margin-top: 2pt; }

    .pie { margin-top: 5pt; text-align: center; }
    .pie-linea { border-top: 0.75pt solid #000; margin-bottom: 3pt; }
    .pie-texto { font-size: 4.6pt; color: #000; line-height: 1.4; }
</style>
</head>
<body>

@php
    $money = fn ($v) => number_format((float) $v, 0, ',', '.');
    $periodoTexto = $liquidation->periodoLabel;
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
                <div class="doctitle">EGRESO: {{ $liquidation->numero }}</div>
                <div class="docnum">FECHA: {{ ($liquidation->fecha_giro ?? $liquidation->created_at)->format('Y-m-d') }}</div>
            </td>
        </tr>
    </table>
    <div class="top-sep"></div>
</div>

<table class="datos">
    <tr>
        <td class="lbl">PAGADO A</td>
        <td class="val" colspan="3">{{ mb_strtoupper($liquidation->propietario?->nombre_completo ?? '', 'UTF-8') }}</td>
    </tr>
    <tr>
        <td class="lbl">C.C.</td>
        <td class="val">{{ $liquidation->propietario?->numero_documento }}</td>
        <td class="lbl">CONTRATO</td>
        <td class="val">{{ $liquidation->rentalContract?->numero_contrato }}</td>
    </tr>
    <tr>
        <td class="lbl">DIRECCION</td>
        <td class="val" colspan="3">{{ mb_strtoupper($liquidation->property?->direccion ?? '', 'UTF-8') }}</td>
    </tr>
    <tr>
        <td class="lbl">INM</td>
        <td class="val">{{ $liquidation->property?->codigo }}</td>
        <td class="lbl">PERIODO</td>
        <td class="val">{{ $periodoTexto }}</td>
    </tr>
    <tr>
        <td class="lbl">ARRENDATARIO</td>
        <td class="val" colspan="3">{{ mb_strtoupper($liquidation->rentalContract?->arrendatario?->nombre_completo ?? '', 'UTF-8') }}</td>
    </tr>
    <tr>
        <td class="lbl">FORMA PAGO</td>
        <td class="val">{{ $liquidation->forma_giro ? ucfirst($liquidation->forma_giro) : '—' }}</td>
        <td class="lbl">REF</td>
        <td class="val">{{ $liquidation->referencia_giro ?: '—' }}</td>
    </tr>
    <tr class="suma-row">
        <td class="lbl">LA SUMA DE</td>
        <td class="txt" colspan="3">{{ \App\Helpers\NumeroALetras::convertir((float) $liquidation->total_giro) }}</td>
    </tr>
</table>

<table class="concepto">
    <thead>
        <tr><th>POR CONCEPTO DE</th><th class="val" style="width:26%;">VALOR</th></tr>
    </thead>
    <tbody>
        <tr>
            <td>
                CANON DE ARRIENDO COBRADO
                <span class="desc-sub"><br>PERIODO: {{ $periodoTexto }}</span>
            </td>
            <td class="val">{{ $money($liquidation->canon_cobrado) }}</td>
        </tr>
        <tr class="deduccion">
            <td>(-) COMISION DE ADMINISTRACION ({{ rtrim(rtrim(number_format($liquidation->comision_porcentaje, 2), '0'), '.') }}%)</td>
            <td class="val">-{{ $money($liquidation->comision_valor) }}</td>
        </tr>
        @if($liquidation->iva_comision > 0)
        <tr class="deduccion">
            <td>(-) IVA SOBRE COMISION (19%)</td>
            <td class="val">-{{ $money($liquidation->iva_comision) }}</td>
        </tr>
        @endif
        @if($liquidation->retefuente_valor > 0)
        <tr class="deduccion">
            <td>(-) RETENCION EN LA FUENTE ARRENDAMIENTOS</td>
            <td class="val">-{{ $money($liquidation->retefuente_valor) }}</td>
        </tr>
        @endif
        @if($liquidation->seguro_sura_deducido > 0)
        <tr class="deduccion">
            <td>(-) SEGURO SURA</td>
            <td class="val">-{{ $money($liquidation->seguro_sura_deducido) }}</td>
        </tr>
        @endif
        @if($liquidation->otros_descuentos > 0)
        <tr class="deduccion">
            <td>(-) OTROS DESCUENTOS
                @if($liquidation->descripcion_descuentos)<span class="desc-sub"><br>{{ mb_strtoupper($liquidation->descripcion_descuentos, 'UTF-8') }}</span>@endif
            </td>
            <td class="val">-{{ $money($liquidation->otros_descuentos) }}</td>
        </tr>
        @endif
        <tr class="neto"><td>NETO A GIRAR AL PROPIETARIO</td><td class="val">{{ $money($liquidation->total_giro) }}</td></tr>
    </tbody>
</table>

<div class="notas">
    SE LIQUIDA EL CANON DEL PERIODO {{ $periodoTexto }} AL PROPIETARIO DEL INMUEBLE {{ $liquidation->property?->codigo }}.
    {{ $liquidation->notas }}
</div>

<table class="firmas">
    <tr>
        <td>
            RECIBE EN NOMBRE DEL PROPIETARIO<br>&nbsp;
            <div class="quien">{{ mb_strtoupper($liquidation->propietario?->nombre_completo ?? '', 'UTF-8') }}</div>
        </td>
        <td>
            FIRMA Y SELLO — {{ mb_strtoupper($company?->razon_social ?? 'SERVIARRENDAR SAS', 'UTF-8') }}<br>&nbsp;
            <div class="quien">Elaborado por: {{ $elaboradoPor ?? 'Sistema' }}</div>
        </td>
    </tr>
</table>

<div class="pie">
    <div class="pie-linea"></div>
    <div class="pie-texto">
        Documento generado electrónicamente por ServiArrendar ERP v1.0.0 &bull; Desarrollado por YarOM Technology &bull;<br>
        www.serviarrendar.com &bull; &copy; {{ now()->format('Y') }} Todos los derechos reservados.
    </div>
</div>

</body>
</html>
