<x-filament-panels::page>
@php
    $esIngreso = $this->esIngreso;
    $colorPrincipal = $esIngreso ? '#16a34a' : '#dc2626';
    $colorBg = $esIngreso ? '#f0fdf4' : '#fef2f2';
    $colorBorder = $esIngreso ? '#bbf7d0' : '#fecaca';
@endphp

<style>
.cr-card{background:#fff;border:1px solid #e2e8f0;border-radius:1rem;padding:22px 24px;margin-bottom:16px;box-shadow:0 2px 8px rgba(0,0,0,.05);}
.cr-label{font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:#64748b;display:block;margin-bottom:6px;}
.cr-input,.cr-select{width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:.6rem;font-size:13.5px;color:#0f172a;background:#fff;}
.cr-input:focus,.cr-select:focus{outline:none;border-color:{{ $colorPrincipal }};}
.cr-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
.cr-opcion{border:1.5px solid #e2e8f0;border-radius:.75rem;padding:14px 16px;cursor:pointer;transition:all .15s;}
.cr-opcion:hover{border-color:{{ $colorPrincipal }};background:{{ $colorBg }};}
.cr-opcion.active{border-color:{{ $colorPrincipal }};background:{{ $colorBg }};box-shadow:0 0 0 3px {{ $colorBorder }};}
.cr-pendiente{border:1px solid #e2e8f0;border-radius:.6rem;padding:10px 14px;cursor:pointer;margin-bottom:6px;font-size:12.5px;}
.cr-pendiente:hover{border-color:{{ $colorPrincipal }};background:{{ $colorBg }};}
.cr-pendiente.active{border-color:{{ $colorPrincipal }};background:{{ $colorBg }};font-weight:700;}
.cr-tercero-item{padding:8px 12px;cursor:pointer;font-size:13px;border-bottom:1px solid #f1f5f9;}
.cr-tercero-item:hover{background:#f8fafc;}
</style>

<div class="cr-card" style="background:linear-gradient(135deg,#0F172A 0%,#1e2d45 100%);color:#fff;">
    <div style="display:flex;align-items:center;gap:14px;">
        <div style="width:48px;height:48px;border-radius:14px;background:{{ $colorPrincipal }};display:flex;align-items:center;justify-content:center;font-size:22px;">
            {{ $esIngreso ? '⬇️' : '⬆️' }}
        </div>
        <div>
            <div style="font-size:18px;font-weight:900;">{{ $esIngreso ? 'Comprobante de Ingreso' : 'Comprobante de Egreso' }}</div>
            <div style="font-size:12.5px;color:rgba(255,255,255,.6);">{{ $esIngreso ? 'Dinero que entra — cobros, abonos, ingresos varios' : 'Dinero que sale — giros a propietarios, gastos, pagos' }}</div>
        </div>
    </div>
</div>

{{-- Paso 1: qué se está haciendo --}}
<div class="cr-card">
    <span class="cr-label">1. ¿Qué vas a registrar?</span>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px;">
        @foreach($this->opcionesAplicacion as $key => $label)
            <div class="cr-opcion {{ $aplicacion === $key ? 'active' : '' }}" wire:click="$set('aplicacion', '{{ $key }}')">
                <div style="font-size:13px;font-weight:700;color:#0f172a;">{{ $label }}</div>
            </div>
        @endforeach
    </div>
</div>

{{-- Paso 2: tercero (si aplica) --}}
@if($aplicacion !== 'otro' || true)
<div class="cr-card">
    <span class="cr-label">2. {{ $esIngreso ? '¿Quién paga?' : '¿A quién se le paga?' }} @if($aplicacion === 'otro') (opcional) @endif</span>
    <div style="position:relative;">
        <input type="text" class="cr-input" placeholder="Buscar por nombre o documento..." wire:model.live.debounce.400ms="tercero_search">
        @if($this->terceros->count() > 0 && !$third_id)
            <div style="position:absolute;z-index:20;background:#fff;border:1px solid #e2e8f0;border-radius:.6rem;width:100%;margin-top:4px;max-height:220px;overflow-y:auto;box-shadow:0 8px 24px rgba(0,0,0,.1);">
                @foreach($this->terceros as $t)
                    <div class="cr-tercero-item" wire:click="seleccionarTercero({{ $t->id }})">{{ $t->nombre_completo }} — {{ $t->numero_documento }}</div>
                @endforeach
            </div>
        @endif
    </div>
    @if($third_id)
        <div style="margin-top:8px;display:inline-flex;align-items:center;gap:8px;background:{{ $colorBg }};border:1px solid {{ $colorBorder }};border-radius:99px;padding:5px 14px;font-size:12.5px;font-weight:700;color:{{ $colorPrincipal }};">
            ✓ {{ \App\Models\Third::find($third_id)?->nombre_completo }}
            <span style="cursor:pointer;color:#94a3b8;" wire:click="$set('third_id', null)">✕</span>
        </div>
    @endif
</div>
@endif

{{-- Paso 3: obligación pendiente a cancelar (si aplica) --}}
@if($third_id && in_array($aplicacion, ['factura_pendiente','cxc_heredada','liquidacion_propietario','cxp_heredada']))
<div class="cr-card">
    <span class="cr-label">3. Selecciona qué se está {{ $esIngreso ? 'cobrando' : 'pagando' }}</span>
    @forelse($this->pendientes as $p)
        <div class="cr-pendiente {{ $obligacion === $p['key'] ? 'active' : '' }}" wire:click="$set('obligacion', '{{ $p['key'] }}')">
            {{ $p['label'] }}
        </div>
    @empty
        <div style="color:#94a3b8;font-size:12.5px;padding:10px 0;">Este tercero no tiene pendientes en esta categoría.</div>
    @endforelse
</div>
@endif

{{-- Paso 4 (otro concepto): cuenta contraria manual --}}
@if($aplicacion === 'otro')
<div class="cr-card">
    <span class="cr-label">3. Cuenta contable ({{ $esIngreso ? 'de dónde viene el ingreso' : 'a qué gasto/cuenta se aplica' }})</span>
    <select class="cr-select" wire:model="account_id">
        <option value="">Selecciona una cuenta...</option>
        @foreach($this->cuentasManuales as $c)
            <option value="{{ $c->id }}">{{ $c->codigo }} — {{ $c->nombre }}</option>
        @endforeach
    </select>
</div>
@endif

{{-- Paso final: banco, monto, fecha, concepto --}}
<div class="cr-card">
    <span class="cr-label">{{ $aplicacion === 'otro' ? '4' : '4' }}. Datos del {{ $esIngreso ? 'recaudo' : 'pago' }}</span>
    <div class="cr-grid" style="margin-bottom:14px;">
        <div>
            <span class="cr-label">Cuenta de caja/banco</span>
            <select class="cr-select" wire:model="bank_id">
                <option value="">Selecciona...</option>
                @foreach($this->bancos as $b)
                    <option value="{{ $b->id }}">{{ $b->nombre }} @if($b->numero_cuenta) — {{ $b->numero_cuenta }} @endif</option>
                @endforeach
            </select>
        </div>
        <div>
            <span class="cr-label">Monto ($)</span>
            <input type="number" class="cr-input" wire:model="monto" placeholder="0" @if($obligacion) title="Precargado del pendiente seleccionado — puedes ajustarlo" @endif>
        </div>
        <div>
            <span class="cr-label">Fecha</span>
            <input type="date" class="cr-input" wire:model="fecha">
        </div>
        <div>
            <span class="cr-label">Referencia (opcional)</span>
            <input type="text" class="cr-input" wire:model="referencia" placeholder="N° comprobante, transacción...">
        </div>
    </div>
    <span class="cr-label">Concepto {{ $aplicacion === 'otro' ? '' : '(opcional)' }}</span>
    <textarea class="cr-input" wire:model="concepto" rows="2" placeholder="Describe brevemente el {{ $esIngreso ? 'ingreso' : 'egreso' }}..."></textarea>
</div>

<div style="display:flex;justify-content:flex-end;">
    <button wire:click="guardar" wire:loading.attr="disabled"
        style="background:linear-gradient(135deg,{{ $colorPrincipal }},{{ $esIngreso ? '#15803d' : '#b91c1c' }});color:#fff;border:none;padding:13px 32px;border-radius:.75rem;font-size:14px;font-weight:800;cursor:pointer;box-shadow:0 4px 14px {{ $colorBorder }};">
        <span wire:loading.remove>✓ Registrar y contabilizar</span>
        <span wire:loading>Procesando...</span>
    </button>
</div>
</x-filament-panels::page>
