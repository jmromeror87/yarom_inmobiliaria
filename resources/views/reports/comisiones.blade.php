@extends('reports.layout')
@section('content')
<div class="section-title">📊 &nbsp; INGRESOS POR TIPO DE SERVICIO</div>
<table>
    <thead><tr><th style="width:10%">Cuenta</th><th style="width:55%">Descripción</th><th class="right" style="width:15%">Cantidad</th><th class="right" style="width:20%">Total</th></tr></thead>
    <tbody>
        @foreach($data['por_cuenta'] ?? [] as $r)
        <tr><td style="font-family:monospace;font-size:9px;color:#64748B">{{ $r['cuenta'] }}</td><td style="font-weight:600">{{ $r['nombre'] }}</td><td class="right">{{ $r['cantidad'] }}</td><td class="right money" style="font-weight:700;color:#1E3A8A">${{ number_format($r['total'],0,',','.') }}</td></tr>
        @endforeach
    </tbody>
    <tfoot><tr class="tr-total"><td colspan="3" class="right">TOTAL INGRESOS</td><td class="right money" style="color:#14532D">${{ number_format($data['total_ingresos'],0,',','.') }}</td></tr></tfoot>
</table>

<div class="section-title section-title-blue" style="margin-top:16px">📅 &nbsp; EVOLUCIÓN MENSUAL DE INGRESOS</div>
<table>
    <thead><tr><th style="width:30%">Mes</th><th class="right" style="width:70%">Total ingresos</th></tr></thead>
    <tbody>
        @foreach($data['por_mes'] ?? [] as $r)
        @php $max = collect($data['por_mes'])->max('total'); $pct = $max > 0 ? round(($r['total']/$max)*100) : 0; @endphp
        <tr>
            <td style="font-weight:600">{{ $r['mes'] }}</td>
            <td class="right">
                <div style="display:flex;align-items:center;justify-content:flex-end;gap:8px">
                    <div style="flex:1;max-width:200px;height:8px;background:#E2E8F0;border-radius:4px;overflow:hidden">
                        <div style="width:{{ $pct }}%;height:100%;background:#1E3A8A;border-radius:4px"></div>
                    </div>
                    <span class="money" style="font-weight:700;color:#1E3A8A;min-width:80px;text-align:right">${{ number_format($r['total'],0,',','.') }}</span>
                </div>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

<div style="display:flex;gap:12px;margin-top:14px">
    @foreach([['Comisiones arrend.','total_comision','#DBEAFE','#1E3A8A'],['Ingr. administración','total_admon','#EDE9FE','#4C1D95'],['Intereses mora','total_mora','#FED7AA','#7C2D12']] as [$lbl,$key,$bg,$fg])
    <div style="flex:1;background:{{ $bg }};border-radius:8px;padding:12px;text-align:center">
        <div style="font-size:8.5px;font-weight:700;text-transform:uppercase;color:{{ $fg }};letter-spacing:.06em">{{ $lbl }}</div>
        <div style="font-size:16px;font-weight:900;color:{{ $fg }};margin-top:4px">${{ number_format($data[$key]??0,0,',','.') }}</div>
    </div>
    @endforeach
</div>
@endsection
