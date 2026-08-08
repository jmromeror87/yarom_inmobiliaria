<x-filament-panels::page>
<style>
.ol-wrap { font-family:'Plus Jakarta Sans',sans-serif; }

.ol-search-bar {
    display:flex; align-items:center; gap:10px;
    background:#fff; border:1px solid rgba(226,232,240,.9); border-radius:16px;
    padding:14px 18px; box-shadow:0 2px 10px rgba(15,23,42,.05);
    margin-bottom:22px;
}
.dark .ol-search-bar { background:#1e293b; border-color:rgba(51,65,85,.8); }
.ol-search-icon { color:#94a3b8; flex-shrink:0; }
.ol-search-input {
    border:none; outline:none; background:transparent; width:100%;
    font-size:0.95rem; font-weight:600; color:#0F172A;
}
.dark .ol-search-input { color:#f1f5f9; }
.ol-search-input::placeholder { color:#94a3b8; font-weight:500; }

.ol-empty { text-align:center; padding:60px 20px; color:#94a3b8; }
.ol-empty-icon { font-size:2.5rem; margin-bottom:10px; }
.ol-empty-title { font-size:0.95rem; font-weight:700; color:#64748b; margin-bottom:4px; }
.dark .ol-empty-title { color:#94a3b8; }
.ol-empty-desc { font-size:0.8rem; }

.ol-results { display:flex; flex-direction:column; gap:16px; }

.ol-card {
    background:#fff; border-radius:18px; border:1px solid rgba(226,232,240,.9);
    box-shadow:0 2px 12px rgba(15,23,42,.05); overflow:hidden;
}
.dark .ol-card { background:#1e293b; border-color:rgba(51,65,85,.8); }

.ol-card-head { display:flex; align-items:center; gap:12px; padding:16px 18px; border-bottom:1px solid #f1f5f9; }
.dark .ol-card-head { border-color:#334155; }
.ol-avatar {
    flex-shrink:0; width:42px; height:42px; border-radius:50%;
    display:flex; align-items:center; justify-content:center;
    background:#fef3c7; color:#b45309; font-weight:800; font-size:1rem;
}
.dark .ol-avatar { background:rgba(217,119,6,.15); color:#fbbf24; }
.ol-card-name { font-size:0.95rem; font-weight:800; color:#0F172A; }
.dark .ol-card-name { color:#f1f5f9; }
.ol-card-doc { font-size:0.72rem; color:#94a3b8; font-weight:600; }

.ol-tabs { display:flex; gap:6px; flex-wrap:wrap; padding:12px 18px 0; }
.ol-tab {
    font-size:0.72rem; font-weight:700; padding:6px 13px; border-radius:20px;
    background:#f1f5f9; color:#475569; border:none; cursor:pointer;
    transition:all .15s ease;
}
.dark .ol-tab { background:#0f172a; color:#94a3b8; }
.ol-tab.is-active { background:#b45309; color:#fff; }

.ol-tab-panel { padding:16px 18px 18px; }

.ol-next {
    border-radius:14px; padding:14px 16px; margin-bottom:10px;
    border:1px solid; display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:10px;
}
.ol-next-info { min-width:180px; }
.ol-next-label { font-size:0.62rem; font-weight:800; text-transform:uppercase; letter-spacing:.05em; margin-bottom:3px; }
.ol-next-periodo { font-size:0.85rem; font-weight:800; color:#0F172A; }
.dark .ol-next-periodo { color:#f1f5f9; }
.ol-next-monto { font-size:1.1rem; font-weight:900; letter-spacing:-.01em; }
.ol-next-actions { display:flex; gap:8px; flex-wrap:wrap; }

.ol-btn {
    font-size:0.72rem; font-weight:700; padding:8px 14px; border-radius:10px;
    border:1px solid transparent; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; gap:5px;
    white-space:nowrap;
}
.ol-btn-primary { background:#0F172A; color:#fff; }
.dark .ol-btn-primary { background:#f1f5f9; color:#0F172A; }
.ol-btn-outline { background:transparent; border-color:#cbd5e1; color:#475569; }
.dark .ol-btn-outline { border-color:#475569; color:#cbd5e1; }
.ol-btn-info { background:#2563eb; color:#fff; border-color:#2563eb; }
.ol-btn-wap { background:#25D366; color:#fff; border-color:#25D366; }

.ol-ok {
    border-radius:14px; padding:12px 16px; margin-bottom:10px;
    background:#f0fdf4; border:1px solid #bbf7d0; color:#166534;
    font-size:0.8rem; font-weight:700; display:flex; align-items:center; gap:8px;
}
.dark .ol-ok { background:rgba(34,197,94,.08); border-color:rgba(34,197,94,.25); color:#4ade80; }

.ol-history { margin-top:4px; }
.ol-history summary {
    cursor:pointer; font-size:0.72rem; font-weight:700; color:#64748b;
    list-style:none; display:flex; align-items:center; gap:5px; padding:6px 2px;
}
.dark .ol-history summary { color:#94a3b8; }
.ol-history summary::-webkit-details-marker { display:none; }
.ol-history-row {
    display:flex; align-items:center; justify-content:space-between; gap:10px;
    padding:8px 4px; border-bottom:1px solid #f8fafc; font-size:0.76rem;
}
.dark .ol-history-row { border-color:#273549; }
.ol-history-row:last-child { border-bottom:none; }
.ol-history-periodo { color:#0F172A; font-weight:700; }
.dark .ol-history-periodo { color:#e2e8f0; }
.ol-badge { font-size:0.62rem; font-weight:800; padding:2px 8px; border-radius:20px; white-space:nowrap; }

.ol-filter-bar {
    display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap;
    background:#fffbeb; border:1px solid #fde68a; border-radius:14px; padding:12px 18px; margin-bottom:16px;
}
.dark .ol-filter-bar { background:rgba(217,119,6,.1); border-color:rgba(217,119,6,.3); }
.ol-filter-bar-title { font-size:0.85rem; font-weight:800; color:#92400e; }
.dark .ol-filter-bar-title { color:#fbbf24; }
.ol-filter-bar-count { font-size:0.72rem; color:#b45309; font-weight:600; }
.dark .ol-filter-bar-count { color:#fbbf24; }

.ol-flat-list { background:#fff; border-radius:18px; border:1px solid rgba(226,232,240,.9); box-shadow:0 2px 12px rgba(15,23,42,.05); overflow:hidden; }
.dark .ol-flat-list { background:#1e293b; border-color:rgba(51,65,85,.8); }
.ol-flat-row {
    display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;
    padding:14px 18px; border-bottom:1px solid #f1f5f9;
}
.dark .ol-flat-row { border-color:#334155; }
.ol-flat-row:last-child { border-bottom:none; }
.ol-flat-row-name { font-size:0.85rem; font-weight:800; color:#0F172A; }
.dark .ol-flat-row-name { color:#f1f5f9; }
.ol-flat-row-sub { font-size:0.72rem; color:#94a3b8; font-weight:600; margin-top:2px; }

@media (min-width: 900px) {
    .ol-results { display:grid; grid-template-columns:repeat(2, 1fr); align-items:start; }
}
@media (min-width: 1400px) {
    .ol-results { grid-template-columns:repeat(3, 1fr); }
}
</style>

@php
    $estadoInfo = fn (string $estado) => match ($estado) {
        'aprobada' => ['label' => 'Aprobada', 'bg' => '#dbeafe', 'fg' => '#2563eb'],
        'pagada'   => ['label' => 'Pagada',   'bg' => '#d1fae5', 'fg' => '#059669'],
        'anulada'  => ['label' => 'Anulada',  'bg' => '#f1f5f9', 'fg' => '#64748b'],
        default    => ['label' => 'Pendiente','bg' => '#fef3c7', 'fg' => '#b45309'],
    };
@endphp

<div class="ol-wrap">

    <div class="ol-search-bar">
        <span class="ol-search-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;height:20px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
        </span>
        <input
            type="text"
            wire:model.live.debounce.400ms="search"
            class="ol-search-input"
            placeholder="Buscar propietario por cédula, nombre o inmueble..."
        />
    </div>

    @if ($filtro && trim($search) === '')
        @php $liquidaciones = $this->getResultadosPorFiltro(); @endphp

        <div class="ol-filter-bar">
            <div>
                <div class="ol-filter-bar-title">🔎 {{ $this->getFiltroLabel() }}</div>
                <div class="ol-filter-bar-count">{{ $liquidaciones->count() }} liquidación{{ $liquidaciones->count() === 1 ? '' : 'es' }}{{ $liquidaciones->count() >= 100 ? ' (mostrando las primeras 100)' : '' }}</div>
            </div>
            <a href="{{ \App\Filament\Resources\OwnerLiquidations\OwnerLiquidationResource::getUrl('index') }}" class="ol-btn ol-btn-outline">✕ Quitar filtro</a>
        </div>

        @if ($liquidaciones->isEmpty())
            <div class="ol-empty">
                <div class="ol-empty-icon">✅</div>
                <div class="ol-empty-title">No hay liquidaciones en este filtro ahora mismo.</div>
            </div>
        @else
            <div class="ol-flat-list">
                @foreach ($liquidaciones as $l)
                    @php $info = $estadoInfo($l->estado); @endphp
                    <div class="ol-flat-row">
                        <div>
                            <div class="ol-flat-row-name">{{ $l->propietario?->nombre_completo ?? '—' }}</div>
                            <div class="ol-flat-row-sub">{{ $l->numero }} · {{ $l->property?->codigo ?? '—' }} · {{ $l->periodo_label }}</div>
                        </div>
                        <div style="display:flex;align-items:center;gap:12px;">
                            <div style="text-align:right;">
                                <span class="ol-badge" style="background:{{ $info['bg'] }};color:{{ $info['fg'] }};">{{ $info['label'] }}</span>
                                <div style="font-weight:800;margin-top:3px;">${{ number_format($l->total_giro, 0, ',', '.') }}</div>
                            </div>
                            <a href="{{ \App\Filament\Resources\OwnerLiquidations\OwnerLiquidationResource::getUrl('edit', ['record' => $l]) }}" class="ol-btn ol-btn-primary">Ver</a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    @else

    @php $resultados = $this->getResultados(); @endphp

    @if (trim($search) === '')
        <div class="ol-empty">
            <div class="ol-empty-icon">🔍</div>
            <div class="ol-empty-title">Busca un propietario para empezar</div>
            <div class="ol-empty-desc">Escribe la cédula, el nombre o la dirección/código del inmueble.</div>
        </div>
    @elseif ($resultados->isEmpty())
        <div class="ol-empty">
            <div class="ol-empty-icon">🗂️</div>
            <div class="ol-empty-title">Sin resultados para "{{ $search }}"</div>
            <div class="ol-empty-desc">Revisa la cédula, el nombre o intenta con parte de la dirección del inmueble.</div>
        </div>
    @else
        <div class="ol-results">
            @foreach ($resultados as $propietario)
                <div class="ol-card">
                    <div class="ol-card-head">
                        <div class="ol-avatar">{{ strtoupper(mb_substr($propietario->nombre_completo, 0, 1)) }}</div>
                        <div>
                            <div class="ol-card-name">{{ $propietario->nombre_completo }}</div>
                            <div class="ol-card-doc">CC/NIT {{ $propietario->numero_documento ?? '—' }} · {{ $propietario->properties->count() }} inmueble(s)</div>
                        </div>
                    </div>

                    @if ($propietario->properties->isEmpty())
                        <div class="ol-tab-panel">
                            <div class="ol-empty-desc" style="text-align:left;padding:0;">Sin inmuebles registrados.</div>
                        </div>
                    @else
                        <div x-data="{ tab: 0 }">
                            @if ($propietario->properties->count() > 1)
                                <div class="ol-tabs">
                                    @foreach ($propietario->properties as $i => $property)
                                        <button
                                            type="button"
                                            @click="tab = {{ $i }}"
                                            :class="{ 'is-active': tab === {{ $i }} }"
                                            class="ol-tab"
                                        >
                                            {{ $property->codigo ?? 'Inmueble ' . ($i + 1) }}
                                        </button>
                                    @endforeach
                                </div>
                            @endif

                            @foreach ($propietario->properties as $i => $property)
                                <div x-show="tab === {{ $i }}" x-cloak class="ol-tab-panel">
                                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:10px;">
                                        <div style="font-size:0.72rem;font-weight:700;color:#94a3b8;">
                                            {{ $property->direccion ?? 'Sin dirección' }}
                                        </div>
                                        @php $origen = $property->businessOrigin?->nombre; @endphp
                                        @if ($origen)
                                            @php $esVictoria = str_contains(strtolower($origen), 'victoria'); @endphp
                                            <span class="ol-badge" style="background:{{ $esVictoria ? '#fef3c7' : '#e0f2fe' }};color:{{ $esVictoria ? '#d97706' : '#0284c7' }};">{{ $origen }}</span>
                                        @endif
                                    </div>

                                    @php
                                        $claveMes = fn ($l) => $l->anio * 100 + $l->mes;
                                        $liqs = $property->ownerLiquidations;
                                        $porGirar = $liqs->whereNotIn('estado', ['pagada', 'anulada']);
                                        $proxima = $porGirar->sortByDesc($claveMes)->first();
                                        $resto = ($proxima ? $liqs->reject(fn ($l) => $l->id === $proxima->id) : $liqs)
                                            ->sortBy($claveMes)->values();
                                    @endphp

                                    @if ($proxima)
                                        @php $info = $estadoInfo($proxima->estado); @endphp
                                        <div class="ol-next" style="background:{{ $info['bg'] }}22;border-color:{{ $info['bg'] }};">
                                            <div class="ol-next-info">
                                                <div class="ol-next-label" style="color:{{ $info['fg'] }};">Próxima a girar · {{ $info['label'] }}</div>
                                                <div class="ol-next-periodo">{{ $proxima->numero }} — {{ $proxima->periodo_label }}</div>
                                                <div class="ol-next-monto" style="color:{{ $info['fg'] }};">${{ number_format($proxima->total_giro, 0, ',', '.') }}</div>
                                            </div>
                                            <div class="ol-next-actions">
                                                <a href="{{ \App\Filament\Resources\OwnerLiquidations\OwnerLiquidationResource::getUrl('edit', ['record' => $proxima]) }}" class="ol-btn ol-btn-primary">
                                                    Ver / Gestionar
                                                </a>
                                                @if ($proxima->estado === 'pendiente')
                                                    <button type="button" wire:click="mountAction('aprobar', { record: {{ $proxima->id }} })" class="ol-btn ol-btn-info">
                                                        Aprobar
                                                    </button>
                                                @elseif (in_array($proxima->estado, ['aprobada', 'pagada']))
                                                    <button type="button" wire:click="mountAction('enviarWapLiquidacion', { record: {{ $proxima->id }} })" class="ol-btn ol-btn-wap">
                                                        <svg viewBox="0 0 32 32" width="14" height="14" fill="currentColor" style="flex-shrink:0;"><path d="M16.001 2.667c-7.363 0-13.334 5.97-13.334 13.333 0 2.353.615 4.66 1.784 6.686L2.7 29.333l6.826-1.79a13.27 13.27 0 0 0 6.475 1.65h.006c7.362 0 13.333-5.97 13.333-13.333s-5.971-13.333-13.339-13.333Zm0 24.4a11.03 11.03 0 0 1-5.616-1.537l-.403-.24-4.05 1.062 1.082-3.949-.263-.406a11.03 11.03 0 0 1-1.688-5.897c0-6.099 4.964-11.062 11.062-11.062 6.1 0 11.063 4.963 11.063 11.062 0 6.1-4.963 11.063-11.063 11.063l-.124-.096Zm6.062-8.284c-.332-.166-1.965-.97-2.27-1.081-.305-.111-.527-.166-.749.166-.222.333-.86 1.081-1.054 1.303-.194.222-.388.25-.72.083-.332-.166-1.402-.517-2.67-1.649-.987-.88-1.654-1.967-1.848-2.3-.194-.332-.02-.512.146-.677.15-.15.332-.389.499-.583.166-.194.221-.333.332-.555.111-.222.055-.417-.028-.583-.083-.166-.749-1.804-1.026-2.472-.27-.65-.545-.562-.749-.572-.194-.01-.416-.012-.638-.012a1.225 1.225 0 0 0-.887.416c-.305.333-1.165 1.14-1.165 2.777 0 1.638 1.192 3.221 1.359 3.443.166.222 2.347 3.583 5.685 5.026.794.343 1.414.548 1.897.7.797.253 1.522.217 2.096.132.639-.095 1.965-.803 2.242-1.58.277-.777.277-1.443.194-1.58-.083-.138-.305-.222-.638-.388Z"/></svg>
                                                        WhatsApp
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <div class="ol-ok">✅ Este inmueble está al día, sin liquidaciones pendientes de girar.</div>
                                    @endif

                                    @if ($resto->isNotEmpty())
                                        <details class="ol-history">
                                            <summary>▸ Ver historial ({{ $resto->count() }} liquidación{{ $resto->count() === 1 ? '' : 'es' }})</summary>
                                            @foreach ($resto as $l)
                                                @php $infoB = $estadoInfo($l->estado); @endphp
                                                <div class="ol-history-row">
                                                    <div>
                                                        <div class="ol-history-periodo">{{ $l->periodo_label }}</div>
                                                        <div style="color:#94a3b8;font-size:0.68rem;">{{ $l->numero }}</div>
                                                    </div>
                                                    <div style="text-align:right;">
                                                        <span class="ol-badge" style="background:{{ $infoB['bg'] }};color:{{ $infoB['fg'] }};">{{ $infoB['label'] }}</span>
                                                        <div style="font-weight:700;margin-top:2px;">${{ number_format($l->total_giro, 0, ',', '.') }}</div>
                                                    </div>
                                                    <a href="{{ \App\Filament\Resources\OwnerLiquidations\OwnerLiquidationResource::getUrl('edit', ['record' => $l]) }}" class="ol-btn ol-btn-outline" style="padding:5px 10px;">Ver</a>
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
     mano o los header actions (Generar liquidaciones, Aprobar, etc.) no
     abren nada. --}}
<x-filament-actions::modals />
</x-filament-panels::page>
