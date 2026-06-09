@php $fmt = fn($v) => '$'.number_format((float)$v, 0, ',', '.'); @endphp

<div style="display:grid;grid-template-columns:3fr 2fr;gap:20px">
<div>
  <div style="font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#1E3A8A;padding:8px 0;border-bottom:2px solid #BFDBFE;margin-bottom:8px">📊 Ingresos por tipo de servicio</div>
  <table style="width:100%;border-collapse:collapse">
    <thead><tr style="background:#EFF6FF"><th style="text-align:left;padding:6px 10px;font-size:9.5px;color:#1E3A8A;font-weight:700;width:12%">Cuenta</th><th style="text-align:left;padding:6px 10px;font-size:9.5px;color:#1E3A8A;font-weight:700">Descripción</th><th style="text-align:right;padding:6px 10px;font-size:9.5px;color:#1E3A8A;font-weight:700;width:12%">N°</th><th style="text-align:right;padding:6px 10px;font-size:9.5px;color:#1E3A8A;font-weight:700;width:18%">Total</th></tr></thead>
    <tbody>
      @foreach($data['por_cuenta'] ?? [] as $r)
      <tr style="border-bottom:1px solid #F1F5F9"><td style="padding:7px 10px;font-family:monospace;font-size:10px;color:#64748B">{{ $r['cuenta'] }}</td><td style="padding:7px 10px;font-size:12px;font-weight:600">{{ $r['nombre'] }}</td><td style="text-align:right;padding:7px 10px;font-size:12px">{{ $r['cantidad'] }}</td><td style="text-align:right;padding:7px 10px;font-family:monospace;font-size:13px;font-weight:700;color:#1E3A8A">{{ $fmt($r['total']) }}</td></tr>
      @endforeach
    </tbody>
    <tfoot><tr style="background:#DBEAFE"><td colspan="3" style="text-align:right;padding:8px 10px;font-size:12px;font-weight:700;color:#1E3A8A">TOTAL INGRESOS</td><td style="text-align:right;padding:8px 10px;font-family:monospace;font-size:14px;font-weight:900;color:#1E3A8A">{{ $fmt($data['total_ingresos'] ?? 0) }}</td></tr></tfoot>
  </table>
</div>
<div>
  <div style="font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#064E3B;padding:8px 0;border-bottom:2px solid #A7F3D0;margin-bottom:8px">📅 Evolución mensual</div>
  @php $max = collect($data['por_mes'] ?? [])->max('total'); @endphp
  @foreach($data['por_mes'] ?? [] as $r)
  @php $pct = $max > 0 ? round(($r['total']/$max)*100) : 0; @endphp
  <div style="margin-bottom:8px">
    <div style="display:flex;justify-content:space-between;margin-bottom:3px"><span style="font-size:11px;font-weight:600;color:#0F172A">{{ $r['mes'] }}</span><span style="font-family:monospace;font-size:11px;font-weight:700;color:#16A34A">{{ $fmt($r['total']) }}</span></div>
    <div style="height:6px;background:#E2E8F0;border-radius:99px;overflow:hidden"><div style="width:{{ $pct }}%;height:100%;background:linear-gradient(90deg,#1E3A8A,#16A34A);border-radius:99px"></div></div>
  </div>
  @endforeach
</div>
</div>

<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-top:20px">
    @foreach([['🏠','Comisiones arrend.','total_comision','#DBEAFE','#1E3A8A'],['📋','Ingr. administración','total_admon','#EDE9FE','#4C1D95'],['⏰','Intereses mora','total_mora','#FED7AA','#7C2D12']] as [$icon,$lbl,$key,$bg,$fg])
    <div style="background:{{ $bg }};border-radius:10px;padding:16px;text-align:center">
        <div style="font-size:24px">{{ $icon }}</div>
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;color:{{ $fg }};margin-top:6px;letter-spacing:.06em">{{ $lbl }}</div>
        <div style="font-size:18px;font-weight:900;font-family:monospace;color:{{ $fg }};margin-top:4px">{{ $fmt($data[$key] ?? 0) }}</div>
    </div>
    @endforeach
</div>
