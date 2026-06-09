@extends('reports.layout')
@section('content')
<div class="section-title section-title-blue">📊 &nbsp; DETALLE MENSUAL DE IVA</div>
<table>
    <thead><tr><th style="width:22%">Período</th><th class="right" style="width:26%">Base comisiones</th><th class="right" style="width:26%">IVA generado 19%</th><th class="right" style="width:26%">Saldo a pagar</th></tr></thead>
    <tbody>
        @forelse($data['por_mes'] ?? [] as $r)
        <tr>
            <td style="font-weight:600">{{ $r['mes'] }}</td>
            <td class="right money">${{ number_format($r['iva_generado'] / 0.19,0,',','.') }}</td>
            <td class="right money" style="color:#1E3A8A;font-weight:700">${{ number_format($r['iva_generado'],0,',','.') }}</td>
            <td class="right money" style="color:#DC2626;font-weight:700">${{ number_format($r['saldo'],0,',','.') }}</td>
        </tr>
        @empty<tr><td colspan="4" class="text-center" style="color:#94A3B8;padding:14px">Sin movimiento de IVA en el período</td></tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr class="tr-total">
            <td>TOTALES</td>
            <td class="right money">${{ number_format($data['base_comisiones'],0,',','.') }}</td>
            <td class="right money" style="color:#1E3A8A">${{ number_format($data['iva_generado'],0,',','.') }}</td>
            <td class="right money" style="color:#DC2626">${{ number_format($data['saldo'],0,',','.') }}</td>
        </tr>
    </tfoot>
</table>

<div style="display:flex;gap:12px;margin-top:16px">
    <div style="flex:1;background:#F0F9FF;border:1.5px solid #BAE6FD;border-radius:10px;padding:14px;text-align:center">
        <div style="font-size:9px;font-weight:700;text-transform:uppercase;color:#0C4A6E">Base comisiones</div>
        <div style="font-size:18px;font-weight:900;color:#0284C7;margin-top:4px">${{ number_format($data['base_comisiones'],0,',','.') }}</div>
    </div>
    <div style="flex:1;background:#DBEAFE;border:1.5px solid #93C5FD;border-radius:10px;padding:14px;text-align:center">
        <div style="font-size:9px;font-weight:700;text-transform:uppercase;color:#1E3A8A">IVA generado (19%)</div>
        <div style="font-size:18px;font-weight:900;color:#1E3A8A;margin-top:4px">${{ number_format($data['iva_generado'],0,',','.') }}</div>
    </div>
    <div style="flex:1;background:#F0FDF4;border:1.5px solid #BBF7D0;border-radius:10px;padding:14px;text-align:center">
        <div style="font-size:9px;font-weight:700;text-transform:uppercase;color:#14532D">IVA descontable</div>
        <div style="font-size:18px;font-weight:900;color:#16A34A;margin-top:4px">${{ number_format($data['iva_descontable'],0,',','.') }}</div>
    </div>
    <div style="flex:1;background:#FEE2E2;border:1.5px solid #FCA5A5;border-radius:10px;padding:14px;text-align:center">
        <div style="font-size:9px;font-weight:700;text-transform:uppercase;color:#7F1D1D">Saldo a pagar DIAN</div>
        <div style="font-size:18px;font-weight:900;color:#DC2626;margin-top:4px">${{ number_format($data['saldo'],0,',','.') }}</div>
    </div>
</div>
<div class="nota" style="margin-top:14px">Según Estatuto Tributario Art. 485. IVA descontable sobre compras/servicios con tarifa. Para régimen cuatrimestral: declarar en Form. 300 dentro de los plazos DIAN según NIT.</div>
@endsection
