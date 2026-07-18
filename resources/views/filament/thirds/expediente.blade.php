<x-filament-panels::page>
@php
    $r = $this->record;
    $k = $this->kpis;
    $fmt = fn($v) => '$' . number_format((float)$v, 0, ',', '.');
    $fmtDec = fn($v) => '$' . number_format((float)$v, 2, ',', '.');
    $mesesNombre = ['01'=>'Enero','02'=>'Febrero','03'=>'Marzo','04'=>'Abril','05'=>'Mayo','06'=>'Junio','07'=>'Julio','08'=>'Agosto','09'=>'Septiembre','10'=>'Octubre','11'=>'Noviembre','12'=>'Diciembre'];

    $initials = collect([$r->primer_nombre ?? $r->razon_social, $r->primer_apellido])
        ->filter()->map(fn($w) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($w,0,1)))->join('');

    $roles = [];
    if ($r->es_propietario)    $roles[] = ['label'=>'🏠 Propietario',  'bg'=>'#eff6ff','color'=>'#1d4ed8'];
    if ($r->es_arrendatario)   $roles[] = ['label'=>'🔑 Arrendatario', 'bg'=>'#fef2f2','color'=>'#991b1b'];
    if ($r->es_cliente_compra) $roles[] = ['label'=>'🛒 Comprador',    'bg'=>'#f0fdf4','color'=>'#166534'];
    if ($r->es_fiador)         $roles[] = ['label'=>'🤝 Fiador',       'bg'=>'#fdf4ff','color'=>'#7e22ce'];
    if ($r->es_proveedor)      $roles[] = ['label'=>'🔧 Proveedor',    'bg'=>'#fffbeb','color'=>'#92400e'];

    $estadoBillColor = [
        'pendiente'=>['#d97706','#fffbeb'],'pagada'=>['#16a34a','#f0fdf4'],
        'en_mora'=>['#dc2626','#fef2f2'],'anulada'=>['#64748b','#f8fafc'],'parcial'=>['#2563eb','#eff6ff'],
    ];
    $estadoLiqColor = [
        'pendiente'=>['#d97706','#fffbeb'],'pagada'=>['#16a34a','#f0fdf4'],
        'aprobada'=>['#2563eb','#eff6ff'],'anulada'=>['#64748b','#f8fafc'],
    ];

    $tabs = [
        'resumen' => ['label' => '👤 Resumen', 'show' => true],
        'pagos' => ['label' => '🧾 Pagos y Facturas', 'show' => $r->es_arrendatario],
        'historico' => ['label' => '🗓️ Histórico Siinmob', 'show' => true],
        'liquidaciones' => ['label' => '💰 Liquidaciones', 'show' => $r->es_propietario],
        'movimientos' => ['label' => '📊 Movimientos Contables', 'show' => true],
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
.t-num{font-family:monospace;font-weight:700;}
.t-badge{display:inline-block;padding:3px 10px;border-radius:99px;font-size:10.5px;font-weight:800;}
.xp-tabs{display:flex;gap:4px;margin-bottom:18px;border-bottom:2px solid #e2e8f0;overflow-x:auto;}
.xp-tab{padding:10px 18px;font-size:12.5px;font-weight:700;color:#64748b;cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-2px;white-space:nowrap;}
.xp-tab.active{color:#E11D48;border-bottom-color:#E11D48;}
.xp-tab:hover{color:#0f172a;}
.xp-pagination{padding:12px 22px;display:flex;justify-content:center;}
</style>

{{-- ── HERO ──────────────────────────────────────────────────────────── --}}
<div style="background:linear-gradient(135deg,#0F172A 0%,#1e2d45 55%,#1a1f3a 100%);border-radius:1.25rem;padding:28px 32px;margin-bottom:20px;position:relative;overflow:hidden;">
    <div style="position:absolute;right:-40px;top:-40px;width:220px;height:220px;border-radius:50%;background:radial-gradient(circle,rgba(225,29,72,.18),transparent 70%);pointer-events:none;"></div>
    <div style="position:absolute;right:80px;bottom:-30px;width:130px;height:130px;border-radius:50%;background:radial-gradient(circle,rgba(37,99,235,.12),transparent 70%);pointer-events:none;"></div>

    <div style="display:flex;align-items:center;gap:24px;flex-wrap:wrap;">
        <div style="width:76px;height:76px;border-radius:20px;background:linear-gradient(135deg,#1e3a8a,#E11D48);display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 8px 24px rgba(225,29,72,.28);">
            <span style="font-size:26px;font-weight:900;color:#fff;">{{ $initials ?: '?' }}</span>
        </div>
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
        <div style="display:flex;gap:10px;flex-shrink:0;">
            @foreach([
                ['n'=>$k['facturas_count'],      'label'=>'Facturas',      'color'=>'#2563EB'],
                ['n'=>$k['liquidaciones_count'],'label'=>'Liquidaciones','color'=>'#7c3aed'],
                ['n'=>$k['contratos_count'],'label'=>'Contratos','color'=>'#E11D48'],
            ] as $cnt)
            <div style="background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.12);border-radius:14px;padding:14px 20px;text-align:center;min-width:90px;">
                <div style="font-size:28px;font-weight:900;color:#fff;line-height:1;">{{ $cnt['n'] }}</div>
                <div style="font-size:9.5px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:rgba(255,255,255,.5);margin-top:5px;">{{ $cnt['label'] }}</div>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ── RESUMEN FINANCIERO (siempre visible) ──────────────────────────── --}}
@if($r->es_arrendatario || $r->es_propietario)
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:14px;margin-bottom:20px;">
    @if($r->es_arrendatario)
    @foreach([
        ['v'=>$k['total_facturado'],'label'=>'Total Facturado','color'=>'#2563EB','border'=>'#2563EB'],
        ['v'=>$k['total_pagado'],   'label'=>'Total Pagado',   'color'=>'#16a34a','border'=>'#16a34a'],
        ['v'=>$k['total_pendiente'],'label'=>'Saldo Pendiente', 'color'=>$k['total_pendiente']>0?'#dc2626':'#64748b','border'=>$k['total_pendiente']>0?'#dc2626':'#e2e8f0'],
    ] as $s)
    <div style="background:#fff;border:1px solid #e5e7eb;border-left:4px solid {{ $s['border'] }};border-radius:1rem;padding:18px 20px;box-shadow:0 1px 4px rgba(0,0,0,.06);">
        <div style="font-size:24px;font-weight:900;color:{{ $s['color'] }};line-height:1;">{{ $fmt($s['v']) }}</div>
        <div style="font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;margin-top:6px;">{{ $s['label'] }}</div>
    </div>
    @endforeach
    @endif
    @if($r->es_propietario)
    @foreach([
        ['v'=>$k['total_liquidado'],'label'=>'Canon Liquidado','color'=>'#7c3aed','border'=>'#7c3aed'],
        ['v'=>$k['total_girado'],   'label'=>'Total Girado',   'color'=>'#16a34a','border'=>'#16a34a'],
        ['v'=>$k['total_por_girar_hoy'],'label'=>'Pendiente por Girar (HOY)','color'=>$k['total_por_girar_hoy']>0?'#dc2626':'#64748b','border'=>$k['total_por_girar_hoy']>0?'#dc2626':'#e2e8f0'],
        ['v'=>$k['total_comision'], 'label'=>'Comisiones',     'color'=>'#E11D48','border'=>'#E11D48'],
    ] as $s)
    <div style="background:#fff;border:1px solid #e5e7eb;border-left:4px solid {{ $s['border'] }};border-radius:1rem;padding:18px 20px;box-shadow:0 1px 4px rgba(0,0,0,.06);">
        <div style="font-size:24px;font-weight:900;color:{{ $s['color'] }};line-height:1;">{{ $fmt($s['v']) }}</div>
        <div style="font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;margin-top:6px;">{{ $s['label'] }}</div>
    </div>
    @endforeach
    @endif
</div>
@endif

{{-- ── PESTAÑAS ─────────────────────────────────────────────────────── --}}
<div class="xp-tabs">
    @foreach($tabs as $key => $t)
        @if($t['show'])
        <div class="xp-tab {{ $tab === $key ? 'active' : '' }}" wire:click="setTab('{{ $key }}')">{{ $t['label'] }}</div>
        @endif
    @endforeach
</div>

{{-- ═══════════════ TAB: RESUMEN ═══════════════ --}}
@if($tab === 'resumen')
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
<div>
    @if($this->contratos->count())
    <div class="xp-card">
        <div class="xp-card-head"><div class="icon" style="background:#fef2f2;">🔑</div><h3>Contratos de Arrendamiento</h3></div>
        <div style="padding:12px 16px;">
        @foreach($this->contratos as $c)
        <div style="border:1px solid #e2e8f0;border-left:4px solid #E11D48;border-radius:.875rem;padding:14px 16px;margin-bottom:10px;display:flex;justify-content:space-between;align-items:center;">
            <div>
                <div style="font-weight:800;font-size:13px;color:#0f172a;">{{ $c->numero_contrato ?? 'Sin número' }}</div>
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

    @if($this->propiedades->count())
    <div class="xp-card">
        <div class="xp-card-head"><div class="icon" style="background:#f0fdf4;">🏠</div><h3>Inmuebles (propietario)</h3></div>
        <div style="padding:4px 0;">
        @foreach($this->propiedades as $p)
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
    <div class="xp-card">
        <div class="xp-card-head"><div class="icon" style="background:#f8fafc;">👤</div><h3>Información del Tercero</h3></div>
        <div style="padding:4px 0;">
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

    @if($this->carteraHeredada->count())
    <div class="xp-card" style="border-left:4px solid #7c3aed;">
        <div class="xp-card-head"><div class="icon" style="background:#fdf4ff;">📜</div><h3>Cartera Heredada (Siinmob)</h3></div>
        <div style="padding:12px 22px;font-size:12px;color:#7c3aed;background:#fdf4ff;">⚠️ Deuda de antes del 1 de julio de 2026 — ver detalle en "Histórico Siinmob".</div>
        @foreach($this->carteraHeredada as $cxc)
        <div class="xp-row"><label>{{ $cxc->numero }}</label><span style="color:{{ $cxc->saldo>0?'#dc2626':'#64748b' }};">{{ $fmt($cxc->saldo) }}</span></div>
        @endforeach
    </div>
    @endif

    @if($this->cxpHeredada->count())
    <div class="xp-card" style="border-left:4px solid #dc2626;">
        <div class="xp-card-head"><div class="icon" style="background:#fef2f2;">📜</div><h3>Por Pagar Heredado (Siinmob)</h3></div>
        <div style="padding:12px 22px;font-size:12px;color:#991b1b;background:#fef2f2;">⚠️ Pendiente de girar de antes del 1 de julio de 2026 — ver detalle en "Histórico Siinmob".</div>
        @foreach($this->cxpHeredada as $cxp)
        <div class="xp-row"><label>{{ $cxp->numero }}</label><span style="color:{{ $cxp->saldo>0?'#dc2626':'#64748b' }};">{{ $fmt($cxp->saldo) }}</span></div>
        @endforeach
    </div>
    @endif

    @if($r->es_propietario)
    <div class="xp-card" style="border-left:4px solid {{ $r->portal_activo ? '#16a34a' : '#e2e8f0' }};">
        <div class="xp-card-head">
            <div class="icon" style="background:{{ $r->portal_activo ? '#f0fdf4' : '#f8fafc' }};">🔗</div>
            <h3>Portal del Propietario</h3>
        </div>
        @if($r->portal_activo && $r->portal_url)
        <div style="padding:14px 20px;display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
            <input type="text" readonly value="{{ $r->portal_url }}" onclick="this.select()"
                style="flex:1;font-size:11px;font-family:monospace;padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;background:#f8fafc;color:#374151;min-width:0;">
            <button onclick="navigator.clipboard.writeText('{{ $r->portal_url }}').then(()=>{this.textContent='✓ Copiado';setTimeout(()=>this.textContent='Copiar',1500)})"
                style="padding:8px 16px;background:#2563EB;color:#fff;border:none;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;">Copiar</button>
        </div>
        @else
        <div style="padding:14px 22px;font-size:12.5px;color:#94a3b8;">🔒 Sin acceso generado aún.</div>
        @endif
    </div>
    @endif
</div>
</div>
@endif

{{-- ═══════════════ TAB: PAGOS Y FACTURAS ═══════════════ --}}
@if($tab === 'pagos')
<div class="xp-card">
    <div class="xp-card-head"><div class="icon" style="background:#eff6ff;">🧾</div><h3>Facturas de Arrendamiento (sistema nuevo)</h3></div>
    <table class="t-table">
        <thead><tr>
            <th>N° Factura</th><th>Contrato</th><th>Período</th><th>Fecha límite</th><th>Fecha de pago</th>
            <th style="text-align:right;">Total</th><th style="text-align:right;">Pagado</th><th>Estado</th>
        </tr></thead>
        <tbody>
        @forelse($this->facturas as $bill)
        @php [$bc,$bb] = $estadoBillColor[$bill->estado] ?? ['#64748b','#f8fafc']; @endphp
        <tr>
            <td class="t-num" style="color:#2563eb;">{{ $bill->numero }}</td>
            <td style="font-size:11px;color:#64748b;">{{ $bill->rentalContract?->numero_contrato ?? '—' }}</td>
            <td style="font-size:11px;">{{ $bill->mes }}/{{ $bill->anio }}</td>
            <td style="font-size:11px;color:#64748b;">{{ $bill->fecha_limite_pago?->format('d/m/Y') ?? '—' }}</td>
            <td style="font-size:11px;">
                @forelse($bill->payments->sortBy('fecha_pago') as $pago)
                    <div style="color:{{ $pago->fecha_pago?->gt($bill->fecha_limite_pago) ? '#dc2626' : '#16a34a' }};">
                        {{ $pago->fecha_pago?->format('d/m/Y') }} <span style="color:#94a3b8;">({{ $fmt($pago->total_pagado) }})</span>
                    </div>
                @empty
                    <span style="color:#94a3b8;">— sin pago</span>
                @endforelse
            </td>
            <td class="t-num" style="text-align:right;">{{ $fmt($bill->total_factura) }}</td>
            <td class="t-num" style="text-align:right;color:#16a34a;">{{ $fmt($bill->total_pagado) }}</td>
            <td><span class="t-badge" style="background:{{ $bb }};color:{{ $bc }};">{{ ucfirst($bill->estado) }}</span></td>
        </tr>
        @empty
        <tr><td colspan="8" style="text-align:center;color:#94a3b8;padding:24px;">Sin facturas registradas en el sistema nuevo</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="xp-pagination">{{ $this->facturas->links() }}</div>
</div>
@endif

{{-- ═══════════════ TAB: HISTÓRICO SIINMOB ═══════════════ --}}
@if($tab === 'historico')
@if($this->historicoMensualCartera->count())
<div class="xp-card" style="border-left:4px solid #7c3aed;">
    <div class="xp-card-head"><div class="icon" style="background:#fdf4ff;">🗓️</div><h3>Histórico Mensual — Cartera Arrendatario (Siinmob)</h3></div>
    <div style="padding:10px 22px;font-size:12px;color:#7c3aed;background:#fdf4ff;">📜 Facturado y pagado mes a mes antes del 1 de julio de 2026.</div>
    <table class="t-table">
        <thead><tr><th>Período</th><th style="text-align:right;">Facturado</th><th style="text-align:right;">Pagado</th></tr></thead>
        <tbody>
        @foreach($this->historicoMensualCartera as $row)
        @php [$anioMes, $mesMes] = explode('-', $row->mes); @endphp
        <tr>
            <td style="font-weight:700;">{{ $mesesNombre[$mesMes] ?? $mesMes }} {{ $anioMes }}</td>
            <td class="t-num" style="text-align:right;">{{ $row->cargo > 0 ? $fmt($row->cargo) : '—' }}</td>
            <td class="t-num" style="text-align:right;color:#16a34a;">{{ $row->pago > 0 ? $fmt($row->pago) : '—' }}</td>
        </tr>
        @endforeach
        </tbody>
    </table>
    <div class="xp-pagination">{{ $this->historicoMensualCartera->links() }}</div>
</div>
@endif

@if($this->historicoMensualCxp->count())
<div class="xp-card" style="border-left:4px solid #7c3aed;">
    <div class="xp-card-head"><div class="icon" style="background:#fdf4ff;">🗓️</div><h3>Histórico Mensual — Giros Propietario (Siinmob)</h3></div>
    <div style="padding:10px 22px;font-size:12px;color:#7c3aed;background:#fdf4ff;">📜 Liquidado y girado mes a mes antes del 1 de julio de 2026.</div>
    <table class="t-table">
        <thead><tr><th>Período</th><th style="text-align:right;">Liquidado</th><th style="text-align:right;">Girado</th></tr></thead>
        <tbody>
        @foreach($this->historicoMensualCxp as $row)
        @php [$anioMes, $mesMes] = explode('-', $row->mes); @endphp
        <tr>
            <td style="font-weight:700;">{{ $mesesNombre[$mesMes] ?? $mesMes }} {{ $anioMes }}</td>
            <td class="t-num" style="text-align:right;">{{ $row->liquidado > 0 ? $fmt($row->liquidado) : '—' }}</td>
            <td class="t-num" style="text-align:right;color:#16a34a;">{{ $row->girado > 0 ? $fmt($row->girado) : '—' }}</td>
        </tr>
        @endforeach
        </tbody>
    </table>
    <div class="xp-pagination">{{ $this->historicoMensualCxp->links() }}</div>
</div>
@endif

@if($this->carteraHeredada->count())
<div class="xp-card" style="border-left:4px solid #7c3aed;">
    <div class="xp-card-head"><div class="icon" style="background:#fdf4ff;">📜</div><h3>Saldo de Apertura Pendiente (Arrendatario)</h3></div>
    <table class="t-table">
        <thead><tr><th>N° Cuenta</th><th>Concepto</th><th style="text-align:right;">Original</th><th style="text-align:right;">Pagado</th><th style="text-align:right;">Saldo</th><th>Estado</th></tr></thead>
        <tbody>
        @foreach($this->carteraHeredada as $cxc)
        <tr>
            <td class="t-num" style="color:#7c3aed;">{{ $cxc->numero }}</td>
            <td style="font-size:11px;color:#64748b;">{{ \Illuminate\Support\Str::limit($cxc->concepto, 30) }}</td>
            <td class="t-num" style="text-align:right;">{{ $fmt($cxc->valor_original) }}</td>
            <td class="t-num" style="text-align:right;color:#16a34a;">{{ $fmt($cxc->valor_pagado) }}</td>
            <td class="t-num" style="text-align:right;color:{{ $cxc->saldo>0?'#dc2626':'#64748b' }};font-weight:900;">{{ $fmt($cxc->saldo) }}</td>
            <td><span class="t-badge" style="background:{{ $cxc->estado === 'pagado' ? '#f0fdf4' : '#fef2f2' }};color:{{ $cxc->estado === 'pagado' ? '#166534' : '#991b1b' }};">{{ ucfirst($cxc->estado) }}</span></td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endif

@if($this->cxpHeredada->count())
<div class="xp-card" style="border-left:4px solid #dc2626;">
    <div class="xp-card-head"><div class="icon" style="background:#fef2f2;">📜</div><h3>Saldo de Apertura Pendiente (Propietario)</h3></div>
    <table class="t-table">
        <thead><tr><th>N° Cuenta</th><th>Concepto</th><th style="text-align:right;">Original</th><th style="text-align:right;">Pagado</th><th style="text-align:right;">Saldo</th><th>Estado</th></tr></thead>
        <tbody>
        @foreach($this->cxpHeredada as $cxp)
        <tr>
            <td class="t-num" style="color:#dc2626;">{{ $cxp->numero }}</td>
            <td style="font-size:11px;color:#64748b;">{{ \Illuminate\Support\Str::limit($cxp->concepto, 30) }}</td>
            <td class="t-num" style="text-align:right;">{{ $fmt($cxp->valor_original) }}</td>
            <td class="t-num" style="text-align:right;color:#16a34a;">{{ $fmt($cxp->valor_pagado) }}</td>
            <td class="t-num" style="text-align:right;color:{{ $cxp->saldo>0?'#dc2626':'#64748b' }};font-weight:900;">{{ $fmt($cxp->saldo) }}</td>
            <td><span class="t-badge" style="background:{{ $cxp->estado === 'pagado' ? '#f0fdf4' : '#fef2f2' }};color:{{ $cxp->estado === 'pagado' ? '#166534' : '#991b1b' }};">{{ ucfirst($cxp->estado) }}</span></td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endif

@if(!$this->historicoMensualCartera->count() && !$this->historicoMensualCxp->count())
<div class="xp-card"><div style="padding:30px;text-align:center;color:#94a3b8;">Sin movimientos históricos de Siinmob vinculados a este tercero.</div></div>
@endif
@endif

{{-- ═══════════════ TAB: LIQUIDACIONES ═══════════════ --}}
@if($tab === 'liquidaciones')
<div class="xp-card">
    <div class="xp-card-head"><div class="icon" style="background:#fdf4ff;">💰</div><h3>Liquidaciones al Propietario (sistema nuevo)</h3></div>
    <table class="t-table">
        <thead><tr>
            <th>N° Liq.</th><th>Inmueble</th><th>Período</th>
            <th style="text-align:right;">Canon</th><th style="text-align:right;">Giro neto</th><th>Fecha de giro</th><th>Estado</th>
        </tr></thead>
        <tbody>
        @forelse($this->liquidaciones as $liq)
        @php [$lc,$lb] = $estadoLiqColor[$liq->estado] ?? ['#64748b','#f8fafc']; @endphp
        <tr>
            <td class="t-num" style="color:#7c3aed;">{{ $liq->numero }}</td>
            <td style="font-size:11px;color:#64748b;">{{ \Illuminate\Support\Str::limit($liq->property?->direccion ?? '—', 22) }}</td>
            <td style="font-size:11px;">{{ $liq->mes }}/{{ $liq->anio }}</td>
            <td class="t-num" style="text-align:right;">{{ $fmt($liq->canon_cobrado) }}</td>
            <td class="t-num" style="text-align:right;color:#16a34a;">{{ $fmt($liq->total_giro) }}</td>
            <td style="font-size:11px;color:{{ $liq->fecha_giro ? '#16a34a' : '#dc2626' }};">{{ $liq->fecha_giro?->format('d/m/Y') ?? 'Sin girar' }}</td>
            <td><span class="t-badge" style="background:{{ $lb }};color:{{ $lc }};">{{ ucfirst($liq->estado) }}</span></td>
        </tr>
        @empty
        <tr><td colspan="7" style="text-align:center;color:#94a3b8;padding:24px;">Sin liquidaciones registradas en el sistema nuevo</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="xp-pagination">{{ $this->liquidaciones->links() }}</div>
</div>
@endif

{{-- ═══════════════ TAB: MOVIMIENTOS CONTABLES ═══════════════ --}}
@if($tab === 'movimientos')
<div class="xp-card">
    <div class="xp-card-head"><div class="icon" style="background:#eff6ff;">📊</div><h3>Movimientos Contables (todos los orígenes)</h3></div>
    <table class="t-table">
        <thead><tr>
            <th>Fecha</th><th>Comprobante</th><th>Cuenta</th>
            <th style="text-align:right;">Débito</th><th style="text-align:right;">Crédito</th>
        </tr></thead>
        <tbody>
        @forelse($this->movimientos as $line)
        <tr>
            <td style="font-size:11px;">{{ $line->entry?->fecha?->format('d/m/Y') }}</td>
            <td style="font-size:11px;color:#2563eb;">{{ $line->entry?->numero }}</td>
            <td style="font-size:11px;color:#475569;"><span style="font-family:monospace;color:#0f172a;">{{ $line->account?->codigo }}</span> {{ $line->account?->nombre }}</td>
            <td class="t-num" style="text-align:right;color:#2563eb;font-size:11px;">{{ $line->debito>0?$fmtDec($line->debito):'' }}</td>
            <td class="t-num" style="text-align:right;color:#16a34a;font-size:11px;">{{ $line->credito>0?$fmtDec($line->credito):'' }}</td>
        </tr>
        @empty
        <tr><td colspan="5" style="text-align:center;color:#94a3b8;padding:24px;">Sin movimientos contables vinculados</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="xp-pagination">{{ $this->movimientos->links() }}</div>
</div>
@endif

</x-filament-panels::page>
