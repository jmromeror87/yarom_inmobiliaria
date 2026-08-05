<x-filament-panels::page>

@php
    $entries  = $this->getEntries();
    $periodos = $this->getPeriodos();
    $totales  = $this->getTotales();
    $totalDeb = $totales['debitos'];
    $totalCre = $totales['creditos'];
    $fmt = fn($v) => '$' . number_format($v, 2, ',', '.');
    $tipoLabels = [
        'CC'=>'Cont.','CI'=>'Ingreso','CE'=>'Egreso','ND'=>'N.Déb','NC'=>'N.Cre','CA'=>'Ajuste',
    ];
    $tipoColors = [
        'CC'=>'#2563eb','CI'=>'#16a34a','CE'=>'#dc2626','ND'=>'#d97706','NC'=>'#7c3aed','CA'=>'#64748b',
    ];
    $diff = abs($totalDeb - $totalCre);
    $cuadrado = $diff < 0.01;
@endphp

<style>
    .ld-wrap { --ld-ink:#0f172a; --ld-muted:#64748b; --ld-line:#e2e8f0; }

    /* ── Buscador ── */
    .ld-search-wrap{position:relative;max-width:520px;margin-bottom:14px;}
    .ld-search-field{display:flex;align-items:center;gap:9px;background:#f8fafc;border:1.5px solid var(--ld-line);
                      border-radius:11px;padding:0 14px;transition:border-color .15s,background .15s;}
    .ld-search-field:focus-within{background:#fff;border-color:#E11D48;box-shadow:0 0 0 3px rgba(225,29,72,.08);}
    .ld-search-icon{width:16px;height:16px;color:#94a3b8;flex-shrink:0;}
    .ld-search-field:focus-within .ld-search-icon{color:#E11D48;}
    .ld-search-input{flex:1;background:transparent;border:none;outline:none;box-shadow:none;font-size:13.5px;
                      font-weight:500;color:#0f172a;padding:10px 0;width:100%;}
    .ld-search-input::placeholder{color:#94a3b8;font-weight:500;}
    .ld-search-clear{width:20px;height:20px;border-radius:50%;background:#e2e8f0;color:#64748b;display:flex;
                      align-items:center;justify-content:center;cursor:pointer;flex-shrink:0;font-size:12px;line-height:1;border:none;}
    .ld-search-clear:hover{background:#cbd5e1;color:#0f172a;}

    /* ── Barra de filtros (mismo estilo que Libro Auxiliar) ── */
    .ld-filterbar { background:linear-gradient(135deg,#0f172a,#7f1d3a,#E11D48); border-radius:16px; padding:16px 18px;
                    margin-bottom:18px; display:flex; gap:12px; flex-wrap:wrap; align-items:flex-end; }
    .ld-field { display:flex; flex-direction:column; gap:5px; min-width:0; }
    .ld-field label { font-size:0.66rem; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:rgba(255,255,255,.6); }
    .ld-field select, .ld-field input[type="date"] {
        background:rgba(255,255,255,.1); border:1px solid rgba(255,255,255,.2); color:#fff;
        border-radius:10px; padding:9px 12px; font-size:0.82rem; font-weight:600; width:100%;
    }
    .ld-field select option { color:#0f172a; }
    .ld-field select:focus, .ld-field input:focus { outline:none; border-color:#fca5b5; background:rgba(255,255,255,.16); }
    .ld-sep { color:rgba(255,255,255,.4); font-size:0.7rem; font-weight:700; padding-bottom:9px; white-space:nowrap; }
    .ld-clear { background:rgba(255,255,255,.12); color:#fff; border:none; border-radius:10px; padding:9px 14px;
                font-size:0.76rem; font-weight:800; cursor:pointer; white-space:nowrap; }
    .ld-clear:hover { background:rgba(255,255,255,.2); }
    .ld-count { color:rgba(255,255,255,.75); font-size:0.74rem; font-weight:700; padding-bottom:9px; white-space:nowrap; margin-left:auto; }

    /* ── Mini stats ── */
    .ld-stats { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin-bottom:16px; }
    .ld-stat { background:#fff; border:1px solid var(--ld-line); border-radius:14px; padding:12px 14px; }
    .ld-stat-label { font-size:0.66rem; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:var(--ld-muted); margin-bottom:4px; }
    .ld-stat-value { font-size:1.05rem; font-weight:900; font-family:monospace; }
    .ld-deb { color:#2563eb; font-family:monospace; font-weight:600; }
    .ld-cre { color:#16a34a; font-family:monospace; font-weight:600; }

    .ld-empty { text-align:center; padding:56px 20px; color:#94a3b8; background:#f8fafc; border-radius:16px; }
    .ld-empty-icon { font-size:2.4rem; margin-bottom:8px; }
    .ld-empty-text { font-size:0.88rem; font-weight:700; }

    /* ── Acordeón de comprobantes ── */
    .ld-grid { display:grid; gap:10px; margin-bottom:14px; }
    .ld-entry { border:1px solid var(--ld-line); border-radius:14px; background:#fff; overflow:hidden;
                box-shadow:0 1px 2px rgba(0,0,0,.03); transition:.15s; }
    .ld-entry:hover { box-shadow:0 4px 14px rgba(0,0,0,.06); }
    .ld-entry-head { display:flex; align-items:center; gap:14px; padding:13px 16px; cursor:pointer; list-style:none; flex-wrap:wrap; }
    .ld-entry-head::-webkit-details-marker { display:none; }
    .ld-entry-head::after { content:'▾'; margin-left:auto; color:#94a3b8; font-size:0.7rem; transition:.15s; flex-shrink:0; }
    .ld-entry[open] .ld-entry-head::after { transform:rotate(180deg); }

    .ld-entry-fecha { font-family:monospace; font-weight:700; font-size:0.78rem; color:var(--ld-ink); flex-shrink:0; width:72px; }
    .ld-entry-main { min-width:0; flex:1; }
    .ld-entry-comp-row { display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
    .ld-entry-comp { font-family:monospace; font-weight:700; font-size:0.72rem; color:#2563eb; }
    .ld-badge { display:inline-block; padding:2px 9px; border-radius:99px; font-size:0.62rem; font-weight:800; }
    .ld-entry-desc { font-size:0.82rem; color:var(--ld-ink); font-weight:600; overflow:hidden; text-overflow:ellipsis;
                      white-space:nowrap; margin-top:2px; }

    .ld-entry-amounts { display:flex; gap:16px; align-items:center; flex-shrink:0; }
    .ld-entry-amt { text-align:right; min-width:110px; }
    .ld-entry-amt-label { font-size:0.6rem; font-weight:800; text-transform:uppercase; color:#94a3b8; letter-spacing:.04em; }
    .ld-entry-amt-value { font-family:monospace; font-weight:700; font-size:0.84rem; }

    .ld-entry-detail { padding:0 16px 14px; }
    .ld-entry-detail-title { font-size:0.66rem; font-weight:800; text-transform:uppercase; letter-spacing:.06em;
                              color:var(--ld-muted); margin:2px 0 8px; padding-top:10px; border-top:1px dashed var(--ld-line); }
    .ld-imput-table { width:100%; border-collapse:collapse; font-size:0.78rem; background:#f8fafc; border-radius:10px; overflow:hidden; }
    .ld-imput-table th { text-align:left; padding:7px 10px; font-size:0.62rem; font-weight:800; text-transform:uppercase;
                          letter-spacing:.05em; color:#94a3b8; border-bottom:1px solid var(--ld-line); }
    .ld-imput-table td { padding:7px 10px; border-bottom:1px solid #eef2f7; }
    .ld-imput-table tr:last-child td { border-bottom:none; }
    .ld-imput-cuenta { font-family:monospace; font-weight:700; color:var(--ld-ink); }
    .ld-imput-nombre { font-size:0.72rem; color:var(--ld-muted); }
    .ld-imput-tercero { font-size:0.72rem; color:var(--ld-muted); }

    /* ── Barra de totales ── */
    .ld-bar { display:flex; align-items:center; justify-content:space-between; gap:10px; padding:12px 16px;
              border-radius:12px; font-size:0.78rem; font-weight:800; text-transform:uppercase; letter-spacing:.04em; margin-bottom:8px; }
    .ld-bar.totales { background:#f8fafc; border:1px solid var(--ld-line); color:var(--ld-ink); }
    .ld-bar-value { font-family:monospace; font-size:0.9rem; }
    .ld-cuadre { text-align:center; padding:12px; border-radius:12px; font-weight:900; font-size:0.85rem; }
    .ld-cuadre.ok { background:#f0fdf4; color:#15803d; }
    .ld-cuadre.no { background:#fef2f2; color:#dc2626; }

    /* ── Paginación (partial compartido, clases .acc-pagination) ── */
    .ld-pagination { display:flex; justify-content:flex-end; align-items:center; gap:4px; margin:12px 0; flex-wrap:wrap; }
    .acc-pagination{display:flex;justify-content:flex-end;align-items:center;gap:4px;flex-wrap:wrap;}
    .acc-pagination a,.acc-pagination span{display:inline-flex;align-items:center;justify-content:center;min-width:32px;height:32px;padding:0 8px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;}
    .acc-pagination a{color:#334155;background:#fff;border:1px solid #e2e8f0;}
    .acc-pagination a:hover{background:#f1f5f9;}
    .acc-pagination span.acc-page-current{background:#0f172a;color:#fff;}
    .acc-pagination span.acc-page-disabled{color:#cbd5e1;background:#f8fafc;border:1px solid #f1f5f9;}
    .acc-pagination .acc-page-info{font-size:12px;color:#94a3b8;margin-left:8px;white-space:nowrap;}

    /* ── Responsive ── */
    @media (max-width: 780px) {
        .ld-filterbar { padding:14px; }
        .ld-stats { grid-template-columns:1fr; }
        .ld-count { margin-left:0; }

        .ld-entry-head { padding:12px 14px; }
        .ld-entry-fecha { width:auto; }
        .ld-entry-main { flex-basis:100%; order:2; }
        .ld-entry-fecha { order:1; }
        .ld-entry-amounts { order:3; flex-basis:100%; justify-content:space-between; margin-top:4px; }
        .ld-entry-amt { min-width:0; text-align:left; }

        .ld-imput-table { display:block; }
        .ld-imput-table thead { display:none; }
        .ld-imput-table tbody, .ld-imput-table tr, .ld-imput-table td { display:block; width:100%; }
        .ld-imput-table tr { padding:8px 10px; border-bottom:1px solid #e2e8f0; }
        .ld-imput-table td { padding:2px 0; border:none; }
        .ld-imput-table td[data-label]::before {
            content: attr(data-label); display:inline-block; min-width:70px; font-size:0.6rem; font-weight:800;
            text-transform:uppercase; letter-spacing:.04em; color:#94a3b8; margin-right:6px;
        }

        .ld-bar { flex-wrap:wrap; }
        .ld-pagination { justify-content:center; }
    }
</style>

<div class="ld-wrap">

    {{-- ── Buscador ── --}}
    <div class="ld-search-wrap">
        <div class="ld-search-field">
            <svg class="ld-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            <input
                type="search"
                wire:model.live.debounce.400ms="buscar"
                placeholder="Buscar por N° comprobante, descripción, tercero o cuenta…"
                class="ld-search-input"
            />
            @if($buscar)
            <button type="button" class="ld-search-clear" wire:click="limpiarBusqueda" title="Limpiar búsqueda">✕</button>
            @endif
        </div>
    </div>

    {{-- ── Filtros ── --}}
    <div class="ld-filterbar">
        <div class="ld-field">
            <label>Período</label>
            <select wire:model.live="periodo_id">
                <option value="">— Todos —</option>
                @foreach($periodos as $id => $nombre)
                <option value="{{ $id }}" @selected($this->periodo_id == $id)>{{ $nombre }}</option>
                @endforeach
            </select>
        </div>

        <span class="ld-sep">o rango:</span>

        <div class="ld-field">
            <label>Desde</label>
            <input type="date" wire:model.live="fecha_inicio" />
        </div>

        <div class="ld-field">
            <label>Hasta</label>
            <input type="date" wire:model.live="fecha_fin" />
        </div>

        @if($fecha_inicio || $fecha_fin)
        <button type="button" class="ld-clear" wire:click="limpiarFechas">✕ Quitar rango</button>
        @endif

        <div class="ld-field">
            <label>Por página</label>
            <select wire:model.live="perPage">
                @foreach([25, 50, 100, 200] as $n)
                <option value="{{ $n }}" @selected($this->perPage == $n)>{{ $n }}</option>
                @endforeach
            </select>
        </div>

        <span class="ld-count">{{ $totales['count'] }} comprobantes contabilizados</span>
    </div>

    @if($entries->isEmpty())
    <div class="ld-empty">
        <div class="ld-empty-icon">📖</div>
        <div class="ld-empty-text">No hay comprobantes contabilizados en este período.</div>
    </div>
    @else

    {{-- ── Mini stats ── --}}
    <div class="ld-stats">
        <div class="ld-stat">
            <div class="ld-stat-label">Comprobantes</div>
            <div class="ld-stat-value">{{ $totales['count'] }}</div>
        </div>
        <div class="ld-stat">
            <div class="ld-stat-label">Total débitos</div>
            <div class="ld-stat-value ld-deb">{{ $fmt($totalDeb) }}</div>
        </div>
        <div class="ld-stat">
            <div class="ld-stat-label">Total créditos</div>
            <div class="ld-stat-value ld-cre">{{ $fmt($totalCre) }}</div>
        </div>
    </div>

    {{-- Paginación (arriba) --}}
    <div class="ld-pagination">@include('filament.accounting.partials.paginacion')</div>

    {{-- ── Acordeón de comprobantes ── --}}
    <div class="ld-grid">
        @foreach($entries as $entry)
        <div class="ld-entry">
            <details>
                <summary class="ld-entry-head">
                    <div class="ld-entry-fecha">{{ $entry->fecha->format('d/m/Y') }}</div>
                    <div class="ld-entry-main">
                        <div class="ld-entry-comp-row">
                            <span class="ld-entry-comp">{{ $entry->numero }}</span>
                            <span class="ld-badge" style="background:{{ $tipoColors[$entry->tipo] ?? '#64748b' }}22;color:{{ $tipoColors[$entry->tipo] ?? '#64748b' }};">
                                {{ $tipoLabels[$entry->tipo] ?? $entry->tipo }}
                            </span>
                        </div>
                        <div class="ld-entry-desc" title="{{ $entry->descripcion }}">{{ $entry->descripcion }}</div>
                    </div>
                    <div class="ld-entry-amounts">
                        <div class="ld-entry-amt">
                            <div class="ld-entry-amt-label">Débito</div>
                            <div class="ld-entry-amt-value ld-deb">{{ $fmt($entry->total_debitos) }}</div>
                        </div>
                        <div class="ld-entry-amt">
                            <div class="ld-entry-amt-label">Crédito</div>
                            <div class="ld-entry-amt-value ld-cre">{{ $fmt($entry->total_creditos) }}</div>
                        </div>
                    </div>
                </summary>

                <div class="ld-entry-detail">
                    <div class="ld-entry-detail-title">Imputación completa — cuentas afectadas al debe y al haber</div>
                    <table class="ld-imput-table">
                        <thead>
                            <tr>
                                <th>Cuenta</th>
                                <th>Tercero</th>
                                <th style="text-align:right;">Débito</th>
                                <th style="text-align:right;">Crédito</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($entry->lines as $line)
                            <tr>
                                <td data-label="Cuenta">
                                    <span class="ld-imput-cuenta">{{ $line->account?->codigo }}</span>
                                    <div class="ld-imput-nombre">{{ $line->account?->nombre }}</div>
                                    @if($line->descripcion)
                                    <div class="ld-imput-nombre" style="font-style:italic;">{{ $line->descripcion }}</div>
                                    @endif
                                </td>
                                <td data-label="Tercero" class="ld-imput-tercero">{{ $line->third?->nombre_completo ?? '—' }}</td>
                                <td data-label="Débito" class="ld-deb" style="text-align:right;">{{ $line->debito > 0 ? $fmt($line->debito) : '—' }}</td>
                                <td data-label="Crédito" class="ld-cre" style="text-align:right;">{{ $line->credito > 0 ? $fmt($line->credito) : '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </details>
        </div>
        @endforeach
    </div>

    {{-- Totales del período completo (no solo la página actual) --}}
    <div class="ld-bar totales">
        <span>Totales del período</span>
        <span class="ld-bar-value">Débito {{ $fmt($totalDeb) }} · Crédito {{ $fmt($totalCre) }}</span>
    </div>
    <div class="ld-cuadre {{ $cuadrado ? 'ok' : 'no' }}">
        {{ $cuadrado ? '✅ Libro cuadrado — Débitos = Créditos' : '❌ DESCUADRE: ' . $fmt($diff) }}
    </div>

    {{-- Paginación (abajo) --}}
    <div class="ld-pagination" style="margin-top:14px;">@include('filament.accounting.partials.paginacion')</div>
    @endif
</div>

</x-filament-panels::page>
