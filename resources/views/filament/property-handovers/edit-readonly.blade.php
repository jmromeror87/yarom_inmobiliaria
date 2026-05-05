<x-filament-panels::page>
@php
    $record = $this->record->load(['property.tipo','property.municipio','arrendatario','asesor','items','history.changedBy','rentalContract']);
    $ambienteLabels = [
        'sala'=>'🛋️ Sala','comedor'=>'🪑 Comedor','cocina'=>'🍳 Cocina',
        'habitacion_principal'=>'🛏️ Hab. Principal','habitacion_2'=>'🛏️ Hab. 2',
        'habitacion_3'=>'🛏️ Hab. 3','bano_principal'=>'🚿 Baño Principal',
        'bano_secundario'=>'🚿 Baño Secundario','bano_social'=>'🚿 Baño Social',
        'garaje'=>'🚗 Garaje','deposito'=>'📦 Depósito','patio'=>'🌿 Patio',
        'balcon'=>'🏠 Balcón','zona_lavanderia'=>'👕 Lavandería',
        'estudio'=>'💻 Estudio','otro'=>'📍 Otro',
    ];
@endphp

<style>
    .marca-agua { position:fixed;top:50%;left:50%;transform:translate(-50%,-50%) rotate(-35deg);font-size:90px;font-weight:900;color:#15803d;opacity:0.05;pointer-events:none;z-index:0;white-space:nowrap;user-select:none; }
    .ro-card { background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:20px 24px;margin-bottom:14px; }
    .ro-grid { display:grid;grid-template-columns:1fr 1fr;gap:12px; }
    .ro-item label { font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:0.08em;color:#94a3b8;display:block;margin-bottom:3px; }
    .ro-item span { font-size:14px;font-weight:600;color:#0f172a; }
    .sec-title { font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:0.1em;color:#64748b;margin:18px 0 10px;display:flex;align-items:center;gap:8px; }
    .ambiente-grupo { margin-bottom:14px; }
    .ambiente-titulo { font-size:12px;font-weight:800;text-transform:uppercase;color:#2563eb;margin-bottom:6px; }
    .item-row { display:flex;justify-content:space-between;align-items:center;padding:6px 10px;border-radius:8px;background:#f8fafc;margin-bottom:4px;font-size:13px; }
    .badge { display:inline-block;padding:2px 10px;border-radius:99px;font-size:11px;font-weight:800; }
    .hist-item { display:flex;gap:10px;margin-bottom:10px;align-items:flex-start; }
    .firma-img { border:1px solid #e2e8f0;border-radius:8px;padding:4px;max-height:80px; }
</style>

<div class="marca-agua">ENTREGADO</div>

{{-- Header --}}
<div style="background:#f0fdf4;border:1.5px solid #16a34a;border-radius:16px;padding:20px 24px;margin-bottom:20px;display:flex;justify-content:space-between;align-items:center;">
    <div>
        <div style="font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:0.1em;color:#94a3b8;margin-bottom:4px;">
            Acta de {{ $record->tipo === 'devolucion' ? 'Devolución' : 'Entrega' }}
        </div>
        <div style="font-size:22px;font-weight:900;color:#0f172a;">{{ $record->numero }}</div>
        <div style="font-size:13px;color:#64748b;margin-top:4px;">
            {{ $record->fecha_acta?->format('d/m/Y') }} {{ $record->hora_acta }} · {{ $record->lugar_acta }}
        </div>
    </div>
    <div style="text-align:right;">
        <div style="font-size:40px;">✅</div>
        <div style="font-size:16px;font-weight:900;color:#15803d;">ENTREGADO</div>
        @if($record->fecha_firma)
        <div style="font-size:12px;color:#94a3b8;">Firmado: {{ $record->fecha_firma->format('d/m/Y') }}</div>
        @endif
    </div>
</div>

{{-- Datos generales --}}
<div class="ro-card">
    <div class="ro-grid">
        <div class="ro-item"><label>Inmueble</label><span>{{ $record->property?->codigo }} — {{ $record->property?->direccion }}</span></div>
        <div class="ro-item"><label>Contrato</label><span>{{ $record->rentalContract?->numero_contrato }}</span></div>
        <div class="ro-item"><label>Arrendatario</label><span>{{ $record->arrendatario?->nombre_completo }}</span></div>
        <div class="ro-item"><label>CC</label><span>{{ number_format((float)$record->arrendatario?->numero_documento, 0, ',', '.') }}</span></div>
        <div class="ro-item"><label>Asesor</label><span>{{ $record->asesor?->name ?? 'N/A' }}</span></div>
        <div class="ro-item"><label>Estado general</label>
            <span style="color:{{ match($record->estado_general) { 'excelente'=>'#15803d','bueno'=>'#2563eb','regular'=>'#d97706',default=>'#dc2626' } }};">
                {{ strtoupper($record->estado_general) }}
            </span>
        </div>
    </div>
</div>

{{-- Medidores --}}
<div class="ro-card">
    <div class="sec-title">⚡ Lecturas de medidores</div>
    <div class="ro-grid" style="grid-template-columns:repeat(3,1fr);">
        <div class="ro-item"><label>💧 Agua</label><span>{{ $record->lectura_agua ?? 'N/A' }}</span></div>
        <div class="ro-item"><label>⚡ Energía</label><span>{{ $record->lectura_energia ?? 'N/A' }}</span></div>
        <div class="ro-item"><label>🔥 Gas</label><span>{{ $record->lectura_gas ?? 'N/A' }}</span></div>
    </div>
</div>

{{-- Llaves --}}
<div class="ro-card">
    <div class="sec-title">🔑 Llaves entregadas</div>
    <div class="ro-grid" style="grid-template-columns:repeat(4,1fr);">
        <div class="ro-item"><label>Inmueble</label><span>{{ $record->llaves_entregadas }}</span></div>
        <div class="ro-item"><label>Control acceso</label><span>{{ $record->llaves_control_acceso }}</span></div>
        <div class="ro-item"><label>Parqueadero</label><span>{{ $record->llaves_parqueadero }}</span></div>
        <div class="ro-item"><label>Depósito</label><span>{{ $record->llaves_deposito }}</span></div>
    </div>
</div>

{{-- Inventario --}}
@if($record->items->isNotEmpty())
<div class="ro-card">
    <div class="sec-title">🏠 Inventario por ambientes</div>
    @foreach($record->items->groupBy('ambiente') as $ambiente => $items)
    <div class="ambiente-grupo">
        <div class="ambiente-titulo">{{ $ambienteLabels[$ambiente] ?? ucfirst($ambiente) }}</div>
        @foreach($items as $item)
        <div class="item-row">
            <span>{{ $item->elemento }}</span>
            <div style="display:flex;align-items:center;gap:8px;">
                @if($item->descripcion)<span style="font-size:11px;color:#94a3b8;">{{ $item->descripcion }}</span>@endif
                <span class="badge" style="background:{{ match($item->estado) { 'excelente'=>'#f0fdf4','bueno'=>'#eff6ff','regular'=>'#fffbeb',default=>'#fef2f2' } }};color:{{ match($item->estado) { 'excelente'=>'#15803d','bueno'=>'#2563eb','regular'=>'#d97706',default=>'#dc2626' } }};">
                    {{ strtoupper($item->estado) }}
                </span>
            </div>
        </div>
        @endforeach
    </div>
    @endforeach
</div>
@endif

{{-- Firmas digitales --}}
<div class="ro-card">
    <div class="sec-title">✍️ Firmas digitales</div>
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;text-align:center;">
        <div>
            <div style="font-size:11px;font-weight:700;color:#94a3b8;margin-bottom:8px;text-transform:uppercase;">Representante Legal</div>
            <div style="border-top:1px solid #000;padding-top:8px;font-size:12px;font-weight:700;">
                {{ $record->firmado_asesor ?? 'Serviarrendar S.A.S' }}
            </div>
        </div>
        <div>
            <div style="font-size:11px;font-weight:700;color:#94a3b8;margin-bottom:8px;text-transform:uppercase;">Arrendatario</div>
            @if($record->firma_digital_arrendatario)
            <img src="{{ $record->firma_digital_arrendatario }}" class="firma-img" style="margin-bottom:6px;">
            @endif
            <div style="border-top:1px solid #000;padding-top:8px;font-size:12px;font-weight:700;">
                {{ $record->firmado_arrendatario }}
            </div>
        </div>
        <div>
            <div style="font-size:11px;font-weight:700;color:#94a3b8;margin-bottom:8px;text-transform:uppercase;">Asesor</div>
            @if($record->firma_digital_asesor)
            <img src="{{ $record->firma_digital_asesor }}" class="firma-img" style="margin-bottom:6px;">
            @endif
            <div style="border-top:1px solid #000;padding-top:8px;font-size:12px;font-weight:700;">
                {{ $record->firmado_asesor }}
            </div>
        </div>
    </div>
</div>

{{-- Historial --}}
@if($record->history->isNotEmpty())
<div class="ro-card">
    <div class="sec-title">📋 Historial de estados</div>
    @foreach($record->history as $h)
    @php
    $hLabels = ['borrador'=>['label'=>'Borrador','icon'=>'📝','color'=>'#64748b'],'en_proceso'=>['label'=>'En proceso','icon'=>'🔄','color'=>'#2563eb'],'firmada'=>['label'=>'Firmada','icon'=>'✍️','color'=>'#0369a1'],'cerrada'=>['label'=>'Cerrada','icon'=>'✅','color'=>'#15803d']];
    $info = $hLabels[$h->estado_nuevo] ?? ['label'=>$h->estado_nuevo,'icon'=>'•','color'=>'#64748b'];
    @endphp
    <div class="hist-item">
        <div style="width:30px;height:30px;border-radius:50%;background:{{ $info['color'] }}20;border:1.5px solid {{ $info['color'] }};display:flex;align-items:center;justify-content:center;font-size:13px;flex-shrink:0;">{{ $info['icon'] }}</div>
        <div style="flex:1;background:#f8fafc;border-radius:8px;padding:8px 12px;">
            <div style="display:flex;justify-content:space-between;">
                <span style="font-weight:800;font-size:12px;color:{{ $info['color'] }};">{{ $info['label'] }}</span>
                <span style="font-size:11px;color:#94a3b8;">{{ $h->cambiado_en?->format('d/m/Y H:i') }} · {{ $h->changedBy?->name ?? 'Sistema' }}</span>
            </div>
            @if($h->razon_cambio)<div style="font-size:11px;color:#64748b;margin-top:3px;">{{ $h->razon_cambio }}</div>@endif
            @if($h->canal)<div style="font-size:10px;color:#94a3b8;text-transform:uppercase;">{{ $h->canal }}</div>@endif
        </div>
    </div>
    @endforeach
</div>
@endif

</x-filament-panels::page>
