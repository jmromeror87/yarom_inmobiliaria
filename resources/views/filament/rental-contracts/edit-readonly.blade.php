<x-filament-panels::page>
@php
    $record = $this->record->load(['property.tipo','property.municipio','arrendatario','thirds.third','asesor']);
    $estado = $record->estado;
    $config = match($estado) {
        'activo'    => ['color'=>'#15803d','bg'=>'#f0fdf4','border'=>'#16a34a','marca'=>'ACTIVO','icon'=>'🟢'],
        'terminado' => ['color'=>'#64748b','bg'=>'#f8fafc','border'=>'#94a3b8','marca'=>'TERMINADO','icon'=>'🔴'],
        'cancelado' => ['color'=>'#dc2626','bg'=>'#fef2f2','border'=>'#f87171','marca'=>'CANCELADO','icon'=>'❌'],
        default     => ['color'=>'#64748b','bg'=>'#f8fafc','border'=>'#94a3b8','marca'=>strtoupper($estado),'icon'=>'🔒'],
    };
@endphp

<style>
    .marca-agua { position:fixed;top:50%;left:50%;transform:translate(-50%,-50%) rotate(-35deg);font-size:100px;font-weight:900;color:{{ $config['color'] }};opacity:0.05;pointer-events:none;z-index:0;white-space:nowrap;user-select:none; }
    .ro-card { background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:20px 24px;margin-bottom:16px; }
    .ro-grid { display:grid;grid-template-columns:1fr 1fr;gap:12px; }
    .ro-item label { font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:0.08em;color:#94a3b8;display:block;margin-bottom:3px; }
    .ro-item span { font-size:14px;font-weight:600;color:#0f172a; }
    .sec-title { font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:0.1em;color:#64748b;margin:20px 0 10px; }
    .alerta { border-radius:12px;padding:14px 18px;margin-bottom:16px;display:flex;align-items:center;gap:12px; }
</style>

<div class="marca-agua">{{ $config['marca'] }}</div>

{{-- Alerta vencimiento --}}
@if($estado === 'activo' && ($record->estaProximoAVencer() || $record->estaVencido()))
<div class="alerta" style="background:{{ $record->estaVencido() ? '#fef2f2' : '#fffbeb' }};border:1.5px solid {{ $record->estaVencido() ? '#f87171' : '#fcd34d' }};">
    <span style="font-size:24px;">{{ $record->estaVencido() ? '🚨' : '⚠️' }}</span>
    <div>
        <div style="font-weight:800;font-size:14px;color:{{ $record->estaVencido() ? '#dc2626' : '#d97706' }};">
            {{ $record->estaVencido() ? 'CONTRATO VENCIDO' : 'PRÓXIMO A VENCER' }}
        </div>
        <div style="font-size:13px;color:#64748b;">
            @if($record->estaVencido())
                Venció el {{ $record->fecha_fin->format('d/m/Y') }}. Por ley, si ninguna parte notificó con {{ $record->meses_preaviso }} meses de anticipación, se entiende prorrogado automáticamente.
            @else
                Vence el {{ $record->fecha_fin->format('d/m/Y') }} — en {{ $record->diasParaVencer() }} días. Notifique con {{ $record->meses_preaviso }} meses de anticipación si no va a renovar.
            @endif
        </div>
    </div>
</div>
@endif

{{-- Header --}}
<div style="background:{{ $config['bg'] }};border:1.5px solid {{ $config['border'] }};border-radius:16px;padding:20px 24px;margin-bottom:20px;display:flex;justify-content:space-between;align-items:center;">
    <div>
        <div style="font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:0.1em;color:#94a3b8;margin-bottom:4px;">
            Contrato de arrendamiento {{ $record->tipo === 'comercial' ? 'Comercial' : 'Vivienda Urbana' }}
        </div>
        <div style="font-size:24px;font-weight:900;color:#0f172a;">{{ $record->numero_contrato }}</div>
        <div style="font-size:13px;color:#64748b;margin-top:4px;">
            {{ $record->lugar_contrato }}, {{ $record->fecha_contrato?->format('d/m/Y') }}
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

{{-- Datos principales --}}
<div class="ro-grid" style="margin-bottom:16px;">
    <div class="ro-card">
        <div class="sec-title">🏠 Inmueble</div>
        <div class="ro-grid">
            <div class="ro-item"><label>Código</label><span>{{ $record->property?->codigo }}</span></div>
            <div class="ro-item"><label>Tipo</label><span>{{ $record->property?->tipo?->nombre }}</span></div>
            <div class="ro-item" style="grid-column:span 2;"><label>Dirección</label><span>{{ $record->property?->direccion }}</span></div>
            <div class="ro-item"><label>Ciudad</label><span>{{ $record->property?->municipio?->nombre }}</span></div>
            <div class="ro-item"><label>Folio</label><span>{{ $record->folio_inmobiliario ?? 'N/A' }}</span></div>
        </div>
    </div>
    <div class="ro-card">
        <div class="sec-title">👤 Arrendatario</div>
        <div class="ro-grid">
            <div class="ro-item" style="grid-column:span 2;"><label>Nombre</label><span>{{ $record->arrendatario?->nombre_completo }}</span></div>
            <div class="ro-item"><label>Documento</label><span>{{ $record->arrendatario?->tipo_documento }} {{ $record->arrendatario?->numero_documento }}</span></div>
            <div class="ro-item"><label>Celular</label><span>{{ $record->arrendatario?->celular ?? 'N/A' }}</span></div>
            <div class="ro-item"><label>Email</label><span>{{ $record->arrendatario?->email ?? 'N/A' }}</span></div>
        </div>
    </div>
</div>

<div class="ro-card">
    <div class="sec-title">💰 Condiciones económicas</div>
    <div class="ro-grid" style="grid-template-columns:repeat(4,1fr);">
        <div class="ro-item"><label>Canon mensual</label><span>${{ number_format($record->canon_mensual, 0, ',', '.') }}</span></div>
        <div class="ro-item"><label>Depósito</label><span>${{ number_format($record->deposito, 0, ',', '.') }}</span></div>
        <div class="ro-item"><label>Adm.</label><span>${{ number_format($record->cuota_administracion, 0, ',', '.') }}</span></div>
        <div class="ro-item"><label>Incremento</label><span>{{ $record->tipo_incremento === 'ipc_vivienda' ? 'IPC Vivienda' : $record->porcentaje_incremento . '%' }}</span></div>
        <div class="ro-item"><label>Inicio</label><span>{{ $record->fecha_inicio?->format('d/m/Y') }}</span></div>
        <div class="ro-item"><label>Vence</label><span>{{ $record->fecha_fin?->format('d/m/Y') }}</span></div>
        <div class="ro-item"><label>Duración</label><span>{{ $record->duracion_meses }} meses</span></div>
        <div class="ro-item"><label>Preaviso</label><span>{{ $record->meses_preaviso }} meses</span></div>
        <div class="ro-item" style="grid-column:span 4;"><label>Servicios a cargo del arrendatario</label><span>{{ $record->servicios_cargo_arrendatario }}</span></div>
    </div>
</div>

{{-- Deudores solidarios --}}
@if($record->thirds->isNotEmpty())
<div class="ro-card">
    <div class="sec-title">🤝 Deudores solidarios ({{ $record->thirds->count() }})</div>
    @foreach($record->thirds as $t)
    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:12px 14px;margin-bottom:8px;display:flex;justify-content:space-between;align-items:center;">
        <div>
            <div style="font-weight:800;font-size:14px;color:#0f172a;">{{ $t->third?->nombre_completo }}</div>
            <div style="font-size:12px;color:#64748b;">{{ $t->third?->tipo_documento }} {{ $t->third?->numero_documento }} {{ $t->ciudad_expedicion_doc ? '— Exp: ' . $t->ciudad_expedicion_doc : '' }}</div>
            @if($t->direccion_notificacion)<div style="font-size:12px;color:#64748b;">📍 {{ $t->direccion_notificacion }}</div>@endif
            @if($t->celular_notificacion)<div style="font-size:12px;color:#64748b;">📱 {{ $t->celular_notificacion }}</div>@endif
        </div>
        <span style="background:#e0f2fe;color:#0369a1;font-size:11px;font-weight:800;padding:3px 10px;border-radius:99px;">{{ ucfirst(str_replace('_',' ',$t->rol)) }}</span>
    </div>
    @endforeach
</div>
@endif

{{-- Contrato firmado --}}
@if($record->path_contrato_firmado)
<div class="ro-card">
    <div class="sec-title">📄 Documento firmado y autenticado</div>
    <a href="{{ asset('storage/' . $record->path_contrato_firmado) }}" target="_blank"
       style="color:#2563eb;font-weight:700;font-size:14px;">📄 Ver contrato firmado en notaría</a>
</div>
@endif


{{-- Otrosíes --}}
<div style="margin-top:24px;">
    @livewire(\App\Filament\Resources\RentalContracts\RelationManagers\AmendmentsRelationManager::class, [
        'ownerRecord' => $record,
        'pageClass'   => static::class,
    ])
</div>

</x-filament-panels::page>
