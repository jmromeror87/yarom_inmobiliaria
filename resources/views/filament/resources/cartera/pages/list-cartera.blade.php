<x-filament-panels::page>
@php
    $k = $this->kpis;
    $fmt = fn($v) => '$' . number_format((float)$v, 0, ',', '.');
    $tabs = [
        'resumen' => '📊 Resumen',
        'activa'  => '🧾 Cartera Activa',
        'heredado'=> '🗓️ Heredado Siinmob',
    ];
@endphp

<style>
.ct-kpi{background:#fff;border:1px solid #e2e8f0;border-radius:1rem;padding:18px 20px;box-shadow:0 2px 8px rgba(0,0,0,.05);}
.ct-kpi label{font-size:10.5px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:#94a3b8;display:block;margin-bottom:6px;}
.ct-kpi .val{font-size:21px;font-weight:900;color:#0f172a;letter-spacing:-.02em;}
.ct-kpi .sub{font-size:11.5px;color:#64748b;margin-top:4px;font-weight:600;}
.ct-tabs{display:flex;gap:4px;margin:22px 0 18px;border-bottom:2px solid #e2e8f0;overflow-x:auto;}
.ct-tab{padding:10px 18px;font-size:12.5px;font-weight:700;color:#64748b;cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-2px;white-space:nowrap;}
.ct-tab.active{color:#E11D48;border-bottom-color:#E11D48;}
.ct-tab:hover{color:#0f172a;}
.ct-card{background:#fff;border:1px solid #e2e8f0;border-radius:1rem;overflow:hidden;margin-bottom:16px;box-shadow:0 2px 8px rgba(0,0,0,.05);}
.ct-help{background:#eff6ff;border:1px solid #bfdbfe;border-radius:.75rem;padding:12px 16px;font-size:12.5px;color:#1e40af;margin-bottom:16px;display:flex;gap:10px;align-items:flex-start;}
.t-table{width:100%;border-collapse:collapse;font-size:12.5px;}
.t-table th{background:#f8fafc;padding:10px 16px;text-align:left;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:#64748b;border-bottom:2px solid #e2e8f0;}
.t-table td{padding:10px 16px;border-bottom:1px solid #f1f5f9;vertical-align:middle;color:#334155;}
.t-table tr:last-child td{border-bottom:none;}
.t-table tr:hover td{background:#fafbfc;}
.t-num{font-family:monospace;font-weight:700;}
.t-badge{display:inline-block;padding:3px 10px;border-radius:99px;font-size:10.5px;font-weight:800;}
</style>

{{-- ── KPIs (siempre visibles) ──────────────────────────────────────── --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px;margin-bottom:6px;">
    <div class="ct-kpi">
        <label>Cartera total pendiente</label>
        <div class="val" style="color:#E11D48;">{{ $fmt($k['total_pendiente']) }}</div>
        <div class="sub">{{ $k['total_count'] }} cuentas activas</div>
    </div>
    <div class="ct-kpi">
        <label>Cartera activa (sistema nuevo)</label>
        <div class="val" style="color:#2563eb;">{{ $fmt($k['activa']) }}</div>
        <div class="sub">{{ $k['activa_count'] }} facturas</div>
    </div>
    <div class="ct-kpi">
        <label>Heredado Siinmob</label>
        <div class="val" style="color:#7c3aed;">{{ $fmt($k['heredado']) }}</div>
        <div class="sub">{{ $k['heredado_count'] }} cuentas</div>
    </div>
    <div class="ct-kpi">
        <label>Cartera vencida</label>
        <div class="val" style="color:#dc2626;">{{ $fmt($k['vencida']) }}</div>
        <div class="sub">requiere gestión de cobro</div>
    </div>
    <div class="ct-kpi">
        <label>Recaudado este mes</label>
        <div class="val" style="color:#16a34a;">{{ $fmt($k['recaudado_mes']) }}</div>
        <div class="sub">pagos registrados en {{ now()->translatedFormat('F Y') }}</div>
    </div>
</div>

{{-- ── Tabs ──────────────────────────────────────────────────────────── --}}
<div class="ct-tabs">
    @foreach($tabs as $key => $label)
        <div class="ct-tab {{ $tab === $key ? 'active' : '' }}" wire:click="setTab('{{ $key }}')">{{ $label }}</div>
    @endforeach
</div>

@if($tab === 'resumen')
    <div class="ct-help">
        <span>💡</span>
        <span>Esta gráfica muestra cuánto se debe cobrar según qué tan atrasado está el pago. Las barras <strong>azules</strong> son facturas del sistema nuevo (julio 2026 en adelante); las <strong>moradas</strong> son deuda heredada del sistema anterior (Siinmob), migrada con corte al 30/06/2026. Entre más a la derecha, más urgente es gestionar el cobro.</span>
    </div>
    <div class="ct-card">
        @livewire(\App\Filament\Widgets\CarteraPorEdadWidget::class)
    </div>
@endif

@if($tab === 'activa')
    <div class="ct-help">
        <span>💡</span>
        <span>Facturas generadas por el sistema nuevo (julio 2026 en adelante) que un inquilino todavía debe. Haz clic en una fila para ir directo a la factura y registrar el pago.</span>
    </div>
    <div class="ct-card">
        <table class="t-table">
            <thead><tr>
                <th>N° Factura</th><th>Deudor</th><th>Periodo</th>
                <th style="text-align:right;">Valor original</th><th style="text-align:right;">Pagado</th><th style="text-align:right;">Saldo</th>
                <th>Estado</th><th>Vence</th>
            </tr></thead>
            <tbody>
            @forelse($this->carteraActiva as $bill)
                <tr style="cursor:pointer;" onclick="window.location='{{ \App\Filament\Resources\RentBills\RentBillResource::getUrl('edit', ['record' => $bill]) }}'">
                    <td style="font-weight:700;color:#2563eb;">{{ $bill->numero }}</td>
                    <td>{{ $bill->arrendatario?->nombre_completo }}</td>
                    <td>{{ \Carbon\Carbon::create($bill->anio, $bill->mes, 1)->translatedFormat('F Y') }}</td>
                    <td class="t-num" style="text-align:right;">{{ $fmt($bill->total_factura) }}</td>
                    <td class="t-num" style="text-align:right;color:#16a34a;">{{ $fmt($bill->total_pagado) }}</td>
                    <td class="t-num" style="text-align:right;color:#dc2626;font-weight:800;">{{ $fmt($bill->saldo_pendiente) }}</td>
                    <td>
                        @php $estColor = ['pendiente'=>['#fffbeb','#d97706'],'parcial'=>['#eff6ff','#2563eb'],'en_mora'=>['#fef2f2','#dc2626'],'vencida'=>['#fef2f2','#dc2626']][$bill->estado] ?? ['#f8fafc','#64748b']; @endphp
                        <span class="t-badge" style="background:{{ $estColor[0] }};color:{{ $estColor[1] }};">{{ ucfirst(str_replace('_',' ',$bill->estado)) }}</span>
                    </td>
                    <td style="{{ $bill->fecha_limite_pago?->isPast() ? 'color:#dc2626;font-weight:700;' : '' }}">{{ $bill->fecha_limite_pago?->format('d/m/Y') }}</td>
                </tr>
            @empty
                <tr><td colspan="8" style="text-align:center;color:#94a3b8;padding:24px;">No hay cartera activa pendiente 🎉</td></tr>
            @endforelse
            </tbody>
        </table>
        @include('filament.thirds.partials.paginacion', ['paginator' => $this->carteraActiva, 'pageName' => 'activa_page'])
    </div>
@endif

@if($tab === 'heredado')
    <div class="ct-help">
        <span>💡</span>
        <span>Deuda heredada del sistema anterior (Siinmob) con corte al 30/06/2026, más depósitos, daños y otros conceptos manuales. Haz clic en una fila para ver el detalle o registrar un abono.</span>
    </div>
    <div class="ct-card">
        <table class="t-table">
            <thead><tr>
                <th>N° Cuenta</th><th>Tipo</th><th>Deudor</th>
                <th style="text-align:right;">Valor original</th><th style="text-align:right;">Pagado</th><th style="text-align:right;">Saldo</th>
                <th>Estado</th><th>Vencimiento</th>
            </tr></thead>
            <tbody>
            @forelse($this->heredado as $cuenta)
                <tr style="cursor:pointer;" onclick="window.location='{{ \App\Filament\Resources\Cartera\CarteraResource::getUrl('view', ['record' => $cuenta]) }}'">
                    <td style="font-weight:700;">{{ $cuenta->numero }}</td>
                    <td>
                        @php $tipoLabel = ['deposito_arriendo'=>'Depósito arriendo','mora'=>'Mora','dano'=>'Daño inmueble','saldo_inicial_siinmob'=>'Heredado Siinmob'][$cuenta->tipo] ?? ucfirst($cuenta->tipo); @endphp
                        @php $tipoColor = ['deposito_arriendo'=>['#eff6ff','#2563eb'],'mora'=>['#fef2f2','#dc2626'],'dano'=>['#fffbeb','#d97706'],'saldo_inicial_siinmob'=>['#f5f3ff','#7c3aed']][$cuenta->tipo] ?? ['#f8fafc','#64748b']; @endphp
                        <span class="t-badge" style="background:{{ $tipoColor[0] }};color:{{ $tipoColor[1] }};">{{ $tipoLabel }}</span>
                    </td>
                    <td>{{ $cuenta->third?->nombre_completo }}</td>
                    <td class="t-num" style="text-align:right;">{{ $fmt($cuenta->valor_original) }}</td>
                    <td class="t-num" style="text-align:right;color:#16a34a;">{{ $fmt($cuenta->valor_pagado) }}</td>
                    <td class="t-num" style="text-align:right;color:#dc2626;font-weight:800;">{{ $fmt($cuenta->saldo) }}</td>
                    <td>
                        @php $estColor2 = ['pendiente'=>['#fffbeb','#d97706'],'parcial'=>['#eff6ff','#2563eb'],'pagado'=>['#f0fdf4','#16a34a'],'castigada'=>['#f8fafc','#64748b']][$cuenta->estado] ?? ['#f8fafc','#64748b']; @endphp
                        <span class="t-badge" style="background:{{ $estColor2[0] }};color:{{ $estColor2[1] }};">{{ ucfirst($cuenta->estado) }}</span>
                    </td>
                    <td style="{{ $cuenta->fecha_vencimiento?->isPast() && $cuenta->estado !== 'pagado' ? 'color:#dc2626;font-weight:700;' : '' }}">{{ $cuenta->fecha_vencimiento?->format('d/m/Y') ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="8" style="text-align:center;color:#94a3b8;padding:24px;">Sin cuentas heredadas</td></tr>
            @endforelse
            </tbody>
        </table>
        @include('filament.thirds.partials.paginacion', ['paginator' => $this->heredado, 'pageName' => 'heredado_page'])
    </div>
@endif

</x-filament-panels::page>
