<x-filament-panels::page>
@php
    $record         = $this->record;
    $portada        = $record->images->where('es_portada', true)->first() ?? $record->images->first();
    $contratos      = $record->administrationContracts->sortByDesc('created_at');
    $contratoActivo = $contratos->whereIn('estado', ['activo','firmado'])->first();

    $estadoMap = match($record->estado) {
        'disponible'            => ['color'=>'#16a34a','bg'=>'#dcfce7','label'=>'Disponible'],
        'arrendado'             => ['color'=>'#2563EB','bg'=>'#dbeafe','label'=>'Arrendado'],
        'en_venta'              => ['color'=>'#d97706','bg'=>'#fef3c7','label'=>'En Venta'],
        'vendido'               => ['color'=>'#64748b','bg'=>'#f1f5f9','label'=>'Vendido'],
        'en_captacion'          => ['color'=>'#E11D48','bg'=>'#ffe4e6','label'=>'En Captación'],
        'documentos_pendientes' => ['color'=>'#E11D48','bg'=>'#ffe4e6','label'=>'Docs. Pendientes'],
        'en_mantenimiento'      => ['color'=>'#d97706','bg'=>'#fef3c7','label'=>'Mantenimiento'],
        default                 => ['color'=>'#64748b','bg'=>'#f1f5f9','label'=>$record->estado],
    };

    $solicitudes = \App\Models\Request::where('property_id', $record->id)
        ->with(['thirds.third','asesor','suraStudies'])
        ->orderByDesc('created_at')->get();
@endphp

