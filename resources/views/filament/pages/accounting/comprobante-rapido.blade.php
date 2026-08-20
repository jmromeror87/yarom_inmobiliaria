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

{{-- Paso 4 (otro concepto): varias partidas — estilo tabla contable --}}
@if($aplicacion === 'otro')
<div class="cr-card" style="padding:0;overflow:hidden;">
    <div style="padding:18px 24px 4px;">
        <span class="cr-label" style="margin-bottom:0;">3. Partidas ({{ $esIngreso ? 'de dónde viene el ingreso' : 'a qué gasto(s)/cuenta(s) se aplica' }})</span>
    </div>

    <div style="overflow-x:auto;">
    <table style="width:100%;border-collapse:collapse;font-size:13px;">
        <thead>
            <tr style="background:#f8fafc;border-top:1px solid #e2e8f0;border-bottom:1.5px solid #e2e8f0;">
                <th style="text-align:left;padding:9px 10px;font-size:10.5px;font-weight:800;color:#64748b;text-transform:uppercase;letter-spacing:.05em;width:26%;">Cuenta</th>
                <th style="text-align:left;padding:9px 10px;font-size:10.5px;font-weight:800;color:#64748b;text-transform:uppercase;letter-spacing:.05em;width:22%;">Tercero</th>
                <th style="text-align:left;padding:9px 10px;font-size:10.5px;font-weight:800;color:#64748b;text-transform:uppercase;letter-spacing:.05em;">Descripción</th>
                <th style="text-align:right;padding:9px 10px;font-size:10.5px;font-weight:800;color:#64748b;text-transform:uppercase;letter-spacing:.05em;width:16%;">Valor</th>
                <th style="width:34px;"></th>
            </tr>
        </thead>
        <tbody>
        @foreach($partidas as $i => $partida)
            <tr style="border-bottom:1px solid #f1f5f9;">
                <td style="padding:8px 10px;vertical-align:top;">
                    @if(!empty($partida['account_id']))
                        <div style="display:flex;align-items:center;gap:6px;font-weight:700;color:#0f172a;font-size:12.5px;">
                            <span style="color:{{ $colorPrincipal }};">●</span>
                            <span>{{ $partida['label'] }}</span>
                            <span style="cursor:pointer;color:#cbd5e1;margin-left:auto;" wire:click="$set('partidas.{{ $i }}.account_id', null); $set('partidas.{{ $i }}.label', '')">✕</span>
                        </div>
                    @else
                        <div style="position:relative;">
                            <input type="text" class="cr-input" style="padding:7px 10px;font-size:12.5px;" placeholder="Buscar cuenta..."
                                wire:model.live.debounce.400ms="partidas.{{ $i }}.search">
                            @if(!empty($partida['search']) && mb_strlen($partida['search']) >= 2)
                                @php $opcionesCuenta = $this->cuentasFiltradasPara($partida['search']); @endphp
                                @if($opcionesCuenta->count() > 0)
                                <div style="position:absolute;z-index:20;background:#fff;border:1px solid #e2e8f0;border-radius:.6rem;width:max-content;min-width:100%;margin-top:4px;max-height:200px;overflow-y:auto;box-shadow:0 8px 24px rgba(0,0,0,.1);">
                                    @foreach($opcionesCuenta as $c)
                                        <div class="cr-tercero-item" wire:click="seleccionarCuentaPartida({{ $i }}, {{ $c->id }})">{{ $c->codigo }} — {{ $c->nombre }}</div>
                                    @endforeach
                                </div>
                                @endif
                            @endif
                        </div>
                    @endif
                </td>
                <td style="padding:8px 10px;vertical-align:top;">
                    @if(!empty($partida['third_id']))
                        <div style="display:flex;align-items:center;gap:6px;font-weight:700;color:#0f172a;font-size:12px;">
                            <span>{{ $partida['tercero_label'] }}</span>
                            <span style="cursor:pointer;color:#cbd5e1;margin-left:auto;" wire:click="$set('partidas.{{ $i }}.third_id', null); $set('partidas.{{ $i }}.tercero_label', '')">✕</span>
                        </div>
                    @else
                        <div style="position:relative;">
                            <input type="text" class="cr-input" style="padding:7px 10px;font-size:12.5px;" placeholder="Buscar tercero..."
                                wire:model.live.debounce.400ms="partidas.{{ $i }}.tercero_search">
                            @if(!empty($partida['tercero_search']) && mb_strlen($partida['tercero_search']) >= 2)
                                @php $opcionesTercero = $this->tercerosFiltradosPara($partida['tercero_search']); @endphp
                                @if($opcionesTercero->count() > 0)
                                <div style="position:absolute;z-index:20;background:#fff;border:1px solid #e2e8f0;border-radius:.6rem;width:max-content;min-width:100%;margin-top:4px;max-height:200px;overflow-y:auto;box-shadow:0 8px 24px rgba(0,0,0,.1);">
                                    @foreach($opcionesTercero as $t)
                                        <div class="cr-tercero-item" wire:click="seleccionarTerceroPartida({{ $i }}, {{ $t->id }})">{{ $t->nombre_completo }} — {{ $t->numero_documento }}</div>
                                    @endforeach
                                </div>
                                @endif
                            @endif
                        </div>
                    @endif
                </td>
                <td style="padding:8px 10px;vertical-align:top;">
                    <input type="text" class="cr-input" style="padding:7px 10px;font-size:12.5px;" wire:model="partidas.{{ $i }}.descripcion" placeholder="Detalle (opcional)...">
                </td>
                <td style="padding:8px 10px;vertical-align:top;">
                    <input type="number" class="cr-input" style="padding:7px 10px;font-size:12.5px;text-align:right;font-weight:700;" wire:model="partidas.{{ $i }}.monto" placeholder="0">
                </td>
                <td style="padding:8px 6px;vertical-align:top;text-align:center;">
                    @if(count($partidas) > 1)
                    <span style="cursor:pointer;color:#dc2626;font-size:15px;" wire:click="quitarPartida({{ $i }})" title="Quitar partida">✕</span>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
            <tr style="background:{{ $colorBg }};border-top:1.5px solid {{ $colorBorder }};">
                <td colspan="3" style="padding:12px 10px;text-align:right;font-size:12px;font-weight:800;color:#64748b;letter-spacing:.03em;">TOTAL PARTIDAS</td>
                <td style="padding:12px 10px;text-align:right;font-size:16px;font-weight:900;color:{{ $colorPrincipal }};">${{ number_format($this->montoTotalPartidas, 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
    </div>

    <div style="padding:12px 24px 18px;">
        <button type="button" wire:click="agregarPartida"
            style="background:#fff;border:1.5px dashed #cbd5e1;border-radius:.6rem;padding:9px 16px;font-size:12.5px;font-weight:700;color:#475569;cursor:pointer;width:100%;">
            + Agregar otra partida
        </button>
    </div>
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
            <span class="cr-label">Monto ($) {{ $aplicacion === 'otro' ? '— suma de las partidas' : '' }}</span>
            @if($aplicacion === 'otro')
                <input type="text" class="cr-input" value="${{ number_format($this->montoTotalPartidas, 0, ',', '.') }}" disabled style="background:#f1f5f9;font-weight:800;color:{{ $colorPrincipal }};">
            @else
                <input type="number" class="cr-input" wire:model="monto" placeholder="0" @if($obligacion) title="Precargado del pendiente seleccionado — puedes ajustarlo" @endif>
            @endif
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
