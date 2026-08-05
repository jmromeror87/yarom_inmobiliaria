<x-filament-panels::page>

@php
    $bancos     = $this->getBancos();
    $periodos   = $this->getPeriodos();
    $banco      = $this->getBancoActual();
    $cuenta     = $this->getCuentaActual();
    $movs       = $this->getMovimientos();
    $fmt        = fn ($v) => '$' . number_format($v, 2, ',', '.');

    $saldoInicial = $this->getSaldoInicial();
    $totalDeb    = $movs->sum('debito');
    $totalCre    = $movs->sum('credito');
    $saldoFinal  = $saldoInicial + ($cuenta?->naturaleza === 'debito'
        ? ($totalDeb - $totalCre)
        : ($totalCre - $totalDeb));

    $saldoAcum   = $saldoInicial;
@endphp

<style>
    .lm-wrap { --lm-ink:#0f172a; --lm-muted:#64748b; --lm-line:#e2e8f0; }

    /* ── Tabs de banco/caja ── */
    .lm-bank-tabs { display:flex; gap:8px; margin-bottom:16px; flex-wrap:wrap; }
    .lm-bank-tab { display:flex; align-items:center; gap:10px; padding:10px 16px; border-radius:14px;
                   border:1.5px solid var(--lm-line); background:#fff; cursor:pointer; transition:.15s; flex:1; min-width:160px; }
    .lm-bank-tab:hover { border-color:#fca5b5; }
    .lm-bank-tab.active { border-color:#E11D48; background:linear-gradient(135deg,#fff5f7,#fff); box-shadow:0 2px 8px rgba(225,29,72,.12); }
    .lm-bank-icon { width:38px; height:38px; border-radius:10px; display:flex; align-items:center; justify-content:center;
                    flex-shrink:0; background:#f1f5f9; color:#64748b; }
    .lm-bank-tab.active .lm-bank-icon { background:#E11D48; color:#fff; }
    .lm-bank-icon svg { width:20px; height:20px; }
    .lm-bank-info { min-width:0; }
    .lm-bank-nombre { font-size:0.84rem; font-weight:800; color:var(--lm-ink); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .lm-bank-meta { font-size:0.7rem; color:#94a3b8; }

    /* ── Barra de filtros ── */
    .lm-filterbar { background:linear-gradient(135deg,#0f172a,#7f1d3a,#E11D48); border-radius:16px; padding:16px 18px;
                     margin-bottom:18px; display:flex; gap:12px; flex-wrap:wrap; align-items:flex-end; }
    .lm-field { display:flex; flex-direction:column; gap:5px; min-width:0; }
    .lm-field.grow { flex:1; min-width:220px; }
    .lm-field label { font-size:0.66rem; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:rgba(255,255,255,.6); }
    .lm-field select, .lm-field input[type="date"] {
        background:rgba(255,255,255,.1); border:1px solid rgba(255,255,255,.2); color:#fff;
        border-radius:10px; padding:9px 12px; font-size:0.82rem; font-weight:600; width:100%;
    }
    .lm-field select option { color:#0f172a; }
    .lm-field select:focus, .lm-field input:focus { outline:none; border-color:#fca5b5; background:rgba(255,255,255,.16); }
    .lm-sep { color:rgba(255,255,255,.4); font-size:0.7rem; font-weight:700; padding-bottom:9px; white-space:nowrap; }
    .lm-clear { background:rgba(255,255,255,.12); color:#fff; border:none; border-radius:10px; padding:9px 14px;
                font-size:0.76rem; font-weight:800; cursor:pointer; white-space:nowrap; }
    .lm-clear:hover { background:rgba(255,255,255,.2); }

    /* ── Header de cuenta ── */
    .lm-cuenta-header { background:linear-gradient(135deg,#0f172a,#E11D48); border-radius:16px; padding:20px 22px;
                         margin-bottom:16px; color:#fff; display:flex; justify-content:space-between; align-items:flex-end;
                         flex-wrap:wrap; gap:12px; }
    .lm-cuenta-kicker { font-size:0.68rem; font-weight:800; text-transform:uppercase; letter-spacing:.1em; opacity:.65; margin-bottom:6px; }
    .lm-cuenta-codigo { font-size:1.4rem; font-weight:900; font-family:monospace; }
    .lm-cuenta-nombre { font-size:1rem; font-weight:700; margin-top:2px; }
    .lm-cuenta-meta { font-size:0.74rem; opacity:.7; margin-top:3px; }
    .lm-saldo-block { text-align:right; }
    .lm-saldo-label { font-size:0.68rem; opacity:.7; text-transform:uppercase; font-weight:800; letter-spacing:.06em; }
    .lm-saldo-valor { font-size:1.5rem; font-weight:900; font-family:monospace; }

    /* ── Resumen (mini stats) ── */
    .lm-stats { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin-bottom:16px; }
    .lm-stat { background:#fff; border:1px solid var(--lm-line); border-radius:14px; padding:12px 14px; }
    .lm-stat-label { font-size:0.66rem; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:var(--lm-muted); margin-bottom:4px; }
    .lm-stat-value { font-size:1.05rem; font-weight:900; font-family:monospace; }

    .lm-deb { color:#2563eb; font-family:monospace; font-weight:600; }
    .lm-cre { color:#16a34a; font-family:monospace; font-weight:600; }
    .lm-saldo-pos { color:#16a34a; font-family:monospace; font-weight:800; }
    .lm-saldo-neg { color:#dc2626; font-family:monospace; font-weight:800; }

    .lm-empty { text-align:center; padding:56px 20px; color:#94a3b8; background:#f8fafc; border-radius:16px; }
    .lm-empty-icon { font-size:2.4rem; margin-bottom:8px; }
    .lm-empty-text { font-size:0.88rem; font-weight:700; }

    /* ── Barra saldo anterior / totales ── */
    .lm-bar { display:flex; align-items:center; justify-content:space-between; gap:10px; padding:12px 16px;
              border-radius:12px; font-size:0.78rem; font-weight:800; text-transform:uppercase; letter-spacing:.04em; margin-bottom:8px; }
    .lm-bar.anterior { background:#f8fafc; border:1px solid var(--lm-line); color:var(--lm-ink); }
    .lm-bar.totales { background:#f8fafc; border:1px solid var(--lm-line); color:var(--lm-ink); margin-top:10px; }
    .lm-bar.final { background:#0f172a; color:#fff; }
    .lm-bar-value { font-family:monospace; font-size:0.9rem; }

    /* ── Acordeón de movimientos ── */
    .lm-grid { display:grid; gap:10px; }
    .lm-mov { border:1px solid var(--lm-line); border-radius:14px; background:#fff; overflow:hidden;
              box-shadow:0 1px 2px rgba(0,0,0,.03); transition:.15s; }
    .lm-mov:hover { box-shadow:0 4px 14px rgba(0,0,0,.06); }
    .lm-mov-head { display:flex; align-items:center; gap:14px; padding:13px 16px; cursor:pointer; list-style:none; flex-wrap:wrap; }
    .lm-mov-head::-webkit-details-marker { display:none; }
    .lm-mov-head::after { content:'▾'; margin-left:auto; color:#94a3b8; font-size:0.7rem; transition:.15s; flex-shrink:0; }
    .lm-mov[open] .lm-mov-head::after { transform:rotate(180deg); }

    .lm-mov-fecha { font-family:monospace; font-weight:700; font-size:0.78rem; color:var(--lm-ink); flex-shrink:0; width:72px; }
    .lm-mov-main { min-width:0; flex:1; }
    .lm-mov-comp { font-family:monospace; font-weight:700; font-size:0.72rem; color:#2563eb; }
    .lm-mov-desc { font-size:0.82rem; color:var(--lm-ink); font-weight:600; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .lm-mov-tercero { font-size:0.72rem; color:var(--lm-muted); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }

    .lm-mov-amounts { display:flex; gap:16px; align-items:center; flex-shrink:0; }
    .lm-mov-amt { text-align:right; min-width:110px; }
    .lm-mov-amt-label { font-size:0.6rem; font-weight:800; text-transform:uppercase; color:#94a3b8; letter-spacing:.04em; }
    .lm-mov-amt-value { font-family:monospace; font-weight:700; font-size:0.84rem; }
    .lm-mov-saldo { min-width:120px; text-align:right; }

    .lm-mov-detail { padding:0 16px 14px; }
    .lm-mov-detail-title { font-size:0.66rem; font-weight:800; text-transform:uppercase; letter-spacing:.06em;
                            color:var(--lm-muted); margin:2px 0 8px; padding-top:10px; border-top:1px dashed var(--lm-line); }
    .lm-imput-table { width:100%; border-collapse:collapse; font-size:0.78rem; background:#f8fafc; border-radius:10px; overflow:hidden; }
    .lm-imput-table th { text-align:left; padding:7px 10px; font-size:0.62rem; font-weight:800; text-transform:uppercase;
                          letter-spacing:.05em; color:#94a3b8; border-bottom:1px solid var(--lm-line); }
    .lm-imput-table td { padding:7px 10px; border-bottom:1px solid #eef2f7; }
    .lm-imput-table tr:last-child td { border-bottom:none; }
    .lm-imput-table tr.this-account { background:#eff6ff; }
    .lm-imput-cuenta { font-family:monospace; font-weight:700; color:var(--lm-ink); }
    .lm-imput-nombre { font-size:0.72rem; color:var(--lm-muted); }
    .lm-imput-badge { display:inline-block; margin-left:6px; padding:1px 7px; border-radius:8px; font-size:0.6rem;
                       font-weight:800; background:#2563eb; color:#fff; vertical-align:middle; }

    /* ── Responsive ── */
    @media (max-width: 780px) {
        .lm-bank-tabs { flex-wrap:nowrap; overflow-x:auto; padding-bottom:4px; margin-left:-4px; padding-left:4px; }
        .lm-bank-tab { flex:0 0 auto; min-width:150px; }

        .lm-filterbar { padding:14px; }
        .lm-stats { grid-template-columns:1fr; }
        .lm-cuenta-header { padding:16px; }
        .lm-saldo-block { text-align:left; }

        .lm-mov-head { padding:12px 14px; }
        .lm-mov-fecha { width:auto; }
        .lm-mov-main { flex-basis:100%; order:2; }
        .lm-mov-fecha { order:1; }
        .lm-mov-amounts { order:3; flex-basis:100%; justify-content:space-between; margin-top:4px; }
        .lm-mov-amt { min-width:0; text-align:left; }
        .lm-mov-saldo { min-width:0; text-align:right; }
        .lm-mov-head::after { order:1; }

        .lm-imput-table { display:block; }
        .lm-imput-table thead { display:none; }
        .lm-imput-table tbody, .lm-imput-table tr, .lm-imput-table td { display:block; width:100%; }
        .lm-imput-table tr { padding:8px 10px; border-bottom:1px solid #e2e8f0; }
        .lm-imput-table td { padding:2px 0; border:none; }
        .lm-imput-table td[data-label]::before {
            content: attr(data-label); display:inline-block; min-width:70px; font-size:0.6rem; font-weight:800;
            text-transform:uppercase; letter-spacing:.04em; color:#94a3b8; margin-right:6px;
        }

        .lm-bar { flex-wrap:wrap; }
    }
</style>

<div class="lm-wrap">

    {{-- ── Tabs de banco/caja ── --}}
    <div class="lm-bank-tabs">
        @foreach($bancos as $b)
        <div class="lm-bank-tab {{ $this->bank_id === $b->id ? 'active' : '' }}" wire:click="setBank({{ $b->id }})">
            <div class="lm-bank-icon">
                @if($b->tipo_cuenta === 'caja')
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 3H8a2 2 0 0 0-2 2v2h12V5a2 2 0 0 0-2-2Z"/><circle cx="12" cy="14" r="2"/></svg>
                @else
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M3 10h18"/><path d="M5 6l7-3 7 3"/><path d="M4 10v11"/><path d="M20 10v11"/><path d="M8 14v3"/><path d="M12 14v3"/><path d="M16 14v3"/></svg>
                @endif
            </div>
            <div class="lm-bank-info">
                <div class="lm-bank-nombre">{{ $b->nombre }}</div>
                <div class="lm-bank-meta">{{ $b->numero_cuenta ?: ucfirst($b->tipo_cuenta) }}</div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ── Filtros ── --}}
    <div class="lm-filterbar">
        <div class="lm-field">
            <label>Período</label>
            <select wire:model.live="periodo_id">
                <option value="">— Todos —</option>
                @foreach($periodos as $id => $nombre)
                <option value="{{ $id }}" @selected($this->periodo_id == $id)>{{ $nombre }}</option>
                @endforeach
            </select>
        </div>

        <span class="lm-sep">o rango:</span>

        <div class="lm-field">
            <label>Desde</label>
            <input type="date" wire:model.live="fecha_inicio" />
        </div>

        <div class="lm-field">
            <label>Hasta</label>
            <input type="date" wire:model.live="fecha_fin" />
        </div>

        @if($fecha_inicio || $fecha_fin)
        <button type="button" class="lm-clear" wire:click="limpiarFechas">✕ Quitar rango</button>
        @endif
    </div>

    @if(!$cuenta)
    <div class="lm-empty">
        <div class="lm-empty-icon">🏦</div>
        <div class="lm-empty-text">No hay bancos/caja configurados todavía.</div>
    </div>
    @else

    {{-- ── Header de la cuenta ── --}}
    <div class="lm-cuenta-header">
        <div>
            <div class="lm-cuenta-kicker">{{ $banco->tipo_cuenta === 'caja' ? 'Caja' : 'Cuenta bancaria' }}</div>
            <div class="lm-cuenta-nombre" style="font-size:1.2rem;">{{ $banco->nombre }}</div>
            <div class="lm-cuenta-meta">
                @if($banco->numero_cuenta) N° {{ $banco->numero_cuenta }} · @endif
                PUC {{ $cuenta->codigo }} — {{ $cuenta->nombre }}
            </div>
        </div>
        <div class="lm-saldo-block">
            <div class="lm-saldo-label">Saldo final</div>
            <div class="lm-saldo-valor" style="{{ $saldoFinal >= 0 ? 'color:#86efac' : 'color:#fca5a5' }}">
                {{ $fmt(abs($saldoFinal)) }}
                {{ $saldoFinal < 0 ? '(NEG)' : '' }}
            </div>
        </div>
    </div>

    {{-- ── Mini stats ── --}}
    <div class="lm-stats">
        <div class="lm-stat">
            <div class="lm-stat-label">Saldo anterior</div>
            <div class="lm-stat-value {{ $saldoInicial >= 0 ? 'lm-saldo-pos' : 'lm-saldo-neg' }}">{{ $fmt(abs($saldoInicial)) }}</div>
        </div>
        <div class="lm-stat">
            <div class="lm-stat-label">Total débitos (entradas)</div>
            <div class="lm-stat-value lm-deb">{{ $fmt($totalDeb) }}</div>
        </div>
        <div class="lm-stat">
            <div class="lm-stat-label">Total créditos (salidas)</div>
            <div class="lm-stat-value lm-cre">{{ $fmt($totalCre) }}</div>
        </div>
    </div>

    @if($movs->isEmpty())
    <div class="lm-empty">
        <div class="lm-empty-icon">📭</div>
        <div class="lm-empty-text">Sin movimientos en este período.</div>
    </div>
    @else

    <div class="lm-bar anterior">
        <span>Saldo anterior</span>
        <span class="lm-bar-value {{ $saldoInicial >= 0 ? 'lm-saldo-pos' : 'lm-saldo-neg' }}">{{ $fmt(abs($saldoInicial)) }}</span>
    </div>

    <div class="lm-grid">
        @foreach($movs as $mov)
        @php
            if ($cuenta->naturaleza === 'debito') {
                $saldoAcum += $mov->debito - $mov->credito;
            } else {
                $saldoAcum += $mov->credito - $mov->debito;
            }
            $lineasAsiento = $mov->entry?->lines ?? collect();
        @endphp
        <div class="lm-mov">
            <details>
                <summary class="lm-mov-head">
                    <div class="lm-mov-fecha">{{ $mov->entry?->fecha?->format('d/m/Y') }}</div>
                    <div class="lm-mov-main">
                        <div class="lm-mov-comp">{{ $mov->entry?->numero }}</div>
                        <div class="lm-mov-desc" title="{{ $mov->descripcion ?: $mov->entry?->descripcion }}">
                            {{ $mov->descripcion ?: $mov->entry?->descripcion }}
                        </div>
                        <div class="lm-mov-tercero">{{ $mov->third?->nombre_completo ?? '—' }}</div>
                    </div>
                    <div class="lm-mov-amounts">
                        <div class="lm-mov-amt">
                            <div class="lm-mov-amt-label">Débito</div>
                            <div class="lm-mov-amt-value lm-deb">{{ $mov->debito > 0 ? $fmt($mov->debito) : '—' }}</div>
                        </div>
                        <div class="lm-mov-amt">
                            <div class="lm-mov-amt-label">Crédito</div>
                            <div class="lm-mov-amt-value lm-cre">{{ $mov->credito > 0 ? $fmt($mov->credito) : '—' }}</div>
                        </div>
                        <div class="lm-mov-amt lm-mov-saldo">
                            <div class="lm-mov-amt-label">Saldo</div>
                            <div class="lm-mov-amt-value {{ $saldoAcum >= 0 ? 'lm-saldo-pos' : 'lm-saldo-neg' }}">{{ $fmt(abs($saldoAcum)) }}</div>
                        </div>
                    </div>
                </summary>

                <div class="lm-mov-detail">
                    <div class="lm-mov-detail-title">Imputación completa del comprobante {{ $mov->entry?->numero }}</div>
                    <table class="lm-imput-table">
                        <thead>
                            <tr>
                                <th>Cuenta</th>
                                <th>Tercero</th>
                                <th style="text-align:right;">Débito</th>
                                <th style="text-align:right;">Crédito</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lineasAsiento as $linea)
                            <tr class="{{ $linea->account_id === $mov->account_id ? 'this-account' : '' }}">
                                <td data-label="Cuenta">
                                    <span class="lm-imput-cuenta">{{ $linea->account?->codigo }}</span>
                                    <div class="lm-imput-nombre">
                                        {{ $linea->account?->nombre }}
                                        @if($linea->account_id === $mov->account_id)
                                            <span class="lm-imput-badge">esta cuenta</span>
                                        @endif
                                    </div>
                                    @if($linea->descripcion)
                                    <div class="lm-imput-nombre">{{ $linea->descripcion }}</div>
                                    @endif
                                </td>
                                <td data-label="Tercero" class="lm-mov-tercero">{{ $linea->third?->nombre_completo ?? '—' }}</td>
                                <td data-label="Débito" class="lm-deb" style="text-align:right;">{{ $linea->debito > 0 ? $fmt($linea->debito) : '—' }}</td>
                                <td data-label="Crédito" class="lm-cre" style="text-align:right;">{{ $linea->credito > 0 ? $fmt($linea->credito) : '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </details>
        </div>
        @endforeach
    </div>

    {{-- Totales del período (solo los movimientos listados, sin saldo anterior) --}}
    @php
        $netoPeriodo = $cuenta->naturaleza === 'debito'
            ? ($totalDeb - $totalCre)
            : ($totalCre - $totalDeb);
    @endphp
    <div class="lm-bar totales">
        <span>Totales del período — Débito {{ $fmt($totalDeb) }} · Crédito {{ $fmt($totalCre) }}</span>
        <span class="lm-bar-value {{ $netoPeriodo >= 0 ? 'lm-saldo-pos' : 'lm-saldo-neg' }}">{{ $fmt(abs($netoPeriodo)) }}</span>
    </div>
    <div class="lm-bar final">
        <span>Nuevo saldo</span>
        <span class="lm-bar-value" style="color:{{ $saldoFinal >= 0 ? '#86efac' : '#fca5a5' }};">{{ $fmt(abs($saldoFinal)) }}</span>
    </div>
    @endif
    @endif
</div>

</x-filament-panels::page>
