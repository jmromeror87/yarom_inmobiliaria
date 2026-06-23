<x-filament-panels::page>
<style>
:root {
    --nt-red:    #E11D48;
    --nt-navy:   #0F172A;
    --nt-amber:  #d97706;
    --nt-purple: #7c3aed;
    --nt-blue:   #2563eb;
    --nt-green:  #16a34a;
    --nt-slate:  #64748b;
    --nt-radius: 14px;
}

/* ── Layout ─────────────────────────────────────── */
.nt-page { display:flex; flex-direction:column; gap:22px; max-width:100%; }

/* ── KPIs ───────────────────────────────────────── */
.nt-kpis { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; }
.nt-kpi {
    background:#fff; border:1px solid #e8edf5; border-radius:var(--nt-radius);
    padding:18px 20px; display:flex; align-items:center; gap:16px;
    box-shadow:0 1px 6px rgba(0,0,0,.05);
    transition: transform .15s, box-shadow .15s;
}
.nt-kpi:hover { transform:translateY(-2px); box-shadow:0 4px 16px rgba(0,0,0,.08); }
.nt-kpi-icon { width:44px; height:44px; border-radius:12px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.nt-kpi-val  { font-size:26px; font-weight:900; color:#0f172a; line-height:1; }
.nt-kpi-lbl  { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.07em; color:#94a3b8; margin-top:3px; }

/* ── Grid ───────────────────────────────────────── */
.nt-grid { display:grid; grid-template-columns:1fr 360px; gap:20px; align-items:start; }

/* ── Panel base ─────────────────────────────────── */
.nt-panel {
    background:#fff; border:1px solid #e8edf5; border-radius:var(--nt-radius);
    overflow:hidden; box-shadow:0 2px 10px rgba(0,0,0,.05);
}
.nt-panel-head {
    padding:16px 24px; background:linear-gradient(135deg,#f8fafc,#f1f5f9);
    border-bottom:1px solid #e8edf5; display:flex; align-items:center; gap:10px;
}
.nt-panel-title { font-size:13px; font-weight:800; color:#0f172a; }

/* ── Filtros ────────────────────────────────────── */
.nt-filters { display:flex; gap:6px; padding:14px 20px; border-bottom:1px solid #f1f5f9; flex-wrap:wrap; background:#fafbfc; }
.nt-filter-btn {
    border:1.5px solid #e2e8f0; background:#fff; border-radius:8px;
    padding:5px 14px; font-size:11px; font-weight:700; color:#64748b;
    cursor:pointer; transition:all .15s;
}
.nt-filter-btn:hover { border-color:#cbd5e1; color:#334155; }
.nt-filter-btn.active { background:var(--nt-navy); border-color:var(--nt-navy); color:#fff; }

/* ── Nota card ──────────────────────────────────── */
.nt-nota {
    display:flex; align-items:flex-start; gap:14px; padding:16px 20px;
    border-bottom:1px solid #f8fafc; transition:background .12s;
}
.nt-nota:last-child { border-bottom:none; }
.nt-nota:hover { background:#fafbfc; }
.nt-nota.completada { opacity:.5; }

.nt-check {
    width:20px; height:20px; border:2px solid #e2e8f0; border-radius:6px;
    flex-shrink:0; margin-top:1px; cursor:pointer;
    display:flex; align-items:center; justify-content:center;
    transition:all .15s; background:transparent;
}
.nt-check.done { background:var(--nt-green); border-color:var(--nt-green); }

.nt-nota-body { flex:1; min-width:0; }
.nt-nota-meta { display:flex; align-items:center; gap:6px; margin-bottom:6px; flex-wrap:wrap; }

.nt-badge {
    display:inline-flex; align-items:center; gap:3px; border-radius:99px;
    padding:2px 8px; font-size:9px; font-weight:800; text-transform:uppercase; letter-spacing:.06em;
}
.nt-badge-alta    { background:#fef2f2; color:var(--nt-red); border:1px solid #fecdd3; }
.nt-badge-normal  { background:#f0fdf4; color:var(--nt-green); border:1px solid #bbf7d0; }
.nt-badge-baja    { background:#f8fafc; color:#94a3b8; border:1px solid #e2e8f0; }
.nt-badge-tarea   { background:#eff6ff; color:var(--nt-blue); border:1px solid #bfdbfe; }
.nt-badge-nota    { background:#fdf4ff; color:var(--nt-purple); border:1px solid #e9d5ff; }
.nt-badge-reunion { background:#fff7ed; color:var(--nt-amber); border:1px solid #fed7aa; }
.nt-hora { font-size:9px; color:#cbd5e1; font-weight:600; margin-left:auto; white-space:nowrap; }

.nt-texto { font-size:13px; font-weight:500; color:#1e293b; line-height:1.6; }
.completada .nt-texto { text-decoration:line-through; color:#94a3b8; }

/* ── Acciones ───────────────────────────────────── */
.nt-actions { display:flex; flex-direction:column; gap:4px; align-items:center; }
.nt-action-btn {
    background:none; border:none; color:#cbd5e1; cursor:pointer;
    padding:5px; border-radius:6px; transition:color .12s, background .12s;
    display:flex; align-items:center;
}
.nt-action-btn:hover { color:var(--nt-red); background:#fee2e2; }

/* ── Adjuntos en nota ───────────────────────────── */
.nt-attachments { margin-top:10px; display:flex; flex-wrap:wrap; gap:8px; }

.nt-att-img-wrap {
    position:relative; display:inline-block; border-radius:10px;
    overflow:hidden; border:1.5px solid #e2e8f0;
}
.nt-att-img-wrap img {
    width:90px; height:70px; object-fit:cover; display:block; cursor:pointer;
}
.nt-att-del {
    position:absolute; top:3px; right:3px; background:rgba(15,23,42,.65);
    border:none; color:#fff; border-radius:50%; width:20px; height:20px;
    display:flex; align-items:center; justify-content:center;
    cursor:pointer; font-size:10px; transition:background .12s;
}
.nt-att-del:hover { background:var(--nt-red); }

.nt-att-file {
    display:inline-flex; align-items:center; gap:6px; border-radius:9px;
    border:1.5px solid #e2e8f0; background:#f8fafc; padding:6px 10px;
    font-size:11px; font-weight:700; color:#334155; text-decoration:none;
    max-width:180px; transition:border-color .12s, background .12s;
    position:relative;
}
.nt-att-file:hover { border-color:#93c5fd; background:#eff6ff; color:var(--nt-blue); }
.nt-att-file-name { flex:1; min-width:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.nt-att-file-del {
    background:none; border:none; color:#94a3b8; cursor:pointer;
    padding:0; display:flex; align-items:center; flex-shrink:0;
    transition:color .12s;
}
.nt-att-file-del:hover { color:var(--nt-red); }

/* ── Empty ──────────────────────────────────────── */
.nt-empty { padding:60px 24px; text-align:center; }

/* ── Formulario ─────────────────────────────────── */
.nt-form-panel {
    background:#fff; border:1px solid #e8edf5; border-radius:var(--nt-radius);
    overflow:hidden; box-shadow:0 2px 10px rgba(0,0,0,.05);
    position:sticky; top:80px;
}
.nt-form-head {
    padding:16px 22px;
    background:linear-gradient(135deg, var(--nt-navy) 0%, #1a3050 100%);
    display:flex; align-items:center; gap:10px;
}
.nt-form-title { font-size:13px; font-weight:800; color:#fff; letter-spacing:.02em; }

.nt-form-body { padding:20px; display:flex; flex-direction:column; gap:16px; }

.nt-field { display:flex; flex-direction:column; gap:6px; }
.nt-label { font-size:10px; font-weight:800; text-transform:uppercase; letter-spacing:.07em; color:#94a3b8; }

.nt-textarea {
    width:100%; border:1.5px solid #e2e8f0; border-radius:10px;
    padding:11px 13px; font-size:13px; font-weight:500; color:#1e293b;
    outline:none; resize:none; transition:border-color .15s, box-shadow .15s;
    font-family:inherit; background:#fafbfc; line-height:1.55;
}
.nt-textarea:focus { border-color:var(--nt-navy); background:#fff; box-shadow:0 0 0 3px rgba(15,23,42,.07); }

.nt-pill-group { display:flex; gap:6px; flex-wrap:wrap; }
.nt-pill {
    border:1.5px solid #e2e8f0; background:#f8fafc; border-radius:8px;
    padding:6px 12px; font-size:11px; font-weight:700; color:#64748b;
    cursor:pointer; transition:all .15s; white-space:nowrap;
}
.nt-pill:hover { border-color:#cbd5e1; background:#fff; }
.nt-pill.active-alta     { background:#fef2f2; border-color:#fca5a5; color:var(--nt-red); }
.nt-pill.active-normal   { background:#f0fdf4; border-color:#86efac; color:var(--nt-green); }
.nt-pill.active-baja     { background:#f8fafc; border-color:#cbd5e1; color:#64748b; }
.nt-pill.active-nota     { background:#fdf4ff; border-color:#d8b4fe; color:var(--nt-purple); }
.nt-pill.active-tarea    { background:#eff6ff; border-color:#93c5fd; color:var(--nt-blue); }
.nt-pill.active-reunion  { background:#fff7ed; border-color:#fdba74; color:var(--nt-amber); }

/* ── Upload zone ────────────────────────────────── */
.nt-dropzone {
    border:2px dashed #e2e8f0; border-radius:10px; padding:0;
    background:#fafbfc; transition:border-color .15s, background .15s;
    overflow:hidden;
}
.nt-dropzone:hover { border-color:#94a3b8; background:#f1f5f9; }
.nt-dropzone-label {
    display:flex; flex-direction:column; align-items:center; gap:6px;
    padding:18px 12px; cursor:pointer; text-align:center;
}
.nt-dropzone-label input[type=file] { display:none; }
.nt-dropzone-icon { color:#94a3b8; }
.nt-dropzone-text { font-size:12px; font-weight:700; color:#64748b; }
.nt-dropzone-hint { font-size:10px; color:#94a3b8; font-weight:600; }

.nt-file-queue { display:flex; flex-direction:column; gap:1px; border-top:1px solid #f1f5f9; }
.nt-file-item {
    display:flex; align-items:center; gap:8px; padding:8px 12px;
    background:#fff; border-bottom:1px solid #f8fafc;
}
.nt-file-item:last-child { border-bottom:none; }
.nt-file-icon { flex-shrink:0; color:#64748b; }
.nt-file-info { flex:1; min-width:0; }
.nt-file-name { font-size:11px; font-weight:700; color:#1e293b; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.nt-file-size { font-size:10px; color:#94a3b8; font-weight:600; margin-top:1px; }
.nt-file-remove { background:none; border:none; color:#cbd5e1; cursor:pointer; padding:4px; border-radius:5px; display:flex; align-items:center; transition:color .12s, background .12s; }
.nt-file-remove:hover { color:var(--nt-red); background:#fee2e2; }

/* ── Submit ─────────────────────────────────────── */
.nt-submit {
    width:100%; background:linear-gradient(135deg, var(--nt-red), #be123c);
    color:#fff; border:none; border-radius:10px; padding:12px;
    font-size:12px; font-weight:800; letter-spacing:.04em;
    cursor:pointer; display:flex; align-items:center; justify-content:center;
    gap:8px; transition:filter .15s, transform .1s;
    box-shadow:0 2px 8px rgba(225,29,72,.3);
}
.nt-submit:hover { filter:brightness(1.08); transform:translateY(-1px); box-shadow:0 4px 14px rgba(225,29,72,.35); }
.nt-submit:active { transform:translateY(0); }

/* ── Divider ────────────────────────────────────── */
.nt-divider { border:none; border-top:1px solid #f1f5f9; margin:0; }

@media(max-width:900px){
    .nt-grid { grid-template-columns:1fr; }
    .nt-kpis { grid-template-columns:repeat(2,1fr); }
    .nt-form-panel { position:static; }
}
</style>

@php
    $notas       = $this->getNotasFiltradas();
    $todas       = $this->notas;
    $pendientes  = collect($todas)->where('completada', false)->count();
    $completadas = collect($todas)->where('completada', true)->count();
    $alta        = collect($todas)->where('prioridad', 'alta')->count();

    $priColors = ['alta'=>'nt-badge-alta','normal'=>'nt-badge-normal','baja'=>'nt-badge-baja'];
    $catColors = ['tarea'=>'nt-badge-tarea','nota'=>'nt-badge-nota','reunion'=>'nt-badge-reunion'];
    $priLabel  = ['alta'=>'🔴 Alta','normal'=>'🟢 Normal','baja'=>'⚪ Baja'];
    $catLabel  = ['tarea'=>'✅ Tarea','nota'=>'📝 Nota','reunion'=>'📅 Reunión'];

    function fmtSize(int $bytes): string {
        if ($bytes >= 1048576) return round($bytes/1048576,1).' MB';
        if ($bytes >= 1024)    return round($bytes/1024).' KB';
        return $bytes.' B';
    }
@endphp

<div class="nt-page">

    {{-- ── KPIs ── --}}
    <div class="nt-kpis">
        <div class="nt-kpi">
            <div class="nt-kpi-icon" style="background:#f1f5f9">
                <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="var(--nt-navy)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <div><div class="nt-kpi-val">{{ count($todas) }}</div><div class="nt-kpi-lbl">Total</div></div>
        </div>
        <div class="nt-kpi">
            <div class="nt-kpi-icon" style="background:#fffbeb">
                <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="var(--nt-amber)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div><div class="nt-kpi-val" style="color:var(--nt-amber)">{{ $pendientes }}</div><div class="nt-kpi-lbl">Pendientes</div></div>
        </div>
        <div class="nt-kpi">
            <div class="nt-kpi-icon" style="background:#f0fdf4">
                <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="var(--nt-green)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            </div>
            <div><div class="nt-kpi-val" style="color:var(--nt-green)">{{ $completadas }}</div><div class="nt-kpi-lbl">Completadas</div></div>
        </div>
        <div class="nt-kpi">
            <div class="nt-kpi-icon" style="background:#fef2f2">
                <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="var(--nt-red)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
            </div>
            <div><div class="nt-kpi-val" style="color:var(--nt-red)">{{ $alta }}</div><div class="nt-kpi-lbl">Alta prioridad</div></div>
        </div>
    </div>

    {{-- ── Grid ── --}}
    <div class="nt-grid">

        {{-- ── Lista ── --}}
        <div class="nt-panel">
            <div class="nt-panel-head">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="var(--nt-navy)" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <span class="nt-panel-title">Mis Notas y Tareas</span>
                <span style="margin-left:auto;font-size:10px;font-weight:700;color:#94a3b8;background:#f1f5f9;padding:3px 10px;border-radius:99px;">{{ count($notas) }} elemento(s)</span>
            </div>

            <div class="nt-filters">
                @foreach(['todas'=>'Todas','pendientes'=>'Pendientes','completadas'=>'Completadas','alta'=>'🔴 Alta','tarea'=>'✅ Tareas'] as $val=>$lbl)
                <button class="nt-filter-btn {{ $filtro===$val?'active':'' }}" wire:click="$set('filtro','{{ $val }}')">{{ $lbl }}</button>
                @endforeach
            </div>

            @forelse($notas as $nota)
            <div class="nt-nota {{ $nota['completada']?'completada':'' }}">

                {{-- Checkbox --}}
                <button class="nt-check {{ $nota['completada']?'done':'' }}" wire:click="toggleCompletar({{ $nota['id'] }})">
                    @if($nota['completada'])
                    <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="#fff" stroke-width="3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    @endif
                </button>

                {{-- Cuerpo --}}
                <div class="nt-nota-body">
                    <div class="nt-nota-meta">
                        <span class="nt-badge {{ $priColors[$nota['prioridad']]??'nt-badge-normal' }}">{{ $priLabel[$nota['prioridad']]??$nota['prioridad'] }}</span>
                        <span class="nt-badge {{ $catColors[$nota['categoria']]??'nt-badge-nota' }}">{{ $catLabel[$nota['categoria']]??$nota['categoria'] }}</span>
                        @if(count($nota['attachments']))
                        <span style="font-size:10px;color:#94a3b8;display:inline-flex;align-items:center;gap:3px;">
                            <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                            {{ count($nota['attachments']) }}
                        </span>
                        @endif
                        <span class="nt-hora">{{ $nota['hora'] }}</span>
                    </div>
                    <div class="nt-texto">{{ $nota['texto'] }}</div>

                    {{-- Adjuntos --}}
                    @if(count($nota['attachments']))
                    <div class="nt-attachments">
                        @foreach($nota['attachments'] as $att)
                            @if(str_starts_with($att['mime'], 'image/'))
                            <div class="nt-att-img-wrap">
                                <a href="{{ Storage::url($att['path']) }}" target="_blank">
                                    <img src="{{ Storage::url($att['path']) }}" alt="{{ $att['nombre'] }}" loading="lazy">
                                </a>
                                <button class="nt-att-del" wire:click="eliminarAdjunto({{ $att['id'] }})" title="Eliminar">×</button>
                            </div>
                            @else
                            <div style="display:inline-flex;align-items:center;gap:0;">
                                <a href="{{ Storage::url($att['path']) }}" target="_blank" class="nt-att-file" style="border-radius:9px 0 0 9px;border-right:none;">
                                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                    <span class="nt-att-file-name">{{ $att['nombre'] }}</span>
                                    <span style="font-size:9px;color:#94a3b8;white-space:nowrap;">{{ fmtSize($att['size']) }}</span>
                                </a>
                                <button class="nt-att-file" style="border-radius:0 9px 9px 0;border-left:none;padding:6px 8px;cursor:pointer;" wire:click="eliminarAdjunto({{ $att['id'] }})" title="Eliminar adjunto">
                                    <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="var(--nt-red)" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                            @endif
                        @endforeach
                    </div>
                    @endif
                </div>

                {{-- Eliminar nota --}}
                <div class="nt-actions">
                    <button class="nt-action-btn" wire:click="eliminar({{ $nota['id'] }})" title="Eliminar nota" wire:confirm="¿Eliminar esta nota y sus adjuntos?">
                        <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </div>
            @empty
            <div class="nt-empty">
                <svg width="52" height="52" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1" style="color:#e2e8f0;margin:0 auto 16px;display:block"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <p style="font-size:13px;font-weight:600;color:#94a3b8;margin:0 0 6px;">No hay elementos en esta vista.</p>
                <p style="font-size:12px;color:#cbd5e1;margin:0;">Agrega una nota o tarea desde el panel derecho.</p>
            </div>
            @endforelse
        </div>

        {{-- ── Formulario ── --}}
        <div class="nt-form-panel">
            <div class="nt-form-head">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#fff" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                <span class="nt-form-title">Nueva Nota / Tarea</span>
            </div>
            <div class="nt-form-body">

                {{-- Descripción --}}
                <div class="nt-field">
                    <label class="nt-label">Descripción</label>
                    <textarea wire:model="texto" class="nt-textarea" rows="4"
                        placeholder="Escribe una nota, tarea o recordatorio..."
                        wire:keydown.ctrl.enter="guardar"></textarea>
                    <span style="font-size:9px;color:#cbd5e1;font-weight:600;">Ctrl + Enter para guardar rápido</span>
                </div>

                <hr class="nt-divider">

                {{-- Prioridad --}}
                <div class="nt-field">
                    <label class="nt-label">Prioridad</label>
                    <div class="nt-pill-group">
                        @foreach(['alta'=>'🔴 Alta','normal'=>'🟢 Normal','baja'=>'⚪ Baja'] as $val=>$lbl)
                        <button type="button" class="nt-pill {{ $prioridad===$val?'active-'.$val:'' }}" wire:click="$set('prioridad','{{ $val }}')">{{ $lbl }}</button>
                        @endforeach
                    </div>
                </div>

                {{-- Tipo --}}
                <div class="nt-field">
                    <label class="nt-label">Tipo</label>
                    <div class="nt-pill-group">
                        @foreach(['nota'=>'📝 Nota','tarea'=>'✅ Tarea','reunion'=>'📅 Reunión'] as $val=>$lbl)
                        <button type="button" class="nt-pill {{ $categoria===$val?'active-'.$val:'' }}" wire:click="$set('categoria','{{ $val }}')">{{ $lbl }}</button>
                        @endforeach
                    </div>
                </div>

                <hr class="nt-divider">

                {{-- Adjuntos --}}
                <div class="nt-field">
                    <label class="nt-label">Adjuntos <span style="color:#cbd5e1;font-weight:600;text-transform:none;letter-spacing:0;">(PDF, imágenes · máx 10 MB c/u)</span></label>
                    <div class="nt-dropzone">
                        <label class="nt-dropzone-label">
                            <input type="file" wire:model="adjuntos" multiple accept=".pdf,.jpg,.jpeg,.png,.gif,.webp">
                            <div class="nt-dropzone-icon">
                                <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                            </div>
                            <span class="nt-dropzone-text">Clic para seleccionar archivos</span>
                            <span class="nt-dropzone-hint">PDF · JPG · PNG · GIF · WEBP</span>
                        </label>

                        @if(count($adjuntos))
                        <div class="nt-file-queue">
                            @foreach($adjuntos as $i => $arch)
                            <div class="nt-file-item">
                                @if(str_starts_with($arch->getMimeType(), 'image/'))
                                <svg class="nt-file-icon" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="var(--nt-purple)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                @else
                                <svg class="nt-file-icon" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="var(--nt-red)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                @endif
                                <div class="nt-file-info">
                                    <div class="nt-file-name">{{ $arch->getClientOriginalName() }}</div>
                                    <div class="nt-file-size">{{ fmtSize($arch->getSize()) }}</div>
                                </div>
                                <button type="button" class="nt-file-remove" wire:click="$set('adjuntos.{{ $i }}', null)" title="Quitar">
                                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>

                <button class="nt-submit" wire:click="guardar" wire:loading.attr="disabled">
                    <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <span wire:loading.remove wire:target="guardar">Guardar</span>
                    <span wire:loading wire:target="guardar">Guardando...</span>
                </button>

            </div>
        </div>

    </div>
</div>
</x-filament-panels::page>
