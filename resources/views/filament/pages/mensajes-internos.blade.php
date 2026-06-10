<x-filament-panels::page>
<style>
:root {
    --nt-red:    #E11D48;
    --nt-navy:   #0F172A;
    --nt-amber:  #d97706;
    --nt-purple: #7c3aed;
    --nt-blue:   #2563eb;
    --nt-green:  #16a34a;
}

.nt-page { display:flex; flex-direction:column; gap:20px; max-width:100%; }

.nt-kpis { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; }
.nt-kpi {
    background:#fff; border:1px solid #e2e8f0; border-radius:1rem;
    padding:16px 20px; display:flex; align-items:center; gap:14px;
    box-shadow:0 1px 4px rgba(0,0,0,.04);
}
.nt-kpi-icon { width:40px; height:40px; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.nt-kpi-val  { font-size:22px; font-weight:900; color:#0f172a; line-height:1; }
.nt-kpi-lbl  { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#94a3b8; margin-top:2px; }

.nt-grid { display:grid; grid-template-columns:1fr 340px; gap:20px; align-items:start; }

.nt-panel { background:#fff; border:1px solid #e2e8f0; border-radius:1.25rem; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,.04); }
.nt-panel-head { padding:16px 24px; background:#f8fafc; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; gap:10px; }
.nt-panel-title { font-size:13px; font-weight:800; color:#0f172a; }

.nt-filters { display:flex; gap:6px; padding:12px 20px; border-bottom:1px solid #f1f5f9; flex-wrap:wrap; }
.nt-filter-btn { border:1.5px solid #e2e8f0; background:transparent; border-radius:8px; padding:4px 12px; font-size:11px; font-weight:700; color:#64748b; cursor:pointer; transition:all .15s; }
.nt-filter-btn:hover, .nt-filter-btn.active { background:var(--nt-navy); border-color:var(--nt-navy); color:#fff; }

.nt-nota { display:flex; align-items:flex-start; gap:12px; padding:14px 20px; border-bottom:1px solid #f8fafc; transition:background .12s; }
.nt-nota:last-child { border-bottom:none; }
.nt-nota:hover { background:#fafbfc; }
.nt-nota.completada { opacity:.55; }

.nt-check { width:18px; height:18px; border:2px solid #e2e8f0; border-radius:5px; flex-shrink:0; margin-top:2px; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:all .15s; background:transparent; }
.nt-check.done { background:var(--nt-green); border-color:var(--nt-green); }

.nt-nota-body { flex:1; min-width:0; }
.nt-nota-meta { display:flex; align-items:center; gap:6px; margin-bottom:5px; flex-wrap:wrap; }
.nt-badge { display:inline-flex; align-items:center; gap:3px; border-radius:99px; padding:1px 7px; font-size:9px; font-weight:800; text-transform:uppercase; letter-spacing:.06em; }
.nt-badge-alta    { background:#fef2f2; color:var(--nt-red); }
.nt-badge-normal  { background:#f0fdf4; color:var(--nt-green); }
.nt-badge-baja    { background:#f8fafc; color:#94a3b8; }
.nt-badge-tarea   { background:#eff6ff; color:var(--nt-blue); }
.nt-badge-nota    { background:#fdf4ff; color:var(--nt-purple); }
.nt-badge-reunion { background:#fff7ed; color:var(--nt-amber); }
.nt-hora  { font-size:9px; color:#cbd5e1; font-weight:600; margin-left:auto; }
.nt-texto { font-size:13px; font-weight:500; color:#1e293b; line-height:1.55; }
.completada .nt-texto { text-decoration:line-through; color:#94a3b8; }

.nt-actions { display:flex; gap:4px; margin-top:1px; }
.nt-action-btn { background:none; border:none; color:#cbd5e1; cursor:pointer; padding:4px; border-radius:6px; transition:color .12s, background .12s; display:flex; align-items:center; }
.nt-action-btn:hover { color:var(--nt-red); background:#fee2e2; }

.nt-empty { padding:56px 24px; text-align:center; color:#94a3b8; font-size:13px; font-weight:500; }

.nt-form-panel { background:#fff; border:1px solid #e2e8f0; border-radius:1.25rem; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,.04); position:sticky; top:80px; }
.nt-form-head { padding:14px 20px; background:linear-gradient(135deg, var(--nt-navy), #1e2d45); display:flex; align-items:center; gap:8px; }
.nt-form-title { font-size:12px; font-weight:800; color:#fff; letter-spacing:.02em; }
.nt-form-body  { padding:18px; display:flex; flex-direction:column; gap:12px; }

.nt-label { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#94a3b8; margin-bottom:5px; }
.nt-textarea { width:100%; border:1.5px solid #e2e8f0; border-radius:10px; padding:10px 12px; font-size:13px; font-weight:500; color:#1e293b; outline:none; resize:none; transition:border-color .15s, box-shadow .15s; font-family:inherit; }
.nt-textarea:focus { border-color:var(--nt-navy); box-shadow:0 0 0 3px rgba(15,23,42,.08); }

.nt-pill-group { display:flex; gap:5px; flex-wrap:wrap; }
.nt-pill { border:1.5px solid #e2e8f0; background:#f8fafc; border-radius:8px; padding:5px 11px; font-size:11px; font-weight:700; color:#64748b; cursor:pointer; transition:all .15s; white-space:nowrap; line-height:1.4; }
.nt-pill:hover { border-color:#cbd5e1; background:#fff; }
.nt-pill.active-alta     { background:#fef2f2; border-color:#fca5a5; color:var(--nt-red); }
.nt-pill.active-normal   { background:#f0fdf4; border-color:#86efac; color:var(--nt-green); }
.nt-pill.active-baja     { background:#f8fafc; border-color:#cbd5e1; color:#64748b; }
.nt-pill.active-nota     { background:#fdf4ff; border-color:#d8b4fe; color:var(--nt-purple); }
.nt-pill.active-tarea    { background:#eff6ff; border-color:#93c5fd; color:var(--nt-blue); }
.nt-pill.active-reunion  { background:#fff7ed; border-color:#fdba74; color:var(--nt-amber); }

.nt-submit { width:100%; background:var(--nt-red); color:#fff; border:none; border-radius:10px; padding:11px; font-size:12px; font-weight:800; letter-spacing:.03em; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:7px; transition:filter .15s, transform .1s; }
.nt-submit:hover { filter:brightness(1.1); transform:translateY(-1px); }
.nt-submit:active { transform:translateY(0); }

@media(max-width:860px){
    .nt-grid{grid-template-columns:1fr;}
    .nt-kpis{grid-template-columns:repeat(2,1fr);}
    .nt-form-panel{position:static;}
}
</style>

@php
    $notas       = $this->getNotasFiltradas();
    $todas       = $this->notas;
    $pendientes  = collect($todas)->where('completada', false)->count();
    $completadas = collect($todas)->where('completada', true)->count();
    $alta        = collect($todas)->where('prioridad', 'alta')->count();

    $priColors = ['alta' => 'nt-badge-alta', 'normal' => 'nt-badge-normal', 'baja' => 'nt-badge-baja'];
    $catColors = ['tarea' => 'nt-badge-tarea', 'nota' => 'nt-badge-nota', 'reunion' => 'nt-badge-reunion'];
    $priLabel  = ['alta' => '🔴 Alta', 'normal' => '🟢 Normal', 'baja' => '⚪ Baja'];
    $catLabel  = ['tarea' => '✅ Tarea', 'nota' => '📝 Nota', 'reunion' => '📅 Reunión'];
@endphp

<div class="nt-page">

    {{-- KPIs --}}
    <div class="nt-kpis">
        <div class="nt-kpi">
            <div class="nt-kpi-icon" style="background:#f1f5f9">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="var(--nt-navy)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <div><div class="nt-kpi-val">{{ count($todas) }}</div><div class="nt-kpi-lbl">Total</div></div>
        </div>
        <div class="nt-kpi">
            <div class="nt-kpi-icon" style="background:#fffbeb">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="var(--nt-amber)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div><div class="nt-kpi-val" style="color:var(--nt-amber)">{{ $pendientes }}</div><div class="nt-kpi-lbl">Pendientes</div></div>
        </div>
        <div class="nt-kpi">
            <div class="nt-kpi-icon" style="background:#f0fdf4">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="var(--nt-green)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            </div>
            <div><div class="nt-kpi-val" style="color:var(--nt-green)">{{ $completadas }}</div><div class="nt-kpi-lbl">Completadas</div></div>
        </div>
        <div class="nt-kpi">
            <div class="nt-kpi-icon" style="background:#fef2f2">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="var(--nt-red)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
            </div>
            <div><div class="nt-kpi-val" style="color:var(--nt-red)">{{ $alta }}</div><div class="nt-kpi-lbl">Alta prioridad</div></div>
        </div>
    </div>

    {{-- GRID --}}
    <div class="nt-grid">

        {{-- LISTA --}}
        <div class="nt-panel">
            <div class="nt-panel-head">
                <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="var(--nt-navy)" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <span class="nt-panel-title">Mis Notas y Tareas</span>
                <span style="margin-left:auto;font-size:10px;font-weight:700;color:#94a3b8;">{{ count($notas) }} elemento(s)</span>
            </div>
            <div class="nt-filters">
                @foreach(['todas' => 'Todas', 'pendientes' => 'Pendientes', 'completadas' => 'Completadas', 'alta' => '🔴 Alta prioridad', 'tarea' => '✅ Tareas'] as $val => $lbl)
                <button class="nt-filter-btn {{ $filtro === $val ? 'active' : '' }}" wire:click="$set('filtro', '{{ $val }}')">{{ $lbl }}</button>
                @endforeach
            </div>

            @forelse($notas as $i => $nota)
            <div class="nt-nota {{ $nota['completada'] ? 'completada' : '' }}">
                <button class="nt-check {{ $nota['completada'] ? 'done' : '' }}" wire:click="toggleCompletar({{ $i }})">
                    @if($nota['completada'])
                    <svg width="10" height="10" fill="none" viewBox="0 0 24 24" stroke="#fff" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    @endif
                </button>
                <div class="nt-nota-body">
                    <div class="nt-nota-meta">
                        <span class="nt-badge {{ $priColors[$nota['prioridad']] ?? 'nt-badge-normal' }}">{{ $priLabel[$nota['prioridad']] ?? $nota['prioridad'] }}</span>
                        <span class="nt-badge {{ $catColors[$nota['categoria']] ?? 'nt-badge-nota' }}">{{ $catLabel[$nota['categoria']] ?? $nota['categoria'] }}</span>
                        <span class="nt-hora">{{ $nota['hora'] }}</span>
                    </div>
                    <div class="nt-texto">{{ $nota['texto'] }}</div>
                </div>
                <div class="nt-actions">
                    <button class="nt-action-btn" wire:click="eliminar({{ $i }})" title="Eliminar">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </div>
            @empty
            <div class="nt-empty">
                <svg width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.2" style="color:#e2e8f0;margin:0 auto 14px;display:block"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                No hay elementos en esta vista.<br>
                <span style="color:#cbd5e1;font-size:12px;">Agrega una nota o tarea desde el panel derecho.</span>
            </div>
            @endforelse
        </div>

        {{-- FORMULARIO --}}
        <div class="nt-form-panel">
            <div class="nt-form-head">
                <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="#fff" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                <span class="nt-form-title">Nueva Nota / Tarea</span>
            </div>
            <div class="nt-form-body">
                <div>
                    <div class="nt-label">Descripción</div>
                    <textarea wire:model="texto" class="nt-textarea" rows="4" placeholder="Escribe una nota, tarea o recordatorio..." wire:keydown.ctrl.enter="guardar"></textarea>
                    <div style="font-size:9px;color:#cbd5e1;margin-top:4px;font-weight:600;">Ctrl + Enter para guardar rápido</div>
                </div>
                <div>
                    <div class="nt-label">Prioridad</div>
                    <div class="nt-pill-group">
                        @foreach(['alta' => '🔴 Alta', 'normal' => '🟢 Normal', 'baja' => '⚪ Baja'] as $val => $lbl)
                        <button type="button" class="nt-pill {{ $prioridad === $val ? 'active-'.$val : '' }}" wire:click="$set('prioridad', '{{ $val }}')">{{ $lbl }}</button>
                        @endforeach
                    </div>
                </div>
                <div>
                    <div class="nt-label">Tipo</div>
                    <div class="nt-pill-group">
                        @foreach(['nota' => '📝 Nota', 'tarea' => '✅ Tarea', 'reunion' => '📅 Reunión'] as $val => $lbl)
                        <button type="button" class="nt-pill {{ $categoria === $val ? 'active-'.$val : '' }}" wire:click="$set('categoria', '{{ $val }}')">{{ $lbl }}</button>
                        @endforeach
                    </div>
                </div>
                <button class="nt-submit" wire:click="guardar">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    Guardar
                </button>
            </div>
        </div>

    </div>
</div>
</x-filament-panels::page>
