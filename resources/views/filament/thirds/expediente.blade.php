<x-filament-panels::page>
@php
    $r      = $this->record;
    $fmt    = fn($v) => '$' . number_format((float)$v, 0, ',', '.');
    $fmtDec = fn($v) => '$' . number_format((float)$v, 2, ',', '.');

    $totalFacturado  = $r->rentBills->sum('total_factura');
    $totalPagado     = $r->rentBills->sum('total_pagado');
    $totalPendiente  = $r->rentBills->sum('saldo_pendiente');
    $totalLiquidado  = $r->ownerLiquidations->sum('canon_cobrado');
    $totalGirado     = $r->ownerLiquidations->sum('total_giro');
    $totalComision   = $r->ownerLiquidations->sum('comision_valor');

    $rolColors = [
        'Propietario'    => ['bg'=>'#eff6ff','color'=>'#2563eb'],
        'Arrendatario'   => ['bg'=>'#f0fdf4','color'=>'#16a34a'],
        'Fiador/Codeudor'=> ['bg'=>'#fefce8','color'=>'#ca8a04'],
        'Proveedor'      => ['bg'=>'#faf5ff','color'=>'#7c3aed'],
        'Cliente compra' => ['bg'=>'#fff7ed','color'=>'#ea580c'],
    ];

    $estadoBillColor = [
        'pendiente' => ['#d97706','#fffbeb'],
        'pagada'    => ['#16a34a','#f0fdf4'],
        'en_mora'   => ['#dc2626','#fef2f2'],
        'anulada'   => ['#64748b','#f8fafc'],
    ];
    $estadoLiqColor = [
        'pendiente' => ['#d97706','#fffbeb'],
        'girada'    => ['#16a34a','#f0fdf4'],
        'anulada'   => ['#64748b','#f8fafc'],
    ];
@endphp

