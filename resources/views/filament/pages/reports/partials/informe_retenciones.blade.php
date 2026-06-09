@php $fmt = fn($v) => '$'.number_format((float)$v, 0, ',', '.'); @endphp

<div style="font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#DC2626;padding:8px 0;border-bottom:2px solid #FEE2E2;margin-bottom:8px">📤 Retenciones practicadas por la empresa</div>
<table style="width:100%;border-collapse:collapse;margin-bottom:16px">
  <thead><tr style="background:#FFF1F2"><th style="text-align:left;padding:6px 10px;font-size:9.5px;color:#9F1239;font-weight:700;width:32%">Tercero</th><th style="text-align:left;padding:6px 10px;font-size:9.5px;color:#9F1239;font-weight:700;width:14%">NIT</th><th style="text-align:left;padding:6px 10px;font-size:9.5px;color:#9F1239;font-weight:700;width:10%">Cuenta</th><th style="text-align:left;padding:6px 10px;font-size:9.5px;color:#9F1239;font-weight:700;width:30%">Descripción</th><th style="text-align:right;padding:6px 10px;font-size:9.5px;color:#9F1239;font-weight:700;width:14%">Valor</th></tr></thead>
  <tbody>
    @forelse($data['practicadas'] ?? [] as $r)
    <tr style="border-bottom:1px solid #F1F5F9"><td style="padding:6px 10px;font-size:12px;font-weight:600">{{ $r['tercero'] }}</td><td style="padding:6px 10px;font-family:monospace;font-size:10.5px">{{ $r['nit'] }}</td><td style="padding:6px 10px;font-family:monospace;font-size:10px;color:#64748B">{{ $r['cuenta'] }}</td><td style="padding:6px 10px;font-size:11px">{{ $r['cuenta_nom'] }}</td><td style="text-align:right;padding:6px 10px;font-family:monospace;font-size:12px;font-weight:700;color:#DC2626">{{ $fmt($r['valor']) }}</td></tr>
    @empty<tr><td colspan="5" style="padding:16px;text-align:center;color:#94A3B8">Sin retenciones practicadas en el período</td></tr>
    @endforelse
  </tbody>
  <tfoot><tr style="background:#FEE2E2;font-weight:700"><td colspan="4" style="text-align:right;padding:8px 10px;font-size:12px;color:#7F1D1D">TOTAL PRACTICADAS</td><td style="text-align:right;padding:8px 10px;font-family:monospace;font-size:13px;color:#7F1D1D">{{ $fmt($data['total_practicadas'] ?? 0) }}</td></tr></tfoot>
</table>

<div style="font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#16A34A;padding:8px 0;border-bottom:2px solid #DCFCE7;margin-bottom:8px">📥 Retenciones a favor (practicadas por terceros)</div>
<table style="width:100%;border-collapse:collapse;margin-bottom:16px">
  <thead><tr style="background:#F0FDF4"><th style="text-align:left;padding:6px 10px;font-size:9.5px;color:#14532D;font-weight:700;width:46%">Tercero (Retenedor)</th><th style="text-align:left;padding:6px 10px;font-size:9.5px;color:#14532D;font-weight:700;width:14%">NIT</th><th style="text-align:right;padding:6px 10px;font-size:9.5px;color:#14532D;font-weight:700;width:14%">Base</th><th style="text-align:right;padding:6px 10px;font-size:9.5px;color:#14532D;font-weight:700;width:14%">Valor retenido</th></tr></thead>
  <tbody>
    @forelse($data['a_favor'] ?? [] as $r)
    <tr style="border-bottom:1px solid #F1F5F9"><td style="padding:6px 10px;font-size:12px;font-weight:600">{{ $r['tercero'] }}</td><td style="padding:6px 10px;font-family:monospace;font-size:10.5px">{{ $r['nit'] }}</td><td style="text-align:right;padding:6px 10px;font-family:monospace;font-size:11px">{{ $fmt($r['base'] ?? 0) }}</td><td style="text-align:right;padding:6px 10px;font-family:monospace;font-size:12px;font-weight:700;color:#16A34A">{{ $fmt($r['valor']) }}</td></tr>
    @empty<tr><td colspan="4" style="padding:16px;text-align:center;color:#94A3B8">Sin retenciones a favor en el período</td></tr>
    @endforelse
  </tbody>
  <tfoot><tr style="background:#DCFCE7;font-weight:700"><td colspan="3" style="text-align:right;padding:8px 10px;font-size:12px;color:#14532D">TOTAL A FAVOR</td><td style="text-align:right;padding:8px 10px;font-family:monospace;font-size:13px;color:#14532D">{{ $fmt($data['total_a_favor'] ?? 0) }}</td></tr></tfoot>
</table>

@php $neto = $data['neto'] ?? 0; @endphp
<div style="background:{{ $neto >= 0 ? '#FEE2E2' : '#DCFCE7' }};border-radius:10px;padding:16px 20px;display:flex;justify-content:space-between;align-items:center;border:2px solid {{ $neto >= 0 ? '#FECDD3' : '#BBF7D0' }}">
    <div><div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#64748B;margin-bottom:4px">Neto a pagar DIAN (Form. 350)</div><div style="font-size:12px;color:#475569">Retenciones practicadas menos retenciones a favor</div></div>
    <div style="font-size:24px;font-weight:900;font-family:monospace;color:{{ $neto >= 0 ? '#DC2626' : '#16A34A' }}">{{ $fmt($neto) }}</div>
</div>
