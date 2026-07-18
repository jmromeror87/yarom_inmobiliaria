<x-filament-panels::page>
@php
    $r      = $this->record;
    $fmt    = fn($v) => '$' . number_format((float)$v, 0, ',', '.');
    $fmtDec = fn($v) => '$' . number_format((float)$v, 2, ',', '.');

    $carteraHeredada         = $r->cuentasPorCobrar->where('tipo', 'saldo_inicial_siinmob');
    $carteraHeredadaPendiente = $carteraHeredada->sum('saldo');
    $otrasCuentasPorCobrar    = $r->cuentasPorCobrar->where('tipo', '!=', 'saldo_inicial_siinmob');

    $totalFacturado = $r->rentBills->sum('total_factura');
    $totalPagado    = $r->rentBills->sum('total_pagado');
    // El saldo pendiente real incluye lo que quedó heredado de Siinmob (y cualquier
    // otra cuenta por cobrar aparte de las facturas del sistema nuevo), no solo
    // las facturas generadas desde julio 2026 — si no, un inquilino que venía
    // debiendo de antes aparecería "al día" sin estarlo.
    $totalPendiente = $r->rentBills->sum('saldo_pendiente') + $r->cuentasPorCobrar->whereIn('estado', ['pendiente', 'parcial'])->sum('saldo');
    $totalLiquidado = $r->ownerLiquidations->sum('canon_cobrado');
    $totalGirado    = $r->ownerLiquidations->where('estado', 'pagada')->sum('total_giro');
    $totalComision  = $r->ownerLiquidations->sum('comision_valor');

    // Cartera heredada de Siinmob (saldo que se le debe de antes de julio 2026)
    $cxpHeredada          = $r->cuentasPorPagar->where('tipo', 'saldo_inicial_siinmob');
    $cxpHeredadaPendiente = $cxpHeredada->sum('saldo');

    // Liquidaciones aun sin girar (pendientes o aprobadas, no pagadas ni anuladas)
    $liquidacionesSinGirar   = $r->ownerLiquidations->whereIn('estado', ['pendiente', 'aprobada']);
    $totalLiquidacionesPorGirar = $liquidacionesSinGirar->sum('total_giro');

    // Total unificado que realmente se le debe a este propietario HOY,
    // sumando lo heredado del sistema viejo + lo pendiente del nuevo —
    // para que quien vaya a pagarle vea un solo numero, no dos sueltos.
    $totalPorGirarHoy = $totalLiquidacionesPorGirar + $cxpHeredadaPendiente;

    $initials = collect([$r->primer_nombre ?? $r->razon_social, $r->primer_apellido])
        ->filter()->map(fn($w) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($w,0,1)))->join('');

    $roles = [];
    if ($r->es_propietario)    $roles[] = ['label'=>'🏠 Propietario',  'bg'=>'#eff6ff','color'=>'#1d4ed8'];
    if ($r->es_arrendatario)   $roles[] = ['label'=>'🔑 Arrendatario', 'bg'=>'#fef2f2','color'=>'#991b1b'];
    if ($r->es_cliente_compra) $roles[] = ['label'=>'🛒 Comprador',    'bg'=>'#f0fdf4','color'=>'#166534'];
    if ($r->es_fiador)         $roles[] = ['label'=>'🤝 Fiador',       'bg'=>'#fdf4ff','color'=>'#7e22ce'];
    if ($r->es_proveedor)      $roles[] = ['label'=>'🔧 Proveedor',    'bg'=>'#fffbeb','color'=>'#92400e'];

    $creditColor = match($r->estado_crediticio) {
        'aprobado'    => ['bg'=>'#f0fdf4','color'=>'#166534','label'=>'✓ Aprobado'],
        'rechazado'   => ['bg'=>'#fef2f2','color'=>'#991b1b','label'=>'✕ Rechazado'],
        'condicional' => ['bg'=>'#fffbeb','color'=>'#92400e','label'=>'⚠ Condicional'],
        'en_proceso'  => ['bg'=>'#eff6ff','color'=>'#1d4ed8','label'=>'🔍 En estudio'],
        default       => ['bg'=>'#f8fafc','color'=>'#64748b','label'=>'⏳ Sin evaluar'],
    };
    $estadoBillColor = [
        'pendiente'=>['#d97706','#fffbeb'],'pagada'=>['#16a34a','#f0fdf4'],
        'en_mora'=>['#dc2626','#fef2f2'],'anulada'=>['#64748b','#f8fafc'],
    ];
    $estadoLiqColor = [
        'pendiente'=>['#d97706','#fffbeb'],'girada'=>['#16a34a','#f0fdf4'],
        'aprobada'=>['#2563eb','#eff6ff'],'anulada'=>['#64748b','#f8fafc'],
    ];
