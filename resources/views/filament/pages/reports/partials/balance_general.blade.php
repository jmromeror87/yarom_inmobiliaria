@php $fmt = fn($v) => '$'.number_format((float)$v, 0, ',', '.'); @endphp

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px">
{{-- ACTIVOS --}}
<div>
  <div style="background:#EFF6FF;border-left:4px solid #1E3A8A;padding:8px 14px;font-size:11px;font-weight:800;text-transform:uppercase;color:#1E3A8A;border-radius:0 6px 0 0">💵 Activos Corrientes</div>
  <table style="width:100%;border-collapse:collapse">
    <tbody>
      @foreach($data['activos_corrientes'] ?? [] as $r)
      <tr style="border-bottom:1px solid #F1F5F9"><td style="padding:6px 10px;font-family:monospace;font-size:10px;color:#64748B">{{ $r['codigo'] }}</td><td style="padding:6px 10px;font-size:12px">{{ $r['nombre'] }}</td><td style="text-align:right;padding:6px 10px;font-family:monospace;font-size:12px;font-weight:600;color:#1E3A8A">{{ $fmt($r['saldo']) }}</td></tr>
      @endforeach
    </tbody>
    <tfoot><tr style="background:#DBEAFE"><td colspan="2" style="text-align:right;padding:7px 10px;font-size:11px;font-weight:700;color:#1E3A8A">Subtotal Corrientes</td><td style="text-align:right;padding:7px 10px;font-family:monospace;font-weight:900;color:#1E3A8A">{{ $fmt($data['total_activos_corrientes'] ?? 0) }}</td></tr></tfoot>
  </table>
  @if(count($data['activos_no_corrientes'] ?? []) > 0)
  <div style="background:#EFF6FF;border-left:4px solid #1D4ED8;padding:8px 14px;font-size:11px;font-weight:800;text-transform:uppercase;color:#1D4ED8;margin-top:10px">🏢 Activos No Corrientes</div>
  <table style="width:100%;border-collapse:collapse">
    <tbody>
      @foreach($data['activos_no_corrientes'] as $r)
      <tr style="border-bottom:1px solid #F1F5F9"><td style="padding:6px 10px;font-family:monospace;font-size:10px;color:#64748B">{{ $r['codigo'] }}</td><td style="padding:6px 10px;font-size:12px">{{ $r['nombre'] }}</td><td style="text-align:right;padding:6px 10px;font-family:monospace;font-size:12px;font-weight:600;color:#1D4ED8">{{ $fmt($r['saldo']) }}</td></tr>
      @endforeach
    </tbody>
  </table>
  @endif
  <div style="background:#0F172A;padding:10px 14px;border-radius:0 0 8px 8px;display:flex;justify-content:space-between;margin-top:2px">
    <span style="color:#fff;font-weight:900;font-size:13px">TOTAL ACTIVOS</span>
    <span style="color:#60A5FA;font-family:monospace;font-size:15px;font-weight:900">{{ $fmt($data['total_activos'] ?? 0) }}</span>
  </div>
</div>

{{-- PASIVOS + PATRIMONIO --}}
<div>
  <div style="background:#FFF1F2;border-left:4px solid #DC2626;padding:8px 14px;font-size:11px;font-weight:800;text-transform:uppercase;color:#DC2626;border-radius:0 6px 0 0">📋 Pasivos Corrientes</div>
  <table style="width:100%;border-collapse:collapse">
    <tbody>
      @foreach($data['pasivos_corrientes'] ?? [] as $r)
      <tr style="border-bottom:1px solid #F1F5F9"><td style="padding:6px 10px;font-family:monospace;font-size:10px;color:#64748B">{{ $r['codigo'] }}</td><td style="padding:6px 10px;font-size:12px">{{ $r['nombre'] }}</td><td style="text-align:right;padding:6px 10px;font-family:monospace;font-size:12px;font-weight:600;color:#DC2626">{{ $fmt($r['saldo']) }}</td></tr>
      @endforeach
    </tbody>
    <tfoot><tr style="background:#FEE2E2"><td colspan="2" style="text-align:right;padding:7px 10px;font-size:11px;font-weight:700;color:#DC2626">Subtotal Pasivos</td><td style="text-align:right;padding:7px 10px;font-family:monospace;font-weight:900;color:#DC2626">{{ $fmt($data['total_pasivos'] ?? 0) }}</td></tr></tfoot>
  </table>

  <div style="background:#F0FDF4;border-left:4px solid #16A34A;padding:8px 14px;font-size:11px;font-weight:800;text-transform:uppercase;color:#16A34A;margin-top:10px">💎 Patrimonio</div>
  <table style="width:100%;border-collapse:collapse">
    <tbody>
      @forelse($data['patrimonio'] ?? [] as $r)
      <tr style="border-bottom:1px solid #F1F5F9"><td style="padding:6px 10px;font-family:monospace;font-size:10px;color:#64748B">{{ $r['codigo'] }}</td><td style="padding:6px 10px;font-size:12px">{{ $r['nombre'] }}</td><td style="text-align:right;padding:6px 10px;font-family:monospace;font-size:12px;font-weight:600;color:#16A34A">{{ $fmt($r['saldo']) }}</td></tr>
      @empty<tr><td colspan="3" style="padding:12px;text-align:center;color:#94A3B8;font-size:12px">Sin cuentas de patrimonio con movimiento</td></tr>
      @endforelse
    </tbody>
    <tfoot><tr style="background:#DCFCE7"><td colspan="2" style="text-align:right;padding:7px 10px;font-size:11px;font-weight:700;color:#16A34A">Total Patrimonio</td><td style="text-align:right;padding:7px 10px;font-family:monospace;font-weight:900;color:#16A34A">{{ $fmt($data['total_patrimonio'] ?? 0) }}</td></tr></tfoot>
  </table>

  <div style="background:#0F172A;padding:10px 14px;border-radius:0 0 8px 8px;display:flex;justify-content:space-between;margin-top:2px">
    <span style="color:#fff;font-weight:900;font-size:13px">PASIVOS + PATRIMONIO</span>
    <span style="color:#A7F3D0;font-family:monospace;font-size:15px;font-weight:900">{{ $fmt(($data['total_pasivos'] ?? 0) + ($data['total_patrimonio'] ?? 0)) }}</span>
  </div>
</div>
</div>

@php $cuadra = $data['ecuacion_cuadra'] ?? false; @endphp
<div style="margin-top:16px;padding:14px 18px;border-radius:10px;border:2px solid {{ $cuadra ? '#BBF7D0' : '#FECDD3' }};background:{{ $cuadra ? '#F0FDF4' : '#FFF1F2' }};display:flex;justify-content:space-between;align-items:center">
    <span style="font-size:13px;font-weight:700;color:{{ $cuadra ? '#14532D' : '#7F1D1D' }}">{{ $cuadra ? '✅' : '❌' }} Ecuación: Activos = Pasivos + Patrimonio</span>
    <span style="font-size:14px;font-weight:900;color:{{ $cuadra ? '#16A34A' : '#DC2626' }}">{{ $cuadra ? 'CUADRA ✓' : 'DESCUADRE $'.number_format($data['diferencia']??0,0,',','.') }}</span>
</div>
