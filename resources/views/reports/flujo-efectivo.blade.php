@extends('reports.layout')
@section('content')
@php $flujoNeto = $data['flujo_neto'] ?? 0; $saldoFinal = $data['saldo_final'] ?? 0; @endphp

<div style="display:flex;gap:16px">
    <div style="flex:1">
        <div class="section-title section-title-green">⬆️ &nbsp; ENTRADAS DE EFECTIVO</div>
        <table>
            <thead><tr><th>Concepto</th><th class="right" style="width:30%">Valor</th></tr></thead>
            <tbody>
                @forelse($data['detalle_entradas'] ?? [] as $r)
                <tr><td>{{ $r['concepto'] }}</td><td class="right money">${{ number_format($r['valor'],0,',','.') }}</td></tr>
                @empty
                <tr><td colspan="2" class="text-center" style="color:#94A3B8">Sin entradas en el período</td></tr>
                @endforelse
            </tbody>
            <tfoot><tr class="tr-total"><td>TOTAL ENTRADAS</td><td class="right money" style="color:#14532D">${{ number_format($data['total_entradas'] ?? 0,0,',','.') }}</td></tr></tfoot>
        </table>
    </div>
    <div style="flex:1">
        <div class="section-title section-title-red">⬇️ &nbsp; SALIDAS DE EFECTIVO</div>
        <table>
            <thead><tr><th>Concepto</th><th class="right" style="width:30%">Valor</th></tr></thead>
            <tbody>
                @forelse($data['detalle_salidas'] ?? [] as $r)
                <tr><td>{{ $r['concepto'] }}</td><td class="right money">${{ number_format($r['valor'],0,',','.') }}</td></tr>
                @empty
                <tr><td colspan="2" class="text-center" style="color:#94A3B8">Sin salidas en el período</td></tr>
                @endforelse
            </tbody>
            <tfoot><tr class="tr-total"><td>TOTAL SALIDAS</td><td class="right money" style="color:#7F1D1D">${{ number_format($data['total_salidas'] ?? 0,0,',','.') }}</td></tr></tfoot>
        </table>
    </div>
</div>

<table style="margin-top:16px">
    <tr style="background:#F1F5F9"><td style="padding:8px 12px;font-weight:700">Saldo inicial en bancos</td><td class="right money" style="padding:8px 12px;font-weight:700">${{ number_format($data['saldo_inicial'] ?? 0,0,',','.') }}</td></tr>
    <tr style="{{ $flujoNeto >= 0 ? 'background:#DCFCE7' : 'background:#FEE2E2' }}"><td style="padding:8px 12px;font-weight:900;font-size:12px">{{ $flujoNeto >= 0 ? '📈' : '📉' }} &nbsp; Flujo neto del período</td><td class="right money" style="font-size:14px;font-weight:900;padding:8px 12px;color:{{ $flujoNeto >= 0 ? '#14532D' : '#7F1D1D' }}">${{ number_format($flujoNeto,0,',','.') }}</td></tr>
    <tr style="{{ $saldoFinal >= 0 ? 'background:#DBEAFE' : 'background:#FEE2E2' }}"><td style="padding:10px 12px;font-weight:900;font-size:13px">💰 &nbsp; Saldo final en bancos</td><td class="right money" style="font-size:16px;font-weight:900;padding:10px 12px;color:{{ $saldoFinal >= 0 ? '#1E3A8A' : '#7F1D1D' }}">${{ number_format($saldoFinal,0,',','.') }}</td></tr>
</table>
@endsection
