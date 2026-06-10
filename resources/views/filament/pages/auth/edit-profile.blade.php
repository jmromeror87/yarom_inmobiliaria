<x-filament-panels::page>
@php
    $user  = auth()->user();
    $name  = $user->name ?? 'Usuario';
    $email = $user->email ?? '';
    $ini   = strtoupper(substr($name, 0, 2));
    $rol   = method_exists($user, 'getRoleNames') ? ($user->getRoleNames()->first() ?? 'Sin rol') : 'Sin rol';
    $desde = $user->created_at?->format('M Y') ?? '—';
    $notas = count(session('yarom_inmo_notas_' . $user->id, []));

    $rolMeta = match(strtolower($rol)) {
        'admin', 'administrador', 'super_admin' => ['bg' => '#fef2f2', 'color' => '#E11D48', 'icon' => '⚙️'],
        'agente', 'asesor'                       => ['bg' => '#eff6ff', 'color' => '#2563EB', 'icon' => '🏢'],
        'contador'                               => ['bg' => '#ede9fe', 'color' => '#7c3aed', 'icon' => '🧮'],
        'gerente'                                => ['bg' => '#fff7ed', 'color' => '#c2410c', 'icon' => '📊'],
        default                                  => ['bg' => '#f1f5f9', 'color' => '#475569', 'icon' => '👤'],
    };
@endphp

