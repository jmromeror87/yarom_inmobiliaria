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
            <th style="width:10%">Código</th><th style="width:38%">Cuenta</th>
            <th class="right" style="width:13%">Mov. Débito</th><th class="right" style="width:13%">Mov. Crédito</th>
            <th class="right" style="width:13%">Saldo Déb.</th><th class="right" style="width:13%">Saldo Cred.</th>
        </tr>
    </thead>
    <tbody>
        @foreach($cuentas as $r)
        <tr>
            <td style="font-family:monospace;font-size:8.5px;color:#64748B">{{ $r['codigo'] }}</td>
            <td>{{ $r['nombre'] }}</td>
            <td class="right money">{{ $r['debito'] > 0 ? number_format($r['debito'],0,',','.') : '' }}</td>
            <td class="right money">{{ $r['credito'] > 0 ? number_format($r['credito'],0,',','.') : '' }}</td>
            <td class="right money" style="color:#1E3A8A">{{ $r['saldo_db'] > 0 ? number_format($r['saldo_db'],0,',','.') : '' }}</td>
            <td class="right money" style="color:#7F1D1D">{{ $r['saldo_cr'] > 0 ? number_format($r['saldo_cr'],0,',','.') : '' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endforeach

@php $cuadra = $data['cuadra'] ?? false; @endphp
<table style="margin-top:8px">
    <tfoot>
        <tr class="tr-total">
            <td style="width:10%"></td><td style="width:38%">TOTALES DEL PERÍODO</td>
            <td class="right money" style="width:13%">${{ number_format($data['total_debitos'],0,',','.') }}</td>
            <td class="right money" style="width:13%">${{ number_format($data['total_creditos'],0,',','.') }}</td>
            <td colspan="2" class="text-center" style="font-size:12px;color:{{ $cuadra ? '#14532D' : '#DC2626' }}">{{ $cuadra ? '✅ CUADRA' : '❌ Dif: $'.number_format($data['diferencia']??0,0,',','.') }}</td>
        </tr>
    </tfoot>
</table>
@endsection
