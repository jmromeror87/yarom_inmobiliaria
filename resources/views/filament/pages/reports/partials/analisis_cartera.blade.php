@php $fmt = fn($v) => '$'.number_format((float)$v, 0, ',', '.'); @endphp

@php $hayDatos = collect($data['por_rango'] ?? [])->sum(fn($r) => count($r['facturas'] ?? [])) > 0; @endphp

@if(!$hayDatos)
<div style="text-align:center;padding:48px;color:#94A3B8">
    <div style="font-size:48px;margin-bottom:12px">✅</div>
    <div style="font-size:16px;font-weight:700;color:#16A34A;margin-bottom:6px">¡Sin cartera vencida!</div>
    <div style="font-size:13px">No hay facturas pendientes al corte del {{ \Carbon\Carbon::parse($data['hasta'])->locale('es')->isoFormat('D [de] MMMM [de] YYYY') }}.</div>
</div>
@else

{{-- Resumen por rango (barras) --}}
@php $totalSaldo = $data['total_saldo'] ?? 0; @endphp
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:20px">
    @foreach($data['por_rango'] ?? [] as $rango)
    @php
        $pct = $totalSaldo > 0 ? round(($rango['total_saldo'] / $totalSaldo) * 100, 1) : 0;
        $colores = ['0-30 días'=>['#F0FDF4','#16A34A'],'31-60 días'=>['#FEFCE8','#CA8A04'],'61-90 días'=>['#FFF7ED','#EA580C'],'91-180 días'=>['#FFF1F2','#E11D48'],'181-360 días'=>['#FFF1F2','#BE123C'],'Más de 360'=>['#FDF2F8','#7C3AED']];
        [$bg,$accent] = $colores[$rango['rango']] ?? ['#F8FAFC','#64748B'];
    @endphp
    <div style="background:{{ $bg }};border-radius:10px;padding:14px;border-top:3px solid {{ $accent }}">
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;color:{{ $accent }};margin-bottom:6px">{{ $rango['rango'] }}</div>
        <div style="font-size:16px;font-weight:900;color:{{ $accent }}">{{ $fmt($rango['total_saldo']) }}</div>
        <div style="font-size:10px;color:#64748B;margin-top:4px">
            {{ count($rango['facturas'] ?? []) }} facturas &nbsp;·&nbsp; Prov. {{ $rango['pct_provision'] }}%
        </div>
        <div style="margin-top:8px;height:5px;background:#E2E8F0;border-radius:99px;overflow:hidden">
            <div style="width:{{ $pct }}%;height:100%;background:{{ $accent }};border-radius:99px"></div>
        </div>
        <div style="font-size:9px;color:{{ $accent }};margin-top:2px;font-weight:600">{{ $pct }}% del total</div>
    </div>
    @endforeach
</div>

{{-- Detalle por rango --}}
@foreach($data['por_rango'] ?? [] as $rango)
@if(count($rango['facturas'] ?? []) > 0)
@php
    $bgTitle = ['0-30 días'=>'#16A34A','31-60 días'=>'#CA8A04','61-90 días'=>'#EA580C','91-180 días'=>'#E11D48','181-360 días'=>'#BE123C','Más de 360'=>'#7C3AED'];
    $colorTitle = $bgTitle[$rango['rango']] ?? '#0F172A';
@endphp
<div style="background:{{ $colorTitle }};color:#fff;padding:9px 14px;border-radius:8px 8px 0 0;font-size:11px;font-weight:800;margin-top:14px;display:flex;justify-content:space-between;align-items:center">
    <span>{{ $rango['rango'] }} &nbsp;—&nbsp; Provisión requerida: {{ $rango['pct_provision'] }}%</span>
    <span style="font-family:monospace;font-size:13px">{{ $fmt($rango['total_prov']) }}</span>
