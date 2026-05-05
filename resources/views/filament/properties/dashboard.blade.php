<x-filament-panels::page>
@php
    $record   = $this->record;
    $portada  = $record->images->where('es_portada', true)->first() ?? $record->images->first();
    $contratos = $record->administrationContracts->sortByDesc('created_at');
    $contratoActivo = $contratos->whereIn('estado', ['activo','firmado'])->first();

    $estadoConfig = match($record->estado) {
        'disponible'            => ['color'=>'#15803d','bg'=>'#f0fdf4','label'=>'Disponible','icon'=>'✅'],
        'arrendado'             => ['color'=>'#0369a1','bg'=>'#eff6ff','label'=>'Arrendado','icon'=>'🔑'],
        'en_captacion'          => ['color'=>'#d97706','bg'=>'#fffbeb','label'=>'En captación','icon'=>'📋'],
        'documentos_pendientes' => ['color'=>'#dc2626','bg'=>'#fef2f2','label'=>'Docs. pendientes','icon'=>'📄'],
        'en_venta'              => ['color'=>'#7c3aed','bg'=>'#f5f3ff','label'=>'En venta','icon'=>'🏷️'],
        'vendido'               => ['color'=>'#64748b','bg'=>'#f8fafc','label'=>'Vendido','icon'=>'🤝'],
        'en_mantenimiento'      => ['color'=>'#ea580c','bg'=>'#fff7ed','label'=>'Mantenimiento','icon'=>'🔧'],
        default                 => ['color'=>'#64748b','bg'=>'#f8fafc','label'=>$record->estado,'icon'=>'🏠'],
    };

    $solicitudes = \App\Models\Request::where('property_id', $record->id)
        ->with(['thirds.third','asesor','suraStudies'])
        ->orderByDesc('created_at')->get();

    $catLabels = [
        'fachada'=>'🏠 Fachada','sala'=>'🛋️ Sala','cocina'=>'🍳 Cocina',
        'habitacion'=>'🛏️ Habitación','bano'=>'🚿 Baño','zona_comun'=>'🏊 Zona común',
        'vista'=>'🌅 Vista','plano'=>'📐 Plano','otro'=>'📷 Otro',
    ];
@endphp

