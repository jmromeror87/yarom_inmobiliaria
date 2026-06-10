<x-filament-panels::page>
<style>
.yr-db { font-family:'Plus Jakarta Sans',sans-serif; padding:0 0 80px; }

/* ── Greeting ── */
.yr-greeting {
    display:flex; align-items:center; justify-content:space-between;
    margin-bottom:28px; flex-wrap:wrap; gap:12px;
}
.yr-greeting-title { font-size:1.55rem; font-weight:900; color:#0F172A; letter-spacing:-.03em; margin:0 0 4px; }
.yr-greeting-sub   { font-size:0.8rem; color:#94a3b8; font-weight:600; }
.yr-btn-primary {
    background:linear-gradient(135deg,#1e3a8a,#E11D48); color:#fff; border:none;
    padding:10px 22px; border-radius:12px; font-size:0.8rem; font-weight:800;
    letter-spacing:0.03em; cursor:pointer; text-decoration:none; display:inline-flex;
    align-items:center; gap:8px; transition:transform .12s, box-shadow .12s;
    box-shadow:0 4px 14px rgba(225,29,72,.35);
}
.yr-btn-primary:hover { transform:translateY(-1px); box-shadow:0 6px 20px rgba(225,29,72,.45); color:#fff; }
.yr-btn-secondary {
    background:#fff; color:#0F172A; border:1.5px solid #e2e8f0;
    padding:10px 20px; border-radius:10px; font-size:0.78rem; font-weight:800;
    letter-spacing:0.04em; cursor:pointer; text-decoration:none; display:inline-flex;
    align-items:center; gap:7px; transition:all .15s;
}
.yr-btn-secondary:hover { border-color:#E11D48; color:#E11D48; }

/* ── KPI grid ── */
.yr-kpi-grid { display:grid; grid-template-columns:repeat(6,1fr); gap:14px; margin-bottom:22px; }
@media(max-width:1400px) { .yr-kpi-grid { grid-template-columns:repeat(3,1fr); } }
@media(max-width:900px)  { .yr-kpi-grid { grid-template-columns:repeat(2,1fr); } }

.yr-kpi {
    background:#fff; border-radius:16px; padding:18px 20px;
    border:1px solid rgba(226,232,240,.8);
    box-shadow:0 2px 12px rgba(15,23,42,.05);
    display:flex; flex-direction:column; gap:10px;
    text-decoration:none; transition:box-shadow .15s, transform .15s;
}
.yr-kpi:hover { box-shadow:0 8px 24px rgba(15,23,42,.1); transform:translateY(-2px); }
.yr-kpi-icon {
    width:40px; height:40px; border-radius:11px;
    display:flex; align-items:center; justify-content:center; flex-shrink:0;
}
.yr-kpi-icon svg { width:20px; height:20px; }
.yr-kpi-label { font-size:0.7rem; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.08em; }
.yr-kpi-value { font-size:1.55rem; font-weight:900; color:#0F172A; letter-spacing:-.03em; line-height:1; }
.yr-kpi-desc  { font-size:0.72rem; color:#64748b; font-weight:500; }
.yr-badge {
    display:inline-flex; align-items:center; gap:5px;
    font-size:0.68rem; font-weight:800; padding:3px 9px; border-radius:20px;
}

/* ── Sección dos columnas ── */
.yr-grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px; }
.yr-grid-3 { display:grid; grid-template-columns:1fr 1fr 340px; gap:16px; margin-bottom:16px; }
@media(max-width:1200px) { .yr-grid-2,.yr-grid-3 { grid-template-columns:1fr; } }

.yr-card {
    background:#fff; border-radius:16px; border:1px solid rgba(226,232,240,.8);
    box-shadow:0 2px 12px rgba(15,23,42,.05); overflow:hidden;
}
.yr-card-header {
    padding:16px 20px; border-bottom:1px solid #f1f5f9;
    display:flex; align-items:center; justify-content:space-between;
}
.yr-card-title { font-size:0.82rem; font-weight:800; color:#0F172A; }
.yr-card-sub   { font-size:0.7rem; color:#94a3b8; font-weight:600; }
.yr-card-body  { padding:20px; }

/* ── Tabla actividad ── */
.yr-table { width:100%; border-collapse:collapse; }
.yr-table th { font-size:0.65rem; font-weight:800; color:#94a3b8; text-transform:uppercase; letter-spacing:.08em; padding:0 12px 10px; text-align:left; }
.yr-table td { font-size:0.78rem; color:#334155; padding:10px 12px; border-top:1px solid #f8fafc; }
.yr-table tr:hover td { background:#f8fafc; }
.yr-estado {
    font-size:0.65rem; font-weight:800; padding:3px 9px; border-radius:20px;
    text-transform:uppercase; letter-spacing:.06em;
}
.yr-estado.pendiente { background:#fef3c7; color:#d97706; }
.yr-estado.parcial   { background:#dbeafe; color:#2563EB; }
.yr-estado.pagada    { background:#d1fae5; color:#059669; }
.yr-estado.en_mora   { background:#fee2e2; color:#dc2626; }
.yr-estado.vencida   { background:#fce7f3; color:#db2777; }

/* ── Accesos rápidos ── */
.yr-accesos { display:grid; grid-template-columns:1fr 1fr; gap:10px; padding:16px; }
.yr-acceso {
    display:flex; align-items:center; gap:10px; padding:12px 14px;
    border-radius:12px; border:1.5px solid #f1f5f9; background:#fafafa;
    text-decoration:none; font-size:0.78rem; font-weight:700; color:#334155;
    transition:all .15s;
}
.yr-acceso:hover { border-color:currentColor; background:#fff; }
.yr-acceso-icon { width:34px; height:34px; border-radius:9px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.yr-acceso-icon svg { width:16px; height:16px; }

/* ── Barra progreso ── */
.yr-progress-bar { height:6px; border-radius:3px; background:#f1f5f9; overflow:hidden; margin-top:6px; }
.yr-progress-fill { height:100%; border-radius:3px; transition:width .4s; }

/* ── Contratos vencer ── */
.yr-vencer-item { display:flex; align-items:center; gap:12px; padding:10px 0; border-bottom:1px solid #f8fafc; }
.yr-vencer-item:last-child { border-bottom:none; }
.yr-vencer-avatar { width:36px; height:36px; border-radius:9px; display:flex; align-items:center; justify-content:center; font-size:13px; font-weight:900; color:#fff; flex-shrink:0; }
</style>

<div class="yr-db">

    {{-- ── GREETING ── --}}
    <div class="yr-greeting">
        <div>
            <h1 class="yr-greeting-title">
                @php
                    $hora = now()->hour;
                    $saludo = $hora < 12 ? 'Buenos días' : ($hora < 18 ? 'Buenas tardes' : 'Buenas noches');
                    $emoji  = $hora < 12 ? '☀️' : ($hora < 18 ? '👋' : '🌙');
                @endphp
                {{ $saludo }}, {{ strtoupper(explode(' ', $user->name)[0]) }} {{ $emoji }}
            </h1>
            <p class="yr-greeting-sub">{{ now()->translatedFormat('l, j \d\e F \d\e Y') }} &nbsp;·&nbsp; YarOM ERP Inmobiliaria</p>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            @if($solicitudes > 0)
            <a href="/admin/requests" class="yr-btn-secondary">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="15" height="15"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                {{ $solicitudes }} Solicitud{{ $solicitudes > 1 ? 'es' : '' }}
            </a>
            @endif
            <a href="/admin/contratos-arriendo/create" class="yr-btn-primary">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="15" height="15"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nuevo Contrato
            </a>
        </div>
    </div>

    {{-- ── KPIs ── --}}
    <div class="yr-kpi-grid">

        {{-- Inmuebles --}}
        <a href="/admin/properties" class="yr-kpi" style="text-decoration:none;">
            <div class="yr-kpi-icon" style="background:#dbeafe;">
                <svg fill="none" viewBox="0 0 24 24" stroke="#2563EB" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9.75L12 3l9 6.75V21H15v-6H9v6H3V9.75z"/></svg>
            </div>
            <div>
                <div class="yr-kpi-label">Inmuebles</div>
                <div class="yr-kpi-value">{{ $totalInm }}</div>
            </div>
            <div class="yr-kpi-desc">{{ $arrendados }} arrendados · {{ $disponibles }} disponibles</div>
            <div class="yr-progress-bar">
                <div class="yr-progress-fill" style="width:{{ $ocupacion }}%;background:#2563EB;"></div>
            </div>
        </a>

        {{-- Ocupación --}}
        <a href="/admin/properties" class="yr-kpi">
            <div class="yr-kpi-icon" style="background:{{ $ocupacion >= 80 ? '#d1fae5' : ($ocupacion >= 60 ? '#fef3c7' : '#fee2e2') }};">
                <svg fill="none" viewBox="0 0 24 24" stroke="{{ $ocupacion >= 80 ? '#059669' : ($ocupacion >= 60 ? '#d97706' : '#dc2626') }}" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
            <div>
                <div class="yr-kpi-label">Ocupación</div>
                <div class="yr-kpi-value" style="color:{{ $ocupacion >= 80 ? '#059669' : ($ocupacion >= 60 ? '#d97706' : '#dc2626') }};">{{ $ocupacion }}%</div>
            </div>
            <div class="yr-kpi-desc">del portafolio arrendado</div>
        </a>

        {{-- Contratos activos --}}
        <a href="/admin/contratos-arriendo" class="yr-kpi">
            <div class="yr-kpi-icon" style="background:#ede9fe;">
                <svg fill="none" viewBox="0 0 24 24" stroke="#7c3aed" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div>
                <div class="yr-kpi-label">Contratos activos</div>
                <div class="yr-kpi-value">{{ $contrActivos }}</div>
            </div>
            @if($porVencer30 > 0)
            <span class="yr-badge" style="background:#fee2e2;color:#dc2626;">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="11" height="11" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                {{ $porVencer30 }} vencen en 30 días
            </span>
            @else
            <div class="yr-kpi-desc">Sin vencimientos próximos</div>
            @endif
        </a>

        {{-- Facturado mes --}}
        <a href="/admin/facturacion" class="yr-kpi">
            <div class="yr-kpi-icon" style="background:#fef3c7;">
                <svg fill="none" viewBox="0 0 24 24" stroke="#d97706" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
            </div>
            <div>
                <div class="yr-kpi-label">Facturado {{ $mesLabel }}</div>
                <div class="yr-kpi-value" style="font-size:1.1rem;">{{ $facturadoMes }}</div>
            </div>
            <div class="yr-kpi-desc">Período actual</div>
        </a>

        {{-- Recaudado mes --}}
        <a href="/admin/facturacion?tableFilters[estado][value]=pagada" class="yr-kpi">
            <div class="yr-kpi-icon" style="background:#d1fae5;">
                <svg fill="none" viewBox="0 0 24 24" stroke="#059669" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <div class="yr-kpi-label">Recaudado {{ $mesLabel }}</div>
                <div class="yr-kpi-value" style="font-size:1.1rem;color:#059669;">{{ $recaudadoMes }}</div>
            </div>
            <span class="yr-badge" style="background:{{ $efectividad >= 80 ? '#d1fae5' : '#fef3c7' }};color:{{ $efectividad >= 80 ? '#059669' : '#d97706' }};">
                {{ $efectividad }}% efectividad
            </span>
        </a>

        {{-- Cartera pendiente --}}
        <a href="/admin/facturacion?tableFilters[estado][value]=en_mora" class="yr-kpi">
            <div class="yr-kpi-icon" style="background:#fee2e2;">
                <svg fill="none" viewBox="0 0 24 24" stroke="#dc2626" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <div class="yr-kpi-label">Cartera pendiente</div>
                <div class="yr-kpi-value" style="font-size:1.1rem;color:#dc2626;">{{ $cartera }}</div>
            </div>
            <div class="yr-kpi-desc">{{ $factsMora }} facturas en mora</div>
        </a>

    </div>

    {{-- ── CHARTS + TABLA ── --}}
    <div class="yr-grid-2" style="margin-bottom:16px;">

        {{-- Tendencia recaudo 6 meses --}}
        <div class="yr-card">
            <div class="yr-card-header">
                <div>
                    <div class="yr-card-title">Tendencia de Recaudo</div>
                    <div class="yr-card-sub">Últimos 6 meses · Facturado vs Recaudado</div>
                </div>
                <span class="yr-badge" style="background:#d1fae5;color:#059669;">
                    ${{ number_format($recaudo6meses->sum('valor'), 0, ',', '.') }} total
                </span>
            </div>
            <div class="yr-card-body">
                <canvas id="chartRecaudo" height="130"></canvas>
            </div>
        </div>

        {{-- Tendencia recaudo 7 días --}}
        <div class="yr-card">
            <div class="yr-card-header">
                <div>
                    <div class="yr-card-title">Pagos recibidos</div>
                    <div class="yr-card-sub">Últimos 7 días</div>
                </div>
                <span class="yr-badge" style="background:#dbeafe;color:#2563EB;">
                    ${{ number_format($recaudo7dias->sum('valor'), 0, ',', '.') }} total
                </span>
            </div>
            <div class="yr-card-body">
                <canvas id="chartDiario" height="130"></canvas>
            </div>
        </div>

    </div>

    {{-- ── FACTURAS + ACCESOS ── --}}
    <div class="yr-grid-3">

        {{-- Últimas facturas --}}
        <div class="yr-card" style="grid-column:span 2;">
            <div class="yr-card-header">
                <div>
                    <div class="yr-card-title">Últimas Facturas</div>
                    <div class="yr-card-sub">Transacciones recientes</div>
                </div>
                <a href="/admin/facturacion" style="font-size:0.72rem;color:#2563EB;font-weight:700;text-decoration:none;">Ver todas →</a>
            </div>
            <table class="yr-table">
                <thead>
                    <tr>
                        <th>Factura</th>
                        <th>Arrendatario</th>
                        <th>Inmueble</th>
                        <th>Total</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($ultimasFacturas as $f)
                <tr>
                    <td style="font-weight:700;color:#0F172A;">{{ $f->numero ?? '#' . $f->id }}</td>
                    <td>{{ $f->rentalContract?->arrendatario?->nombre_completo ?? '—' }}</td>
                    <td style="color:#94a3b8;">{{ $f->rentalContract?->property?->codigo ?? '—' }}</td>
                    <td style="font-weight:700;">${{ number_format($f->total_factura, 0, ',', '.') }}</td>
                    <td><span class="yr-estado {{ $f->estado }}">{{ str_replace('_', ' ', $f->estado) }}</span></td>
                </tr>
                @empty
                <tr><td colspan="5" style="text-align:center;color:#94a3b8;padding:24px;">Sin facturas registradas</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- Panel lateral --}}
        <div style="display:flex;flex-direction:column;gap:16px;">

            {{-- Contratos por vencer --}}
            <div class="yr-card" style="flex:1;">
                <div class="yr-card-header">
                    <div class="yr-card-title">Por vencer (60 días)</div>
                    @if($porVencer30 > 0)
                    <span class="yr-badge" style="background:#fee2e2;color:#dc2626;">{{ $porVencer30 }} urgentes</span>
                    @endif
                </div>
                <div class="yr-card-body" style="padding:12px 16px;">
                    @forelse($contratosPorVencer as $c)
                    @php
                        $dias = now()->diffInDays($c->fecha_fin, false);
                        $color = $dias <= 15 ? '#dc2626' : ($dias <= 30 ? '#d97706' : '#2563EB');
                        $ini = strtoupper(substr($c->arrendatario?->nombre_completo ?? 'N', 0, 1));
                    @endphp
                    <div class="yr-vencer-item">
                        <div class="yr-vencer-avatar" style="background:{{ $color }};">{{ $ini }}</div>
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:0.78rem;font-weight:700;color:#0F172A;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $c->arrendatario?->nombre_completo ?? '—' }}</div>
                            <div style="font-size:0.68rem;color:#94a3b8;">{{ $c->property?->codigo ?? '—' }}</div>
                        </div>
                        <div style="text-align:right;flex-shrink:0;">
                            <div style="font-size:0.72rem;font-weight:800;color:{{ $color }};">{{ $dias }}d</div>
                            <div style="font-size:0.65rem;color:#94a3b8;">{{ $c->fecha_fin?->format('d/m/y') }}</div>
                        </div>
                    </div>
                    @empty
                    <div style="text-align:center;padding:20px;color:#94a3b8;font-size:0.78rem;">Sin vencimientos próximos ✓</div>
                    @endforelse
                </div>
            </div>

            {{-- Accesos rápidos --}}
            <div class="yr-card">
                <div class="yr-card-header"><div class="yr-card-title">Accesos Rápidos</div></div>
                <div class="yr-accesos">
                    @foreach($accesos as $acc)
                    <a href="{{ $acc['url'] }}" class="yr-acceso" style="color:{{ $acc['color'] }};">
                        <div class="yr-acceso-icon" style="background:{{ $acc['color'] }}18;">
                            @svg($acc['icon'], ['style' => 'width:16px;height:16px;color:' . $acc['color']])
                        </div>
                        <span style="font-size:0.72rem;color:#334155;">{{ $acc['label'] }}</span>
                    </a>
                    @endforeach
                </div>
            </div>

        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const recaudo6 = @json($recaudo6meses);
    const recaudo7 = @json($recaudo7dias);

    // Chart 6 meses
    new Chart(document.getElementById('chartRecaudo'), {
        type: 'bar',
        data: {
            labels: recaudo6.map(r => r.mes),
            datasets: [
                {
                    label: 'Facturado',
                    data: recaudo6.map(r => r.factu),
                    backgroundColor: 'rgba(37,99,235,0.15)',
                    borderColor: '#2563EB',
                    borderWidth: 2,
                    borderRadius: 6,
                },
                {
                    label: 'Recaudado',
                    data: recaudo6.map(r => r.valor),
                    backgroundColor: 'rgba(5,150,105,0.7)',
                    borderColor: '#059669',
                    borderWidth: 0,
                    borderRadius: 6,
                }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: true,
            plugins: { legend: { labels: { font: { family: 'Plus Jakarta Sans', size: 11, weight: '700' }, boxWidth: 12, boxHeight: 12 } } },
            scales: {
                x: { grid: { display: false }, ticks: { font: { family: 'Plus Jakarta Sans', size: 11 } } },
                y: { grid: { color: '#f1f5f9' }, ticks: { font: { family: 'Plus Jakarta Sans', size: 10 }, callback: v => '$' + (v >= 1000000 ? (v/1000000).toFixed(1) + 'M' : v >= 1000 ? (v/1000).toFixed(0) + 'k' : v) } }
            }
        }
    });

    // Chart 7 días
    new Chart(document.getElementById('chartDiario'), {
        type: 'bar',
        data: {
            labels: recaudo7.map(r => r.dia),
            datasets: [{
                label: 'Pagos',
                data: recaudo7.map(r => r.valor),
                backgroundColor: recaudo7.map(r => r.valor > 0 ? 'rgba(225,29,72,0.75)' : 'rgba(226,232,240,0.6)'),
                borderRadius: 8,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: true,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { font: { family: 'Plus Jakarta Sans', size: 11 } } },
                y: { grid: { color: '#f1f5f9' }, ticks: { font: { family: 'Plus Jakarta Sans', size: 10 }, callback: v => '$' + (v >= 1000000 ? (v/1000000).toFixed(1) + 'M' : v >= 1000 ? (v/1000).toFixed(0) + 'k' : v) } }
            }
        }
    });
});
</script>
</x-filament-panels::page>
