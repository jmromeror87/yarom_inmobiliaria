@php $fmt = fn($v) => '$'.number_format((float)$v, 0, ',', '.'); @endphp

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div>
  <div style="background:#F0FDF4;border-left:4px solid #16A34A;padding:8px 14px;font-size:11px;font-weight:800;text-transform:uppercase;color:#16A34A;border-radius:6px 6px 0 0">⬆️ Entradas de Efectivo</div>
  <table style="width:100%;border-collapse:collapse">
    <tbody>
      @forelse($data['detalle_entradas'] ?? [] as $r)
      <tr style="border-bottom:1px solid #F1F5F9"><td style="padding:7px 10px;font-size:12px">{{ $r['concepto'] }}</td><td style="text-align:right;padding:7px 10px;font-family:monospace;font-size:12px;font-weight:600;color:#16A34A">{{ $fmt($r['valor']) }}</td></tr>
      @empty<tr><td colspan="2" style="padding:14px;text-align:center;color:#94A3B8">Sin entradas en el período</td></tr>
      @endforelse
    </tbody>
    <tfoot><tr style="background:#DCFCE7"><td style="padding:8px 10px;font-weight:700;font-size:12px;color:#14532D">Total entradas</td><td style="text-align:right;padding:8px 10px;font-family:monospace;font-size:13px;font-weight:900;color:#14532D">{{ $fmt($data['total_entradas'] ?? 0) }}</td></tr></tfoot>
  </table>
</div>
<div>
  <div style="background:#FFF1F2;border-left:4px solid #DC2626;padding:8px 14px;font-size:11px;font-weight:800;text-transform:uppercase;color:#DC2626;border-radius:6px 6px 0 0">⬇️ Salidas de Efectivo</div>
  <table style="width:100%;border-collapse:collapse">
    <tbody>
      @forelse($data['detalle_salidas'] ?? [] as $r)
      <tr style="border-bottom:1px solid #F1F5F9"><td style="padding:7px 10px;font-size:12px">{{ $r['concepto'] }}</td><td style="text-align:right;padding:7px 10px;font-family:monospace;font-size:12px;font-weight:600;color:#DC2626">{{ $fmt($r['valor']) }}</td></tr>
      @empty<tr><td colspan="2" style="padding:14px;text-align:center;color:#94A3B8">Sin salidas en el período</td></tr>
      @endforelse
    </tbody>
    <tfoot><tr style="background:#FEE2E2"><td style="padding:8px 10px;font-weight:700;font-size:12px;color:#7F1D1D">Total salidas</td><td style="text-align:right;padding:8px 10px;font-family:monospace;font-size:13px;font-weight:900;color:#7F1D1D">{{ $fmt($data['total_salidas'] ?? 0) }}</td></tr></tfoot>
  </table>
</div>
</div>

@php $flujo = $data['flujo_neto'] ?? 0; $final = $data['saldo_final'] ?? 0; @endphp
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-top:16px">
    <div style="background:#F1F5F9;border-radius:10px;padding:14px;text-align:center">
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;color:#64748B;margin-bottom:6px">Saldo inicial bancos</div>
        <div style="font-size:18px;font-weight:900;font-family:monospace;color:#0F172A">{{ $fmt($data['saldo_inicial'] ?? 0) }}</div>
    </div>
    <div style="background:{{ $flujo >= 0 ? '#F0FDF4' : '#FFF1F2' }};border-radius:10px;padding:14px;text-align:center;border:2px solid {{ $flujo >= 0 ? '#BBF7D0' : '#FECDD3' }}">
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;color:{{ $flujo >= 0 ? '#14532D' : '#7F1D1D' }};margin-bottom:6px">{{ $flujo >= 0 ? '📈' : '📉' }} Flujo neto del período</div>
        <div style="font-size:20px;font-weight:900;font-family:monospace;color:{{ $flujo >= 0 ? '#16A34A' : '#DC2626' }}">{{ $fmt($flujo) }}</div>
    </div>
    <div style="background:{{ $final >= 0 ? '#EFF6FF' : '#FFF1F2' }};border-radius:10px;padding:14px;text-align:center;border:2px solid {{ $final >= 0 ? '#BFDBFE' : '#FECDD3' }}">
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;color:{{ $final >= 0 ? '#1E3A8A' : '#7F1D1D' }};margin-bottom:6px">💰 Saldo final bancos</div>
        <div style="font-size:20px;font-weight:900;font-family:monospace;color:{{ $final >= 0 ? '#1E3A8A' : '#DC2626' }}">{{ $fmt($final) }}</div>
    </div>
</div>
