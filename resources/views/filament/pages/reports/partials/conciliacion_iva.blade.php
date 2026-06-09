@php $fmt = fn($v) => '$'.number_format((float)$v, 0, ',', '.'); @endphp

<table style="width:100%;border-collapse:collapse;margin-bottom:20px">
  <thead><tr style="background:#EFF6FF"><th style="text-align:left;padding:7px 10px;font-size:9.5px;color:#1E3A8A;font-weight:700;width:22%">Período</th><th style="text-align:right;padding:7px 10px;font-size:9.5px;color:#1E3A8A;font-weight:700;width:26%">Base comisiones</th><th style="text-align:right;padding:7px 10px;font-size:9.5px;color:#1E3A8A;font-weight:700;width:26%">IVA generado 19%</th><th style="text-align:right;padding:7px 10px;font-size:9.5px;color:#1E3A8A;font-weight:700;width:26%">Saldo a pagar</th></tr></thead>
  <tbody>
    @forelse($data['por_mes'] ?? [] as $r)
    <tr style="border-bottom:1px solid #F1F5F9">
      <td style="padding:7px 10px;font-size:12px;font-weight:600">{{ $r['mes'] }}</td>
      <td style="text-align:right;padding:7px 10px;font-family:monospace;font-size:12px">{{ $r['iva_generado'] > 0 ? $fmt($r['iva_generado'] / 0.19) : '—' }}</td>
      <td style="text-align:right;padding:7px 10px;font-family:monospace;font-size:12px;font-weight:600;color:#1E3A8A">{{ $fmt($r['iva_generado']) }}</td>
      <td style="text-align:right;padding:7px 10px;font-family:monospace;font-size:12px;font-weight:600;color:#DC2626">{{ $fmt($r['saldo']) }}</td>
    </tr>
    @empty<tr><td colspan="4" style="padding:16px;text-align:center;color:#94A3B8">Sin movimiento de IVA en el período seleccionado</td></tr>
    @endforelse
  </tbody>
  <tfoot><tr style="background:#DBEAFE;font-weight:700"><td style="padding:8px 10px;font-size:12px;color:#1E3A8A">TOTALES</td><td style="text-align:right;padding:8px 10px;font-family:monospace;font-size:12px;color:#1E3A8A">{{ $fmt($data['base_comisiones'] ?? 0) }}</td><td style="text-align:right;padding:8px 10px;font-family:monospace;font-size:13px;color:#1E3A8A">{{ $fmt($data['iva_generado'] ?? 0) }}</td><td style="text-align:right;padding:8px 10px;font-family:monospace;font-size:13px;color:#DC2626">{{ $fmt($data['saldo'] ?? 0) }}</td></tr></tfoot>
</table>

<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px">
    @foreach([['📊','Base comisiones','base_comisiones','#F0F9FF','#0C4A6E','#BAE6FD'],['📈','IVA generado 19%','iva_generado','#EFF6FF','#1E3A8A','#BFDBFE'],['📉','IVA descontable','iva_descontable','#F0FDF4','#14532D','#BBF7D0'],['💸','Saldo a pagar','saldo','#FFF1F2','#9F1239','#FECDD3']] as [$icon,$lbl,$key,$bg,$fg,$border])
    <div style="background:{{ $bg }};border:1.5px solid {{ $border }};border-radius:10px;padding:14px;text-align:center">
        <div style="font-size:20px">{{ $icon }}</div>
        <div style="font-size:9.5px;font-weight:700;text-transform:uppercase;color:{{ $fg }};margin-top:6px;letter-spacing:.06em">{{ $lbl }}</div>
        <div style="font-size:16px;font-weight:900;font-family:monospace;color:{{ $fg }};margin-top:4px">{{ $fmt($data[$key] ?? 0) }}</div>
    </div>
    @endforeach
</div>
<div style="margin-top:12px;padding:10px 14px;background:#FEFCE8;border-left:3px solid #FDE68A;border-radius:0 6px 6px 0;font-size:11px;color:#78350F">
    📌 IVA cuatrimestral (ET Art. 600). Régimen ordinario. Declarar en Form. 300 según calendario DIAN y últimos dígitos del NIT.
</div>
