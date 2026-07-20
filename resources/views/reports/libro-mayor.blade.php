@extends('reports.layout')
@section('content')
@php
$clases = ['1'=>['ACTIVOS','#0C4A6E'],'2'=>['PASIVOS','#7F1D1D'],'3'=>['PATRIMONIO','#14532D'],'4'=>['INGRESOS','#064E3B'],'5'=>['GASTOS','#92400E'],'8'=>['CUENTAS ORDEN DEB.','#312E81'],'9'=>['CUENTAS ORDEN ACRE.','#4A044E']];
$porClase = collect($data['cuentas'] ?? [])->groupBy('clase');
@endphp

@foreach($porClase as $clase => $cuentas)
@php [$nombreClase, $color] = $clases[$clase] ?? ["CLASE {$clase}", '#0F172A']; @endphp
<div class="section-title" style="background:{{ $color }}">CLASE {{ $clase }} — {{ $nombreClase }}</div>
<table>
    <thead>
        <tr>
            <th style="width:10%">Código</th><th style="width:30%">Cuenta</th>
            <th class="right" style="width:15%">Saldo Inicial</th>
            <th class="right" style="width:15%">Débito</th><th class="right" style="width:15%">Crédito</th>
            <th class="right" style="width:15%">Saldo Final</th>
        </tr>
    </thead>
    <tbody>
        @foreach($cuentas as $r)
        <tr>
            <td style="font-family:monospace;font-size:8.5px;color:#64748B">{{ $r['codigo'] }}</td>
            <td>{{ $r['nombre'] }}</td>
            <td class="right money" style="color:#64748B">{{ number_format($r['saldo_inicial'],0,',','.') }}</td>
            <td class="right money">{{ $r['debito'] > 0 ? number_format($r['debito'],0,',','.') : '' }}</td>
            <td class="right money">{{ $r['credito'] > 0 ? number_format($r['credito'],0,',','.') : '' }}</td>
            <td class="right money" style="color:#0F172A;font-weight:700">{{ number_format($r['saldo_final'],0,',','.') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endforeach

@php $cuadra = $data['cuadra'] ?? false; @endphp
<table style="margin-top:8px">
    <tfoot>
        <tr class="tr-total">
            <td style="width:10%"></td><td style="width:30%">TOTALES DEL PERÍODO</td>
            <td style="width:15%"></td>
            <td class="right money" style="width:15%">${{ number_format($data['total_debitos'],0,',','.') }}</td>
            <td class="right money" style="width:15%">${{ number_format($data['total_creditos'],0,',','.') }}</td>
            <td class="text-center" style="width:15%;font-size:12px;color:{{ $cuadra ? '#14532D' : '#DC2626' }}">{{ $cuadra ? '✅ CUADRA' : '❌ Dif: $'.number_format($data['diferencia']??0,0,',','.') }}</td>
        </tr>
    </tfoot>
</table>
@endsection
