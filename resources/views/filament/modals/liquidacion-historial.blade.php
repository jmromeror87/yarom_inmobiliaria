<div style="font-family:'Plus Jakarta Sans',sans-serif;padding:8px 0;">
@php
$labels = [
    'pendiente' => ['label' => 'Pendiente', 'icon' => '🕐', 'color' => '#d97706'],
    'aprobada'  => ['label' => 'Aprobada',  'icon' => '✅', 'color' => '#2563eb'],
    'pagada'    => ['label' => 'Pagada',    'icon' => '💰', 'color' => '#16a34a'],
    'anulada'   => ['label' => 'Anulada',   'icon' => '❌', 'color' => '#dc2626'],
];
@endphp

@forelse($historial as $h)
    @php $info = $labels[$h->estado_nuevo] ?? ['label' => $h->estado_nuevo, 'icon' => '•', 'color' => '#64748b']; @endphp

    <div style="display:flex;gap:12px;margin-bottom:14px;align-items:flex-start;">

        {{-- Círculo con icono --}}
        <div style="width:34px;height:34px;border-radius:50%;background:{{ $info['color'] }}20;border:2px solid {{ $info['color'] }};display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0;">
            {{ $info['icon'] }}
        </div>

        {{-- Tarjeta --}}
        <div style="flex:1;background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:12px 14px;">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                <div>
                    <div style="font-weight:800;font-size:13px;color:{{ $info['color'] }};">
                        {{ $info['label'] }}
                    </div>
                    @if($h->estado_anterior)
                        @php $infoAnt = $labels[$h->estado_anterior] ?? ['label' => $h->estado_anterior]; @endphp
                        <div style="font-size:11px;color:#94a3b8;">
                            Desde: {{ $infoAnt['label'] }}
                        </div>
                    @endif
                </div>
                <div style="text-align:right;font-size:11px;color:#94a3b8;">
                    <div style="font-weight:700;color:#475569;">{{ $h->usuario?->name ?? 'Sistema' }}</div>
                    <div>{{ $h->cambiado_en?->format('d/m/Y H:i') }}</div>
                    @if($h->ip)
                        <div style="color:#cbd5e1;">{{ $h->ip }}</div>
                    @endif
                </div>
            </div>

            @if($h->notas)
                <div style="margin-top:6px;font-size:12px;color:#64748b;background:#f8fafc;border-radius:6px;padding:6px 10px;">
                    {{ $h->notas }}
                </div>
            @endif
        </div>
    </div>

@empty
    <div style="text-align:center;padding:40px 0;">
        <div style="font-size:32px;margin-bottom:8px;">📭</div>
        <div style="font-size:13px;color:#94a3b8;">Sin historial de cambios registrado</div>
    </div>
@endforelse

<div style="text-align:center;font-size:11px;color:#94a3b8;padding:6px 0;">
    {{ $historial->count() }} cambio(s) registrado(s)
</div>
</div>