<style>
.exp-card{background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:20px 24px;margin-bottom:16px;}
.exp-card h3{font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.1em;color:#64748b;margin:0 0 14px;display:flex;align-items:center;gap:8px;}
.exp-row{display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid #f1f5f9;font-size:13px;}
.exp-row:last-child{border-bottom:none;}
.exp-row label{color:#94a3b8;font-weight:600;}
.exp-row span{color:#0f172a;font-weight:700;text-align:right;}
.stat-box{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:16px 20px;text-align:center;}
.stat-box .num{font-size:22px;font-weight:900;color:#0f172a;}
.stat-box .lbl{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#94a3b8;margin-top:4px;}
.badge-rol{display:inline-block;padding:3px 10px;border-radius:99px;font-size:11px;font-weight:800;margin:2px;}
.t-table{width:100%;border-collapse:collapse;font-size:12.5px;}
.t-table th{background:#f8fafc;padding:9px 12px;text-align:left;font-size:10.5px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:#64748b;border-bottom:2px solid #e2e8f0;}
.t-table td{padding:9px 12px;border-bottom:1px solid #f1f5f9;vertical-align:middle;}
.t-table tr:hover td{background:#fafbfc;}
.t-num{font-family:monospace;font-weight:700;}
.t-badge{display:inline-block;padding:2px 8px;border-radius:99px;font-size:10px;font-weight:800;}
.tl-item{display:flex;gap:12px;margin-bottom:12px;align-items:flex-start;}
.tl-dot{width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;flex-shrink:0;margin-top:2px;}
.tl-body{flex:1;background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:10px 14px;}
</style>

{{-- ── HEADER TERCERO ─────────────────────────────────────────────── --}}
<div style="background:linear-gradient(135deg,#0f172a,#2563eb);border-radius:16px;padding:24px 28px;margin-bottom:20px;color:#fff;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px;">
        <div>
            <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;opacity:.6;margin-bottom:6px;">
                {{ $r->tipo_documento }} {{ $r->numero_documento }}
                @if($r->tipo_persona === 'juridica') · Persona Jurídica @endif
            </div>
            <div style="font-size:24px;font-weight:900;margin-bottom:6px;">{{ $r->nombre_completo }}</div>
            <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:10px;">
                @foreach(array_filter(['Propietario'=>$r->es_propietario,'Arrendatario'=>$r->es_arrendatario,'Fiador/Codeudor'=>$r->es_fiador,'Proveedor'=>$r->es_proveedor,'Cliente compra'=>$r->es_cliente_compra]) as $rol=>$activo)
                <span style="background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.3);padding:3px 10px;border-radius:99px;font-size:11px;font-weight:800;">{{ $rol }}</span>
                @endforeach
            </div>
            <div style="font-size:13px;opacity:.75;display:flex;gap:20px;flex-wrap:wrap;">
                @if($r->celular) <span>📱 {{ $r->celular }}</span> @endif
                @if($r->email)   <span>📧 {{ $r->email }}</span>  @endif
                @if($r->municipio) <span>📍 {{ $r->municipio->nombre }}</span> @endif
            </div>
        </div>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;min-width:320px;">
            <div style="background:rgba(255,255,255,.1);border-radius:10px;padding:12px 14px;text-align:center;">
                <div style="font-size:18px;font-weight:900;">{{ $r->rentBills->count() }}</div>
                <div style="font-size:10px;font-weight:700;text-transform:uppercase;opacity:.7;margin-top:2px;">Facturas</div>
            </div>
            <div style="background:rgba(255,255,255,.1);border-radius:10px;padding:12px 14px;text-align:center;">
                <div style="font-size:18px;font-weight:900;">{{ $r->ownerLiquidations->count() }}</div>
                <div style="font-size:10px;font-weight:700;text-transform:uppercase;opacity:.7;margin-top:2px;">Liquidaciones</div>
            </div>
            <div style="background:rgba(255,255,255,.1);border-radius:10px;padding:12px 14px;text-align:center;">
                <div style="font-size:18px;font-weight:900;">{{ $r->rentalContracts->count() + $r->properties->count() }}</div>
                <div style="font-size:10px;font-weight:700;text-transform:uppercase;opacity:.7;margin-top:2px;">Contratos</div>
            </div>
        </div>
    </div>
</div>

{{-- ── RESUMEN FINANCIERO ──────────────────────────────────────────── --}}
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px;margin-bottom:20px;">
    @if($r->es_arrendatario)
    <div class="stat-box"><div class="num" style="color:#2563eb;">{{ $fmt($totalFacturado) }}</div><div class="lbl">Total facturado</div></div>
    <div class="stat-box"><div class="num" style="color:#16a34a;">{{ $fmt($totalPagado) }}</div><div class="lbl">Total pagado</div></div>
    <div class="stat-box"><div class="num" style="color:{{ $totalPendiente > 0 ? '#dc2626' : '#64748b' }};">{{ $fmt($totalPendiente) }}</div><div class="lbl">Saldo pendiente</div></div>
    @endif
    @if($r->es_propietario)
    <div class="stat-box"><div class="num" style="color:#7c3aed;">{{ $fmt($totalLiquidado) }}</div><div class="lbl">Canon liquidado</div></div>
    <div class="stat-box"><div class="num" style="color:#16a34a;">{{ $fmt($totalGirado) }}</div><div class="lbl">Total girado</div></div>
    <div class="stat-box"><div class="num" style="color:#ea580c;">{{ $fmt($totalComision) }}</div><div class="lbl">Comisiones</div></div>
    @endif
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
<div>

{{-- ── FACTURAS ────────────────────────────────────────────────────── --}}
@if($r->rentBills->count())
<div class="exp-card" style="padding:0;overflow:hidden;">
    <div style="padding:16px 20px 12px;border-bottom:1px solid #f1f5f9;">
        <h3 style="margin:0;">🧾 Facturas de Arrendamiento</h3>
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
            <td style="font-size:11px;color:#475569;">{{ $bill->rentalContract?->property?->direccion ?? '—' }}</td>
            <td style="font-size:11px;">{{ $bill->mes }}/{{ $bill->anio }}</td>
            <td class="t-num" style="text-align:right;">{{ $fmt($bill->total_factura) }}</td>
            <td class="t-num" style="text-align:right;color:#16a34a;">{{ $fmt($bill->total_pagado) }}</td>
            <td><span class="t-badge" style="background:{{ $bb }};color:{{ $bc }};">{{ ucfirst($bill->estado) }}</span></td>
        </tr>
        @endforeach
        <tr style="background:#f8fafc;font-weight:900;border-top:2px solid #e2e8f0;">
            <td colspan="3" style="padding:10px 12px;font-size:11px;text-transform:uppercase;letter-spacing:.05em;">TOTALES</td>
            <td class="t-num" style="text-align:right;">{{ $fmt($totalFacturado) }}</td>
            <td class="t-num" style="text-align:right;color:#16a34a;">{{ $fmt($totalPagado) }}</td>
            <td></td>
        </tr>
        </tbody>
    </table>
</div>
@endif

{{-- ── CONTRATOS ARRENDAMIENTO ─────────────────────────────────────── --}}
@if($r->rentalContracts->count())
<div class="exp-card">
    <h3>🔑 Contratos de Arrendamiento (inquilino)</h3>
    @foreach($r->rentalContracts->sortByDesc('fecha_inicio') as $c)
    <div style="border:1px solid #e2e8f0;border-radius:10px;padding:12px 14px;margin-bottom:8px;">
        <div style="display:flex;justify-content:space-between;align-items:center;">
            <div>
                <div style="font-weight:800;font-size:13px;color:#0f172a;">{{ $c->numero ?? 'Sin número' }}</div>
                <div style="font-size:12px;color:#64748b;">{{ $c->property?->direccion }}</div>
                <div style="font-size:11px;color:#94a3b8;margin-top:2px;">
                    {{ $c->fecha_inicio?->format('d/m/Y') }} — {{ $c->fecha_fin?->format('d/m/Y') ?? 'Vigente' }}
                </div>
            </div>
            <div style="text-align:right;">
                <div style="font-family:monospace;font-weight:900;font-size:16px;color:#2563eb;">{{ $fmt($c->canon_mensual ?? 0) }}</div>
                <div style="font-size:10px;color:#94a3b8;">canon/mes</div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- ── INMUEBLES (propietario) ─────────────────────────────────────── --}}
@if($r->properties->count())
<div class="exp-card">
    <h3>🏠 Inmuebles (propietario)</h3>
    @foreach($r->properties as $p)
    <div class="exp-row">
        <label>{{ $p->codigo }}</label>
        <span>{{ $p->direccion }} <span style="font-size:10px;color:#94a3b8;">· {{ ucfirst($p->estado) }}</span></span>
    </div>
    @endforeach
</div>
@endif

</div>
<div>

{{-- ── LIQUIDACIONES PROPIETARIO ───────────────────────────────────── --}}
@if($r->ownerLiquidations->count())
<div class="exp-card" style="padding:0;overflow:hidden;">
    <div style="padding:16px 20px 12px;border-bottom:1px solid #f1f5f9;">
        <h3 style="margin:0;">💰 Liquidaciones al Propietario</h3>
    </div>
    <table class="t-table">
        <thead><tr>
            <th>N° Liq.</th><th>Inmueble</th><th>Período</th>
            <th style="text-align:right;">Canon</th><th style="text-align:right;">Giro neto</th><th>Estado</th>
        </tr></thead>
        <tbody>
        @foreach($r->ownerLiquidations->sortByDesc('anio')->sortByDesc('mes') as $liq)
        @php [$lc,$lb] = $estadoLiqColor[$liq->estado] ?? ['#64748b','#f8fafc']; @endphp
        <tr>
            <td class="t-num" style="color:#7c3aed;">{{ $liq->numero }}</td>
            <td style="font-size:11px;color:#475569;">{{ $liq->property?->direccion ?? '—' }}</td>
            <td style="font-size:11px;">{{ $liq->mes }}/{{ $liq->anio }}</td>
            <td class="t-num" style="text-align:right;">{{ $fmt($liq->canon_cobrado) }}</td>
            <td class="t-num" style="text-align:right;color:#16a34a;">{{ $fmt($liq->total_giro) }}</td>
            <td><span class="t-badge" style="background:{{ $lb }};color:{{ $lc }};">{{ ucfirst($liq->estado) }}</span></td>
        </tr>
        @endforeach
        <tr style="background:#f8fafc;font-weight:900;border-top:2px solid #e2e8f0;">
            <td colspan="3" style="padding:10px 12px;font-size:11px;text-transform:uppercase;letter-spacing:.05em;">TOTALES</td>
            <td class="t-num" style="text-align:right;">{{ $fmt($totalLiquidado) }}</td>
            <td class="t-num" style="text-align:right;color:#16a34a;">{{ $fmt($totalGirado) }}</td>
            <td></td>
        </tr>
        </tbody>
    </table>
</div>
@endif

{{-- ── MOVIMIENTOS CONTABLES ───────────────────────────────────────── --}}
@if($r->accountingLines->count())
<div class="exp-card" style="padding:0;overflow:hidden;margin-top:16px;">
    <div style="padding:16px 20px 12px;border-bottom:1px solid #f1f5f9;">
        <h3 style="margin:0;">📊 Movimientos Contables</h3>
    </div>
    <table class="t-table">
        <thead><tr>
            <th>Fecha</th><th>Comprobante</th><th>Cuenta</th>
            <th style="text-align:right;">Débito</th><th style="text-align:right;">Crédito</th>
        </tr></thead>
        <tbody>
        @php $totalContDeb = 0; $totalContCre = 0; @endphp
        @foreach($r->accountingLines->filter(fn($l) => $l->entry?->estado === 'contabilizado')->sortByDesc(fn($l) => $l->entry?->fecha) as $line)
        @php $totalContDeb += $line->debito; $totalContCre += $line->credito; @endphp
        <tr>
            <td class="t-num" style="font-size:11px;">{{ $line->entry?->fecha?->format('d/m/Y') }}</td>
            <td style="font-size:11px;color:#2563eb;">{{ $line->entry?->numero }}</td>
            <td style="font-size:11px;color:#475569;">
                <span style="font-family:monospace;color:#0f172a;">{{ $line->account?->codigo }}</span>
                {{ $line->account?->nombre }}
            </td>
            <td class="t-num" style="text-align:right;color:#2563eb;font-size:11px;">{{ $line->debito > 0 ? $fmtDec($line->debito) : '' }}</td>
            <td class="t-num" style="text-align:right;color:#16a34a;font-size:11px;">{{ $line->credito > 0 ? $fmtDec($line->credito) : '' }}</td>
        </tr>
        @endforeach
        <tr style="background:#f8fafc;font-weight:900;border-top:2px solid #e2e8f0;">
            <td colspan="3" style="padding:10px 12px;font-size:11px;text-transform:uppercase;letter-spacing:.05em;">TOTALES CONTABILIZADOS</td>
            <td class="t-num" style="text-align:right;color:#2563eb;">{{ $fmtDec($totalContDeb) }}</td>
            <td class="t-num" style="text-align:right;color:#16a34a;">{{ $fmtDec($totalContCre) }}</td>
        </tr>
        </tbody>
    </table>
</div>
@endif

{{-- ── INFORMACIÓN PERSONAL ────────────────────────────────────────── --}}
<div class="exp-card" style="margin-top:16px;">
    <h3>👤 Información del Tercero</h3>
    @if($r->tipo_empleo)
    <div class="exp-row"><label>Empleo</label><span>{{ $r->empresa_donde_trabaja }} — {{ $r->cargo }}</span></div>
    @endif
    @if($r->ingresos_mensuales)
    <div class="exp-row"><label>Ingresos mensuales</label><span>{{ $fmt($r->ingresos_mensuales) }}</span></div>
    @endif
    @if($r->banco)
    <div class="exp-row"><label>Cuenta bancaria</label><span>{{ $r->banco }} {{ $r->tipo_cuenta }} {{ $r->numero_cuenta }}</span></div>
    @endif
    @if($r->estado_crediticio)
    <div class="exp-row">
        <label>Estado crediticio</label>
        <span>{{ ucfirst($r->estado_crediticio) }}
            @if($r->score_crediticio) (Score: {{ $r->score_crediticio }}) @endif
        </span>
    </div>
    @endif
    @if($r->tipo_garantia)
    <div class="exp-row"><label>Garantía</label><span>{{ ucfirst($r->tipo_garantia) }} @if($r->numero_poliza)— Póliza: {{ $r->numero_poliza }}@endif</span></div>
    @endif
    @if($r->notas)
    <div style="margin-top:10px;padding:10px 12px;background:#f8fafc;border-radius:8px;font-size:12px;color:#475569;">
        <strong>Notas:</strong> {{ $r->notas }}
    </div>
    @endif
</div>

</div>
</div>

{{-- ── PORTAL DEL PROPIETARIO ─────────────────────────────────────── --}}
@if($r->es_propietario)
<div class="exp-card" style="margin-top:16px; border-left: 4px solid {{ $r->portal_activo ? '#22c55e' : '#e2e8f0' }};">
    <h3>
        <svg style="width:14px;height:14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244"/>
        </svg>
        Portal del propietario
    </h3>

    @if($r->portal_activo && $r->portal_url)
        <div style="display:flex; align-items:center; gap:10px; margin-bottom:10px; flex-wrap:wrap;">
            <span style="display:inline-flex;align-items:center;gap:5px;font-size:12px;font-weight:700;color:#22c55e;background:#f0fdf4;padding:4px 12px;border-radius:999px;border:1px solid #bbf7d0;">
                ✅ Activo desde {{ $r->portal_token_generado_at?->format('d/m/Y H:i') }}
            </span>
        </div>
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
            <input
                type="text" readonly
                value="{{ $r->portal_url }}"
                onclick="this.select()"
                style="flex:1;font-size:11px;font-family:monospace;padding:7px 10px;border:1px solid #e2e8f0;border-radius:6px;background:#f8fafc;color:#374151;min-width:0;"
            >
            <button
                onclick="navigator.clipboard.writeText('{{ $r->portal_url }}').then(()=>{this.textContent='✓ Copiado';setTimeout(()=>this.textContent='Copiar',1500)})"
                style="padding:7px 14px;background:#6366f1;color:#fff;border:none;border-radius:6px;font-size:12px;font-weight:600;cursor:pointer;white-space:nowrap;"
            >Copiar</button>
            @if($r->celular)
            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $r->celular) }}?text={{ urlencode('Hola ' . $r->primer_nombre . ', le compartimos su portal de propietario: ' . $r->portal_url) }}"
               target="_blank"
               style="padding:7px 14px;background:#22c55e;color:#fff;border:none;border-radius:6px;font-size:12px;font-weight:600;cursor:pointer;text-decoration:none;white-space:nowrap;">
                📱 WhatsApp
            </a>
            @endif
        </div>
    @else
        <div style="display:flex;align-items:center;gap:10px;">
            <span style="font-size:12px;color:#94a3b8;">🔒 Sin acceso generado aún</span>
        </div>
    @endif
</div>
@endif

</x-filament-panels::page>
