<x-filament-panels::page>
<style>
.gr-wrap { font-family:'Plus Jakarta Sans',sans-serif; max-width:520px; margin:0 auto; padding-bottom:60px; }

.gr-header { margin-bottom:20px; display:flex; align-items:flex-end; justify-content:space-between; gap:12px; flex-wrap:wrap; }
.gr-header-title { font-size:1.3rem; font-weight:900; color:#0F172A; letter-spacing:-.02em; margin:0 0 2px; }
.dark .gr-header-title { color:#f1f5f9; }
.gr-header-sub { font-size:0.78rem; color:#94a3b8; font-weight:600; }

.gr-hero {
    background:linear-gradient(135deg,#1e3a8a,#E11D48);
    border-radius:18px; padding:22px 24px 20px; margin-bottom:16px;
    box-shadow:0 8px 24px rgba(30,58,138,.25);
    grid-column: 1 / -1;
}
.gr-hero-label { font-size:0.68rem; font-weight:800; color:rgba(255,255,255,.75); text-transform:uppercase; letter-spacing:.08em; margin-bottom:6px; }
.gr-hero-value { font-size:2rem; font-weight:900; color:#fff; letter-spacing:-.03em; line-height:1; margin-bottom:4px; }
.gr-hero-desc { font-size:0.75rem; color:rgba(255,255,255,.85); font-weight:600; }
.gr-hero-row { display:flex; gap:10px; margin-top:14px; flex-wrap:wrap; }
.gr-hero-chip { flex:1; min-width:140px; background:rgba(255,255,255,.14); border-radius:12px; padding:10px 12px; }
.gr-hero-chip-label { font-size:0.62rem; font-weight:700; color:rgba(255,255,255,.7); text-transform:uppercase; letter-spacing:.05em; }
.gr-hero-chip-value { font-size:1.02rem; font-weight:800; color:#fff; margin-top:2px; }

.gr-dashboard { display:grid; grid-template-columns:1fr; gap:16px; align-items:start; }

.gr-section { margin-bottom:0; }
.gr-section-title { font-size:0.78rem; font-weight:800; color:#64748b; text-transform:uppercase; letter-spacing:.06em; margin:0 0 8px 2px; display:flex; align-items:center; gap:6px; }
.dark .gr-section-title { color:#94a3b8; }

.gr-grid2 { display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:10px; }

.gr-kpi {
    background:#fff; border-radius:14px; padding:14px 16px;
    border:1px solid rgba(226,232,240,.8); box-shadow:0 2px 10px rgba(15,23,42,.04);
    transition:box-shadow .15s ease, transform .15s ease;
}
.gr-kpi:hover { box-shadow:0 6px 18px rgba(15,23,42,.08); transform:translateY(-1px); }
.gr-kpi-label { font-size:0.66rem; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.05em; margin-bottom:4px; }
.gr-kpi-value { font-size:1.28rem; font-weight:900; color:#0F172A; letter-spacing:-.02em; line-height:1.1; }
.gr-kpi-desc { font-size:0.68rem; color:#64748b; font-weight:600; margin-top:3px; }

.dark .gr-kpi { background:#1e293b; border-color:rgba(51,65,85,.8); }
.dark .gr-kpi-value { color:#f1f5f9; }

.gr-card {
    background:#fff; border-radius:14px; border:1px solid rgba(226,232,240,.8);
    box-shadow:0 2px 10px rgba(15,23,42,.04); overflow:hidden; margin-bottom:0;
    height:100%; display:flex; flex-direction:column;
}
.dark .gr-card { background:#1e293b; border-color:rgba(51,65,85,.8); }
.gr-card-head { padding:12px 16px; border-bottom:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:center; gap:8px; }
.dark .gr-card-head { border-color:#334155; }
.gr-card-title { font-size:0.8rem; font-weight:800; color:#0F172A; }
.dark .gr-card-title { color:#f1f5f9; }
.gr-card-badge { font-size:0.66rem; font-weight:800; padding:3px 9px; border-radius:20px; white-space:nowrap; }

.gr-row { display:flex; align-items:center; justify-content:space-between; padding:10px 16px; border-bottom:1px solid #f8fafc; gap:10px; }
.dark .gr-row { border-color:#273549; }
.gr-row:last-child { border-bottom:none; }
.gr-row-name { font-size:0.78rem; font-weight:700; color:#0F172A; }
.dark .gr-row-name { color:#e2e8f0; }
.gr-row-sub { font-size:0.68rem; color:#94a3b8; font-weight:600; }
.gr-row-value { font-size:0.82rem; font-weight:800; text-align:right; white-space:nowrap; }

.gr-empty { padding:20px 16px; text-align:center; color:#94a3b8; font-size:0.75rem; font-weight:600; }

.gr-footer { text-align:center; margin-top:24px; font-size:0.65rem; color:#cbd5e1; font-weight:600; }

/* Tablet: 2 columnas para grupos KPI y listas */
@media (min-width: 768px) {
    .gr-hero-value { font-size:2.4rem; }
    .gr-dashboard { grid-template-columns:repeat(2, 1fr); }
}

/* Escritorio: 3 columnas, más espacio de aire */
@media (min-width: 1200px) {
    .gr-wrap { max-width:1400px; }
    .gr-dashboard { grid-template-columns:repeat(3, 1fr); gap:20px; }
}
</style>

<div class="gr-wrap">

    <div class="gr-header">
        <div>
            <div class="gr-header-title">Hola, {{ str(auth()->user()->name)->before(' ') }} 👋</div>
            <div class="gr-header-sub">{{ now()->translatedFormat('l, d \d\e F \d\e Y') }} · Resumen del negocio</div>
        </div>
    </div>

    <div class="gr-dashboard">

        {{-- HERO: Utilidad neta del mes --}}
        <div class="gr-hero">
            <div class="gr-hero-label">Utilidad neta — {{ now()->translatedFormat('F Y') }}</div>
            <div class="gr-hero-value">${{ number_format($utilidadNeta, 0, ',', '.') }}</div>
            <div class="gr-hero-desc">Margen: {{ $margen }}% · Comisión ganada: ${{ number_format($comisionMes, 0, ',', '.') }}</div>
            <div class="gr-hero-row">
                <div class="gr-hero-chip">
                    <div class="gr-hero-chip-label">Ingresos (comisión)</div>
                    <div class="gr-hero-chip-value">${{ number_format($comisionMes, 0, ',', '.') }}</div>
                </div>
                <div class="gr-hero-chip">
                    <div class="gr-hero-chip-label">Gastos del mes</div>
                    <div class="gr-hero-chip-value">${{ number_format($gastosMes, 0, ',', '.') }}</div>
                </div>
                <div class="gr-hero-chip">
                    <div class="gr-hero-chip-label">Cartera pendiente</div>
                    <div class="gr-hero-chip-value">${{ number_format($carteraPendiente, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>

        {{-- HOY --}}
        <div class="gr-section">
            <div class="gr-section-title">📅 Hoy</div>
            <div class="gr-grid2">
                <div class="gr-kpi">
                    <div class="gr-kpi-label">Entró</div>
                    <div class="gr-kpi-value" style="color:#059669;">${{ number_format($ingresoHoy, 0, ',', '.') }}</div>
                    <div class="gr-kpi-desc">Pagos de inquilinos</div>
                </div>
                <div class="gr-kpi">
                    <div class="gr-kpi-label">Salió</div>
                    <div class="gr-kpi-value" style="color:#dc2626;">${{ number_format($giroHoy + $gastosHoy, 0, ',', '.') }}</div>
                    <div class="gr-kpi-desc">Giros + gastos</div>
                </div>
            </div>
        </div>

        {{-- RECAUDO DEL MES --}}
        <div class="gr-section">
            <div class="gr-section-title">💰 Recaudo del mes</div>
            <div class="gr-grid2">
                <div class="gr-kpi">
                    <div class="gr-kpi-label">Facturado</div>
                    <div class="gr-kpi-value">${{ number_format($facturadoMes, 0, ',', '.') }}</div>
                </div>
                <div class="gr-kpi">
                    <div class="gr-kpi-label">Recaudado</div>
                    <div class="gr-kpi-value" style="color:#059669;">${{ number_format($recaudadoMes, 0, ',', '.') }}</div>
                </div>
            </div>
            <div class="gr-grid2">
                <div class="gr-kpi">
                    <div class="gr-kpi-label">Efectividad</div>
                    <div class="gr-kpi-value" style="color:{{ $efectividad >= 80 ? '#059669' : ($efectividad >= 60 ? '#d97706' : '#dc2626') }};">{{ $efectividad }}%</div>
                </div>
                <div class="gr-kpi">
                    <div class="gr-kpi-label">Cartera pendiente</div>
                    <div class="gr-kpi-value" style="color:#dc2626;">${{ number_format($carteraPendiente, 0, ',', '.') }}</div>
                    <div class="gr-kpi-desc">{{ $facturasEnMora }} en mora</div>
                </div>
            </div>
        </div>

        {{-- OCUPACIÓN --}}
        <div class="gr-section">
            <div class="gr-section-title">🏠 Portafolio</div>
            <div class="gr-grid2">
                <div class="gr-kpi">
                    <div class="gr-kpi-label">Ocupación</div>
                    <div class="gr-kpi-value" style="color:{{ $ocupacion >= 80 ? '#059669' : ($ocupacion >= 60 ? '#d97706' : '#dc2626') }};">{{ $ocupacion }}%</div>
                    <div class="gr-kpi-desc">{{ $arrendados }} de {{ $totalInm }} inmuebles</div>
                </div>
                <div class="gr-kpi">
                    <div class="gr-kpi-label">Contratos activos</div>
                    <div class="gr-kpi-value">{{ $contratosActivos }}</div>
                    <div class="gr-kpi-desc">{{ $porVencer30 }} vencen en 30 días</div>
                </div>
            </div>
        </div>

        {{-- GASTOS --}}
        <div class="gr-section">
            <div class="gr-section-title">🧾 Gastos</div>
            <div class="gr-card">
                <div class="gr-card-head">
                    <div class="gr-card-title">{{ $totalGastosMes }} comprobante(s) este mes</div>
                    <span class="gr-card-badge" style="background:#fee2e2;color:#dc2626;">${{ number_format($gastosMes, 0, ',', '.') }}</span>
                </div>
                @forelse($gastosDetalle as $g)
                <div class="gr-row">
                    <div>
                        <div class="gr-row-name">{{ $g->third?->nombre_completo ?? $g->descripcion ?? 'Gasto' }}</div>
                        <div class="gr-row-sub">{{ $g->numero }} · {{ $g->fecha?->format('d/m') }}</div>
                    </div>
                    <div class="gr-row-value" style="color:#dc2626;">${{ number_format($g->total_debitos, 0, ',', '.') }}</div>
                </div>
                @empty
                <div class="gr-empty">Sin gastos registrados este mes.</div>
                @endforelse
            </div>
        </div>

        {{-- INQUILINOS QUE DEBEN --}}
        <div class="gr-section">
            <div class="gr-section-title">🔴 Inquilinos que deben</div>
            <div class="gr-card">
                <div class="gr-card-head">
                    <div class="gr-card-title">{{ $totalInquilinosDeben }} factura(s) pendiente(s) este mes</div>
                </div>
                @forelse($inquilinosDeben as $f)
                <div class="gr-row">
                    <div>
                        <div class="gr-row-name">{{ $f->arrendatario?->nombre_completo ?? '—' }}</div>
                        <div class="gr-row-sub">{{ $f->property?->codigo ?? '—' }} · {{ $f->numero }}</div>
                    </div>
                    <div class="gr-row-value" style="color:#dc2626;">${{ number_format($f->saldo_pendiente, 0, ',', '.') }}</div>
                </div>
                @empty
                <div class="gr-empty">✅ Nadie debe este mes.</div>
                @endforelse
            </div>
        </div>

        {{-- INQUILINOS QUE PAGARON --}}
        <div class="gr-section">
            <div class="gr-section-title">🟢 Inquilinos que pagaron</div>
            <div class="gr-card">
                <div class="gr-card-head">
                    <div class="gr-card-title">{{ $totalInquilinosPagaron }} inquilino(s) al día este mes</div>
                </div>
                @forelse($inquilinosPagaron as $p)
                <div class="gr-row">
                    <div>
                        <div class="gr-row-name">{{ $p->arrendatario?->nombre_completo ?? '—' }}</div>
                        <div class="gr-row-sub">{{ $p->bill?->property?->codigo ?? '—' }} · {{ \Carbon\Carbon::parse($p->fecha_pago)->format('d/m') }}</div>
                    </div>
                    <div class="gr-row-value" style="color:#059669;">${{ number_format($p->total_pagado, 0, ',', '.') }}</div>
                </div>
                @empty
                <div class="gr-empty">Sin pagos registrados este mes.</div>
                @endforelse
            </div>
        </div>

        {{-- PROPIETARIOS PAGADOS --}}
        <div class="gr-section">
            <div class="gr-section-title">🟢 Propietarios pagados</div>
            <div class="gr-card">
                <div class="gr-card-head">
                    <div class="gr-card-title">{{ $totalPropietariosPagados }} giro(s) este mes</div>
                    <span class="gr-card-badge" style="background:#d1fae5;color:#059669;">${{ number_format($montoPropietariosPagados, 0, ',', '.') }}</span>
                </div>
                @forelse($propietariosPagados as $l)
                <div class="gr-row">
                    <div>
                        <div class="gr-row-name">{{ $l->propietario?->nombre_completo ?? '—' }}</div>
                        <div class="gr-row-sub">{{ $l->numero }} · {{ $l->fecha_giro?->format('d/m') }}</div>
                    </div>
                    <div class="gr-row-value" style="color:#059669;">${{ number_format($l->total_giro, 0, ',', '.') }}</div>
                </div>
                @empty
                <div class="gr-empty">Sin giros realizados este mes.</div>
                @endforelse
            </div>
        </div>

        {{-- PROPIETARIOS PENDIENTES --}}
        <div class="gr-section">
            <div class="gr-section-title">🔴 Propietarios sin pagar</div>
            <div class="gr-card">
                <div class="gr-card-head">
                    <div class="gr-card-title">{{ $totalPropietariosPendientes }} pendiente(s)</div>
                    <span class="gr-card-badge" style="background:#fee2e2;color:#dc2626;">${{ number_format($montoPropietariosPendientes, 0, ',', '.') }}</span>
                </div>
                @forelse($propietariosPendientes as $l)
                <div class="gr-row">
                    <div>
                        <div class="gr-row-name">{{ $l->propietario?->nombre_completo ?? '—' }}</div>
                        <div class="gr-row-sub">{{ $l->numero }} · {{ ucfirst($l->estado) }}</div>
                    </div>
                    <div class="gr-row-value" style="color:#dc2626;">${{ number_format($l->total_giro, 0, ',', '.') }}</div>
                </div>
                @empty
                <div class="gr-empty">✅ Todos los propietarios están al día.</div>
                @endforelse
            </div>
        </div>

    </div>

    <div class="gr-footer">Serviarrendar S.A.S. · Panel Gerencial · YarOM ERP</div>

</div>
</x-filament-panels::page>
