<div style="font-family:'Plus Jakarta Sans',sans-serif;padding:8px 0;">
@php
$estadoLabels = [
    'borrador'            => ['label'=>'Borrador','icon'=>'📝','color'=>'#64748b'],
    'enviado_propietario' => ['label'=>'Enviado al propietario','icon'=>'📤','color'=>'#2563eb'],
    'en_revision'         => ['label'=>'En revisión','icon'=>'🔍','color'=>'#d97706'],
    'aprobado_gerencia'   => ['label'=>'Aprobado gerencia','icon'=>'✅','color'=>'#16a34a'],
    'enviado_notaria'     => ['label'=>'Enviado a notaría','icon'=>'🏛️','color'=>'#7c3aed'],
    'autenticado_notaria' => ['label'=>'Autenticado','icon'=>'🔏','color'=>'#0891b2'],
    'firmado'             => ['label'=>'Firmado','icon'=>'✍️','color'=>'#0369a1'],
    'activo'              => ['label'=>'Activo','icon'=>'🟢','color'=>'#15803d'],
    'terminado'           => ['label'=>'Terminado','icon'=>'🔴','color'=>'#64748b'],
    'cancelado'           => ['label'=>'Cancelado','icon'=>'❌','color'=>'#dc2626'],
];
@endphp

@foreach($historial as $h)
@php $info = $estadoLabels[$h->estado_nuevo] ?? ['label'=>$h->estado_nuevo,'icon'=>'•','color'=>'#64748b']; @endphp
<div style="display:flex;gap:14px;margin-bottom:16px;align-items:flex-start;">
    <div style="width:36px;height:36px;border-radius:50%;background:{{ $info['color'] }}20;border:2px solid {{ $info['color'] }};display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;">
        {{ $info['icon'] }}
    </div>
    <div style="flex:1;background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:14px;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;">
            <div>
                <div style="font-weight:800;font-size:14px;color:{{ $info['color'] }};">{{ $info['label'] }}</div>
                @if($h->estado_anterior)
                <div style="font-size:12px;color:#94a3b8;margin-top:2px;">
                    Desde: {{ $estadoLabels[$h->estado_anterior]['label'] ?? $h->estado_anterior }}
                </div>
                @endif
            </div>
            <div style="text-align:right;font-size:11px;color:#94a3b8;">
                <div style="font-weight:700;color:#475569;">{{ $h->changedBy?->name ?? 'Sistema' }}</div>
                <div>{{ $h->cambiado_en?->format('d/m/Y H:i') }}</div>
                @if($h->canal)<div style="text-transform:uppercase;letter-spacing:0.05em;">{{ $h->canal }}</div>@endif
            </div>
        </div>
        @if($h->razon_cambio)
        <div style="margin-top:8px;font-size:13px;color:#374151;background:#f8fafc;border-radius:8px;padding:8px 12px;">
            {{ $h->razon_cambio }}
        </div>
        @endif
    </div>
</div>
@endforeach
<div style="text-align:center;font-size:12px;color:#94a3b8;padding:8px 0;">
    {{ $historial->count() }} cambio(s) de estado registrado(s)
</div>
</div>