<style>
:root{ --pc:#E11D48; --pn:#0F172A; }

/* ── HERO ── */
.pf-hero {
    background: linear-gradient(135deg, var(--pn) 0%, #1e2d45 60%, var(--pc) 100%);
    border-radius: 1.5rem; padding: 32px 36px;
    display: flex; align-items: center; gap: 24px;
    margin-bottom: 24px; position: relative; overflow: hidden;
    box-shadow: 0 10px 32px -6px rgba(15,23,42,.35);
}
.pf-hero::before { content:''; position:absolute; top:-40px; right:-40px; width:200px; height:200px; border-radius:50%; background:rgba(255,255,255,.05); pointer-events:none; }
.pf-hero::after  { content:''; position:absolute; bottom:-50px; right:100px; width:140px; height:140px; border-radius:50%; background:rgba(225,29,72,.12); pointer-events:none; }
.pf-avatar { width:72px; height:72px; border-radius:18px; background:rgba(255,255,255,.15); border:3px solid rgba(255,255,255,.3); color:#fff; font-size:22px; font-weight:900; display:flex; align-items:center; justify-content:center; flex-shrink:0; position:relative; }
.pf-status-dot { position:absolute; bottom:3px; right:3px; width:12px; height:12px; background:#4ade80; border-radius:50%; border:2px solid #fff; }
.pf-hero-name  { font-size:22px; font-weight:900; color:#fff; letter-spacing:-.03em; margin-bottom:3px; }
.pf-hero-email { font-size:12px; color:rgba(255,255,255,.6); font-weight:500; margin-bottom:10px; }
.pf-hero-badges{ display:flex; gap:7px; flex-wrap:wrap; }
.pf-badge { display:inline-flex; align-items:center; gap:4px; background:rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.18); border-radius:99px; padding:3px 10px; font-size:11px; font-weight:700; color:#fff; }
.pf-stats { display:flex; flex-direction:column; gap:7px; align-items:flex-end; flex-shrink:0; margin-left:auto; }
.pf-stat { background:rgba(255,255,255,.1); border:1px solid rgba(255,255,255,.15); border-radius:10px; padding:8px 16px; text-align:center; min-width:80px; }
.pf-stat-val { font-size:18px; font-weight:900; color:#fff; line-height:1; }
.pf-stat-lbl { font-size:9px; font-weight:700; color:rgba(255,255,255,.55); text-transform:uppercase; letter-spacing:.06em; margin-top:1px; }

/* ── WRAP principal — sidebar DERECHO fijo ── */
.pf-wrap {
    display: flex;
    gap: 20px;
    align-items: flex-start;
}
.pf-form-col {
    flex: 1;
    min-width: 0;
}
.pf-sidebar {
    width: 280px;
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
    gap: 14px;
    position: sticky;
    top: 80px;
}

/* ── Tarjetas sidebar ── */
.pf-card { background:#fff; border:1px solid #e2e8f0; border-radius:1.1rem; overflow:hidden; box-shadow:0 2px 6px rgba(0,0,0,.04); }
.pf-card-head { padding:14px 18px; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; gap:9px; }
.pf-card-icon { width:28px; height:28px; border-radius:7px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.pf-card-title { font-size:12px; font-weight:800; color:#0f172a; }
.pf-card-body  { padding:6px 14px 14px; }
.pf-info-row   { display:flex; align-items:center; gap:10px; padding:9px 0; border-bottom:1px solid #f8fafc; }
.pf-info-row:last-child { border-bottom:none; }
.pf-info-icon  { width:28px; height:28px; border-radius:7px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.pf-info-lbl   { font-size:9px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#94a3b8; }
.pf-info-val   { font-size:12px; font-weight:600; color:#1e293b; margin-top:1px; }

/* ── Override form sections ── */
.pf-form-col .fi-section { background:#fff!important; border:1px solid #e2e8f0!important; border-radius:1.1rem!important; box-shadow:0 2px 6px rgba(0,0,0,.04)!important; margin-bottom:14px!important; overflow:hidden!important; }
.pf-form-col .fi-section-header { padding:13px 18px!important; border-bottom:1px solid #f1f5f9!important; background:#fafbfc!important; }
.pf-form-col .fi-section-header-heading { font-size:13px!important; font-weight:800!important; color:#0f172a!important; }
.pf-form-col .fi-section-header-description { font-size:11px!important; color:#94a3b8!important; }
.pf-form-col .fi-section-content-ctn { padding:0!important; }
.pf-form-col .fi-section-content { padding:16px 18px!important; }

/* Botón guardar Filament */
.pf-form-col [type="submit"]{background:var(--pc)!important;border-color:var(--pc)!important;}
.pf-btn-back { background:transparent; color:#64748b; border:1.5px solid #e2e8f0; border-radius:9px; padding:8px 16px; font-size:12px; font-weight:700; cursor:pointer; margin-left:8px; }
.pf-btn-back:hover { background:#f1f5f9; }
.pf-actions-row { display:flex; align-items:center; margin-top:6px; }

@media(max-width:800px){ .pf-wrap{flex-direction:column;} .pf-sidebar{width:100%;position:static;} }
</style>

{{-- HERO --}}
<div class="pf-hero">
    <div class="pf-avatar">
        {{ $ini }}
        <span class="pf-status-dot"></span>
    </div>
    <div>
        <div class="pf-hero-name">{{ $name }}</div>
        <div class="pf-hero-email">{{ $email }}</div>
        <div class="pf-hero-badges">
            <span class="pf-badge">{{ $rolMeta['icon'] }} {{ $rol }}</span>
            <span class="pf-badge">
                <svg width="10" height="10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Activo desde {{ $desde }}
            </span>
            <span class="pf-badge" style="background:rgba(74,222,128,.18);border-color:rgba(74,222,128,.3);">
                <span style="width:6px;height:6px;border-radius:50%;background:#4ade80;display:inline-block;"></span>
                En línea
            </span>
        </div>
    </div>
    <div class="pf-stats">
        <div class="pf-stat"><div class="pf-stat-val">{{ $notas }}</div><div class="pf-stat-lbl">Notas</div></div>
        <div class="pf-stat"><div class="pf-stat-val">{{ (int)($user->created_at?->diffInDays(now()) ?? 0) }}</div><div class="pf-stat-lbl">Días activo</div></div>
    </div>
</div>

{{-- WRAP FLEX --}}
<div class="pf-wrap">

    {{-- FORMULARIO --}}
    <div class="pf-form-col">
        {{ $this->content }}
    </div>

    {{-- SIDEBAR --}}
    <div class="pf-sidebar">

        <div class="pf-card">
            <div class="pf-card-head">
                <div class="pf-card-icon" style="background:#f8fafc">
                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="#64748b" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <div class="pf-card-title">Datos de la cuenta</div>
            </div>
            <div class="pf-card-body">
                <div class="pf-info-row">
                    <div class="pf-info-icon" style="background:{{ $rolMeta['bg'] }}"><span style="font-size:12px;">{{ $rolMeta['icon'] }}</span></div>
                    <div><div class="pf-info-lbl">Rol asignado</div><div class="pf-info-val" style="color:{{ $rolMeta['color'] }}">{{ $rol }}</div></div>
                </div>
                <div class="pf-info-row">
                    <div class="pf-info-icon" style="background:#fef2f2">
                        <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="var(--pc)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                    <div style="min-width:0"><div class="pf-info-lbl">Correo</div><div class="pf-info-val" style="word-break:break-all;font-size:11px;">{{ $email }}</div></div>
                </div>
                <div class="pf-info-row">
                    <div class="pf-info-icon" style="background:#eff6ff">
                        <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="#2563EB" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <div><div class="pf-info-lbl">Miembro desde</div><div class="pf-info-val">{{ $user->created_at?->format('d \d\e F, Y') ?? '—' }}</div></div>
                </div>
                <div class="pf-info-row">
                    <div class="pf-info-icon" style="background:#f0fdf4">
                        <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="#16a34a" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div><div class="pf-info-lbl">Estado</div>
                    <div class="pf-info-val" style="color:#15803d;display:flex;align-items:center;gap:4px;">
                        <span style="width:6px;height:6px;border-radius:50%;background:#22c55e;display:inline-block;"></span> Activo
                    </div></div>
                </div>
            </div>
        </div>

        <div class="pf-card" style="border-color:#fde68a;background:#fffbeb;">
            <div style="padding:14px 16px;display:flex;gap:10px;align-items:flex-start;">
                <div style="width:28px;height:28px;border-radius:7px;background:#fef3c7;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="#d97706" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <div>
                    <div style="font-size:11px;font-weight:800;color:#92400e;margin-bottom:3px;">Consejo de seguridad</div>
                    <div style="font-size:11px;color:#a16207;line-height:1.5;font-weight:500;">Usa una contraseña de al menos 8 caracteres con mayúsculas, números y símbolos.</div>
                </div>
            </div>
        </div>

    </div>
</div>

</x-filament-panels::page>
