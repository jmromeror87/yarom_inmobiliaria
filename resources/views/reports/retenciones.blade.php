@extends('reports.layout')
@section('content')
<div class="section-title section-title-red">📤 &nbsp; RETENCIONES PRACTICADAS POR LA EMPRESA</div>
<table>
    <thead><tr><th style="width:30%">Tercero</th><th style="width:14%">NIT</th><th style="width:9%">Cuenta</th><th style="width:33%">Descripción</th><th class="right" style="width:14%">Valor</th></tr></thead>
    <tbody>
        @forelse($data['practicadas'] ?? [] as $r)
        <tr><td style="font-weight:600">{{ $r['tercero'] }}</td><td style="font-family:monospace">{{ $r['nit'] }}</td><td style="font-family:monospace;font-size:8.5px">{{ $r['cuenta'] }}</td><td style="font-size:9px">{{ $r['cuenta_nom'] }}</td><td class="right money" style="color:#DC2626;font-weight:700">${{ number_format($r['valor'],0,',','.') }}</td></tr>
        @empty<tr><td colspan="5" class="text-center" style="color:#94A3B8;padding:14px">Sin retenciones practicadas en el período</td></tr>
        @endforelse
    </tbody>
    <tfoot><tr class="tr-total"><td colspan="4" class="right">TOTAL RETENCIONES PRACTICADAS</td><td class="right money" style="color:#7F1D1D">${{ number_format($data['total_practicadas'],0,',','.') }}</td></tr></tfoot>
</table>

<div class="section-title section-title-green" style="margin-top:16px">📥 &nbsp; RETENCIONES A FAVOR (PRACTICADAS POR TERCEROS)</div>
<table>
    <thead><tr><th style="width:44%">Tercero (Retenedor)</th><th style="width:14%">NIT</th><th class="right" style="width:14%">Base</th><th class="right" style="width:14%">Valor retenido</th></tr></thead>
    <tbody>
        @forelse($data['a_favor'] ?? [] as $r)
        <tr><td style="font-weight:600">{{ $r['tercero'] }}</td><td style="font-family:monospace">{{ $r['nit'] }}</td><td class="right money">${{ number_format($r['base'],0,',','.') }}</td><td class="right money" style="color:#16A34A;font-weight:700">${{ number_format($r['valor'],0,',','.') }}</td></tr>
        @empty<tr><td colspan="4" class="text-center" style="color:#94A3B8;padding:14px">Sin retenciones a favor en el período</td></tr>
        @endforelse
    </tbody>
    <tfoot><tr class="tr-total"><td colspan="3" class="right">TOTAL A FAVOR</td><td class="right money" style="color:#14532D">${{ number_format($data['total_a_favor'],0,',','.') }}</td></tr></tfoot>
</table>

@php $neto = $data['neto'] ?? 0; @endphp
<table style="margin-top:10px">
    <tr style="background:{{ $neto >= 0 ? '#FEE2E2' : '#DCFCE7' }};height:34px">
        <td style="font-weight:900;font-size:13px;padding:0 12px">⚖️ &nbsp; NETO A PAGAR / CRÉDITO FISCAL (Form. 350)</td>
        <td class="right money" style="font-size:16px;font-weight:900;padding:0 12px;color:{{ $neto >= 0 ? '#DC2626' : '#16A34A' }}">${{ number_format($neto,0,',','.') }}</td>
    </tr>
</table>
@endsection
