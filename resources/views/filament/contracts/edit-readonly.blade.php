<x-filament-panels::page>
@php
    $record = $this->record->load([
        'property.tipo','property.municipio','propietario',
        'asesor','clauses','statusHistory.changedBy','notaryTracking'
    ]);
    $estado = $record->estado;
    $config = match($estado) {
        'activo'    => ['color'=>'#15803d','bg'=>'#f0fdf4','border'=>'#16a34a','marca'=>'ACTIVO','icon'=>'🟢'],
        'firmado'   => ['color'=>'#0369a1','bg'=>'#eff6ff','border'=>'#2563eb','marca'=>'FIRMADO','icon'=>'✍️'],
        'terminado' => ['color'=>'#64748b','bg'=>'#f8fafc','border'=>'#94a3b8','marca'=>'TERMINADO','icon'=>'🔴'],
        'cancelado' => ['color'=>'#dc2626','bg'=>'#fef2f2','border'=>'#f87171','marca'=>'CANCELADO','icon'=>'❌'],
        default     => ['color'=>'#64748b','bg'=>'#f8fafc','border'=>'#94a3b8','marca'=>strtoupper($estado),'icon'=>'🔒'],
    };
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

<style>
    .marca-agua {
        position:fixed;top:50%;left:50%;
        transform:translate(-50%,-50%) rotate(-35deg);
        font-size:100px;font-weight:900;
        color:{{ $config['color'] }};opacity:0.05;
        pointer-events:none;z-index:0;white-space:nowrap;user-select:none;
    }
    .ro-card { background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:20px 24px;margin-bottom:16px; }
    .ro-grid { display:grid;grid-template-columns:1fr 1fr;gap:12px; }
    .ro-item label { font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:0.08em;color:#94a3b8;display:block;margin-bottom:3px; }
    .ro-item span { font-size:14px;font-weight:600;color:#0f172a; }
    .sec-title { font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:0.1em;color:#64748b;margin:20px 0 10px;display:flex;align-items:center;gap:8px; }
    .clausula-item { background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px;margin-bottom:8px; }
    .clausula-editada { border-left:3px solid #f59e0b;background:#fffbeb; }
    .hist-item { display:flex;gap:12px;margin-bottom:12px;align-items:flex-start; }
    .notaria-card { background:#eff6ff;border:1px solid #bfdbfe;border-radius:12px;padding:16px;margin-bottom:16px; }

    @if($record->estaProximoAVencer())
    .alerta-vencimiento { background:#fffbeb;border:1.5px solid #fcd34d;border-radius:12px;padding:14px 18px;margin-bottom:16px;display:flex;align-items:center;gap:12px; }
    @endif
    @if($record->estaVencido())
    .alerta-vencimiento { background:#fef2f2;border:1.5px solid #f87171;border-radius:12px;padding:14px 18px;margin-bottom:16px;display:flex;align-items:center;gap:12px; }
    @endif
</style>

<div class="marca-agua">{{ $config['marca'] }}</div>

{{-- Alerta de vencimiento --}}
@if($estado === 'activo' && ($record->estaProximoAVencer() || $record->estaVencido()))
<div class="alerta-vencimiento">
    <span style="font-size:24px;">{{ $record->estaVencido() ? '🚨' : '⚠️' }}</span>
    <div>
        <div style="font-weight:800;font-size:14px;color:{{ $record->estaVencido() ? '#dc2626' : '#d97706' }};">
            {{ $record->estaVencido() ? 'CONTRATO VENCIDO' : 'PRÓXIMO A VENCER' }}
        </div>
        <div style="font-size:13px;color:#64748b;">
            @if($record->estaVencido())
                Venció el {{ $record->fecha_fin->format('d/m/Y') }} — hace {{ abs($record->diasParaVencer()) }} días.
                Por ley (Art. 7 Ley 820), si ninguna parte notificó con 30 días de anticipación, se entiende prorrogado.
            @else
                Vence el {{ $record->fecha_fin->format('d/m/Y') }} — en {{ $record->diasParaVencer() }} días.
                La ley exige notificar con 30 días de anticipación si no se va a renovar.
            @endif
        </div>
    </div>
</div>
@endif

{{-- Header --}}
<div style="background:{{ $config['bg'] }};border:1.5px solid {{ $config['border'] }};border-radius:16px;padding:20px 24px;margin-bottom:20px;display:flex;justify-content:space-between;align-items:center;">
    <div>
        <div style="font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:0.1em;color:#94a3b8;margin-bottom:4px;">Contrato de Administración</div>
        <div style="font-size:24px;font-weight:900;color:#0f172a;">{{ $record->numero_contrato }}</div>
        <div style="font-size:13px;color:#64748b;margin-top:4px;">
            {{ $record->fecha_inicio?->format('d/m/Y') }} — {{ $record->fecha_fin?->format('d/m/Y') }}
            · Renovación: {{ $record->renovacion === 'automatica' ? 'Automática' : 'Manual' }}
        </div>
    </div>
    <div style="text-align:right;">
        <div style="font-size:40px;">{{ $config['icon'] }}</div>
        <div style="font-size:16px;font-weight:900;color:{{ $config['color'] }};">{{ $config['marca'] }}</div>
        @if($record->fecha_firma)
        <div style="font-size:12px;color:#94a3b8;">Firmado: {{ $record->fecha_firma->format('d/m/Y') }}</div>
        @endif
    </div>
</div>

{{-- Datos generales --}}
<div class="ro-card">
    <div class="ro-grid">
        <div class="ro-item"><label>Inmueble</label><span>{{ $record->property?->codigo }} — {{ $record->property?->direccion }}</span></div>
        <div class="ro-item"><label>Propietario</label><span>{{ $record->propietario?->nombre_completo }}</span></div>
        <div class="ro-item"><label>Canon pactado</label><span>${{ number_format($record->canon_pactado, 0, ',', '.') }} COP</span></div>
        <div class="ro-item"><label>Comisión</label><span>{{ $record->comision_porcentaje }}%</span></div>
        <div class="ro-item"><label>Asesor</label><span>{{ $record->asesor?->name ?? 'N/A' }}</span></div>
        <div class="ro-item"><label>Firmado por</label><span>{{ $record->firmado_por ?? 'N/A' }}</span></div>
    </div>
</div>

{{-- Notaría --}}
@if($record->notaryTracking)
@php $n = $record->notaryTracking; @endphp
<div class="notaria-card">
    <div class="sec-title" style="margin:0 0 12px;">🏛️ Trámite notarial</div>
    <div class="ro-grid">
        <div class="ro-item"><label>Notaría</label><span>{{ $n->notaria_nombre }}</span></div>
        <div class="ro-item"><label>Ciudad</label><span>{{ $n->notaria_ciudad }}</span></div>
        <div class="ro-item"><label>Fecha envío</label><span>{{ $n->fecha_envio_notaria?->format('d/m/Y') }}</span></div>
        <div class="ro-item"><label>N° Radicado</label><span>{{ $n->numero_radicado_notaria ?? 'N/A' }}</span></div>
        <div class="ro-item"><label>Fecha autenticación</label><span>{{ $n->fecha_autenticacion?->format('d/m/Y') ?? 'Pendiente' }}</span></div>
        <div class="ro-item"><label>N° Escritura</label><span>{{ $n->numero_escritura ?? 'N/A' }}</span></div>
        @if($n->valor_autenticacion)
        <div class="ro-item"><label>Valor autenticación</label><span>${{ number_format($n->valor_autenticacion, 0, ',', '.') }}</span></div>
        @endif
        @if($n->recibido_por)
        <div class="ro-item"><label>Recibido por</label><span>{{ $n->recibido_por }}</span></div>
        @endif
    </div>
    @if($n->path_contrato_firmado)
    <div style="margin-top:12px;">
        <a href="{{ asset('storage/' . $n->path_contrato_firmado) }}" target="_blank"
           style="font-size:13px;color:#2563eb;font-weight:700;">📄 Ver contrato firmado y autenticado</a>
    </div>
    @endif
</div>
@endif

{{-- Cláusulas --}}
<div class="sec-title">📄 Cláusulas del contrato ({{ $record->clauses->count() }})</div>
@foreach($record->clauses as $clausula)
<div class="clausula-item {{ $clausula->fue_editada ? 'clausula-editada' : '' }}">
    <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
        <div style="font-weight:800;font-size:12px;color:#0f172a;text-transform:uppercase;">
            {{ $clausula->numero }}: {{ $clausula->titulo }}
        </div>
        @if($clausula->fue_editada)
        <span style="font-size:10px;background:#fef9c3;color:#854d0e;border:1px solid #fcd34d;padding:2px 8px;border-radius:99px;font-weight:700;">
            ⚠️ Modificada
        </span>
        @endif
    </div>
    <div style="font-size:13px;color:#374151;line-height:1.6;">{{ Str::limit($clausula->contenido_actual, 300) }}</div>
</div>
@endforeach

{{-- Historial de estados --}}
@if($record->statusHistory->isNotEmpty())
<div class="sec-title">📋 Historial de estados</div>
@foreach($record->statusHistory as $h)
@php $info = $estadoLabels[$h->estado_nuevo] ?? ['label'=>$h->estado_nuevo,'icon'=>'•','color'=>'#64748b']; @endphp
<div class="hist-item">
    <div style="width:32px;height:32px;border-radius:50%;background:{{ $info['color'] }}20;border:1.5px solid {{ $info['color'] }};display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0;">{{ $info['icon'] }}</div>
    <div style="flex:1;background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:10px 14px;">
        <div style="display:flex;justify-content:space-between;">
            <div style="font-weight:800;font-size:13px;color:{{ $info['color'] }};">{{ $info['label'] }}</div>
            <div style="font-size:11px;color:#94a3b8;">{{ $h->cambiado_en?->format('d/m/Y H:i') }} · {{ $h->changedBy?->name ?? 'Sistema' }}</div>
        </div>
        @if($h->razon_cambio)
        <div style="font-size:12px;color:#64748b;margin-top:4px;">{{ $h->razon_cambio }}</div>
        @endif
    </div>
</div>
@endforeach
@endif

</x-filament-panels::page>