@endphp

<style>
.xp-card{background:#fff;border:1px solid #e2e8f0;border-radius:1rem;overflow:hidden;margin-bottom:16px;box-shadow:0 2px 8px rgba(0,0,0,.05);}
.xp-card-head{display:flex;align-items:center;gap:10px;padding:16px 22px;border-bottom:1px solid #f1f5f9;}
.xp-card-head .icon{width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:15px;}
.xp-card-head h3{font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.1em;color:#334155;margin:0;}
.xp-row{display:flex;justify-content:space-between;padding:8px 22px;border-bottom:1px solid #f8fafc;font-size:13px;}
.xp-row:last-child{border-bottom:none;}
.xp-row label{color:#94a3b8;font-weight:600;}
.xp-row span{color:#0f172a;font-weight:700;}
.t-table{width:100%;border-collapse:collapse;font-size:12.5px;}
.t-table th{background:#f8fafc;padding:10px 16px;text-align:left;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:#64748b;border-bottom:2px solid #e2e8f0;}
.t-table td{padding:10px 16px;border-bottom:1px solid #f1f5f9;vertical-align:middle;color:#334155;}
.t-table tr:last-child td{border-bottom:none;}
.t-table tr:hover td{background:#fafbfc;}
.t-table tfoot td{background:#f8fafc;font-weight:900;border-top:2px solid #e2e8f0;color:#0f172a;}
.t-num{font-family:monospace;font-weight:700;}
.t-badge{display:inline-block;padding:3px 10px;border-radius:99px;font-size:10.5px;font-weight:800;}
</style>

{{-- ── HERO ──────────────────────────────────────────────────────────── --}}
<div style="background:linear-gradient(135deg,#0F172A 0%,#1e2d45 55%,#1a1f3a 100%);border-radius:1.25rem;padding:28px 32px;margin-bottom:20px;position:relative;overflow:hidden;">
    <div style="position:absolute;right:-40px;top:-40px;width:220px;height:220px;border-radius:50%;background:radial-gradient(circle,rgba(225,29,72,.18),transparent 70%);pointer-events:none;"></div>
    <div style="position:absolute;right:80px;bottom:-30px;width:130px;height:130px;border-radius:50%;background:radial-gradient(circle,rgba(37,99,235,.12),transparent 70%);pointer-events:none;"></div>

    <div style="display:flex;align-items:center;gap:24px;flex-wrap:wrap;">
        {{-- Avatar --}}
        <div style="width:76px;height:76px;border-radius:20px;background:linear-gradient(135deg,#1e3a8a,#E11D48);display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 8px 24px rgba(225,29,72,.28);">
            <span style="font-size:26px;font-weight:900;color:#fff;">{{ $initials ?: '?' }}</span>
        </div>

        {{-- Info --}}
        <div style="flex:1;min-width:0;">
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:4px;">
                <span style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:rgba(255,255,255,.5);">
                    {{ $r->tipo_documento }} {{ $r->numero_documento }}
                </span>
                @if($r->is_active)
                    <span style="font-size:9.5px;font-weight:800;background:#16a34a;color:#fff;border-radius:99px;padding:2px 9px;letter-spacing:.06em;text-transform:uppercase;">● Activo</span>
                @endif
            </div>
            <h2 style="font-size:22px;font-weight:900;color:#fff;margin:0 0 10px;letter-spacing:-.02em;">{{ \Illuminate\Support\Str::upper($r->nombre_completo) }}</h2>
            <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:10px;">
                @foreach($roles as $role)
                    <span style="font-size:10.5px;font-weight:700;background:{{ $role['bg'] }};color:{{ $role['color'] }};border-radius:99px;padding:3px 11px;">{{ $role['label'] }}</span>
                @endforeach
            </div>
            <div style="font-size:12.5px;color:rgba(255,255,255,.55);display:flex;gap:20px;flex-wrap:wrap;">
                @if($r->celular)<span>📱 {{ $r->celular }}</span>@endif
                @if($r->email)<span>📧 {{ $r->email }}</span>@endif
                @if($r->municipio)<span>📍 {{ $r->municipio->nombre }}</span>@endif
            </div>
        </div>

        {{-- Counters --}}
        <div style="display:flex;gap:10px;flex-shrink:0;">
            @foreach([
                ['n'=>$r->rentBills->count(),      'label'=>'Facturas',      'color'=>'#2563EB'],
                ['n'=>$r->ownerLiquidations->count(),'label'=>'Liquidaciones','color'=>'#7c3aed'],
                ['n'=>$r->rentalContracts->count() + $r->properties->count(),'label'=>'Contratos','color'=>'#E11D48'],
            ] as $cnt)
            <div style="background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.12);border-radius:14px;padding:14px 20px;text-align:center;min-width:90px;">
                <div style="font-size:28px;font-weight:900;color:#fff;line-height:1;">{{ $cnt['n'] }}</div>
                <div style="font-size:9.5px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:rgba(255,255,255,.5);margin-top:5px;">{{ $cnt['label'] }}</div>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ── RESUMEN FINANCIERO ──────────────────────────────────────────────── --}}
@if($r->es_arrendatario || $r->es_propietario)
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:14px;margin-bottom:20px;">
    @if($r->es_arrendatario)
    @foreach([
        ['v'=>$totalFacturado,'label'=>'Total Facturado','color'=>'#2563EB','bg'=>'#eff6ff','border'=>'#2563EB'],
        ['v'=>$totalPagado,   'label'=>'Total Pagado',   'color'=>'#16a34a','bg'=>'#f0fdf4','border'=>'#16a34a'],
        ['v'=>$totalPendiente,'label'=>'Saldo Pendiente', 'color'=>$totalPendiente>0?'#dc2626':'#64748b','bg'=>$totalPendiente>0?'#fef2f2':'#f8fafc','border'=>$totalPendiente>0?'#dc2626':'#e2e8f0'],
    ] as $s)
    <div style="background:#fff;border:1px solid #e5e7eb;border-left:4px solid {{ $s['border'] }};border-radius:1rem;padding:18px 20px;box-shadow:0 1px 4px rgba(0,0,0,.06);">
        <div style="font-size:24px;font-weight:900;color:{{ $s['color'] }};line-height:1;">{{ $fmt($s['v']) }}</div>
        <div style="font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;margin-top:6px;">{{ $s['label'] }}</div>
    </div>
    @endforeach
    @endif
    @if($r->es_propietario)
    @foreach([
        ['v'=>$totalLiquidado,'label'=>'Canon Liquidado','color'=>'#7c3aed','border'=>'#7c3aed'],
        ['v'=>$totalGirado,   'label'=>'Total Girado',   'color'=>'#16a34a','border'=>'#16a34a'],
        ['v'=>$totalPorGirarHoy,'label'=>'Pendiente por Girar (HOY)','color'=>$totalPorGirarHoy>0?'#dc2626':'#64748b','border'=>$totalPorGirarHoy>0?'#dc2626':'#e2e8f0'],
        ['v'=>$totalComision, 'label'=>'Comisiones',     'color'=>'#E11D48','border'=>'#E11D48'],
    ] as $s)
    <div style="background:#fff;border:1px solid #e5e7eb;border-left:4px solid {{ $s['border'] }};border-radius:1rem;padding:18px 20px;box-shadow:0 1px 4px rgba(0,0,0,.06);">
        <div style="font-size:24px;font-weight:900;color:{{ $s['color'] }};line-height:1;">{{ $fmt($s['v']) }}</div>
        <div style="font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;margin-top:6px;">{{ $s['label'] }}</div>
    </div>
    @endforeach
    @endif
</div>
@endif

{{-- ── COLUMNAS ────────────────────────────────────────────────────────── --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
<div>

{{-- Facturas --}}
@if($r->rentBills->count())
<div class="xp-card">
    <div class="xp-card-head">
        <div class="icon" style="background:#eff6ff;">🧾</div>
        <h3>Facturas de Arrendamiento</h3>
    </div>
    <table class="t-table">
        <thead><tr>
            <th>N° Factura</th><th>Inmueble</th><th>Período</th>
            <th style="text-align:right;">Total</th><th style="text-align:right;">Pagado</th><th>Estado</th>
        </tr></thead>
        <tbody>
        @foreach($r->rentBills->sortByDesc('periodo_inicio') as $bill)
        @php [$bc,$bb] = $estadoBillColor[$bill->estado] ?? ['#64748b','#f8fafc']; @endphp
        <tr>
            <td class="t-num" style="color:#2563eb;">{{ $bill->numero }}</td>
            <td style="font-size:11px;color:#64748b;">{{ \Illuminate\Support\Str::limit($bill->rentalContract?->property?->direccion ?? '—', 22) }}</td>
            <td style="font-size:11px;">{{ $bill->mes }}/{{ $bill->anio }}</td>
            <td class="t-num" style="text-align:right;">{{ $fmt($bill->total_factura) }}</td>
            <td class="t-num" style="text-align:right;color:#16a34a;">{{ $fmt($bill->total_pagado) }}</td>
            <td><span class="t-badge" style="background:{{ $bb }};color:{{ $bc }};">{{ ucfirst($bill->estado) }}</span></td>
        </tr>
        @endforeach
        </tbody>
        <tfoot><tr>
            <td colspan="3" style="padding:10px 16px;font-size:10.5px;text-transform:uppercase;letter-spacing:.05em;">Totales</td>
            <td class="t-num" style="text-align:right;">{{ $fmt($totalFacturado) }}</td>
            <td class="t-num" style="text-align:right;color:#16a34a;">{{ $fmt($totalPagado) }}</td>
            <td></td>
        </tr></tfoot>
    </table>
</div>
@endif

{{-- Cartera heredada de Siinmob (deuda de antes de julio 2026) --}}
@if($carteraHeredada->count())
<div class="xp-card" style="border-left:4px solid #7c3aed;">
    <div class="xp-card-head">
        <div class="icon" style="background:#fdf4ff;">📜</div>
        <h3>Cartera Heredada — Sistema Anterior (Siinmob)</h3>
    </div>
    <div style="padding:12px 22px;font-size:12px;color:#7c3aed;background:#fdf4ff;border-bottom:1px solid #f1f5f9;">
        ⚠️ Deuda pendiente de antes del 1 de julio de 2026, migrada del sistema anterior. Se suma al saldo pendiente total.
    </div>
    <table class="t-table">
        <thead><tr>
            <th>N° Cuenta</th><th>Concepto</th>
            <th style="text-align:right;">Original</th><th style="text-align:right;">Pagado</th><th style="text-align:right;">Saldo</th><th>Estado</th>
        </tr></thead>
        <tbody>
        @foreach($carteraHeredada as $cxc)
        <tr>
            <td class="t-num" style="color:#7c3aed;">{{ $cxc->numero }}</td>
            <td style="font-size:11px;color:#64748b;">{{ \Illuminate\Support\Str::limit($cxc->concepto, 30) }}</td>
            <td class="t-num" style="text-align:right;">{{ $fmt($cxc->valor_original) }}</td>
            <td class="t-num" style="text-align:right;color:#16a34a;">{{ $fmt($cxc->valor_pagado) }}</td>
            <td class="t-num" style="text-align:right;color:{{ $cxc->saldo > 0 ? '#dc2626' : '#64748b' }};font-weight:900;">{{ $fmt($cxc->saldo) }}</td>
            <td><span class="t-badge" style="background:{{ $cxc->estado === 'pagado' ? '#f0fdf4' : '#fef2f2' }};color:{{ $cxc->estado === 'pagado' ? '#166534' : '#991b1b' }};">{{ ucfirst($cxc->estado) }}</span></td>
        </tr>
        @endforeach
        </tbody>
        <tfoot><tr>
            <td colspan="4" style="padding:10px 16px;font-size:10.5px;text-transform:uppercase;letter-spacing:.05em;">Saldo heredado pendiente</td>
            <td class="t-num" style="text-align:right;color:#dc2626;">{{ $fmt($carteraHeredadaPendiente) }}</td>
            <td></td>
        </tr></tfoot>
    </table>
</div>
@endif

{{-- Contratos arriendo --}}
@if($r->rentalContracts->count())
<div class="xp-card">
    <div class="xp-card-head">
        <div class="icon" style="background:#fef2f2;">🔑</div>
        <h3>Contratos de Arrendamiento</h3>
    </div>
    <div style="padding:12px 16px;">
    @foreach($r->rentalContracts->sortByDesc('fecha_inicio') as $c)
    <div style="border:1px solid #e2e8f0;border-left:4px solid #E11D48;border-radius:.875rem;padding:14px 16px;margin-bottom:10px;display:flex;justify-content:space-between;align-items:center;">
        <div>
            <div style="font-weight:800;font-size:13px;color:#0f172a;">{{ $c->numero ?? 'Sin número' }}</div>
            <div style="font-size:11.5px;color:#64748b;margin-top:2px;">{{ $c->property?->direccion }}</div>
            <div style="font-size:11px;color:#94a3b8;margin-top:2px;">{{ $c->fecha_inicio?->format('d/m/Y') }} — {{ $c->fecha_fin?->format('d/m/Y') ?? 'Vigente' }}</div>
        </div>
        <div style="text-align:right;">
            <div style="font-family:monospace;font-weight:900;font-size:17px;color:#E11D48;">{{ $fmt($c->canon_mensual ?? 0) }}</div>
            <div style="font-size:10px;color:#94a3b8;">canon/mes</div>
        </div>
    </div>
    @endforeach
    </div>
</div>
@endif

{{-- Inmuebles propietario --}}
@if($r->properties->count())
<div class="xp-card">
    <div class="xp-card-head">
        <div class="icon" style="background:#f0fdf4;">🏠</div>
        <h3>Inmuebles (propietario)</h3>
    </div>
    <div style="padding:4px 0;">
    @foreach($r->properties as $p)
    <div class="xp-row">
        <label>{{ $p->codigo }}</label>
        <span style="font-size:12.5px;">{{ $p->direccion }} <span style="font-size:10px;color:#94a3b8;font-weight:500;">· {{ ucfirst($p->estado) }}</span></span>
    </div>
    @endforeach
    </div>
</div>
@endif

</div>
<div>

{{-- Cartera heredada de Siinmob - lo que se le debe al propietario --}}
@if($cxpHeredada->count())
<div class="xp-card" style="border-left:4px solid #dc2626;">
    <div class="xp-card-head">
        <div class="icon" style="background:#fef2f2;">📜</div>
        <h3>Por Pagar — Heredado Sistema Anterior</h3>
    </div>
    <div style="padding:12px 22px;font-size:12px;color:#991b1b;background:#fef2f2;border-bottom:1px solid #f1f5f9;">
        ⚠️ Saldo pendiente de girarle de antes del 1 de julio de 2026 (Siinmob). Se suma a lo que le falta girar hoy — verifíquelo junto con sus liquidaciones antes de pagarle.
    </div>
    <table class="t-table">
        <thead><tr>
            <th>N° Cuenta</th><th>Concepto</th>
            <th style="text-align:right;">Original</th><th style="text-align:right;">Pagado</th><th style="text-align:right;">Saldo</th><th>Estado</th>
        </tr></thead>
        <tbody>
        @foreach($cxpHeredada as $cxp)
        <tr>
            <td class="t-num" style="color:#dc2626;">{{ $cxp->numero }}</td>
            <td style="font-size:11px;color:#64748b;">{{ \Illuminate\Support\Str::limit($cxp->concepto, 30) }}</td>
            <td class="t-num" style="text-align:right;">{{ $fmt($cxp->valor_original) }}</td>
            <td class="t-num" style="text-align:right;color:#16a34a;">{{ $fmt($cxp->valor_pagado) }}</td>
            <td class="t-num" style="text-align:right;color:{{ $cxp->saldo > 0 ? '#dc2626' : '#64748b' }};font-weight:900;">{{ $fmt($cxp->saldo) }}</td>
            <td><span class="t-badge" style="background:{{ $cxp->estado === 'pagado' ? '#f0fdf4' : '#fef2f2' }};color:{{ $cxp->estado === 'pagado' ? '#166534' : '#991b1b' }};">{{ ucfirst($cxp->estado) }}</span></td>
        </tr>
        @endforeach
        </tbody>
        <tfoot><tr>
            <td colspan="4" style="padding:10px 16px;font-size:10.5px;text-transform:uppercase;letter-spacing:.05em;">Saldo heredado pendiente</td>
            <td class="t-num" style="text-align:right;color:#dc2626;">{{ $fmt($cxpHeredadaPendiente) }}</td>
            <td></td>
        </tr></tfoot>
    </table>
</div>
@endif

{{-- Liquidaciones --}}
@if($r->ownerLiquidations->count())
<div class="xp-card">
    <div class="xp-card-head">
        <div class="icon" style="background:#fdf4ff;">💰</div>
        <h3>Liquidaciones al Propietario</h3>
    </div>
    @if($totalLiquidacionesPorGirar > 0)
    <div style="padding:10px 22px;font-size:12px;color:#92400e;background:#fffbeb;border-bottom:1px solid #f1f5f9;">
        🕐 {{ $liquidacionesSinGirar->count() }} liquidación(es) del sistema nuevo sin girar todavía: {{ $fmt($totalLiquidacionesPorGirar) }}
    </div>
    @endif
    <table class="t-table">
        <thead><tr>
            <th>N° Liq.</th><th>Inmueble</th><th>Período</th>
            <th style="text-align:right;">Canon</th><th style="text-align:right;">Giro neto</th><th>Estado</th>
        </tr></thead>
        <tbody>
        @foreach($r->ownerLiquidations->sortByDesc('anio') as $liq)
        @php [$lc,$lb] = $estadoLiqColor[$liq->estado] ?? ['#64748b','#f8fafc']; @endphp
        <tr>
            <td class="t-num" style="color:#7c3aed;">{{ $liq->numero }}</td>
            <td style="font-size:11px;color:#64748b;">{{ \Illuminate\Support\Str::limit($liq->property?->direccion ?? '—', 22) }}</td>
            <td style="font-size:11px;">{{ $liq->mes }}/{{ $liq->anio }}</td>
            <td class="t-num" style="text-align:right;">{{ $fmt($liq->canon_cobrado) }}</td>
            <td class="t-num" style="text-align:right;color:#16a34a;">{{ $fmt($liq->total_giro) }}</td>
            <td><span class="t-badge" style="background:{{ $lb }};color:{{ $lc }};">{{ ucfirst($liq->estado) }}</span></td>
        </tr>
        @endforeach
        </tbody>
        <tfoot><tr>
            <td colspan="3" style="padding:10px 16px;font-size:10.5px;text-transform:uppercase;letter-spacing:.05em;">Totales</td>
            <td class="t-num" style="text-align:right;">{{ $fmt($totalLiquidado) }}</td>
            <td class="t-num" style="text-align:right;color:#16a34a;">{{ $fmt($totalGirado) }}</td>
            <td></td>
        </tr></tfoot>
    </table>
</div>
@endif

{{-- Movimientos contables --}}
@if($r->accountingLines->count())
<div class="xp-card">
    <div class="xp-card-head">
        <div class="icon" style="background:#eff6ff;">📊</div>
        <h3>Movimientos Contables</h3>
    </div>
    <table class="t-table">
        <thead><tr>
            <th>Fecha</th><th>Comprobante</th><th>Cuenta</th>
            <th style="text-align:right;">Débito</th><th style="text-align:right;">Crédito</th>
        </tr></thead>
        <tbody>
        @php $tDeb=0; $tCre=0; @endphp
        @foreach($r->accountingLines->filter(fn($l)=>$l->entry?->estado==='contabilizado')->sortByDesc(fn($l)=>$l->entry?->fecha) as $line)
        @php $tDeb+=$line->debito; $tCre+=$line->credito; @endphp
        <tr>
            <td style="font-size:11px;">{{ $line->entry?->fecha?->format('d/m/Y') }}</td>
            <td style="font-size:11px;color:#2563eb;">{{ $line->entry?->numero }}</td>
            <td style="font-size:11px;color:#475569;"><span style="font-family:monospace;color:#0f172a;">{{ $line->account?->codigo }}</span> {{ $line->account?->nombre }}</td>
            <td class="t-num" style="text-align:right;color:#2563eb;font-size:11px;">{{ $line->debito>0?$fmtDec($line->debito):'' }}</td>
            <td class="t-num" style="text-align:right;color:#16a34a;font-size:11px;">{{ $line->credito>0?$fmtDec($line->credito):'' }}</td>
        </tr>
        @endforeach
        </tbody>
        <tfoot><tr>
            <td colspan="3" style="padding:10px 16px;font-size:10.5px;text-transform:uppercase;letter-spacing:.05em;">Totales contabilizados</td>
            <td class="t-num" style="text-align:right;color:#2563eb;">{{ $fmtDec($tDeb) }}</td>
            <td class="t-num" style="text-align:right;color:#16a34a;">{{ $fmtDec($tCre) }}</td>
        </tr></tfoot>
    </table>
</div>
@endif

{{-- Info personal --}}
<div class="xp-card">
    <div class="xp-card-head">
        <div class="icon" style="background:#f8fafc;">👤</div>
        <h3>Información del Tercero</h3>
    </div>
    <div style="padding:4px 0;">
        <div class="xp-row"><label>Crédito</label><span style="background:{{ $creditColor['bg'] }};color:{{ $creditColor['color'] }};border-radius:99px;padding:2px 10px;font-size:11px;">{{ $creditColor['label'] }}</span></div>
        @if($r->tipo_empleo)<div class="xp-row"><label>Empleo</label><span>{{ $r->empresa_donde_trabaja }} — {{ $r->cargo }}</span></div>@endif
        @if($r->ingresos_mensuales)<div class="xp-row"><label>Ingresos mensuales</label><span>{{ $fmt($r->ingresos_mensuales) }}</span></div>@endif
        @if($r->banco)<div class="xp-row"><label>Cuenta bancaria</label><span>{{ $r->banco }} · {{ $r->tipo_cuenta }} · {{ $r->numero_cuenta }}</span></div>@endif
        @if($r->tipo_garantia)<div class="xp-row"><label>Garantía</label><span>{{ ucfirst($r->tipo_garantia) }}@if($r->numero_poliza) — {{ $r->numero_poliza }}@endif</span></div>@endif
        <div class="xp-row"><label>Habeas Data</label>
            @if($r->habeas_data_aceptado)
                <span style="background:#f0fdf4;color:#166534;border-radius:99px;padding:2px 10px;font-size:11px;">✓ Firmado {{ $r->habeas_data_fecha?->format('d/m/Y') }}</span>
            @else
                <span style="background:#fef2f2;color:#991b1b;border-radius:99px;padding:2px 10px;font-size:11px;">✕ Pendiente</span>
            @endif
        </div>
        @if($r->notas)
        <div style="margin:8px 22px;padding:10px 14px;background:#f8fafc;border-radius:8px;border-left:3px solid #e2e8f0;font-size:12px;color:#475569;">
            <strong style="color:#334155;">Notas:</strong> {{ $r->notas }}
        </div>
        @endif
    </div>
</div>

{{-- Portal propietario --}}
@if($r->es_propietario)
<div class="xp-card" style="border-left:4px solid {{ $r->portal_activo ? '#16a34a' : '#e2e8f0' }};">
    <div class="xp-card-head">
        <div class="icon" style="background:{{ $r->portal_activo ? '#f0fdf4' : '#f8fafc' }};">🔗</div>
        <h3>Portal del Propietario</h3>
        @if($r->portal_activo)
            <span style="font-size:9.5px;font-weight:800;background:#f0fdf4;color:#16a34a;border-radius:99px;padding:2px 9px;letter-spacing:.06em;margin-left:auto;">● Activo</span>
        @else
            <span style="font-size:9.5px;font-weight:800;background:#f8fafc;color:#94a3b8;border-radius:99px;padding:2px 9px;letter-spacing:.06em;margin-left:auto;">Sin acceso</span>
        @endif
    </div>
    @if($r->portal_activo && $r->portal_url)
    <div style="padding:14px 20px;display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
        <input type="text" readonly value="{{ $r->portal_url }}" onclick="this.select()"
            style="flex:1;font-size:11px;font-family:monospace;padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;background:#f8fafc;color:#374151;min-width:0;">
        <button onclick="navigator.clipboard.writeText('{{ $r->portal_url }}').then(()=>{this.textContent='✓ Copiado';setTimeout(()=>this.textContent='Copiar',1500)})"
            style="padding:8px 16px;background:#2563EB;color:#fff;border:none;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;">Copiar</button>
        @if($r->celular)
        <a href="https://wa.me/{{ preg_replace('/[^0-9]/','', $r->celular) }}?text={{ urlencode('Hola '.$r->primer_nombre.', su portal: '.$r->portal_url) }}"
            target="_blank" style="padding:8px 16px;background:#16a34a;color:#fff;border-radius:8px;font-size:12px;font-weight:700;text-decoration:none;">📱 WhatsApp</a>
        @endif
    </div>
    @else
    <div style="padding:14px 22px;font-size:12.5px;color:#94a3b8;">🔒 Sin acceso generado aún. Genérelo desde la lista de terceros.</div>
    @endif
</div>
@endif

</div>
</div>
</x-filament-panels::page>
