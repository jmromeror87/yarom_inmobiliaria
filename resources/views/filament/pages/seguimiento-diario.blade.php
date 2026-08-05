<x-filament-panels::page>
    <style>
        .sd-wrap { --sd-ink:#0f172a; --sd-muted:#64748b; --sd-line:#e2e8f0; }

        /* ── Navegador de fecha ── */
        .sd-datebar { display:flex; align-items:center; gap:10px; background:linear-gradient(135deg,#0f172a,#1e3a5f);
                      border-radius:16px; padding:14px 18px; margin-bottom:18px; flex-wrap:wrap; }
        .sd-nav-btn { width:36px; height:36px; border-radius:10px; border:none; background:rgba(255,255,255,.12);
                      color:#fff; display:flex; align-items:center; justify-content:center; cursor:pointer; transition:.15s; flex-shrink:0; }
        .sd-nav-btn:hover { background:rgba(255,255,255,.22); }
        .sd-nav-btn:disabled { opacity:.3; cursor:not-allowed; }
        .sd-nav-btn svg { width:18px; height:18px; }
        .sd-date-center { flex:1; text-align:center; min-width:180px; }
        .sd-date-label { color:#fff; font-weight:800; font-size:0.95rem; }
        .sd-date-badge { display:inline-block; margin-top:2px; padding:2px 10px; border-radius:20px;
                          font-size:0.66rem; font-weight:700; text-transform:uppercase; letter-spacing:.04em; }
        .sd-date-badge.hoy { background:#22c55e; color:#04240f; }
        .sd-date-badge.pasado { background:rgba(255,255,255,.15); color:#cbd5e1; }
        .sd-date-input { background:rgba(255,255,255,.1); border:1px solid rgba(255,255,255,.2); color:#fff;
                          border-radius:9px; padding:6px 10px; font-size:0.78rem; }
        .sd-hoy-btn { background:#fff; color:#0f172a; border:none; border-radius:9px; padding:8px 14px;
                      font-size:0.78rem; font-weight:800; cursor:pointer; flex-shrink:0; }
        .sd-hoy-btn:hover { opacity:.9; }

        /* ── Tabs ── */
        .sd-tabs { display:flex; gap:6px; margin-bottom:16px; background:#f1f5f9; border-radius:12px; padding:4px; }
        .sd-tab { flex:1; text-align:center; padding:10px 14px; font-size:0.85rem; font-weight:700; color:#64748b;
                  cursor:pointer; border-radius:9px; transition:.15s; }
        .sd-tab.active { color:#0f172a; background:#fff; box-shadow:0 1px 4px rgba(0,0,0,.08); }

        .sd-search { position:relative; margin-bottom:16px; }
        .sd-search-icon { position:absolute; left:14px; top:50%; transform:translateY(-50%);
                           width:18px; height:18px; color:#94a3b8; pointer-events:none; }
        .sd-search-input { width:100%; padding:12px 40px 12px 42px; border:1.5px solid var(--sd-line);
                            border-radius:12px; font-size:0.86rem; color:var(--sd-ink); background:#fff;
                            transition:.15s; }
        .sd-search-input:focus { outline:none; border-color:#2563eb; box-shadow:0 0 0 3px rgba(37,99,235,.12); }
        .sd-search-input::placeholder { color:#94a3b8; }
        .sd-search-clear { position:absolute; right:10px; top:50%; transform:translateY(-50%);
                            width:24px; height:24px; border:none; border-radius:8px; background:#f1f5f9;
                            color:#64748b; font-size:0.75rem; cursor:pointer; display:flex; align-items:center; justify-content:center; }
        .sd-search-clear:hover { background:#e2e8f0; color:#0f172a; }

        .sd-alert { display:flex; gap:10px; align-items:center; background:#f0fdf4; border:1px solid #bbf7d0;
                    border-radius:10px; padding:10px 14px; margin-bottom:8px; font-size:0.82rem; color:#166534; }
        .sd-alert.giro { background:#eff6ff; border-color:#bfdbfe; color:#1e40af; }

        .sd-recien { border:1px solid #bbf7d0; background:#f0fdf4; border-radius:12px; margin-bottom:16px; overflow:hidden; }
        .sd-recien-summary { list-style:none; cursor:pointer; padding:12px 16px; font-size:0.84rem; color:#166534;
                              display:flex; align-items:center; gap:6px; flex-wrap:wrap; }
        .sd-recien-summary::-webkit-details-marker { display:none; }
        .sd-recien-toggle { margin-left:auto; font-size:0.72rem; font-weight:700; color:#15803d; opacity:.8; }
        .sd-recien[open] .sd-recien-toggle { display:none; }
        .sd-recien-list { max-height:320px; overflow-y:auto; padding:0 12px 12px; border-top:1px solid #bbf7d0; }
        .sd-recien-list .sd-alert { margin-top:8px; margin-bottom:0; }

        /* ── Cards ── */
        .sd-grid { display:grid; gap:12px; }
        .sd-card { border:1px solid var(--sd-line); border-radius:16px; background:#fff; overflow:hidden;
                    transition:.15s; box-shadow:0 1px 2px rgba(0,0,0,.03); }
        .sd-card:hover { box-shadow:0 4px 14px rgba(0,0,0,.06); }
        .sd-card.revisado { background:#f8fafc; }
        .sd-card.revisado .sd-avatar { filter:grayscale(.6); opacity:.7; }
        .sd-card.pagado { border-color:#86efac; background:#f0fdf4; }

        .sd-card-head { display:flex; align-items:center; gap:14px; padding:16px; cursor:pointer; list-style:none; }
        .sd-card-head::-webkit-details-marker { display:none; }
        .sd-avatar { width:44px; height:44px; border-radius:12px; flex-shrink:0; display:flex; align-items:center;
                     justify-content:center; font-weight:800; font-size:0.95rem; color:#fff;
                     background:linear-gradient(135deg,#1e3a5f,#2563eb); }
        .sd-avatar.ok { background:linear-gradient(135deg,#15803d,#22c55e); }

        .sd-id-block { min-width:0; flex-shrink:0; }
        .sd-nombre { font-weight:800; color:var(--sd-ink); font-size:0.92rem; line-height:1.3;
                     overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:220px; }
        .sd-doc { font-size:0.7rem; color:#94a3b8; }
        .sd-dia-fijo { font-size:0.68rem; color:#94a3b8; margin-top:1px; }

        .sd-badges { display:flex; gap:6px; margin-left:auto; align-items:center; flex-wrap:wrap; justify-content:flex-end; }
        .sd-badge { padding:4px 10px; border-radius:20px; font-size:0.7rem; font-weight:700; white-space:nowrap; }
        .sd-badge.red { background:#fee2e2; color:#991b1b; }
        .sd-badge.green { background:#dcfce7; color:#166534; }
        .sd-badge.amber { background:#fef9c3; color:#854d0e; }
        .sd-badge.gray { background:#f1f5f9; color:#475569; }
        .sd-badge.pagado { background:#16a34a; color:#fff; }
        .sd-total { font-size:0.92rem; font-weight:900; color:var(--sd-ink); }

        .sd-check-btn { border:none; border-radius:10px; padding:9px 14px; font-size:0.74rem; font-weight:800;
                        cursor:pointer; display:flex; align-items:center; gap:6px; transition:.15s; flex-shrink:0; white-space:nowrap; }
        .sd-check-btn.off { background:#0f172a; color:#fff; }
        .sd-check-btn.off:hover { background:#1e293b; transform:translateY(-1px); }
        .sd-check-btn.on { background:#dcfce7; color:#166534; border:1.5px solid #86efac; }
        .sd-revisado-info { font-size:0.68rem; color:#94a3b8; padding:0 16px 10px; margin-top:-6px; }

        .sd-detail { padding:2px 16px 16px; }
        .sd-mora-total { font-size:0.78rem; color:#64748b; margin-bottom:8px; }
        .sd-inm-row { display:flex; justify-content:space-between; align-items:center; gap:10px; padding:10px 12px;
                      border-radius:10px; background:#f8fafc; margin-bottom:6px; font-size:0.8rem; }
        .sd-inm-dir { font-weight:700; color:var(--sd-ink); }
        .sd-mini-badge { display:inline-block; margin-left:6px; padding:1px 7px; border-radius:8px;
                          font-size:0.62rem; font-weight:700; vertical-align:middle; }
        .sd-mini-badge.ok { background:#dcfce7; color:#166534; }
        .sd-mini-badge.no { background:#fee2e2; color:#991b1b; }
        .sd-mini-badge.gracia { background:#fef9c3; color:#854d0e; }
        .sd-inm-meta { color:#64748b; font-size:0.71rem; margin-top:1px; }
        .sd-inm-valor { font-weight:800; color:var(--sd-ink); text-align:right; flex-shrink:0; }

        .sd-empty { text-align:center; padding:60px 20px; color:#94a3b8; }
        .sd-empty-icon { font-size:2.5rem; margin-bottom:10px; }
        .sd-empty-text { font-size:0.9rem; font-weight:600; }

        /* ── Comprobantes (ingresos/egresos) ── */
        .sd-comp-head { display:flex; align-items:center; gap:14px; padding:16px; cursor:pointer; list-style:none; }
        .sd-comp-head::-webkit-details-marker { display:none; }
        .sd-comp-avatar { width:44px; height:44px; border-radius:12px; flex-shrink:0; display:flex; align-items:center;
                           justify-content:center; font-weight:900; font-size:0.72rem; color:#fff; }
        .sd-comp-avatar.ci { background:linear-gradient(135deg,#15803d,#22c55e); }
        .sd-comp-avatar.ce { background:linear-gradient(135deg,#991b1b,#dc2626); }
        .sd-comp-id-block { min-width:0; flex-shrink:1; }
        .sd-comp-numero { font-family:monospace; font-weight:800; color:var(--sd-ink); font-size:0.86rem; }
        .sd-comp-desc { font-size:0.8rem; color:#475569; margin-top:1px; overflow:hidden; text-overflow:ellipsis;
                        white-space:nowrap; max-width:320px; }
        .sd-comp-tercero { font-size:0.7rem; color:#94a3b8; margin-top:1px; }
        .sd-comp-amounts { margin-left:auto; display:flex; gap:14px; align-items:center; flex-wrap:wrap; justify-content:flex-end; }
        .sd-comp-amt-label { font-size:0.6rem; font-weight:800; text-transform:uppercase; color:#94a3b8; letter-spacing:.04em; }
        .sd-comp-amt-value { font-family:monospace; font-weight:800; font-size:0.86rem; }
        .sd-imput-table { width:100%; border-collapse:collapse; font-size:0.78rem; background:#f8fafc; border-radius:10px;
                           overflow:hidden; margin-top:4px; }
        .sd-imput-table th { text-align:left; padding:7px 10px; font-size:0.62rem; font-weight:800; text-transform:uppercase;
                              letter-spacing:.05em; color:#94a3b8; border-bottom:1px solid var(--sd-line); }
        .sd-imput-table td { padding:7px 10px; border-bottom:1px solid #eef2f7; vertical-align:top; }
        .sd-imput-table tr:last-child td { border-bottom:none; }
        .sd-imput-cuenta { font-family:monospace; font-weight:700; color:var(--sd-ink); }
        .sd-imput-nombre { font-size:0.72rem; color:#64748b; }

        /* ── Responsive ── */
        @media (max-width: 640px) {
            .sd-datebar { padding:12px; }
            .sd-card-head { flex-wrap:wrap; }
            .sd-badges { width:100%; justify-content:flex-start; margin-left:0; margin-top:6px; }
            .sd-nombre { max-width:150px; }
            .sd-inm-row { flex-direction:column; align-items:flex-start; }
            .sd-inm-valor { align-self:flex-end; }

            .sd-comp-head { flex-wrap:wrap; padding:12px 14px; }
            .sd-comp-id-block { flex-basis:calc(100% - 60px); max-width:none; }
            .sd-comp-desc, .sd-comp-numero { max-width:none; }
            .sd-comp-amounts { width:100%; justify-content:space-between; margin-left:0; margin-top:8px; }
            .sd-imput-table { display:block; }
            .sd-imput-table thead { display:none; }
            .sd-imput-table tbody, .sd-imput-table tr, .sd-imput-table td { display:block; width:100%; }
            .sd-imput-table tr { padding:8px 10px; border-bottom:1px solid #e2e8f0; }
            .sd-imput-table td { padding:2px 0; border:none; }
            .sd-imput-table td[data-label]::before {
                content: attr(data-label); display:inline-block; min-width:70px; font-size:0.6rem; font-weight:800;
                text-transform:uppercase; letter-spacing:.04em; color:#94a3b8; margin-right:6px;
            }
        }
    </style>

    <div class="sd-wrap" wire:loading.class="opacity-60">

        {{-- ── Navegador de fecha ── --}}
        <div class="sd-datebar">
            <button type="button" class="sd-nav-btn" wire:click="diaAnterior" title="Día anterior">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
            </button>

            <div class="sd-date-center">
                <div class="sd-date-label">{{ $this->fechaLabel }}</div>
                <span class="sd-date-badge {{ $this->esHoy() ? 'hoy' : 'pasado' }}">
                    {{ $this->esHoy() ? '● Hoy — en vivo' : '📋 Planilla guardada' }}
                </span>
            </div>

            <button type="button" class="sd-nav-btn" wire:click="diaSiguiente" @if($this->esHoy()) disabled @endif title="Día siguiente">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
            </button>

            <input type="date" wire:model.live="fecha" max="{{ now()->toDateString() }}" class="sd-date-input">

            @unless($this->esHoy())
                <button type="button" class="sd-hoy-btn" wire:click="irAHoy">Volver a hoy</button>
            @endunless
        </div>

        {{-- ── Tabs ── --}}
        <div class="sd-tabs">
            <div class="sd-tab {{ $tab === 'inquilinos' ? 'active' : '' }}" wire:click="setTab('inquilinos')">
                👤 Inquilinos ({{ count($this->inquilinos) }})
            </div>
            <div class="sd-tab {{ $tab === 'propietarios' ? 'active' : '' }}" wire:click="setTab('propietarios')">
                🏠 Propietarios ({{ count($this->propietarios) }})
            </div>
            <div class="sd-tab {{ $tab === 'ingresos' ? 'active' : '' }}" wire:click="setTab('ingresos')">
                🟢 Comp. Ingreso ({{ count($this->ingresos) }})
            </div>
            <div class="sd-tab {{ $tab === 'egresos' ? 'active' : '' }}" wire:click="setTab('egresos')">
                🔴 Comp. Egreso ({{ count($this->egresos) }})
            </div>
        </div>

        {{-- ── Buscador ── --}}
        @php
            $placeholders = [
                'inquilinos'   => 'Buscar inquilino por nombre o documento...',
                'propietarios' => 'Buscar propietario por nombre o documento...',
                'ingresos'     => 'Buscar comprobante de ingreso por número, descripción o tercero...',
                'egresos'      => 'Buscar comprobante de egreso por número, descripción o tercero...',
            ];
        @endphp
        <div class="sd-search">
            <svg class="sd-search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/>
            </svg>
            <input type="text" wire:model.live.debounce.300ms="busqueda"
                   placeholder="{{ $placeholders[$tab] ?? 'Buscar...' }}"
                   class="sd-search-input">
            @if($busqueda !== '')
                <button type="button" class="sd-search-clear" wire:click="$set('busqueda', '')" title="Limpiar">✕</button>
            @endif
        </div>

        {{-- ── TAB INQUILINOS ── --}}
        @if($tab === 'inquilinos')
            <div class="sd-grid">
                @forelse($this->inquilinos as $inq)
                    <div class="sd-card {{ $inq['revisado'] ? 'revisado' : '' }} {{ $inq['ya_pago'] ? 'pagado' : '' }}">
                        <details>
                            <summary class="sd-card-head">
                                <div class="sd-avatar {{ ($inq['max_dias'] ?? 0) == 0 || $inq['ya_pago'] ? 'ok' : '' }}">
                                    {{ mb_strtoupper(mb_substr($inq['nombre'] ?? '?', 0, 1)) }}
                                </div>
                                <div class="sd-id-block">
                                    <div class="sd-nombre" title="{{ $inq['nombre'] }}">{{ $inq['nombre'] }}</div>
                                    <div class="sd-doc">CC/NIT {{ $inq['documento'] ?? '—' }}</div>
                                    @if($inq['dia_pago'] ?? null)
                                        <div class="sd-dia-fijo">📅 Recauda el día {{ $inq['dia_pago'] }} de cada mes</div>
                                    @endif
                                </div>
                                <div class="sd-badges">
                                    @if(count($inq['inmuebles'] ?? []) === 0)
                                        <span class="sd-badge green">✅ Al día</span>
                                    @else
                                        @if($inq['ya_pago'])
                                            <span class="sd-badge pagado">✅ Ya pagó ${{ number_format($inq['ya_pago']['monto'],0,',','.') }} — {{ \Carbon\Carbon::parse($inq['ya_pago']['fecha'])->format('d/m/Y') }}</span>
                                        @endif
                                        <span class="sd-badge {{ ($inq['max_dias'] ?? 0) > 0 ? 'red' : 'green' }}">{{ $inq['max_dias'] ?? 0 }} días mora</span>
                                        <span class="sd-badge amber">Riesgo {{ $inq['riesgo'] ?? 0 }}%</span>
                                        <span class="sd-badge gray">{{ count($inq['inmuebles']) }} mes(es)</span>
                                        <span class="sd-total">${{ number_format($inq['total_debe'] ?? 0,0,',','.') }}</span>
                                    @endif
                                    <button type="button"
                                            wire:click.stop="toggleRevisado('inquilino', {{ $inq['id'] }})"
                                            class="sd-check-btn {{ $inq['revisado'] ? 'on' : 'off' }}">
                                        @if($inq['revisado']) ✅ Revisado @else ☐ Marcar revisado @endif
                                    </button>
                                </div>
                            </summary>
                            @if($inq['revisado'])
                                <div class="sd-revisado-info">Revisado por {{ $inq['revisado_por'] }} — {{ $inq['revisado_en'] ? \Carbon\Carbon::parse($inq['revisado_en'])->format('d/m/Y h:i A') : '' }}</div>
                            @endif
                            <div class="sd-detail">
                                @if(count($inq['inmuebles'] ?? []) === 0)
                                    @if($inq['periodo_actual'] ?? null)
                                        <div class="sd-inm-row">
                                            <div>
                                                <div class="sd-inm-dir">
                                                    Período actual
                                                    @if($inq['periodo_actual']['estado'] === 'pagada')
                                                        <span class="sd-mini-badge ok">✅ Pagado</span>
                                                    @else
                                                        <span class="sd-mini-badge gracia">🕐 Aún no vence</span>
                                                    @endif
                                                </div>
                                                <div class="sd-inm-meta">
                                                    {{ $inq['periodo_actual']['inicio'] }} al {{ $inq['periodo_actual']['fin'] }}
                                                    — 💳 fecha límite: <strong>{{ $inq['periodo_actual']['fecha_limite'] }}</strong>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    @if($inq['ultimo_pago_historico'] ?? null)
                                        <div class="sd-inm-row">
                                            <div>
                                                <div class="sd-inm-dir">Último pago registrado</div>
                                                <div class="sd-inm-meta">{{ \Carbon\Carbon::parse($inq['ultimo_pago_historico']['fecha'])->format('d/m/Y') }}</div>
                                            </div>
                                            <div class="sd-inm-valor">${{ number_format($inq['ultimo_pago_historico']['monto'],0,',','.') }}</div>
                                        </div>
                                    @endif
                                @else
                                    <div class="sd-mora-total">Mora acumulada total: <strong style="color:#dc2626;">${{ number_format($inq['total_mora'] ?? 0,0,',','.') }}</strong></div>
                                @endif
                                @foreach($inq['inmuebles'] ?? [] as $im)
                                    <div class="sd-inm-row">
                                        <div>
                                            <div class="sd-inm-dir">
                                                {{ $im['direccion'] }}
                                                @if($im['pagada'] ?? false)
                                                    <span class="sd-mini-badge ok">✅ Pagada</span>
                                                @elseif($im['en_gracia'] ?? false)
                                                    <span class="sd-mini-badge gracia">🕐 En gracia</span>
                                                @else
                                                    <span class="sd-mini-badge no">🔴 Pendiente</span>
                                                @endif
                                            </div>
                                            <div class="sd-inm-meta">
                                                {{ ucfirst($im['mes']) }} ({{ $im['periodo_inicio'] ?? '—' }} al {{ $im['periodo_fin'] ?? '—' }}) — {{ $im['numero'] }} — {{ $im['dias_mora'] }} día(s) — interés ${{ number_format($im['mora'],0,',','.') }}
                                                @if($im['fecha_limite'] ?? null)
                                                    <br>💳 Pronto pago / fecha límite: <strong>{{ $im['fecha_limite'] }}</strong>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="sd-inm-valor">${{ number_format($im['saldo'],0,',','.') }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </details>
                    </div>
                @empty
                    <div class="sd-empty">
                        <div class="sd-empty-icon">🎉</div>
                        <div class="sd-empty-text">
                            @if($busqueda !== '') No se encontró ningún inquilino con "{{ $busqueda }}".
                            @elseif($this->esHoy()) No hay inquilinos en mora por revisar hoy.
                            @else No hay planilla guardada para este día.
                            @endif
                        </div>
                    </div>
                @endforelse
            </div>
        @endif

        {{-- ── TAB PROPIETARIOS ── --}}
        @if($tab === 'propietarios')
            <div class="sd-grid">
                @forelse($this->propietarios as $prop)
                    <div class="sd-card {{ $prop['revisado'] ? 'revisado' : '' }} {{ $prop['ya_giro'] ? 'pagado' : '' }}">
                        <details>
                            <summary class="sd-card-head">
                                <div class="sd-avatar {{ ($prop['max_dias'] ?? 0) <= 5 || $prop['ya_giro'] ? 'ok' : '' }}">
                                    {{ mb_strtoupper(mb_substr($prop['nombre'] ?? '?', 0, 1)) }}
                                </div>
                                <div class="sd-id-block">
                                    <div class="sd-nombre" title="{{ $prop['nombre'] }}">{{ $prop['nombre'] }}</div>
                                    <div class="sd-doc">CC/NIT {{ $prop['documento'] ?? '—' }}</div>
                                    @if($prop['dia_giro'] ?? null)
                                        <div class="sd-dia-fijo">📅 Se le gira el día {{ $prop['dia_giro'] }} de cada mes</div>
                                    @endif
                                </div>
                                <div class="sd-badges">
                                    @if(count($prop['inmuebles'] ?? []) === 0)
                                        <span class="sd-badge green">✅ Sin giros pendientes</span>
                                    @else
                                        @if($prop['ya_giro'])
                                            <span class="sd-badge pagado">✅ Ya se le giró ${{ number_format($prop['ya_giro']['monto'],0,',','.') }} — {{ \Carbon\Carbon::parse($prop['ya_giro']['fecha'])->format('d/m/Y') }}</span>
                                        @endif
                                        <span class="sd-badge {{ ($prop['max_dias'] ?? 0) > 5 ? 'red' : 'amber' }}">{{ $prop['max_dias'] ?? 0 }} días esperando</span>
                                        <span class="sd-badge gray">{{ count($prop['inmuebles']) }} inmueble(s)</span>
                                        <span class="sd-total">${{ number_format($prop['total_girar'] ?? 0,0,',','.') }}</span>
                                    @endif
                                    <button type="button"
                                            wire:click.stop="toggleRevisado('propietario', {{ $prop['id'] }})"
                                            class="sd-check-btn {{ $prop['revisado'] ? 'on' : 'off' }}">
                                        @if($prop['revisado']) ✅ Revisado @else ☐ Marcar revisado @endif
                                    </button>
                                </div>
                            </summary>
                            @if($prop['revisado'])
                                <div class="sd-revisado-info">Revisado por {{ $prop['revisado_por'] }} — {{ $prop['revisado_en'] ? \Carbon\Carbon::parse($prop['revisado_en'])->format('d/m/Y h:i A') : '' }}</div>
                            @endif
                            <div class="sd-detail">
                                @if(count($prop['inmuebles'] ?? []) === 0)
                                    @if($prop['periodo_actual'] ?? null)
                                        <div class="sd-inm-row">
                                            <div>
                                                <div class="sd-inm-dir">
                                                    Período actual
                                                    @if($prop['periodo_actual']['estado'] === 'pagada')
                                                        <span class="sd-mini-badge ok">✅ Pagado</span>
                                                    @else
                                                        <span class="sd-mini-badge gracia">🕐 En proceso</span>
                                                    @endif
                                                </div>
                                                <div class="sd-inm-meta">{{ $prop['periodo_actual']['inicio'] }} al {{ $prop['periodo_actual']['fin'] }}</div>
                                            </div>
                                        </div>
                                    @endif
                                    @if($prop['ultimo_giro_historico'] ?? null)
                                        <div class="sd-inm-row">
                                            <div>
                                                <div class="sd-inm-dir">Último giro pagado</div>
                                                <div class="sd-inm-meta">{{ \Carbon\Carbon::parse($prop['ultimo_giro_historico']['fecha'])->format('d/m/Y') }}</div>
                                            </div>
                                            <div class="sd-inm-valor">${{ number_format($prop['ultimo_giro_historico']['monto'],0,',','.') }}</div>
                                        </div>
                                    @endif
                                @else
                                    @foreach($prop['inmuebles'] as $im)
                                        <div class="sd-inm-row">
                                            <div>
                                                <div class="sd-inm-dir">{{ $im['direccion'] }}</div>
                                                <div class="sd-inm-meta">{{ ucfirst($im['periodo']) }} — {{ $im['numero'] }} — {{ $im['estado'] === 'aprobada' ? 'Aprobada' : 'Pendiente' }}</div>
                                            </div>
                                            <div class="sd-inm-valor">${{ number_format($im['monto'],0,',','.') }}</div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </details>
                    </div>
                @empty
                    <div class="sd-empty">
                        <div class="sd-empty-icon">🎉</div>
                        <div class="sd-empty-text">
                            @if($busqueda !== '') No se encontró ningún propietario con "{{ $busqueda }}".
                            @elseif($this->esHoy()) No hay giros pendientes por revisar hoy.
                            @else No hay planilla guardada para este día.
                            @endif
                        </div>
                    </div>
                @endforelse
            </div>
        @endif

        {{-- ── TAB COMPROBANTES DE INGRESO ── --}}
        @if($tab === 'ingresos')
            <div class="sd-grid">
                @forelse($this->ingresos as $comp)
                    <div class="sd-card {{ $comp['revisado'] ? 'revisado' : '' }}">
                        <details>
                            <summary class="sd-comp-head">
                                <div class="sd-comp-avatar ci">CI</div>
                                <div class="sd-comp-id-block">
                                    <div class="sd-comp-numero">{{ $comp['numero'] }}</div>
                                    <div class="sd-comp-desc" title="{{ $comp['descripcion'] }}">{{ $comp['descripcion'] }}</div>
                                    @if($comp['tercero'])
                                        <div class="sd-comp-tercero">{{ $comp['tercero'] }}</div>
                                    @endif
                                </div>
                                <div class="sd-comp-amounts">
                                    <div>
                                        <div class="sd-comp-amt-label">Total</div>
                                        <div class="sd-comp-amt-value" style="color:#16a34a;">${{ number_format($comp['total_creditos'],0,',','.') }}</div>
                                    </div>
                                    <button type="button"
                                            wire:click.stop="toggleRevisado('comprobante_ingreso', {{ $comp['id'] }})"
                                            class="sd-check-btn {{ $comp['revisado'] ? 'on' : 'off' }}">
                                        @if($comp['revisado']) ✅ Revisado @else ☐ Marcar revisado @endif
                                    </button>
                                </div>
                            </summary>
                            @if($comp['revisado'])
                                <div class="sd-revisado-info">Revisado por {{ $comp['revisado_por'] }} — {{ $comp['revisado_en'] ? \Carbon\Carbon::parse($comp['revisado_en'])->format('d/m/Y h:i A') : '' }}</div>
                            @endif
                            <div class="sd-detail">
                                <table class="sd-imput-table">
                                    <thead>
                                        <tr>
                                            <th>Cuenta</th>
                                            <th>Tercero</th>
                                            <th style="text-align:right;">Débito</th>
                                            <th style="text-align:right;">Crédito</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($comp['lineas'] as $linea)
                                        <tr>
                                            <td data-label="Cuenta">
                                                <span class="sd-imput-cuenta">{{ $linea['cuenta_codigo'] }}</span>
                                                <div class="sd-imput-nombre">{{ $linea['cuenta_nombre'] }}</div>
                                            </td>
                                            <td data-label="Tercero" class="sd-imput-nombre">{{ $linea['tercero'] ?? '—' }}</td>
                                            <td data-label="Débito" style="text-align:right;color:#2563eb;font-family:monospace;">{{ $linea['debito'] > 0 ? '$'.number_format($linea['debito'],0,',','.') : '—' }}</td>
                                            <td data-label="Crédito" style="text-align:right;color:#16a34a;font-family:monospace;">{{ $linea['credito'] > 0 ? '$'.number_format($linea['credito'],0,',','.') : '—' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </details>
                    </div>
                @empty
                    <div class="sd-empty">
                        <div class="sd-empty-icon">🟢</div>
                        <div class="sd-empty-text">
                            @if($busqueda !== '') No se encontró ningún comprobante con "{{ $busqueda }}".
                            @else No hay comprobantes de ingreso contabilizados este día.
                            @endif
                        </div>
                    </div>
                @endforelse
            </div>
        @endif

        {{-- ── TAB COMPROBANTES DE EGRESO ── --}}
        @if($tab === 'egresos')
            <div class="sd-grid">
                @forelse($this->egresos as $comp)
                    <div class="sd-card {{ $comp['revisado'] ? 'revisado' : '' }}">
                        <details>
                            <summary class="sd-comp-head">
                                <div class="sd-comp-avatar ce">CE</div>
                                <div class="sd-comp-id-block">
                                    <div class="sd-comp-numero">{{ $comp['numero'] }}</div>
                                    <div class="sd-comp-desc" title="{{ $comp['descripcion'] }}">{{ $comp['descripcion'] }}</div>
                                    @if($comp['tercero'])
                                        <div class="sd-comp-tercero">{{ $comp['tercero'] }}</div>
                                    @endif
                                </div>
                                <div class="sd-comp-amounts">
                                    <div>
                                        <div class="sd-comp-amt-label">Total</div>
                                        <div class="sd-comp-amt-value" style="color:#dc2626;">${{ number_format($comp['total_debitos'],0,',','.') }}</div>
                                    </div>
                                    <button type="button"
                                            wire:click.stop="toggleRevisado('comprobante_egreso', {{ $comp['id'] }})"
                                            class="sd-check-btn {{ $comp['revisado'] ? 'on' : 'off' }}">
                                        @if($comp['revisado']) ✅ Revisado @else ☐ Marcar revisado @endif
                                    </button>
                                </div>
                            </summary>
                            @if($comp['revisado'])
                                <div class="sd-revisado-info">Revisado por {{ $comp['revisado_por'] }} — {{ $comp['revisado_en'] ? \Carbon\Carbon::parse($comp['revisado_en'])->format('d/m/Y h:i A') : '' }}</div>
                            @endif
                            <div class="sd-detail">
                                <table class="sd-imput-table">
                                    <thead>
                                        <tr>
                                            <th>Cuenta</th>
                                            <th>Tercero</th>
                                            <th style="text-align:right;">Débito</th>
                                            <th style="text-align:right;">Crédito</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($comp['lineas'] as $linea)
                                        <tr>
                                            <td data-label="Cuenta">
                                                <span class="sd-imput-cuenta">{{ $linea['cuenta_codigo'] }}</span>
                                                <div class="sd-imput-nombre">{{ $linea['cuenta_nombre'] }}</div>
                                            </td>
                                            <td data-label="Tercero" class="sd-imput-nombre">{{ $linea['tercero'] ?? '—' }}</td>
                                            <td data-label="Débito" style="text-align:right;color:#2563eb;font-family:monospace;">{{ $linea['debito'] > 0 ? '$'.number_format($linea['debito'],0,',','.') : '—' }}</td>
                                            <td data-label="Crédito" style="text-align:right;color:#16a34a;font-family:monospace;">{{ $linea['credito'] > 0 ? '$'.number_format($linea['credito'],0,',','.') : '—' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </details>
                    </div>
                @empty
                    <div class="sd-empty">
                        <div class="sd-empty-icon">🔴</div>
                        <div class="sd-empty-text">
                            @if($busqueda !== '') No se encontró ningún comprobante con "{{ $busqueda }}".
                            @else No hay comprobantes de egreso contabilizados este día.
                            @endif
                        </div>
                    </div>
                @endforelse
            </div>
        @endif
    </div>
</x-filament-panels::page>
