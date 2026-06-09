@extends('reports.layout')
@section('content')
@foreach($data['por_rango'] ?? [] as $rango)
@if(count($rango['facturas'] ?? []) > 0)
<div class="section-title" style="background:{{ $rango['color'] }}">
    📊 &nbsp; {{ $rango['rango'] }} &nbsp;—&nbsp; Provisión: {{ $rango['pct_provision'] }}%
</div>
<table>
    <thead>
        <tr>
            <th style="width:11%">N° Factura</th><th style="width:28%">Arrendatario</th><th style="width:9%">Inmueble</th>
            <th class="right" style="width:9%">Días</th><th class="right" style="width:13%">Saldo</th>
            <th class="right" style="width:13%">Mora</th><th class="right" style="width:13%">Provisión</th>
            <th class="right" style="width:4%">%</th>
        </tr>
    </thead>
    <tbody>
        @foreach($rango['facturas'] as $f)
        <tr>
            <td style="font-family:monospace;font-weight:600;color:#1E3A8A">{{ $f['numero'] }}</td>
            <td>{{ $f['arrendatario'] }}</td>
            <td class="center">{{ $f['inmueble'] ?? '—' }}</td>
            <td class="right" style="color:{{ $rango['color'] }};font-weight:700">{{ $f['dias'] }}</td>
            <td class="right money">${{ number_format($f['saldo'],0,',','.') }}</td>
            <td class="right money" style="color:#DC2626">${{ number_format($f['mora'],0,',','.') }}</td>
            <td class="right money" style="color:#7F1D1D;font-weight:700">${{ number_format($f['provision'],0,',','.') }}</td>
            <td class="right" style="font-size:8.5px;color:#64748B">{{ $rango['pct_provision'] }}%</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr class="tr-total">
            <td colspan="4" class="right">SUBTOTAL</td>
            <td class="right money">${{ number_format($rango['total_saldo'],0,',','.') }}</td>
            <td></td>
            <td class="right money" style="color:#7F1D1D">${{ number_format($rango['total_prov'],0,',','.') }}</td>
            <td></td>
        </tr>
    </tfoot>
</table>
@endif
@endforeach

<table style="margin-top:12px">
    <tr style="background:#0F172A;height:36px">
        <td style="color:#fff;font-weight:900;font-size:12px;padding:0 12px" colspan="2">TOTAL CARTERA VENCIDA</td>
        <td class="right money" style="color:#60A5FA;font-size:14px;font-weight:900;padding:0 12px">${{ number_format($data['total_saldo'],0,',','.') }}</td>
        <td style="color:#fff;font-weight:900;font-size:12px;padding:0 12px">PROVISIÓN REQUERIDA</td>
        <td class="right money" style="color:#FCA5A5;font-size:14px;font-weight:900;padding:0 12px">${{ number_format($data['total_provision'],0,',','.') }}</td>
    </tr>
</table>
<div class="nota">Provisión calculada según tabla de antigüedad NIIF Pymes + Circular Externa SFC adaptada para empresas comerciales (0-30d: 0% | 31-60d: 5% | 61-90d: 10% | 91-180d: 15% | 181-360d: 33% | +360d: 100%).</div>
@endsection
