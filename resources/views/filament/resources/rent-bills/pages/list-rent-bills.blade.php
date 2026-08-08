<x-filament-panels::page>
<style>
.fb-wrap { font-family:'Plus Jakarta Sans',sans-serif; }

.fb-search-bar {
    display:flex; align-items:center; gap:10px;
    background:#fff; border:1px solid rgba(226,232,240,.9); border-radius:16px;
    padding:14px 18px; box-shadow:0 2px 10px rgba(15,23,42,.05);
    margin-bottom:22px;
}
.dark .fb-search-bar { background:#1e293b; border-color:rgba(51,65,85,.8); }
.fb-search-icon { color:#94a3b8; flex-shrink:0; }
.fb-search-input {
    border:none; outline:none; background:transparent; width:100%;
    font-size:0.95rem; font-weight:600; color:#0F172A;
}
.dark .fb-search-input { color:#f1f5f9; }
.fb-search-input::placeholder { color:#94a3b8; font-weight:500; }

.fb-empty {
    text-align:center; padding:60px 20px; color:#94a3b8;
}
.fb-empty-icon { font-size:2.5rem; margin-bottom:10px; }
.fb-empty-title { font-size:0.95rem; font-weight:700; color:#64748b; margin-bottom:4px; }
.dark .fb-empty-title { color:#94a3b8; }
.fb-empty-desc { font-size:0.8rem; }

.fb-results { display:flex; flex-direction:column; gap:16px; }

.fb-card {
    background:#fff; border-radius:18px; border:1px solid rgba(226,232,240,.9);
    box-shadow:0 2px 12px rgba(15,23,42,.05); overflow:hidden;
}
.dark .fb-card { background:#1e293b; border-color:rgba(51,65,85,.8); }

.fb-card-head { display:flex; align-items:center; gap:12px; padding:16px 18px; border-bottom:1px solid #f1f5f9; }
.dark .fb-card-head { border-color:#334155; }
.fb-avatar {
    flex-shrink:0; width:42px; height:42px; border-radius:50%;
    display:flex; align-items:center; justify-content:center;
    background:#eef2ff; color:#4f46e5; font-weight:800; font-size:1rem;
}
.dark .fb-avatar { background:rgba(99,102,241,.15); color:#a5b4fc; }
.fb-card-name { font-size:0.95rem; font-weight:800; color:#0F172A; }
.dark .fb-card-name { color:#f1f5f9; }
.fb-card-doc { font-size:0.72rem; color:#94a3b8; font-weight:600; }

.fb-tabs { display:flex; gap:6px; flex-wrap:wrap; padding:12px 18px 0; }
.fb-tab {
    font-size:0.72rem; font-weight:700; padding:6px 13px; border-radius:20px;
    background:#f1f5f9; color:#475569; border:none; cursor:pointer;
    transition:all .15s ease;
}
.dark .fb-tab { background:#0f172a; color:#94a3b8; }
.fb-tab.is-active { background:#4f46e5; color:#fff; }

.fb-tab-panel { padding:16px 18px 18px; }

.fb-next {
    border-radius:14px; padding:14px 16px; margin-bottom:10px;
    border:1px solid; display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:10px;
}
.fb-next-info { min-width:180px; }
.fb-next-label { font-size:0.62rem; font-weight:800; text-transform:uppercase; letter-spacing:.05em; margin-bottom:3px; }
.fb-next-periodo { font-size:0.85rem; font-weight:800; color:#0F172A; }
.dark .fb-next-periodo { color:#f1f5f9; }
.fb-next-monto { font-size:1.1rem; font-weight:900; letter-spacing:-.01em; }
.fb-next-actions { display:flex; gap:8px; flex-wrap:wrap; }

.fb-btn {
    font-size:0.72rem; font-weight:700; padding:8px 14px; border-radius:10px;
    border:1px solid transparent; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; gap:5px;
    white-space:nowrap;
}
.fb-btn-primary { background:#0F172A; color:#fff; }
.dark .fb-btn-primary { background:#f1f5f9; color:#0F172A; }
.fb-btn-outline { background:transparent; border-color:#cbd5e1; color:#475569; }
.dark .fb-btn-outline { border-color:#475569; color:#cbd5e1; }
.fb-btn-wap { background:#25D366; color:#fff; border-color:#25D366; }
.fb-btn-wap:hover { background:#1ebe5a; border-color:#1ebe5a; }

.fb-ok {
    border-radius:14px; padding:12px 16px; margin-bottom:10px;
    background:#f0fdf4; border:1px solid #bbf7d0; color:#166534;
    font-size:0.8rem; font-weight:700; display:flex; align-items:center; gap:8px;
}
.dark .fb-ok { background:rgba(34,197,94,.08); border-color:rgba(34,197,94,.25); color:#4ade80; }

.fb-history { margin-top:4px; }
.fb-history summary {
    cursor:pointer; font-size:0.72rem; font-weight:700; color:#64748b;
    list-style:none; display:flex; align-items:center; gap:5px; padding:6px 2px;
}
.dark .fb-history summary { color:#94a3b8; }
.fb-history summary::-webkit-details-marker { display:none; }
.fb-history-row {
    display:flex; align-items:center; justify-content:space-between; gap:10px;
    padding:8px 4px; border-bottom:1px solid #f8fafc; font-size:0.76rem;
}
.dark .fb-history-row { border-color:#273549; }
.fb-history-row:last-child { border-bottom:none; }
.fb-history-periodo { color:#0F172A; font-weight:700; }
.dark .fb-history-periodo { color:#e2e8f0; }
.fb-badge { font-size:0.62rem; font-weight:800; padding:2px 8px; border-radius:20px; white-space:nowrap; }

.fb-filter-bar {
    display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap;
    background:#eef2ff; border:1px solid #c7d2fe; border-radius:14px; padding:12px 18px; margin-bottom:16px;
}
.dark .fb-filter-bar { background:rgba(99,102,241,.1); border-color:rgba(99,102,241,.3); }
.fb-filter-bar-title { font-size:0.85rem; font-weight:800; color:#3730a3; }
.dark .fb-filter-bar-title { color:#a5b4fc; }
.fb-filter-bar-count { font-size:0.72rem; color:#4f46e5; font-weight:600; }
.dark .fb-filter-bar-count { color:#818cf8; }

.fb-flat-list { background:#fff; border-radius:18px; border:1px solid rgba(226,232,240,.9); box-shadow:0 2px 12px rgba(15,23,42,.05); overflow:hidden; }
.dark .fb-flat-list { background:#1e293b; border-color:rgba(51,65,85,.8); }
.fb-flat-row {
    display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;
    padding:14px 18px; border-bottom:1px solid #f1f5f9;
}
.dark .fb-flat-row { border-color:#334155; }
.fb-flat-row:last-child { border-bottom:none; }
.fb-flat-row-name { font-size:0.85rem; font-weight:800; color:#0F172A; }
.dark .fb-flat-row-name { color:#f1f5f9; }
.fb-flat-row-sub { font-size:0.72rem; color:#94a3b8; font-weight:600; margin-top:2px; }

@media (min-width: 900px) {
    .fb-results { display:grid; grid-template-columns:repeat(2, 1fr); align-items:start; }
}
@media (min-width: 1400px) {
    .fb-results { grid-template-columns:repeat(3, 1fr); }
}
</style>

@php
    $estadoInfo = fn (string $estado) => match ($estado) {
        'pagada'    => ['label' => 'Pagada',    'bg' => '#d1fae5', 'fg' => '#059669'],
        'parcial'   => ['label' => 'Parcial',   'bg' => '#fef3c7', 'fg' => '#d97706'],
        'en_mora'   => ['label' => 'En mora',   'bg' => '#fee2e2', 'fg' => '#dc2626'],
        'vencida'   => ['label' => 'Vencida',   'bg' => '#fee2e2', 'fg' => '#dc2626'],
        'anulada'   => ['label' => 'Anulada',   'bg' => '#f1f5f9', 'fg' => '#64748b'],
        default     => ['label' => 'Pendiente', 'bg' => '#e0f2fe', 'fg' => '#0284c7'],
    };
@endphp

<div class="fb-wrap">

    <div class="fb-search-bar">
        <span class="fb-search-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;height:20px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
        </span>
        <input
            type="text"
            wire:model.live.debounce.400ms="search"
            class="fb-search-input"
            placeholder="Buscar inquilino por cédula, nombre o inmueble..."
        />
    </div>

    @if ($filtro && trim($search) === '')
        @php $facturas = $this->getResultadosPorFiltro(); @endphp

        <div class="fb-filter-bar">
            <div>
                <div class="fb-filter-bar-title">🔎 {{ $this->getFiltroLabel() }}</div>
                <div class="fb-filter-bar-count">{{ $facturas->count() }} factura{{ $facturas->count() === 1 ? '' : 's' }}{{ $facturas->count() >= 100 ? ' (mostrando las primeras 100)' : '' }}</div>
            </div>
            <a href="{{ \App\Filament\Resources\RentBills\RentBillResource::getUrl('index') }}" class="fb-btn fb-btn-outline">✕ Quitar filtro</a>
        </div>

        @if ($facturas->isEmpty())
            <div class="fb-empty">
                <div class="fb-empty-icon">✅</div>
                <div class="fb-empty-title">No hay facturas en este filtro ahora mismo.</div>
            </div>
        @else
            <div class="fb-flat-list">
                @foreach ($facturas as $f)
                    @php $info = $estadoInfo($f->estado); @endphp
                    <div class="fb-flat-row">
                        <div>
                            <div class="fb-flat-row-name">{{ $f->arrendatario?->nombre_completo ?? '—' }}</div>
                            <div class="fb-flat-row-sub">{{ $f->numero }} · {{ $f->property?->codigo ?? '—' }} · {{ \Carbon\Carbon::create($f->anio, $f->mes, 1)->translatedFormat('F Y') }}</div>
                        </div>
                        <div style="display:flex;align-items:center;gap:12px;">
                            <div style="text-align:right;">
                                <span class="fb-badge" style="background:{{ $info['bg'] }};color:{{ $info['fg'] }};">{{ $info['label'] }}</span>
                                <div style="font-weight:800;margin-top:3px;">${{ number_format($f->saldo_pendiente > 0 ? $f->saldo_pendiente : $f->total_factura, 0, ',', '.') }}</div>
                            </div>
                            <a href="{{ \App\Filament\Resources\RentBills\RentBillResource::getUrl('edit', ['record' => $f]) }}" class="fb-btn fb-btn-primary">Ver</a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    @else

    @php $resultados = $this->getResultados(); @endphp

    @if (trim($search) === '')
        <div class="fb-empty">
            <div class="fb-empty-icon">🔍</div>
            <div class="fb-empty-title">Busca un inquilino para empezar</div>
            <div class="fb-empty-desc">Escribe la cédula, el nombre o la dirección/código del inmueble.</div>
        </div>
    @elseif ($resultados->isEmpty())
        <div class="fb-empty">
            <div class="fb-empty-icon">🗂️</div>
            <div class="fb-empty-title">Sin resultados para "{{ $search }}"</div>
            <div class="fb-empty-desc">Revisa la cédula, el nombre o intenta con parte de la dirección del inmueble.</div>
        </div>
    @else
        <div class="fb-results">
            @foreach ($resultados as $tenant)
                <div class="fb-card">
                    <div class="fb-card-head">
                        <div class="fb-avatar">{{ strtoupper(mb_substr($tenant->nombre_completo, 0, 1)) }}</div>
                        <div>
                            <div class="fb-card-name">{{ $tenant->nombre_completo }}</div>
                            <div class="fb-card-doc">CC/NIT {{ $tenant->numero_documento ?? '—' }} · {{ $tenant->rentalContracts->count() }} inmueble(s)</div>
                        </div>
                    </div>

                    @if ($tenant->rentalContracts->isEmpty())
                        <div class="fb-tab-panel">
                            <div class="fb-empty-desc" style="text-align:left;padding:0;">Sin contratos de arriendo registrados.</div>
                        </div>
                    @else
                        <div x-data="{ tab: 0 }">
                            @if ($tenant->rentalContracts->count() > 1)
                                <div class="fb-tabs">
                                    @foreach ($tenant->rentalContracts as $i => $contract)
                                        <button
                                            type="button"
                                            @click="tab = {{ $i }}"
                                            :class="{ 'is-active': tab === {{ $i }} }"
                                            class="fb-tab"
                                        >
                                            {{ $contract->property?->codigo ?? 'Inmueble ' . ($i + 1) }}
                                        </button>
                                    @endforeach
                                </div>
                            @endif

                            @foreach ($tenant->rentalContracts as $i => $contract)
                                <div x-show="tab === {{ $i }}" x-cloak class="fb-tab-panel">
                                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:10px;">
                                        <div style="font-size:0.72rem;font-weight:700;color:#94a3b8;">
                                            {{ $contract->property?->direccion ?? 'Sin dirección' }}
                                        </div>
                                        @php $origen = $contract->property?->businessOrigin?->nombre; @endphp
                                        @if ($origen)
                                            @php $esVictoria = str_contains(strtolower($origen), 'victoria'); @endphp
                                            <span class="fb-badge" style="background:{{ $esVictoria ? '#fef3c7' : '#e0f2fe' }};color:{{ $esVictoria ? '#d97706' : '#0284c7' }};">{{ $origen }}</span>
                                        @endif
                                    </div>

                                    @php
                                        // Orden por año/mes (no periodo_inicio, que puede quedar
                                        // desalineado del mes real) — más confiable para decidir
                                        // cuál es la "próxima a pagar" y el orden del historial.
                                        $claveMes = fn ($b) => $b->anio * 100 + $b->mes;
                                        $bills = $contract->rentBills;
                                        $pendientes = $bills->whereNotIn('estado', ['pagada', 'anulada']);
                                        $proxima = $pendientes->sortByDesc($claveMes)->first();
                                        $resto = ($proxima ? $bills->reject(fn ($b) => $b->id === $proxima->id) : $bills)
                                            ->sortBy($claveMes)->values();
                                    @endphp

                                    @if ($proxima)
                                        @php $info = $estadoInfo($proxima->estado); @endphp
                                        <div class="fb-next" style="background:{{ $info['bg'] }}22;border-color:{{ $info['bg'] }};">
                                            <div class="fb-next-info">
                                                <div class="fb-next-label" style="color:{{ $info['fg'] }};">Próxima a pagar · {{ $info['label'] }}</div>
                                                <div class="fb-next-periodo">{{ $proxima->numero }} — {{ \Carbon\Carbon::create($proxima->anio, $proxima->mes, 1)->translatedFormat('F Y') }}</div>
                                                <div class="fb-next-monto" style="color:{{ $info['fg'] }};">${{ number_format($proxima->saldo_pendiente, 0, ',', '.') }}</div>
                                            </div>
                                            <div class="fb-next-actions">
                                                <a href="{{ \App\Filament\Resources\RentBills\RentBillResource::getUrl('edit', ['record' => $proxima]) }}" class="fb-btn fb-btn-primary">
                                                    Ver / Pagar
                                                </a>
                                                <button
                                                    type="button"
                                                    wire:click="mountAction('enviarLink', { record: {{ $proxima->id }} })"
                                                    class="fb-btn fb-btn-wap"
                                                >
                                                    <svg viewBox="0 0 32 32" width="14" height="14" fill="currentColor" style="flex-shrink:0;">
                                                        <path d="M16.001 2.667c-7.363 0-13.334 5.97-13.334 13.333 0 2.353.615 4.66 1.784 6.686L2.7 29.333l6.826-1.79a13.27 13.27 0 0 0 6.475 1.65h.006c7.362 0 13.333-5.97 13.333-13.333s-5.971-13.333-13.339-13.333Zm0 24.4a11.03 11.03 0 0 1-5.616-1.537l-.403-.24-4.05 1.062 1.082-3.949-.263-.406a11.03 11.03 0 0 1-1.688-5.897c0-6.099 4.964-11.062 11.062-11.062 6.1 0 11.063 4.963 11.063 11.062 0 6.1-4.963 11.063-11.063 11.063l-.124-.096Zm6.062-8.284c-.332-.166-1.965-.97-2.27-1.081-.305-.111-.527-.166-.749.166-.222.333-.86 1.081-1.054 1.303-.194.222-.388.25-.72.083-.332-.166-1.402-.517-2.67-1.649-.987-.88-1.654-1.967-1.848-2.3-.194-.332-.02-.512.146-.677.15-.15.332-.389.499-.583.166-.194.221-.333.332-.555.111-.222.055-.417-.028-.583-.083-.166-.749-1.804-1.026-2.472-.27-.65-.545-.562-.749-.572-.194-.01-.416-.012-.638-.012a1.225 1.225 0 0 0-.887.416c-.305.333-1.165 1.14-1.165 2.777 0 1.638 1.192 3.221 1.359 3.443.166.222 2.347 3.583 5.685 5.026.794.343 1.414.548 1.897.7.797.253 1.522.217 2.096.132.639-.095 1.965-.803 2.242-1.58.277-.777.277-1.443.194-1.58-.083-.138-.305-.222-.638-.388Z"/>
                                                    </svg>
                                                    Enviar link
                                                </button>
                                            </div>
                                        </div>
                                    @else
                                        <div class="fb-ok">✅ Este inmueble está al día, sin facturas pendientes.</div>
                                    @endif

                                    @if ($resto->isNotEmpty())
                                        <details class="fb-history">
                                            <summary>▸ Ver historial ({{ $resto->count() }} período{{ $resto->count() === 1 ? '' : 's' }})</summary>
                                            @foreach ($resto as $b)
                                                @php $infoB = $estadoInfo($b->estado); @endphp
                                                <div class="fb-history-row">
                                                    <div>
                                                        <div class="fb-history-periodo">{{ \Carbon\Carbon::create($b->anio, $b->mes, 1)->translatedFormat('F Y') }}</div>
                                                        <div style="color:#94a3b8;font-size:0.68rem;">{{ $b->numero }}</div>
                                                    </div>
                                                    <div style="text-align:right;">
                                                        <span class="fb-badge" style="background:{{ $infoB['bg'] }};color:{{ $infoB['fg'] }};">{{ $infoB['label'] }}</span>
                                                        <div style="font-weight:700;margin-top:2px;">${{ number_format($b->total_factura, 0, ',', '.') }}</div>
                                                    </div>
                                                    <a href="{{ \App\Filament\Resources\RentBills\RentBillResource::getUrl('edit', ['record' => $b]) }}" class="fb-btn fb-btn-outline" style="padding:5px 10px;">Ver</a>
                                                </div>
                                            @endforeach
                                        </details>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    @endif

</div>

{{-- La página sigue implementando HasTable (hereda de ListRecords), así que
     Filament asume que el componente {{ $this->table }} traerá su propio
     contenedor de modales — como no lo renderizamos, hay que ponerlo aquí a
     mano o los header actions (Generar facturas, etc.) no abren nada. --}}
<x-filament-actions::modals />
</x-filament-panels::page>
