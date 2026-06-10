<div>
@if($record)
@php
    $estadoColor = match($record->estado) {
        'disponible'            => ['bg'=>'#dcfce7','tx'=>'#16a34a','lbl'=>'Disponible'],
        'arrendado'             => ['bg'=>'#dbeafe','tx'=>'#2563EB','lbl'=>'Arrendado'],
        'en_venta'              => ['bg'=>'#fef3c7','tx'=>'#d97706','lbl'=>'En Venta'],
        'vendido'               => ['bg'=>'#f1f5f9','tx'=>'#64748b','lbl'=>'Vendido'],
        'en_captacion'          => ['bg'=>'#ffe4e6','tx'=>'#E11D48','lbl'=>'En Captación'],
        'documentos_pendientes' => ['bg'=>'#ffe4e6','tx'=>'#E11D48','lbl'=>'Docs. Pendientes'],
        'en_mantenimiento'      => ['bg'=>'#fef3c7','tx'=>'#d97706','lbl'=>'Mantenimiento'],
        default                 => ['bg'=>'#f1f5f9','tx'=>'#64748b','lbl'=>$record->estado],
    };
    $ocupacion = \App\Models\Property::count() > 0
        ? round((\App\Models\Property::where('estado','arrendado')->count() / \App\Models\Property::count()) * 100)
        : 0;
@endphp
<div style="background:linear-gradient(135deg,#0F172A 0%,#1e2d45 55%,#1a1f3a 100%);border-radius:1.25rem;padding:24px 32px;margin-bottom:16px;display:flex;align-items:center;gap:24px;position:relative;overflow:hidden;">

    <div style="position:absolute;right:-30px;top:-30px;width:200px;height:200px;border-radius:50%;background:radial-gradient(circle,rgba(225,29,72,.12),transparent 70%);pointer-events:none;"></div>
    <div style="position:absolute;left:40%;bottom:-40px;width:150px;height:150px;border-radius:50%;background:radial-gradient(circle,rgba(37,99,235,.08),transparent 70%);pointer-events:none;"></div>

    {{-- Ícono inmueble --}}
    <div style="width:72px;height:72px;border-radius:18px;background:linear-gradient(135deg,#1e3a8a,#E11D48);display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 8px 24px rgba(225,29,72,.28);">
        <svg width="34" height="34" fill="none" viewBox="0 0 24 24" stroke="#fff" stroke-width="1.6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 9.75L12 3l9 6.75V21H15v-6H9v6H3V9.75z"/>
        </svg>
    </div>

    {{-- Info principal --}}
    <div style="flex:1;min-width:0;">
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:4px;">
            <h2 style="font-size:20px;font-weight:900;color:#fff;margin:0;letter-spacing:-.02em;">{{ strtoupper($record->codigo) }}</h2>
            <span style="font-size:10px;font-weight:800;color:{{ $estadoColor['tx'] }};background:{{ $estadoColor['bg'] }};padding:3px 10px;border-radius:20px;letter-spacing:.06em;">{{ $estadoColor['lbl'] }}</span>
            @if($record->tipo)
            <span style="font-size:10px;font-weight:700;color:rgba(255,255,255,.6);background:rgba(255,255,255,.1);padding:3px 10px;border-radius:20px;">{{ $record->tipo->nombre }}</span>
            @endif
        </div>
        <p style="font-size:13px;color:rgba(255,255,255,.65);margin:0 0 6px;font-weight:500;">
            {{ $record->direccion }}@if($record->municipio), {{ $record->municipio->nombre }}@endif
        </p>
        <div style="display:flex;gap:16px;flex-wrap:wrap;">
            @if($record->propietario)
            <span style="font-size:11px;color:rgba(255,255,255,.5);font-weight:600;">
                <span style="color:rgba(255,255,255,.3);">Propietario:</span> {{ $record->propietario->nombre_completo }}
            </span>
            @endif
            @if($record->estrato)
            <span style="font-size:11px;color:rgba(255,255,255,.5);font-weight:600;">
                <span style="color:rgba(255,255,255,.3);">Estrato:</span> {{ $record->estrato }}
            </span>
            @endif
            @if($record->area_construida_m2)
            <span style="font-size:11px;color:rgba(255,255,255,.5);font-weight:600;">
                <span style="color:rgba(255,255,255,.3);">Área:</span> {{ $record->area_construida_m2 }} m²
            </span>
            @endif
        </div>
    </div>

    {{-- Stats boxes --}}
    <div style="display:flex;gap:12px;flex-shrink:0;">
        <div style="background:rgba(255,255,255,.08);border-radius:12px;padding:12px 18px;text-align:center;min-width:90px;border:1px solid rgba(255,255,255,.1);">
            <div style="font-size:20px;font-weight:900;color:#fff;">{{ $record->canon_arriendo ? '$'.number_format($record->canon_arriendo,0,',','.') : '—' }}</div>
            <div style="font-size:9px;font-weight:700;color:rgba(255,255,255,.45);text-transform:uppercase;letter-spacing:.06em;margin-top:3px;">Canon/mes</div>
        </div>
        <div style="background:rgba(255,255,255,.08);border-radius:12px;padding:12px 18px;text-align:center;min-width:90px;border:1px solid rgba(255,255,255,.1);">
            <div style="font-size:20px;font-weight:900;color:{{ $record->porcentaje_documentos == 100 ? '#4ade80' : ($record->porcentaje_documentos >= 50 ? '#fbbf24' : '#f87171') }};">{{ $record->porcentaje_documentos }}%</div>
            <div style="font-size:9px;font-weight:700;color:rgba(255,255,255,.45);text-transform:uppercase;letter-spacing:.06em;margin-top:3px;">Documentos</div>
        </div>
        <div style="background:rgba(255,255,255,.08);border-radius:12px;padding:12px 18px;text-align:center;min-width:90px;border:1px solid rgba(255,255,255,.1);">
            <div style="font-size:20px;font-weight:900;color:{{ $record->ctl_tiene_limitacion ? '#f87171' : '#4ade80' }};">{{ $record->ctl_tiene_limitacion ? '🚫' : '✅' }}</div>
            <div style="font-size:9px;font-weight:700;color:rgba(255,255,255,.45);text-transform:uppercase;letter-spacing:.06em;margin-top:3px;">CTL</div>
        </div>
    </div>

</div>
@endif
</div>
