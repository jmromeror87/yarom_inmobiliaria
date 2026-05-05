<div style="font-family:'Plus Jakarta Sans',sans-serif;padding:8px 0;">
@php
$labels = [
    'borrador'   => ['label'=>'Borrador',   'icon'=>'📝','color'=>'#64748b'],
    'en_proceso' => ['label'=>'En proceso', 'icon'=>'🔄','color'=>'#2563eb'],
    'firmada'    => ['label'=>'Firmada',    'icon'=>'✍️','color'=>'#0369a1'],
    'cerrada'    => ['label'=>'Cerrada',    'icon'=>'✅','color'=>'#15803d'],
];
@endphp
@foreach($historial as $h)
@php $info = $labels[$h->estado_nuevo] ?? ['label'=>$h->estado_nuevo,'icon'=>'•','color'=>'#64748b']; @endphp
<div style="display:flex;gap:12px;margin-bottom:14px;align-items:flex-start;">
    <div style="width:34px;height:34px;border-radius:50%;background:{{ $info['color'] }}20;border:2px solid {{ $info['color'] }};display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0;">{{ $info['icon'] }}</div>
    <div style="flex:1;background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:12px 14px;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;">
            <div>
                <div style="font-weight:800;font-size:13px;color:{{ $info['color'] }};">{{ $info['label'] }}</div>
                @if($h->estado_anterior)
                <div style="font-size:11px;color:#94a3b8;">Desde: {{ $labels[$h->estado_anterior]['label'] ?? $h->estado_anterior }}</div>
                @endif
                @if($h->canal)
                <div style="font-size:11px;color:#94a3b8;text-transform:uppercase;">Canal: {{ $h->canal }}</div>
                @endif
            </div>
            <div style="text-align:right;font-size:11px;color:#94a3b8;">
                <div style="font-weight:700;color:#475569;">{{ $h->changedBy?->name ?? 'Sistema' }}</div>
                <div>{{ $h->cambiado_en?->format('d/m/Y H:i') }}</div>
                @if($h->ip_address)<div>IP: {{ $h->ip_address }}</div>@endif
            </div>
        </div>
        @if($h->razon_cambio)
        <div style="margin-top:6px;font-size:12px;color:#64748b;background:#f8fafc;border-radius:6px;padding:6px 10px;">{{ $h->razon_cambio }}</div>
        @endif
    </div>
</div>
@endforeach
<div style="text-align:center;font-size:11px;color:#94a3b8;padding:6px 0;">{{ $historial->count() }} evento(s) registrado(s)</div>
</div>
