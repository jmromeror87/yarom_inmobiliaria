@php $fmt = fn($v) => '$'.number_format((float)$v,0,',','.'); @endphp

{{-- INGRESOS --}}
<div style="font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#14532D;padding:10px 0 8px;border-bottom:2px solid #DCFCE7;margin-bottom:8px">📈 Ingresos Operacionales</div>
<table style="width:100%;border-collapse:collapse;margin-bottom:16px">
    <thead><tr style="background:#F0FDF4">
        <th style="text-align:left;padding:6px 8px;font-size:10px;color:#64748B;font-weight:700;width:10%">Código</th>
        <th style="text-align:left;padding:6px 8px;font-size:10px;color:#64748B;font-weight:700;width:50%">Cuenta</th>
        <th style="text-align:right;padding:6px 8px;font-size:10px;color:#64748B;font-weight:700;width:13%">Débitos</th>
        <th style="text-align:right;padding:6px 8px;font-size:10px;color:#64748B;font-weight:700;width:13%">Créditos</th>
        <th style="text-align:right;padding:6px 8px;font-size:10px;color:#64748B;font-weight:700;width:14%">Saldo</th>
    </tr></thead>
    <tbody>
        @forelse($data['ingresos'] ?? [] as $r)
        <tr style="border-bottom:1px solid #F1F5F9">
            <td style="padding:6px 8px;font-family:monospace;font-size:9px;color:#64748B">{{ $r['codigo'] }}</td>
            <td style="padding:6px 8px;font-size:12px">{{ $r['nombre'] }}</td>
            <td style="text-align:right;padding:6px 8px;font-family:monospace;font-size:11px">{{ $fmt($r['debito']) }}</td>
            <td style="text-align:right;padding:6px 8px;font-family:monospace;font-size:11px">{{ $fmt($r['credito']) }}</td>
            <td style="text-align:right;padding:6px 8px;font-family:monospace;font-size:12px;font-weight:700;color:#16A34A">{{ $fmt($r['saldo']) }}</td>
        </tr>
        @empty
        <tr><td colspan="5" style="text-align:center;padding:16px;color:#94A3B8">Sin ingresos en el período</td></tr>
        @endforelse
    </tbody>
    <tfoot><tr style="background:#DCFCE7;font-weight:900">
        <td colspan="4" style="text-align:right;padding:8px;font-size:12px;color:#14532D">TOTAL INGRESOS</td>
        <td style="text-align:right;padding:8px;font-family:monospace;font-size:13px;color:#14532D">{{ $fmt($data['total_ingresos']) }}</td>
    </tr></tfoot>
</table>

{{-- GASTOS --}}
<div style="font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#DC2626;padding:10px 0 8px;border-bottom:2px solid #FEE2E2;margin-bottom:8px">📉 Gastos Operacionales</div>
<table style="width:100%;border-collapse:collapse;margin-bottom:16px">
    <thead><tr style="background:#FFF1F2">
        <th style="text-align:left;padding:6px 8px;font-size:10px;color:#64748B;font-weight:700;width:10%">Código</th>
        <th style="text-align:left;padding:6px 8px;font-size:10px;color:#64748B;font-weight:700;width:50%">Cuenta</th>
        <th style="text-align:right;padding:6px 8px;font-size:10px;color:#64748B;font-weight:700;width:13%">Débitos</th>
        <th style="text-align:right;padding:6px 8px;font-size:10px;color:#64748B;font-weight:700;width:13%">Créditos</th>
        <th style="text-align:right;padding:6px 8px;font-size:10px;color:#64748B;font-weight:700;width:14%">Saldo</th>
    </tr></thead>
    <tbody>
        @forelse($data['gastos'] ?? [] as $r)
        <tr style="border-bottom:1px solid #F1F5F9">
            <td style="padding:6px 8px;font-family:monospace;font-size:9px;color:#64748B">{{ $r['codigo'] }}</td>
            <td style="padding:6px 8px;font-size:12px">{{ $r['nombre'] }}</td>
            <td style="text-align:right;padding:6px 8px;font-family:monospace;font-size:11px">{{ $fmt($r['debito']) }}</td>
            <td style="text-align:right;padding:6px 8px;font-family:monospace;font-size:11px">{{ $fmt($r['credito']) }}</td>
            <td style="text-align:right;padding:6px 8px;font-family:monospace;font-size:12px;font-weight:700;color:#DC2626">{{ $fmt($r['saldo']) }}</td>
        </tr>
        @empty
        <tr><td colspan="5" style="text-align:center;padding:16px;color:#94A3B8">Sin gastos en el período</td></tr>
        @endforelse
    </tbody>
    <tfoot><tr style="background:#FEE2E2;font-weight:900">
        <td colspan="4" style="text-align:right;padding:8px;font-size:12px;color:#DC2626">TOTAL GASTOS</td>
        <td style="text-align:right;padding:8px;font-family:monospace;font-size:13px;color:#DC2626">{{ $fmt($data['total_gastos']) }}</td>
    </tr></tfoot>
</table>

{{-- RESULTADO --}}
@php $uOp = $data['utilidad_operacional'] ?? 0; @endphp
<div style="background:{{ $uOp >= 0 ? '#F0FDF4' : '#FFF1F2' }};border:2px solid {{ $uOp >= 0 ? '#BBF7D0' : '#FECDD3' }};border-radius:12px;padding:18px 24px;display:flex;justify-content:space-between;align-items:center;margin-top:8px">
    <div>
        <div style="font-size:11px;font-weight:700;text-transform:uppercase;color:#64748B;letter-spacing:.06em">Utilidad Operacional del Período</div>
        <div style="display:flex;gap:24px;margin-top:8px">
            <span style="font-size:11px;color:#64748B">Margen bruto: <strong style="color:{{ $uOp >= 0 ? '#16A34A' : '#DC2626' }}">{{ $data['margen_bruto'] ?? 0 }}%</strong></span>
            <span style="font-size:11px;color:#64748B">Margen operacional: <strong style="color:{{ $uOp >= 0 ? '#16A34A' : '#DC2626' }}">{{ $data['margen_operacional'] ?? 0 }}%</strong></span>
        </div>
    </div>
    <div style="font-size:28px;font-weight:900;color:{{ $uOp >= 0 ? '#16A34A' : '#DC2626' }};font-family:monospace">
        {{ $uOp >= 0 ? '' : '-' }}${{ number_format(abs($uOp), 0, ',', '.') }}
    </div>
</div>
