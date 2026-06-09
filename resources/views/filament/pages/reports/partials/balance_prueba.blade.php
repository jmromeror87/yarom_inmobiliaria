@php $fmt = fn($v) => '$'.number_format((float)$v, 0, ',', '.'); @endphp
@php
$clases = [
    '1' => ['label'=>'ACTIVOS',                'bg'=>'#EFF6FF','border'=>'#BFDBFE','color'=>'#1E3A8A'],
    '2' => ['label'=>'PASIVOS',                'bg'=>'#FFF1F2','border'=>'#FECDD3','color'=>'#9F1239'],
    '3' => ['label'=>'PATRIMONIO',             'bg'=>'#F0FDF4','border'=>'#BBF7D0','color'=>'#14532D'],
    '4' => ['label'=>'INGRESOS',               'bg'=>'#F0FDF4','border'=>'#BBF7D0','color'=>'#064E3B'],
    '5' => ['label'=>'GASTOS',                 'bg'=>'#FFF7ED','border'=>'#FED7AA','color'=>'#7C2D12'],
    '8' => ['label'=>'CUENTAS ORDEN DEUDORAS', 'bg'=>'#EEF2FF','border'=>'#C7D2FE','color'=>'#312E81'],
    '9' => ['label'=>'CUENTAS ORDEN ACREEDORAS','bg'=>'#FAF5FF','border'=>'#DDD6FE','color'=>'#4C1D95'],
];
$cuentasPorClase = collect($data['cuentas'] ?? [])->groupBy('clase');
@endphp

@forelse($cuentasPorClase as $clase => $cuentas)
@php $info = $clases[(string)$clase] ?? ['label'=>"CLASE {$clase}",'bg'=>'#F8FAFC','border'=>'#E2E8F0','color'=>'#0F172A']; @endphp
<div style="background:{{ $info['bg'] }};border-left:4px solid {{ $info['color'] }};padding:8px 14px;margin:16px 0 0;border-radius:0 6px 0 0">
    <span style="font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:{{ $info['color'] }}">Clase {{ $clase }} — {{ $info['label'] }}</span>
</div>
<table style="width:100%;border-collapse:collapse">
    <thead><tr style="background:{{ $info['bg'] }};border-bottom:2px solid {{ $info['border'] }}">
        <th style="text-align:left;padding:6px 10px;font-size:9.5px;color:{{ $info['color'] }};font-weight:700;width:11%">Código</th>
        <th style="text-align:left;padding:6px 10px;font-size:9.5px;color:{{ $info['color'] }};font-weight:700;width:37%">Cuenta</th>
        <th style="text-align:right;padding:6px 10px;font-size:9.5px;color:{{ $info['color'] }};font-weight:700;width:13%">Mov. Débito</th>
        <th style="text-align:right;padding:6px 10px;font-size:9.5px;color:{{ $info['color'] }};font-weight:700;width:13%">Mov. Crédito</th>
        <th style="text-align:right;padding:6px 10px;font-size:9.5px;color:{{ $info['color'] }};font-weight:700;width:13%">Saldo Déb.</th>
        <th style="text-align:right;padding:6px 10px;font-size:9.5px;color:{{ $info['color'] }};font-weight:700;width:13%">Saldo Cred.</th>
    </tr></thead>
    <tbody>
        @foreach($cuentas as $r)
        <tr style="border-bottom:1px solid #F1F5F9">
            <td style="padding:7px 10px;font-family:monospace;font-size:10.5px;color:#475569;font-weight:600">{{ $r['codigo'] }}</td>
            <td style="padding:7px 10px;font-size:12px;color:#0F172A">{{ $r['nombre'] }}</td>
            <td style="text-align:right;padding:7px 10px;font-family:monospace;font-size:11px;color:#1E3A8A">{{ $r['debito'] > 0 ? $fmt($r['debito']) : '—' }}</td>
            <td style="text-align:right;padding:7px 10px;font-family:monospace;font-size:11px;color:#7F1D1D">{{ $r['credito'] > 0 ? $fmt($r['credito']) : '—' }}</td>
            <td style="text-align:right;padding:7px 10px;font-family:monospace;font-size:11px;color:#1E3A8A;font-weight:600">{{ $r['saldo_db'] > 0 ? $fmt($r['saldo_db']) : '' }}</td>
            <td style="text-align:right;padding:7px 10px;font-family:monospace;font-size:11px;color:#7F1D1D;font-weight:600">{{ $r['saldo_cr'] > 0 ? $fmt($r['saldo_cr']) : '' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@empty
<div style="text-align:center;padding:48px;color:#94A3B8"><div style="font-size:36px;margin-bottom:10px">📭</div>Sin movimientos contabilizados en el período.</div>
@endforelse

@if(!empty($data['cuentas']))
@php $cuadra = $data['cuadra'] ?? false; @endphp
<div style="display:flex;justify-content:space-between;align-items:center;margin-top:18px;padding:16px 18px;border-radius:10px;border:2px solid {{ $cuadra ? '#BBF7D0' : '#FECDD3' }};background:{{ $cuadra ? '#F0FDF4' : '#FFF1F2' }}">
    <div>
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#64748B;margin-bottom:8px">Verificación de cuadre contable</div>
        <div style="display:flex;gap:28px">
            <span style="font-size:13px;color:#0F172A">Total débitos: <strong style="font-family:monospace;color:#1E3A8A">{{ $fmt($data['total_debitos']) }}</strong></span>
            <span style="font-size:13px;color:#0F172A">Total créditos: <strong style="font-family:monospace;color:#7F1D1D">{{ $fmt($data['total_creditos']) }}</strong></span>
            @if(!$cuadra)<span style="font-size:13px;color:#DC2626">Diferencia: <strong>{{ $fmt($data['diferencia'] ?? 0) }}</strong></span>@endif
        </div>
    </div>
    <div style="font-size:20px;font-weight:900;color:{{ $cuadra ? '#16A34A' : '#DC2626' }}">{{ $cuadra ? '✅ CUADRA' : '❌ DESCUADRE' }}</div>
</div>
@endif