<style>
    .exp-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px; }
    .exp-card { background:#fff; border:1px solid #e2e8f0; border-radius:16px; padding:20px 24px; }
    .exp-card h3 { font-size:12px; font-weight:800; text-transform:uppercase; letter-spacing:0.1em; color:#64748b; margin:0 0 14px; display:flex; align-items:center; gap:8px; }
    .exp-row { display:flex; justify-content:space-between; padding:6px 0; border-bottom:1px solid #f1f5f9; font-size:13px; }
    .exp-row:last-child { border-bottom:none; }
    .exp-row label { color:#94a3b8; font-weight:600; }
    .exp-row span { color:#0f172a; font-weight:700; text-align:right; max-width:60%; }
    .badge-est { display:inline-block; padding:4px 12px; border-radius:99px; font-size:12px; font-weight:800; }
    .timeline-item { display:flex; gap:12px; margin-bottom:14px; }
    .tl-dot { width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:14px; flex-shrink:0; }
    .tl-body { flex:1; background:#fff; border:1px solid #e2e8f0; border-radius:10px; padding:12px 14px; }
    .sol-card { background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:14px 16px; margin-bottom:10px; }
    .photo-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(140px,1fr)); gap:8px; }
    .photo-item { border-radius:10px; overflow:hidden; aspect-ratio:4/3; position:relative; }
    .photo-item img { width:100%; height:100%; object-fit:cover; display:block; transition:transform 0.2s; }
    .photo-item:hover img { transform:scale(1.05); }
    .doc-item { display:flex; align-items:center; justify-content:space-between; padding:8px 0; border-bottom:1px solid #f1f5f9; font-size:13px; }
    .stat-box { background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:16px 20px; text-align:center; }
    .stat-box .num { font-size:28px; font-weight:900; color:#0f172a; }
    .stat-box .lbl { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:#94a3b8; margin-top:4px; }
</style>

{{-- ── HEADER INMUEBLE ── --}}
<div style="background:{{ $estadoConfig['bg'] }};border:1.5px solid {{ $estadoConfig['color'] }};border-radius:16px;padding:0;margin-bottom:20px;overflow:hidden;display:flex;">

    {{-- Foto portada --}}
    @if($portada)
    <div style="width:220px;flex-shrink:0;">
        <img src="{{ asset('storage/' . $portada->path) }}"
             style="width:100%;height:100%;object-fit:cover;display:block;">
    </div>
    @endif

    <div style="padding:20px 24px;flex:1;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:14px;">
            <div>
                <div style="font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:0.1em;color:#94a3b8;">
                    {{ $record->tipo?->nombre }} · {{ $record->codigo }}
                </div>
                <div style="font-size:22px;font-weight:900;color:#0f172a;margin:4px 0;">
                    {{ $record->direccion }}
                    @if($record->conjunto_edificio), {{ $record->conjunto_edificio }}@endif
                    @if($record->apto_casa_oficina), {{ $record->apto_casa_oficina }}@endif
                </div>
                <div style="font-size:14px;color:#64748b;">
                    {{ $record->barrio ? $record->barrio . ' · ' : '' }}
                    {{ $record->municipio?->nombre }}, {{ $record->municipio?->departamento?->nombre }}
                    · Estrato {{ $record->estrato }}
                </div>
            </div>
            <div>
                <span class="badge-est" style="background:{{ $estadoConfig['color'] }}20;color:{{ $estadoConfig['color'] }};border:1.5px solid {{ $estadoConfig['color'] }};">
                    {{ $estadoConfig['icon'] }} {{ $estadoConfig['label'] }}
                </span>
            </div>
        </div>

        {{-- Stats rápidos --}}
        <div style="display:flex;gap:12px;flex-wrap:wrap;">
            @if($record->canon_arriendo)
            <div style="background:rgba(255,255,255,0.7);border-radius:8px;padding:8px 14px;font-size:13px;">
                <span style="color:#94a3b8;font-size:10px;font-weight:700;text-transform:uppercase;display:block;">Canon</span>
                <span style="font-weight:900;color:#0f172a;">${{ number_format($record->canon_arriendo, 0, ',', '.') }}</span>
            </div>
            @endif
            @if($record->area_construida_m2)
            <div style="background:rgba(255,255,255,0.7);border-radius:8px;padding:8px 14px;font-size:13px;">
                <span style="color:#94a3b8;font-size:10px;font-weight:700;text-transform:uppercase;display:block;">Área</span>
                <span style="font-weight:900;color:#0f172a;">{{ $record->area_construida_m2 }} m²</span>
            </div>
            @endif
            <div style="background:rgba(255,255,255,0.7);border-radius:8px;padding:8px 14px;font-size:13px;">
                <span style="color:#94a3b8;font-size:10px;font-weight:700;text-transform:uppercase;display:block;">Hab.</span>
                <span style="font-weight:900;color:#0f172a;">{{ $record->habitaciones }}</span>
            </div>
            <div style="background:rgba(255,255,255,0.7);border-radius:8px;padding:8px 14px;font-size:13px;">
                <span style="color:#94a3b8;font-size:10px;font-weight:700;text-transform:uppercase;display:block;">Baños</span>
                <span style="font-weight:900;color:#0f172a;">{{ $record->banos }}</span>
            </div>
            @if($record->garajes)
            <div style="background:rgba(255,255,255,0.7);border-radius:8px;padding:8px 14px;font-size:13px;">
                <span style="color:#94a3b8;font-size:10px;font-weight:700;text-transform:uppercase;display:block;">Garajes</span>
                <span style="font-weight:900;color:#0f172a;">{{ $record->garajes }}</span>
            </div>
            @endif
            <div style="background:rgba(255,255,255,0.7);border-radius:8px;padding:8px 14px;font-size:13px;">
                <span style="color:#94a3b8;font-size:10px;font-weight:700;text-transform:uppercase;display:block;">Docs.</span>
                <span style="font-weight:900;color:#0f172a;">{{ $record->porcentaje_documentos }}%</span>
            </div>
            <div style="background:rgba(255,255,255,0.7);border-radius:8px;padding:8px 14px;font-size:13px;">
                <span style="color:#94a3b8;font-size:10px;font-weight:700;text-transform:uppercase;display:block;">En sistema desde</span>
                <span style="font-weight:900;color:#0f172a;">{{ $record->fecha_captacion?->format('d/m/Y') ?? $record->created_at->format('d/m/Y') }}</span>
            </div>
        </div>
    </div>
</div>

{{-- ── ESTADÍSTICAS RÁPIDAS ── --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px;">
    <div class="stat-box">
        <div class="num">{{ $solicitudes->count() }}</div>
        <div class="lbl">Solicitudes totales</div>
    </div>
    <div class="stat-box">
        <div class="num" style="color:#15803d;">{{ $solicitudes->where('estado','aprobada')->count() }}</div>
        <div class="lbl">Aprobadas</div>
    </div>
    <div class="stat-box">
        <div class="num" style="color:#dc2626;">{{ $solicitudes->where('estado','rechazada')->count() }}</div>
        <div class="lbl">Rechazadas</div>
    </div>
    <div class="stat-box">
        <div class="num" style="color:#2563eb;">{{ $contratos->count() }}</div>
        <div class="lbl">Contratos admin.</div>
    </div>
</div>

{{-- ── DOS COLUMNAS ── --}}
<div class="exp-grid">

    {{-- PROPIETARIO --}}
    <div class="exp-card">
        <h3>🏠 Propietario</h3>
        <div class="exp-row"><label>Nombre</label><span>{{ $record->propietario?->nombre_completo }}</span></div>
        <div class="exp-row"><label>Documento</label><span>{{ $record->propietario?->tipo_documento }} {{ $record->propietario?->numero_documento }}</span></div>
        <div class="exp-row"><label>Celular</label><span>{{ $record->propietario?->celular ?? 'N/A' }}</span></div>
        <div class="exp-row"><label>Email</label><span>{{ $record->propietario?->email ?? 'N/A' }}</span></div>
        <div class="exp-row"><label>Banco</label><span>{{ $record->propietario?->banco ?? 'N/A' }}</span></div>
        <div class="exp-row"><label>Cuenta</label><span>{{ $record->propietario?->tipo_cuenta ? ucfirst($record->propietario->tipo_cuenta) . ' · ' . $record->propietario->numero_cuenta : 'N/A' }}</span></div>
    </div>

    {{-- CONTRATO ACTIVO --}}
    <div class="exp-card">
        <h3>📋 Contrato de administración</h3>
        @if($contratoActivo)
        <div class="exp-row"><label>N° Contrato</label><span>{{ $contratoActivo->numero_contrato }}</span></div>
        <div class="exp-row"><label>Estado</label>
            <span style="color:{{ $contratoActivo->estado === 'activo' ? '#15803d' : '#2563eb' }};">
                {{ strtoupper($contratoActivo->estado) }}
            </span>
        </div>
        <div class="exp-row"><label>Vigencia</label><span>{{ $contratoActivo->fecha_inicio?->format('d/m/Y') }} — {{ $contratoActivo->fecha_fin?->format('d/m/Y') }}</span></div>
        <div class="exp-row"><label>Canon</label><span>${{ number_format($contratoActivo->canon_pactado, 0, ',', '.') }}</span></div>
        <div class="exp-row"><label>Comisión</label><span>{{ $contratoActivo->comision_porcentaje }}%</span></div>
        <div class="exp-row"><label>Firmado</label><span>{{ $contratoActivo->fecha_firma?->format('d/m/Y') ?? 'N/A' }}</span></div>
        @else
        <div style="text-align:center;padding:20px;color:#94a3b8;">
            <div style="font-size:32px;margin-bottom:8px;">📄</div>
            <div style="font-weight:700;">Sin contrato activo</div>
        </div>
        @endif
    </div>

</div>

{{-- ── DOCUMENTOS ── --}}
<div class="exp-card" style="margin-bottom:16px;">
    <h3>📎 Documentos del inmueble</h3>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:0;">
        @php
        $docs = [
            ['campo'=>'doc_escritura','label'=>'Escritura pública','path'=>'doc_escritura_path'],
            ['campo'=>'doc_certificado_libertad','label'=>'Certificado de libertad','path'=>'doc_certificado_libertad_path'],
            ['campo'=>'doc_predial','label'=>'Predial al día','path'=>'doc_predial_path'],
            ['campo'=>'doc_paz_salvo_admin','label'=>'Paz y salvo admin.','path'=>'doc_paz_salvo_admin_path'],
            ['campo'=>'doc_documento_propietario','label'=>'Doc. propietario','path'=>'doc_propietario_path'],
            ['campo'=>'doc_recibo_servicios','label'=>'Recibo servicios','path'=>'doc_recibo_servicios_path'],
        ];
        @endphp
        @foreach($docs as $doc)
        <div class="doc-item" style="padding:8px 0;border-bottom:1px solid #f1f5f9;">
            <div style="display:flex;align-items:center;gap:8px;font-size:13px;">
                <span>{{ $record->{$doc['campo']} ? '✅' : '⭕' }}</span>
                <span style="color:#0f172a;font-weight:600;">{{ $doc['label'] }}</span>
            </div>
            @if($record->{$doc['path']} ?? null)
            <a href="{{ asset('storage/' . $record->{$doc['path']}) }}" target="_blank"
               style="font-size:11px;color:#2563eb;font-weight:700;">Ver →</a>
            @endif
        </div>
        @endforeach
    </div>
</div>

{{-- ── GALERÍA ── --}}
@if($record->images->isNotEmpty())
<div class="exp-card" style="margin-bottom:16px;">
    <h3>📷 Galería ({{ $record->images->count() }} fotos)
        <a href="{{ \App\Filament\Resources\Properties\PropertyResource::getUrl('gallery', ['record' => $record]) }}"
           style="font-size:11px;color:#2563eb;font-weight:700;margin-left:auto;">Ver galería completa →</a>
    </h3>
    <div class="photo-grid">
        @foreach($record->images->take(8) as $img)
        <div class="photo-item">
            @if($img->es_portada)
            <div style="position:absolute;top:6px;left:6px;background:linear-gradient(135deg,#E11D48,#2563EB);color:#fff;font-size:9px;font-weight:800;padding:2px 7px;border-radius:99px;z-index:2;">⭐ Portada</div>
            @endif
            <img src="{{ asset('storage/' . $img->path) }}" alt="{{ $img->titulo }}" loading="lazy">
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- ── TIMELINE SOLICITUDES ── --}}
@if($solicitudes->isNotEmpty())
<div class="exp-card" style="margin-bottom:16px;">
    <h3>📋 Historial de solicitudes ({{ $solicitudes->count() }})</h3>
    @foreach($solicitudes as $sol)
    @php
        $solColor = match($sol->estado) {
            'aprobada'    => '#15803d',
            'rechazada'   => '#dc2626',
            'condicional' => '#d97706',
            'en_estudio'  => '#2563eb',
            default       => '#64748b',
        };
        $solIcon = match($sol->estado) {
            'aprobada'    => '✅',
            'rechazada'   => '❌',
            'condicional' => '⚠️',
            'en_estudio'  => '🔍',
            default       => '📋',
        };
    @endphp
    <div class="sol-card">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px;">
            <div>
                <span style="font-weight:900;font-size:14px;color:#0f172a;">{{ $sol->numero }}</span>
                <span style="margin-left:8px;font-size:12px;color:#64748b;">
                    {{ match($sol->tipo) {
                        'estudio_propietario'  => '🏠 Est. propietario',
                        'estudio_arrendatario' => '🔑 Est. arrendatario',
                        'estudio_comprador'    => '🛒 Est. comprador',
                        default => $sol->tipo,
                    } }}
                </span>
            </div>
            <div style="display:flex;align-items:center;gap:8px;">
                <span style="font-size:11px;color:#94a3b8;">{{ $sol->fecha_radicacion?->format('d/m/Y') }}</span>
                <span style="background:{{ $solColor }}20;color:{{ $solColor }};border:1px solid {{ $solColor }};font-size:11px;font-weight:800;padding:2px 10px;border-radius:99px;">
                    {{ $solIcon }} {{ strtoupper($sol->estado) }}
                </span>
            </div>
        </div>

        {{-- Terceros --}}
        @if($sol->thirds->isNotEmpty())
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            @foreach($sol->thirds as $t)
            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:5px 10px;font-size:12px;">
                <span style="color:#94a3b8;">{{ ucfirst($t->rol) }}:</span>
                <span style="font-weight:700;color:#0f172a;">{{ $t->third?->nombre_completo }}</span>
                @if($t->resultado_individual !== 'pendiente')
                <span style="color:{{ $t->resultado_individual === 'aprobado' ? '#15803d' : '#dc2626' }};">
                    · {{ $t->resultado_individual === 'aprobado' ? '✅' : '❌' }}
                </span>
                @endif
            </div>
            @endforeach
        </div>
        @endif

        {{-- Sura --}}
        @if($sol->suraStudies->isNotEmpty())
        @php $ultimaSura = $sol->suraStudies->sortByDesc('created_at')->first(); @endphp
        <div style="margin-top:8px;font-size:12px;color:#64748b;">
            🏢 Sura:
            @if($ultimaSura->numero_solicitud_sura) N° {{ $ultimaSura->numero_solicitud_sura }} · @endif
            <span style="font-weight:700;color:{{ $ultimaSura->resultado_sura === 'aprobada' ? '#15803d' : ($ultimaSura->resultado_sura === 'rechazada' ? '#dc2626' : '#d97706') }};">
                {{ strtoupper($ultimaSura->resultado_sura) }}
            </span>
        </div>
        @endif

        @if($sol->concepto_evaluacion)
        <div style="margin-top:8px;font-size:12px;color:#64748b;font-style:italic;">{{ Str::limit($sol->concepto_evaluacion, 150) }}</div>
        @endif
    </div>
    @endforeach
</div>
@endif

{{-- ── HISTORIAL CONTRATOS ── --}}
@if($contratos->isNotEmpty())
<div class="exp-card">
    <h3>📜 Historial de contratos de administración</h3>
    @foreach($contratos as $cad)
    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px;margin-bottom:8px;">
        <div style="display:flex;justify-content:space-between;align-items:center;">
            <div>
                <span style="font-weight:900;font-size:14px;color:#0f172a;">{{ $cad->numero_contrato }}</span>
                <span style="font-size:12px;color:#64748b;margin-left:8px;">
                    {{ $cad->fecha_inicio?->format('d/m/Y') }} — {{ $cad->fecha_fin?->format('d/m/Y') }}
                </span>
            </div>
            <div style="display:flex;align-items:center;gap:8px;">
                <span style="font-size:12px;color:#64748b;">${{ number_format($cad->canon_pactado, 0, ',', '.') }} · {{ $cad->comision_porcentaje }}%</span>
                <span style="background:{{ match($cad->estado) { 'activo'=>'#f0fdf4', 'firmado'=>'#eff6ff', default=>'#f8fafc' } }};
                             color:{{ match($cad->estado) { 'activo'=>'#15803d', 'firmado'=>'#2563eb', default=>'#64748b' } }};
                             border:1px solid {{ match($cad->estado) { 'activo'=>'#bbf7d0', 'firmado'=>'#bfdbfe', default=>'#e2e8f0' } }};
                             font-size:11px;font-weight:800;padding:2px 10px;border-radius:99px;">
                    {{ strtoupper($cad->estado) }}
                </span>
                <a href="{{ \App\Filament\Resources\AdministrationContracts\AdministrationContractResource::getUrl('edit', ['record' => $cad]) }}"
                   style="font-size:11px;color:#2563eb;font-weight:700;">Ver →</a>
            </div>
        </div>
        @if($cad->statusHistory->isNotEmpty())
        <div style="margin-top:8px;font-size:11px;color:#94a3b8;">
            Último movimiento: {{ $cad->statusHistory->first()->estado_nuevo }} · {{ $cad->statusHistory->first()->cambiado_en?->format('d/m/Y H:i') }}
        </div>
        @endif
    </div>
    @endforeach
</div>
@endif

</x-filament-panels::page>
