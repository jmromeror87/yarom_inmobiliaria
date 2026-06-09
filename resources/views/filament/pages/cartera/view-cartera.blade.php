<x-filament-panels::page>

@php
    $cpc     = $this->record;
    $abonos  = $cpc->abonos()->with('registradoPor')->orderByDesc('fecha_abono')->get();
    $pct     = $cpc->porcentajePagado();
    $vencida = $cpc->fecha_vencimiento?->isPast() && $cpc->estado !== 'pagado';
    $diasMora= $cpc->fecha_vencimiento ? max(0, (int) now()->diffInDays($cpc->fecha_vencimiento, false) * -1) : 0;
    $fmt     = fn($v) => '$' . number_format((float)$v, 0, ',', '.');
    $tipoLabel = match($cpc->tipo) {
        'deposito_arriendo' => 'Depósito en garantía',
        'mora'              => 'Intereses de mora',
        'dano'              => 'Daño en inmueble',
        'otro'              => 'Otro concepto',
        default             => ucfirst($cpc->tipo ?? '—'),
    };
    $estadoColor = match($cpc->estado) {
        'pagado'    => ['bg'=>'#f0fdf4','bd'=>'#86efac','txt'=>'#15803d','lbl'=>'Pagado'],
        'parcial'   => ['bg'=>'#eff6ff','bd'=>'#93c5fd','txt'=>'#1d4ed8','lbl'=>'Pago parcial'],
        'castigada' => ['bg'=>'#f8fafc','bd'=>'#94a3b8','txt'=>'#475569','lbl'=>'Castigada'],
        default     => ['bg'=>'#fef9c3','bd'=>'#fde047','txt'=>'#854d0e','lbl'=>'Pendiente'],
    };
@endphp