<style>
    .yr-exp-hero { background:linear-gradient(135deg,#0F172A 0%,#1e2d45 55%,#1a1f3a 100%); border-radius:1.25rem; padding:28px 32px; margin-bottom:20px; position:relative; overflow:hidden; display:flex; align-items:center; gap:24px; }
    .yr-exp-hero::before { content:''; position:absolute; right:-40px; top:-40px; width:220px; height:220px; border-radius:50%; background:radial-gradient(circle,rgba(225,29,72,.13),transparent 70%); pointer-events:none; }
    .yr-exp-hero::after  { content:''; position:absolute; left:38%; bottom:-50px; width:180px; height:180px; border-radius:50%; background:radial-gradient(circle,rgba(37,99,235,.08),transparent 70%); pointer-events:none; }

    .yr-kpi-row { display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:20px; }
    .yr-kpi-box { background:#fff; border-radius:14px; border-left:5px solid #0F172A; padding:16px 20px; box-shadow:0 2px 10px rgba(15,23,42,.06); }
    .yr-kpi-box .num { font-size:30px; font-weight:900; line-height:1; margin:6px 0 4px; }
    .yr-kpi-box .lbl { font-size:9.5px; font-weight:800; text-transform:uppercase; letter-spacing:.08em; color:#94a3b8; }

    .yr-grid2 { display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px; }
    .yr-card { background:#fff; border-radius:16px; border-left:5px solid #0F172A; padding:20px 24px; box-shadow:0 2px 10px rgba(15,23,42,.05); }
    .yr-card-title { font-size:10px; font-weight:900; text-transform:uppercase; letter-spacing:.1em; color:#94a3b8; margin:0 0 14px; display:flex; align-items:center; gap:8px; }
    .yr-row { display:flex; justify-content:space-between; align-items:center; padding:7px 0; border-bottom:1px solid #f1f5f9; font-size:13px; }
    .yr-row:last-child { border-bottom:none; }
    .yr-row-lbl { color:#94a3b8; font-weight:600; font-size:12px; }
    .yr-row-val { color:#0f172a; font-weight:700; text-align:right; }
    .yr-badge { display:inline-flex; align-items:center; padding:3px 10px; border-radius:99px; font-size:10px; font-weight:800; letter-spacing:.04em; }
    .yr-sol-card { background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:14px 16px; margin-bottom:10px; }
    .yr-sol-card:last-child { margin-bottom:0; }
    .yr-photo-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(130px,1fr)); gap:8px; }
    .yr-photo { border-radius:10px; overflow:hidden; aspect-ratio:4/3; position:relative; }
    .yr-photo img { width:100%; height:100%; object-fit:cover; display:block; transition:transform .2s; }
    .yr-photo:hover img { transform:scale(1.06); }
    .yr-doc-row { display:flex; align-items:center; justify-content:space-between; padding:8px 0; border-bottom:1px solid #f8fafc; font-size:13px; }
    .yr-doc-row:last-child { border-bottom:none; }
    .yr-cad-row { background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:14px 16px; margin-bottom:8px; display:flex; justify-content:space-between; align-items:center; }
    .yr-cad-row:last-child { margin-bottom:0; }
</style>

{{-- ── HERO ── --}}
<div class="yr-exp-hero">
    {{-- Ícono --}}
    <div style="width:72px;height:72px;border-radius:18px;background:linear-gradient(135deg,#1e3a8a,#E11D48);display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 8px 24px rgba(225,29,72,.28);z-index:1;">
        @if($portada)
            <img src="{{ asset('storage/' . $portada->path) }}" style="width:100%;height:100%;object-fit:cover;border-radius:18px;">
        @else
            <svg width="34" height="34" fill="none" viewBox="0 0 24 24" stroke="#fff" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9.75L12 3l9 6.75V21H15v-6H9v6H3V9.75z"/></svg>
        @endif
    </div>

    {{-- Info --}}
    <div style="flex:1;min-width:0;z-index:1;">
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:4px;">
            <h2 style="font-size:20px;font-weight:900;color:#fff;margin:0;letter-spacing:-.02em;">{{ $record->codigo }}</h2>
            <span class="yr-badge" style="background:{{ $estadoMap['bg'] }};color:{{ $estadoMap['color'] }};">{{ $estadoMap['label'] }}</span>
            @if($record->tipo)
            <span class="yr-badge" style="background:rgba(255,255,255,.1);color:rgba(255,255,255,.7);">{{ $record->tipo->nombre }}</span>
            @endif
        </div>
        <p style="font-size:14px;color:rgba(255,255,255,.75);margin:0 0 8px;font-weight:600;">
            {{ $record->direccion }}@if($record->apto_casa_oficina), {{ $record->apto_casa_oficina }}@endif
            @if($record->municipio) · {{ $record->municipio->nombre }}@endif
        </p>
        <div style="display:flex;gap:16px;flex-wrap:wrap;">
            @if($record->barrio)<span style="font-size:11px;color:rgba(255,255,255,.45);font-weight:600;">{{ $record->barrio }}</span>@endif
            @if($record->estrato)<span style="font-size:11px;color:rgba(255,255,255,.45);font-weight:600;">Estrato {{ $record->estrato }}</span>@endif
            @if($record->area_construida_m2)<span style="font-size:11px;color:rgba(255,255,255,.45);font-weight:600;">{{ $record->area_construida_m2 }} m²</span>@endif
            @if($record->propietario)<span style="font-size:11px;color:rgba(255,255,255,.45);font-weight:600;">Prop: {{ $record->propietario->nombre_completo }}</span>@endif
        </div>
    </div>

    {{-- Stat boxes --}}
    <div style="display:flex;gap:12px;flex-shrink:0;z-index:1;">
        <div style="background:rgba(255,255,255,.08);border-radius:12px;padding:14px 20px;text-align:center;border:1px solid rgba(255,255,255,.1);">
            <div style="font-size:18px;font-weight:900;color:#fff;">{{ $record->canon_arriendo ? '$'.number_format($record->canon_arriendo,0,',','.') : '—' }}</div>
            <div style="font-size:9px;font-weight:700;color:rgba(255,255,255,.45);text-transform:uppercase;letter-spacing:.06em;margin-top:3px;">Canon/mes</div>
        </div>
        <div style="background:rgba(255,255,255,.08);border-radius:12px;padding:14px 20px;text-align:center;border:1px solid rgba(255,255,255,.1);">
            <div style="font-size:18px;font-weight:900;color:{{ $record->porcentaje_documentos == 100 ? '#4ade80' : ($record->porcentaje_documentos >= 50 ? '#fbbf24' : '#f87171') }};">{{ $record->porcentaje_documentos }}%</div>
            <div style="font-size:9px;font-weight:700;color:rgba(255,255,255,.45);text-transform:uppercase;letter-spacing:.06em;margin-top:3px;">Documentos</div>
        </div>
        <div style="background:rgba(255,255,255,.08);border-radius:12px;padding:14px 20px;text-align:center;border:1px solid rgba(255,255,255,.1);">
            <div style="font-size:18px;font-weight:900;color:{{ $record->ctl_tiene_limitacion ? '#f87171' : '#4ade80' }};">{{ $record->ctl_tiene_limitacion ? '🚫' : '✅' }}</div>
            <div style="font-size:9px;font-weight:700;color:rgba(255,255,255,.45);text-transform:uppercase;letter-spacing:.06em;margin-top:3px;">CTL</div>
        </div>
    </div>
</div>

{{-- ── KPIs ── --}}
<div class="yr-kpi-row">
    <div class="yr-kpi-box" style="border-left-color:#16a34a;">
        <div class="lbl">Solicitudes totales</div>
        <div class="num" style="color:#16a34a;">{{ $solicitudes->count() }}</div>
        <div style="font-size:10px;color:#94a3b8;">{{ $solicitudes->where('estado','aprobada')->count() }} aprobadas</div>
    </div>
    <div class="yr-kpi-box" style="border-left-color:#E11D48;">
        <div class="lbl">Rechazadas</div>
        <div class="num" style="color:#E11D48;">{{ $solicitudes->where('estado','rechazada')->count() }}</div>
        <div style="font-size:10px;color:#94a3b8;">de {{ $solicitudes->count() }} solicitudes</div>
    </div>
    <div class="yr-kpi-box" style="border-left-color:#2563EB;">
        <div class="lbl">Contratos admin.</div>
        <div class="num" style="color:#2563EB;">{{ $contratos->count() }}</div>
        <div style="font-size:10px;color:#94a3b8;">{{ $contratos->where('estado','activo')->count() }} activo(s)</div>
    </div>
    <div class="yr-kpi-box" style="border-left-color:#f59e0b;">
        <div class="lbl">En sistema desde</div>
        <div style="font-size:20px;font-weight:900;color:#f59e0b;margin:6px 0 4px;">{{ $record->fecha_captacion?->format('d/m/Y') ?? $record->created_at->format('d/m/Y') }}</div>
        <div style="font-size:10px;color:#94a3b8;">{{ $record->fecha_captacion ? $record->fecha_captacion->diffForHumans() : $record->created_at->diffForHumans() }}</div>
    </div>
</div>

{{-- ── PROPIETARIO + CONTRATO ACTIVO ── --}}
<div class="yr-grid2">
    <div class="yr-card" style="border-left-color:#1e3a8a;">
        <div class="yr-card-title">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="#1e3a8a" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
            Propietario
        </div>
        <div class="yr-row"><span class="yr-row-lbl">Nombre</span><span class="yr-row-val">{{ $record->propietario?->nombre_completo ?? '—' }}</span></div>
        <div class="yr-row"><span class="yr-row-lbl">Documento</span><span class="yr-row-val">{{ $record->propietario?->tipo_documento }} {{ $record->propietario?->numero_documento }}</span></div>
        <div class="yr-row"><span class="yr-row-lbl">Celular</span><span class="yr-row-val">{{ $record->propietario?->celular ?? 'N/A' }}</span></div>
        <div class="yr-row"><span class="yr-row-lbl">Email</span><span class="yr-row-val">{{ $record->propietario?->email ?? 'N/A' }}</span></div>
        <div class="yr-row"><span class="yr-row-lbl">Banco</span><span class="yr-row-val">{{ $record->propietario?->banco ?? 'N/A' }}</span></div>
        <div class="yr-row"><span class="yr-row-lbl">Cuenta</span><span class="yr-row-val">{{ $record->propietario?->tipo_cuenta ? ucfirst($record->propietario->tipo_cuenta).' · '.$record->propietario->numero_cuenta : 'N/A' }}</span></div>
    </div>

    <div class="yr-card" style="border-left-color:{{ $contratoActivo ? '#16a34a' : '#94a3b8' }};">
        <div class="yr-card-title">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="{{ $contratoActivo ? '#16a34a' : '#94a3b8' }}" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/></svg>
            Contrato de administración
        </div>
        @if($contratoActivo)
        <div class="yr-row"><span class="yr-row-lbl">N° Contrato</span><span class="yr-row-val">{{ $contratoActivo->numero_contrato }}</span></div>
        <div class="yr-row"><span class="yr-row-lbl">Estado</span>
            <span class="yr-badge" style="background:{{ $contratoActivo->estado==='activo' ? '#dcfce7' : '#dbeafe' }};color:{{ $contratoActivo->estado==='activo' ? '#16a34a' : '#2563EB' }};">{{ strtoupper($contratoActivo->estado) }}</span>
        </div>
        <div class="yr-row"><span class="yr-row-lbl">Vigencia</span><span class="yr-row-val">{{ $contratoActivo->fecha_inicio?->format('d/m/Y') }} — {{ $contratoActivo->fecha_fin?->format('d/m/Y') }}</span></div>
        <div class="yr-row"><span class="yr-row-lbl">Canon pactado</span><span class="yr-row-val">${{ number_format($contratoActivo->canon_pactado,0,',','.') }}</span></div>
        <div class="yr-row"><span class="yr-row-lbl">Comisión</span><span class="yr-row-val">{{ $contratoActivo->comision_porcentaje }}%</span></div>
        <div class="yr-row"><span class="yr-row-lbl">Firmado</span><span class="yr-row-val">{{ $contratoActivo->fecha_firma?->format('d/m/Y') ?? 'N/A' }}</span></div>
        @else
        <div style="text-align:center;padding:30px 0;color:#94a3b8;">
            <svg width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="#cbd5e1" stroke-width="1.5" style="margin:0 auto 8px;display:block;"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
            <div style="font-weight:700;font-size:13px;">Sin contrato activo</div>
        </div>
        @endif
    </div>
</div>

{{-- ── DOCUMENTOS ── --}}
@php
$docs = [
    ['campo'=>'doc_escritura',          'label'=>'Escritura pública',       'path'=>'doc_escritura_path',          'color'=>'#2563EB'],
    ['campo'=>'doc_certificado_libertad','label'=>'Certificado de libertad', 'path'=>'doc_certificado_libertad_path','color'=>'#7c3aed'],
    ['campo'=>'doc_predial',             'label'=>'Predial al día',           'path'=>'doc_predial_path',            'color'=>'#16a34a'],
    ['campo'=>'doc_paz_salvo_admin',     'label'=>'Paz y salvo admin.',       'path'=>'doc_paz_salvo_admin_path',    'color'=>'#d97706'],
    ['campo'=>'doc_documento_propietario','label'=>'Doc. propietario',        'path'=>'doc_propietario_path',        'color'=>'#0891b2'],
    ['campo'=>'doc_recibo_servicios',    'label'=>'Recibo servicios',         'path'=>'doc_recibo_servicios_path',   'color'=>'#E11D48'],
];
$docsOk = collect($docs)->filter(fn($d) => $record->{$d['campo']})->count();
@endphp
<div class="yr-card" style="border-left-color:#2563EB;margin-bottom:16px;">
    <div class="yr-card-title" style="justify-content:space-between;">
        <span style="display:flex;align-items:center;gap:8px;">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="#2563EB" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13"/></svg>
            Documentos del inmueble
        </span>
        <span style="font-size:11px;font-weight:800;color:{{ $docsOk == count($docs) ? '#16a34a' : '#d97706' }};">{{ $docsOk }}/{{ count($docs) }} completados</span>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:0;">
        @foreach($docs as $doc)
        <div class="yr-doc-row" style="padding:9px 0;border-bottom:1px solid #f1f5f9;">
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="width:8px;height:8px;border-radius:50%;background:{{ $record->{$doc['campo']} ? '#16a34a' : '#e2e8f0' }};flex-shrink:0;"></div>
                <span style="font-size:13px;color:#0f172a;font-weight:600;">{{ $doc['label'] }}</span>
            </div>
            @if($record->{$doc['path']} ?? null)
            <a href="{{ asset('storage/'.$record->{$doc['path']}) }}" target="_blank"
               style="font-size:11px;color:#2563EB;font-weight:700;white-space:nowrap;">Ver →</a>
            @else
            <span style="font-size:11px;color:#cbd5e1;font-weight:600;">{{ $record->{$doc['campo']} ? 'Sin archivo' : 'Pendiente' }}</span>
            @endif
        </div>
        @endforeach
    </div>
</div>

{{-- ── GALERÍA ── --}}
@if($record->images->isNotEmpty())
<div class="yr-card" style="border-left-color:#E11D48;margin-bottom:16px;">
    <div class="yr-card-title" style="justify-content:space-between;">
        <span style="display:flex;align-items:center;gap:8px;">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="#E11D48" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
            Galería ({{ $record->images->count() }} fotos)
        </span>
        <a href="{{ \App\Filament\Resources\Properties\PropertyResource::getUrl('gallery', ['record' => $record]) }}"
           style="font-size:11px;color:#E11D48;font-weight:800;">VER GALERÍA COMPLETA →</a>
    </div>
    <div class="yr-photo-grid">
        @foreach($record->images->take(8) as $img)
        <div class="yr-photo">
            @if($img->es_portada)
            <div style="position:absolute;top:6px;left:6px;background:linear-gradient(135deg,#E11D48,#1e3a8a);color:#fff;font-size:9px;font-weight:800;padding:2px 7px;border-radius:99px;z-index:2;letter-spacing:.04em;">⭐ Portada</div>
            @endif
            <img src="{{ asset('storage/'.$img->path) }}" alt="{{ $img->titulo }}" loading="lazy">
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- ── SOLICITUDES ── --}}
@if($solicitudes->isNotEmpty())
<div class="yr-card" style="border-left-color:#16a34a;margin-bottom:16px;">
    <div class="yr-card-title">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="#16a34a" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/></svg>
        Historial de solicitudes ({{ $solicitudes->count() }})
    </div>
    @foreach($solicitudes as $sol)
    @php
        $sColor = match($sol->estado) {
            'aprobada'    => ['c'=>'#16a34a','bg'=>'#dcfce7','icon'=>'✅'],
            'rechazada'   => ['c'=>'#E11D48','bg'=>'#ffe4e6','icon'=>'❌'],
            'condicional' => ['c'=>'#d97706','bg'=>'#fef3c7','icon'=>'⚠️'],
            'en_estudio'  => ['c'=>'#2563EB','bg'=>'#dbeafe','icon'=>'🔍'],
            default       => ['c'=>'#64748b','bg'=>'#f1f5f9','icon'=>'📋'],
        };
    @endphp
    <div class="yr-sol-card">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px;">
            <div>
                <span style="font-weight:900;font-size:14px;color:#0f172a;">{{ $sol->numero }}</span>
                <span style="margin-left:8px;font-size:12px;color:#64748b;">{{ match($sol->tipo) {
                    'estudio_propietario'  => '🏠 Est. propietario',
                    'estudio_arrendatario' => '🔑 Est. arrendatario',
                    'estudio_comprador'    => '🛒 Est. comprador',
                    default => $sol->tipo,
                } }}</span>
            </div>
            <div style="display:flex;align-items:center;gap:8px;">
                <span style="font-size:11px;color:#94a3b8;">{{ $sol->fecha_radicacion?->format('d/m/Y') }}</span>
                <span class="yr-badge" style="background:{{ $sColor['bg'] }};color:{{ $sColor['c'] }};">{{ $sColor['icon'] }} {{ strtoupper($sol->estado) }}</span>
            </div>
        </div>
        @if($sol->thirds->isNotEmpty())
        <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:6px;">
            @foreach($sol->thirds as $t)
            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:4px 10px;font-size:12px;">
                <span style="color:#94a3b8;">{{ ucfirst($t->rol) }}:</span>
                <span style="font-weight:700;color:#0f172a;margin-left:4px;">{{ $t->third?->nombre_completo }}</span>
                @if($t->resultado_individual !== 'pendiente')
                <span style="color:{{ $t->resultado_individual === 'aprobado' ? '#16a34a' : '#E11D48' }};margin-left:4px;">{{ $t->resultado_individual === 'aprobado' ? '✅' : '❌' }}</span>
                @endif
            </div>
            @endforeach
        </div>
        @endif
        @if($sol->suraStudies->isNotEmpty())
        @php $ultimaSura = $sol->suraStudies->sortByDesc('created_at')->first(); @endphp
        <div style="font-size:12px;color:#64748b;">
            🏢 Sura: @if($ultimaSura->numero_solicitud_sura)N° {{ $ultimaSura->numero_solicitud_sura }} · @endif
            <span style="font-weight:800;color:{{ match($ultimaSura->resultado_sura) { 'aprobada'=>'#16a34a','rechazada'=>'#E11D48',default=>'#d97706' } }};">{{ strtoupper($ultimaSura->resultado_sura) }}</span>
        </div>
        @endif
        @if($sol->concepto_evaluacion)
        <div style="margin-top:6px;font-size:12px;color:#94a3b8;font-style:italic;">{{ \Illuminate\Support\Str::limit($sol->concepto_evaluacion, 150) }}</div>
        @endif
    </div>
    @endforeach
</div>
@endif

{{-- ── HISTORIAL CONTRATOS ── --}}
@if($contratos->isNotEmpty())
<div class="yr-card" style="border-left-color:#2563EB;">
    <div class="yr-card-title">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="#2563EB" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
        Historial de contratos de administración
    </div>
    @foreach($contratos as $cad)
    @php
        $cadColor = match($cad->estado) {
            'activo'  => ['c'=>'#16a34a','bg'=>'#dcfce7','bc'=>'#bbf7d0'],
            'firmado' => ['c'=>'#2563EB','bg'=>'#dbeafe','bc'=>'#bfdbfe'],
            default   => ['c'=>'#64748b','bg'=>'#f1f5f9','bc'=>'#e2e8f0'],
        };
    @endphp
    <div class="yr-cad-row">
        <div>
            <div style="font-weight:900;font-size:14px;color:#0f172a;">{{ $cad->numero_contrato }}</div>
            <div style="font-size:12px;color:#64748b;margin-top:2px;">
                {{ $cad->fecha_inicio?->format('d/m/Y') }} — {{ $cad->fecha_fin?->format('d/m/Y') }}
                @if($cad->statusHistory->isNotEmpty())
                · <span style="color:#94a3b8;">Último mov: {{ $cad->statusHistory->first()->cambiado_en?->format('d/m/Y H:i') }}</span>
                @endif
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:10px;">
            <span style="font-size:13px;color:#64748b;font-weight:700;">${{ number_format($cad->canon_pactado,0,',','.') }} · {{ $cad->comision_porcentaje }}%</span>
            <span class="yr-badge" style="background:{{ $cadColor['bg'] }};color:{{ $cadColor['c'] }};border:1px solid {{ $cadColor['bc'] }};">{{ strtoupper($cad->estado) }}</span>
            <a href="{{ \App\Filament\Resources\AdministrationContracts\AdministrationContractResource::getUrl('edit', ['record' => $cad]) }}"
               style="font-size:11px;color:#2563EB;font-weight:800;">Ver →</a>
        </div>
    </div>
    @endforeach
</div>
@endif

</x-filament-panels::page>