</div>
<table style="width:100%;border-collapse:collapse;margin-bottom:0">
    <thead><tr style="background:#F8FAFC;border-bottom:2px solid #E2E8F0">
        <th style="text-align:left;padding:6px 10px;font-size:9.5px;color:#64748B;font-weight:700;width:13%">N° Factura</th>
        <th style="text-align:left;padding:6px 10px;font-size:9.5px;color:#64748B;font-weight:700;width:32%">Arrendatario</th>
        <th style="text-align:center;padding:6px 10px;font-size:9.5px;color:#64748B;font-weight:700;width:9%">Inmueble</th>
        <th style="text-align:right;padding:6px 10px;font-size:9.5px;color:#64748B;font-weight:700;width:9%">Días</th>
        <th style="text-align:right;padding:6px 10px;font-size:9.5px;color:#64748B;font-weight:700;width:15%">Saldo</th>
        <th style="text-align:right;padding:6px 10px;font-size:9.5px;color:#64748B;font-weight:700;width:11%">Mora</th>
        <th style="text-align:right;padding:6px 10px;font-size:9.5px;color:#64748B;font-weight:700;width:11%">Provisión</th>
    </tr></thead>
    <tbody>
        @foreach($rango['facturas'] as $f)
        <tr style="border-bottom:1px solid #F1F5F9">
            <td style="padding:6px 10px;font-family:monospace;font-weight:600;color:#1E3A8A;font-size:11px">{{ $f['numero'] }}</td>
            <td style="padding:6px 10px;font-size:12px">{{ $f['arrendatario'] }}</td>
            <td style="padding:6px 10px;text-align:center;font-size:11px;color:#64748B">{{ $f['inmueble'] ?? '—' }}</td>
            <td style="padding:6px 10px;text-align:right;font-weight:700;color:{{ $colorTitle }};font-size:12px">{{ $f['dias'] }}</td>
            <td style="padding:6px 10px;text-align:right;font-family:monospace;font-size:12px;font-weight:600">{{ $fmt($f['saldo']) }}</td>
            <td style="padding:6px 10px;text-align:right;font-family:monospace;font-size:11px;color:#DC2626">{{ $fmt($f['mora']) }}</td>
            <td style="padding:6px 10px;text-align:right;font-family:monospace;font-size:11px;color:{{ $colorTitle }};font-weight:700">{{ $fmt($f['provision']) }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot><tr style="background:#F8FAFC;font-weight:700;border-top:2px solid #E2E8F0">
        <td colspan="4" style="text-align:right;padding:7px 10px;font-size:11px;color:#0F172A">Subtotal</td>
        <td style="text-align:right;padding:7px 10px;font-family:monospace;font-size:12px">{{ $fmt($rango['total_saldo']) }}</td>
        <td></td>
        <td style="text-align:right;padding:7px 10px;font-family:monospace;font-size:12px;color:{{ $colorTitle }}">{{ $fmt($rango['total_prov']) }}</td>
    </tr></tfoot>
</table>
@endif
@endforeach

{{-- Gran total --}}
<div style="display:flex;justify-content:space-between;align-items:center;margin-top:16px;padding:16px 18px;border-radius:10px;background:#0F172A;color:#fff">
    <div>
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;opacity:.6;margin-bottom:4px">Total cartera vencida al corte</div>
        <div style="font-size:22px;font-weight:900;font-family:monospace;color:#60A5FA">{{ $fmt($data['total_saldo']) }}</div>
    </div>
    <div style="text-align:right">
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;opacity:.6;margin-bottom:4px">Provisión requerida NIIF</div>
        <div style="font-size:22px;font-weight:900;font-family:monospace;color:#FCA5A5">{{ $fmt($data['total_provision']) }}</div>
    </div>
</div>

<div style="margin-top:10px;padding:10px 14px;background:#FEFCE8;border-left:3px solid #FDE68A;border-radius:0 6px 6px 0;font-size:11px;color:#78350F">
    ⚠️ Provisión calculada según antigüedad NIIF Pymes: 0-30d 0% | 31-60d 5% | 61-90d 10% | 91-180d 15% | 181-360d 33% | +360d 100%
</div>
@endif