<style>
.cpc-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px}
.cpc-kpi{background:#fff;border:1.5px solid #e2e8f0;border-radius:14px;padding:16px 20px}
.cpc-kpi-label{font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:#94a3b8;margin-bottom:4px}
.cpc-kpi-val{font-size:22px;font-weight:900;font-family:monospace}
.cpc-card{background:#fff;border:1.5px solid #e2e8f0;border-radius:14px;padding:20px 24px;margin-bottom:16px}
.cpc-card h3{font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:#64748b;margin:0 0 14px}
.cpc-row{display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f1f5f9;font-size:13px}
.cpc-row:last-child{border-bottom:none}
.cpc-bar-wrap{background:#f1f5f9;border-radius:99px;height:10px;margin:10px 0 4px;overflow:hidden}
.cpc-bar-fill{height:10px;border-radius:99px}
.abono-row{display:flex;align-items:flex-start;gap:12px;padding:10px 0;border-bottom:1px solid #f1f5f9}
.abono-row:last-child{border-bottom:none}
.abono-dot{width:10px;height:10px;border-radius:50%;margin-top:3px;flex-shrink:0}
@media(max-width:768px){.cpc-grid{grid-template-columns:1fr 1fr}}
</style>

{{-- HEADER --}}
<div style="background:linear-gradient(135deg,#0f172a,#1e3a8a);border-radius:14px;padding:20px 24px;margin-bottom:20px;display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px">
    <div>
        <div style="font-size:11px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:rgba(255,255,255,.5);margin-bottom:4px">{{ $tipoLabel }}</div>
        <div style="font-size:26px;font-weight:900;color:#fff;font-family:monospace">{{ $cpc->numero }}</div>
        <div style="font-size:13px;color:rgba(255,255,255,.7);margin-top:4px">{{ $cpc->concepto }}</div>
    </div>
    <div style="display:flex;flex-direction:column;align-items:flex-end;gap:8px">
        <span style="background:{{ $estadoColor['bg'] }};color:{{ $estadoColor['txt'] }};border:1.5px solid {{ $estadoColor['bd'] }};padding:4px 14px;border-radius:99px;font-size:12px;font-weight:800">
            {{ $estadoColor['lbl'] }}
        </span>
        @if($vencida && $diasMora > 0)
        <span style="background:rgba(239,68,68,.15);color:#fca5a5;border:1px solid rgba(239,68,68,.3);padding:3px 10px;border-radius:99px;font-size:11px;font-weight:700">
            ⏰ {{ $diasMora }} días en mora
        </span>
        @endif
    </div>
</div>

{{-- KPIs --}}
<div class="cpc-grid">
    <div class="cpc-kpi">
        <div class="cpc-kpi-label">Valor original</div>
        <div class="cpc-kpi-val" style="color:#0f172a">{{ $fmt($cpc->valor_original) }}</div>
    </div>
    <div class="cpc-kpi">
        <div class="cpc-kpi-label">Total pagado</div>
        <div class="cpc-kpi-val" style="color:#15803d">{{ $fmt($cpc->valor_pagado) }}</div>
    </div>
    <div class="cpc-kpi" style="{{ $cpc->saldo > 0 ? 'border-color:#fca5a5;background:#fff5f5' : '' }}">
        <div class="cpc-kpi-label">Saldo pendiente</div>
        <div class="cpc-kpi-val" style="color:{{ $cpc->saldo > 0 ? '#dc2626' : '#15803d' }}">{{ $fmt($cpc->saldo) }}</div>
    </div>
    <div class="cpc-kpi">
        <div class="cpc-kpi-label">Avance de pago</div>
        <div class="cpc-kpi-val" style="color:#1d4ed8">{{ $pct }}%</div>
        <div class="cpc-bar-wrap">
            <div class="cpc-bar-fill" style="width:{{ min(100,$pct) }}%;background:{{ $pct>=100 ? '#16a34a' : ($pct>=50 ? '#2563eb' : '#ef4444') }}"></div>
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">

{{-- INFO --}}
<div class="cpc-card">
    <h3>Información de la cuenta</h3>
    <div class="cpc-row"><span style="color:#64748b;font-weight:500">Deudor</span><span style="font-weight:700">{{ $cpc->third?->nombre_completo ?? '—' }}</span></div>
    <div class="cpc-row"><span style="color:#64748b;font-weight:500">NIT / Cédula</span><span style="font-weight:700">{{ $cpc->third?->numero_documento ?? '—' }}</span></div>
    <div class="cpc-row"><span style="color:#64748b;font-weight:500">Inmueble</span><span style="font-weight:700">{{ $cpc->property?->codigo ?? '—' }}</span></div>
    @if($cpc->property)
    <div class="cpc-row"><span style="color:#64748b;font-weight:500">Dirección</span><span style="font-weight:700">{{ $cpc->property->direccion }}</span></div>
    @endif
    <div class="cpc-row"><span style="color:#64748b;font-weight:500">Contrato</span><span style="font-weight:700">{{ $cpc->rentalContract?->numero_contrato ?? '—' }}</span></div>
    <div class="cpc-row"><span style="color:#64748b;font-weight:500">Fecha origen</span><span style="font-weight:700">{{ $cpc->fecha_origen?->format('d/m/Y') ?? '—' }}</span></div>
    <div class="cpc-row">
        <span style="color:#64748b;font-weight:500">Vencimiento</span>
        <span style="font-weight:700;color:{{ $vencida ? '#dc2626' : 'inherit' }}">
            {{ $cpc->fecha_vencimiento?->format('d/m/Y') ?? '—' }}
            @if($vencida) &nbsp;<small style="color:#dc2626">VENCIDA</small> @endif
        </span>
    </div>
    @if($cpc->fecha_pago_total)
    <div class="cpc-row"><span style="color:#64748b;font-weight:500">Fecha pago total</span><span style="font-weight:700;color:#15803d">{{ $cpc->fecha_pago_total->format('d/m/Y') }}</span></div>
    @endif
    @if($cpc->notas)
    <div style="margin-top:12px;padding:10px;background:#f8fafc;border-radius:8px;font-size:12px;color:#475569">📝 {{ $cpc->notas }}</div>
    @endif
</div>

{{-- ABONOS --}}
<div class="cpc-card">
    <h3>Historial de abonos ({{ $abonos->count() }})</h3>
    @if($abonos->isEmpty())
    <div style="text-align:center;padding:32px 0;color:#94a3b8">
        <div style="font-size:32px;margin-bottom:8px">💳</div>
        <div style="font-size:13px;font-weight:600">Sin abonos registrados</div>
    </div>
    @else
    @foreach($abonos as $abono)
    <div class="abono-row">
        <div class="abono-dot" style="background:{{ $abono->forma_pago === 'efectivo' ? '#16a34a' : '#1d4ed8' }}"></div>
        <div style="flex:1">
            <div style="display:flex;justify-content:space-between;align-items:center">
                <span style="font-size:11px;color:#94a3b8;font-weight:600">{{ $abono->fecha_abono->format('d/m/Y') }}</span>
                <span style="font-family:monospace;font-weight:800;color:#15803d;font-size:14px">{{ $fmt($abono->valor) }}</span>
            </div>
            <div style="font-size:11px;color:#64748b;margin-top:2px">
                {{ match($abono->forma_pago){
                    'transferencia'=>'🏦 Transferencia','efectivo'=>'💵 Efectivo',
                    'cheque'=>'📄 Cheque','pse'=>'🌐 PSE',default=>ucfirst($abono->forma_pago)
                } }}
                @if($abono->referencia) · <span style="font-family:monospace">{{ $abono->referencia }}</span> @endif
                @if($abono->registradoPor) · {{ $abono->registradoPor->name }} @endif
            </div>
            @if($abono->notas)<div style="font-size:11px;color:#94a3b8;margin-top:2px">{{ $abono->notas }}</div>@endif
        </div>
    </div>
    @endforeach
    <div style="display:flex;justify-content:space-between;padding:12px 0 0;font-size:13px;font-weight:900;border-top:2px solid #e2e8f0;margin-top:4px">
        <span style="color:#64748b">TOTAL ABONADO</span>
        <span style="color:#15803d;font-family:monospace">{{ $fmt($abonos->sum('valor')) }}</span>
    </div>
    @endif
</div>

</div>
</x-filament-panels::page>
