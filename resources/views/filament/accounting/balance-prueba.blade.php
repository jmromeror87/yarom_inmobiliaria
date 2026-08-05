<x-filament-panels::page>

@php
    $periodos  = $this->getPeriodos();
    $data      = $this->getBalanceData();
    $fmt       = fn($v) => $v > 0 ? '$' . number_format($v, 2, ',', '.') : '—';

    $sumDeb    = $data->sum('debito');
    $sumCre    = $data->sum('credito');
    $sumSalDeb = $data->sum('saldo_deb');
    $sumSalCre = $data->sum('saldo_cre');
    $cuadrado  = abs($sumDeb - $sumCre) < 0.01 && abs($sumSalDeb - $sumSalCre) < 0.01;

    $clasesLabels = ['1'=>'Activos','2'=>'Pasivos','3'=>'Patrimonio','4'=>'Ingresos','5'=>'Gastos','6'=>'C.Prod','7'=>'C.Ventas'];
@endphp

<style>
    .bp-wrap { --bp-ink:#0f172a; --bp-muted:#64748b; --bp-line:#e2e8f0; }

    /* ── Barra de filtros ── */
    .bp-filterbar { background:linear-gradient(135deg,#0f172a,#7f1d3a,#E11D48); border-radius:16px; padding:16px 18px;
                    margin-bottom:18px; display:flex; gap:12px; flex-wrap:wrap; align-items:center; }
    .bp-field { display:flex; flex-direction:column; gap:5px; min-width:0; }
    .bp-field.grow { flex:1; min-width:220px; }
    .bp-field label { font-size:0.66rem; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:rgba(255,255,255,.6); }
    .bp-field select { background:rgba(255,255,255,.1); border:1px solid rgba(255,255,255,.2); color:#fff;
        border-radius:10px; padding:9px 12px; font-size:0.82rem; font-weight:600; width:100%; }
    .bp-field select option { color:#0f172a; }
    .bp-check { display:flex; align-items:center; gap:8px; color:#fff; font-size:0.8rem; font-weight:700; cursor:pointer; }
    .bp-check input { width:16px; height:16px; accent-color:#fff; }
    .bp-count { color:rgba(255,255,255,.75); font-size:0.74rem; font-weight:700; margin-left:auto; white-space:nowrap; }

    /* ── Mini stats ── */
    .bp-stats { display:grid; grid-template-columns:repeat(4,1fr); gap:10px; margin-bottom:16px; }
    .bp-stat { background:#fff; border:1px solid var(--bp-line); border-radius:14px; padding:12px 14px; }
    .bp-stat-label { font-size:0.64rem; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:var(--bp-muted); margin-bottom:4px; }
    .bp-stat-value { font-size:0.98rem; font-weight:900; font-family:monospace; }

    .bp-empty { text-align:center; padding:56px 20px; color:#94a3b8; background:#f8fafc; border-radius:16px; }
    .bp-empty-icon { font-size:2.4rem; margin-bottom:8px; }
    .bp-empty-text { font-size:0.88rem; font-weight:700; }

    /* ── Tabla ── */
    .bp-table-card { background:#fff; border:1px solid var(--bp-line); border-radius:16px; overflow:hidden; }
    .bp-table { width:100%; border-collapse:collapse; font-size:12.5px; }
    .bp-table th { background:#0f172a; color:#fff; padding:10px 14px; text-align:right; font-size:11px; font-weight:800;
                   text-transform:uppercase; letter-spacing:0.08em; }
    .bp-table th:first-child, .bp-table th:nth-child(2) { text-align:left; }
    .bp-table td { padding:8px 14px; border-bottom:1px solid #f1f5f9; text-align:right; }
    .bp-table td:first-child { text-align:left; font-family:monospace; font-weight:700; color:#2563eb; }
    .bp-table td:nth-child(2) { text-align:left; color:#0f172a; }
    .bp-table tr:hover td { background:#f8fafc; }
    .bp-table tr.group-header td { background:#f1f5f9; font-weight:800; font-size:11px; text-transform:uppercase;
                                    letter-spacing:0.08em; color:#64748b; padding:6px 14px; }
    .bp-table tr.total-row td { background:#0f172a; color:#fff; font-weight:900; font-family:monospace; border:none; }
    .bp-table tr.total-row td:first-child, .bp-table tr.total-row td:nth-child(2) { color:#94a3b8; }
    .num-pos { color:#16a34a; font-family:monospace; }
    .num-zero { color:#94a3b8; }

    .cuadre-ok  { background:#f0fdf4; border:1.5px solid #16a34a; border-radius:12px; padding:14px 20px; color:#15803d;
                  font-weight:900; font-size:13px; display:flex; align-items:center; gap:10px; margin-top:16px; }
    .cuadre-err { background:#fef2f2; border:1.5px solid #dc2626; border-radius:12px; padding:14px 20px; color:#dc2626;
                  font-weight:900; font-size:13px; display:flex; align-items:center; gap:10px; margin-top:16px; }

    /* ── Responsive: tabla → tarjetas apiladas ── */
    @media (max-width: 780px) {
        .bp-filterbar { padding:14px; }
        .bp-stats { grid-template-columns:1fr 1fr; }
        .bp-count { margin-left:0; flex-basis:100%; }

        .bp-table thead { display:none; }
        .bp-table, .bp-table tbody, .bp-table tr, .bp-table td { display:block; width:100%; }
        .bp-table tr:not(.group-header):not(.total-row) {
            border:1px solid var(--bp-line); border-radius:12px; padding:10px 12px; margin:0 0 8px;
        }
        .bp-table tr.group-header { border-radius:8px; margin:10px 0 6px; padding:0; }
        .bp-table tr.total-row { border-radius:12px; padding:12px; margin-top:4px; }
        .bp-table td { padding:3px 0; border:none; text-align:left !important; }
        .bp-table td:first-child { display:inline-block; margin-right:8px; }
        .bp-table td:nth-child(2) { font-weight:700; margin-bottom:4px; }
        .bp-table td[data-label]::before {
            content: attr(data-label); display:inline-block; min-width:96px; font-size:0.62rem; font-weight:800;
            text-transform:uppercase; letter-spacing:.04em; color:#94a3b8; margin-right:6px;
        }
        .bp-table tr.total-row td[data-label]::before { color:#94a3b8; }
    }
</style>

<div class="bp-wrap">

    {{-- ── Filtros ── --}}
    <div class="bp-filterbar">
        <div class="bp-field grow">
            <label>Período</label>
            <select wire:model.live="periodo_id">
                <option value="">— Acumulado todos los períodos —</option>
                @foreach($periodos as $id => $nombre)
                <option value="{{ $id }}" @selected($this->periodo_id == $id)>{{ $nombre }}</option>
                @endforeach
            </select>
        </div>

        <label class="bp-check">
            <input type="checkbox" wire:model.live="solo_con_movimiento">
            Solo cuentas con movimiento
        </label>

        <span class="bp-count">{{ $data->count() }} cuentas</span>
    </div>

    @if($data->isEmpty())
    <div class="bp-empty">
        <div class="bp-empty-icon">⚖️</div>
        <div class="bp-empty-text">No hay movimientos contabilizados.</div>
    </div>
    @else

    {{-- ── Mini stats ── --}}
    <div class="bp-stats">
        <div class="bp-stat">
            <div class="bp-stat-label">Mov. débito</div>
            <div class="bp-stat-value">${{ number_format($sumDeb, 0, ',', '.') }}</div>
        </div>
        <div class="bp-stat">
            <div class="bp-stat-label">Mov. crédito</div>
            <div class="bp-stat-value">${{ number_format($sumCre, 0, ',', '.') }}</div>
        </div>
        <div class="bp-stat">
            <div class="bp-stat-label">Saldo débito</div>
            <div class="bp-stat-value">${{ number_format($sumSalDeb, 0, ',', '.') }}</div>
        </div>
        <div class="bp-stat">
            <div class="bp-stat-label">Saldo crédito</div>
            <div class="bp-stat-value">${{ number_format($sumSalCre, 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="bp-table-card">
        <table class="bp-table">
            <thead>
                <tr>
                    <th style="width:120px;">Código</th>
                    <th>Nombre de la cuenta</th>
                    <th style="width:130px;">Mov. Débito</th>
                    <th style="width:130px;">Mov. Crédito</th>
                    <th style="width:130px;">Saldo Débito</th>
                    <th style="width:130px;">Saldo Crédito</th>
                </tr>
            </thead>
            <tbody>
                @php $claseActual = null; @endphp
                @foreach($data as $row)
                @php $claseRow = substr($row['codigo'], 0, 1); @endphp
                @if($claseRow !== $claseActual)
                @php $claseActual = $claseRow; @endphp
                <tr class="group-header">
                    <td colspan="6">{{ $claseRow }} — {{ $clasesLabels[$claseRow] ?? 'Cuentas de orden' }}</td>
                </tr>
                @endif
                <tr>
                    <td data-label="Código">{{ $row['codigo'] }}</td>
                    <td data-label="Cuenta" style="text-align:left;">{{ $row['nombre'] }}</td>
                    <td data-label="Mov. débito" class="{{ $row['debito'] > 0 ? 'num-pos' : 'num-zero' }}">{{ $fmt($row['debito']) }}</td>
                    <td data-label="Mov. crédito" class="{{ $row['credito'] > 0 ? 'num-pos' : 'num-zero' }}">{{ $fmt($row['credito']) }}</td>
                    <td data-label="Saldo débito" class="{{ $row['saldo_deb'] > 0 ? 'num-pos' : 'num-zero' }}">{{ $fmt($row['saldo_deb']) }}</td>
                    <td data-label="Saldo crédito" class="{{ $row['saldo_cre'] > 0 ? 'num-pos' : 'num-zero' }}">{{ $fmt($row['saldo_cre']) }}</td>
                </tr>
                @endforeach

                {{-- Fila de totales --}}
                <tr class="total-row">
                    <td data-label="" colspan="2" style="text-align:left;font-size:12px;letter-spacing:0.08em;padding:14px 14px;">TOTALES</td>
                    <td data-label="Mov. débito">${{ number_format($sumDeb, 2, ',', '.') }}</td>
                    <td data-label="Mov. crédito">${{ number_format($sumCre, 2, ',', '.') }}</td>
                    <td data-label="Saldo débito">${{ number_format($sumSalDeb, 2, ',', '.') }}</td>
                    <td data-label="Saldo crédito">${{ number_format($sumSalCre, 2, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Indicador de cuadre --}}
    <div class="{{ $cuadrado ? 'cuadre-ok' : 'cuadre-err' }}">
        @if($cuadrado)
        ✅ Balance de prueba cuadrado — Movimientos y saldos coinciden.
        @else
        ❌ DESCUADRE detectado —
        @if(abs($sumDeb - $sumCre) >= 0.01)
            Movimientos: ${{ number_format(abs($sumDeb - $sumCre), 2, ',', '.') }} de diferencia.
        @endif
        @if(abs($sumSalDeb - $sumSalCre) >= 0.01)
            Saldos: ${{ number_format(abs($sumSalDeb - $sumSalCre), 2, ',', '.') }} de diferencia.
        @endif
        @endif
    </div>
    @endif
</div>

</x-filament-panels::page>
