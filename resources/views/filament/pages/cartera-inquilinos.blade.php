<x-filament-panels::page>
    <style>
        .ci-kpis { display:grid; grid-template-columns:repeat(6,1fr); gap:10px; margin-bottom:16px; }
        @media (max-width: 900px) { .ci-kpis { grid-template-columns:repeat(2,1fr); } }
        .ci-kpi { border-radius:12px; padding:12px 14px; }
        .ci-kpi-label { font-size:0.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.04em; opacity:.75; }
        .ci-kpi-valor { font-size:1.05rem; font-weight:800; margin-top:3px; }
        .ci-kpi.dark { background:#1e293b; color:#fff; }
        .ci-kpi.dark .ci-kpi-label { color:#cbd5e1; }
        .ci-kpi.blue { background:#eff6ff; color:#1d4ed8; }
        .ci-kpi.amber { background:#fffbeb; color:#b45309; }
        .ci-kpi.orange { background:#fff7ed; color:#c2410c; }
        .ci-kpi.red { background:#fef2f2; color:#dc2626; }
        .ci-kpi.red-strong { background:#fee2e2; color:#991b1b; border:2px solid #fca5a5; }

        .ci-toolbar { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:14px; }
        .ci-input { border:1px solid #e2e8f0; border-radius:9px; padding:8px 12px; font-size:0.85rem; background:#fff; }
        .ci-input:focus { outline:none; border-color:#6366f1; }

        .ci-table-wrap { border:1px solid #e2e8f0; border-radius:12px; overflow:hidden; }
        table.ci-table { width:100%; border-collapse:collapse; font-size:0.82rem; }
        table.ci-table thead { background:#f8fafc; }
        table.ci-table th { text-align:left; padding:9px 10px; font-size:0.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.03em; color:#64748b; border-bottom:1px solid #e2e8f0; }
        table.ci-table th.num { text-align:right; }
        table.ci-table td { padding:9px 10px; border-bottom:1px solid #f1f5f9; vertical-align:top; }
        table.ci-table td.num { text-align:right; font-variant-numeric:tabular-nums; }
        .ci-row:hover { background:#f8fafc; }
        .ci-nombre { font-weight:700; color:#0f172a; }
        .ci-celular { font-size:0.72rem; color:#94a3b8; }
        .ci-muted { color:#cbd5e1; }
        .ci-val-30 { color:#b45309; font-weight:600; }
        .ci-val-60 { color:#c2410c; font-weight:600; }
        .ci-val-90 { color:#dc2626; font-weight:600; }
        .ci-val-90mas { color:#991b1b; font-weight:800; }
        .ci-total { font-weight:800; color:#0f172a; }
        .ci-toggle { font-size:0.75rem; color:#4f46e5; cursor:pointer; background:none; border:none; text-decoration:underline; }

        .ci-detalle-row td { background:#f8fafc; padding:0; }
        .ci-detalle { padding:8px 20px 14px 20px; }
        table.ci-mini { width:100%; font-size:0.74rem; border-collapse:collapse; }
        table.ci-mini th { text-align:left; padding:4px 8px; color:#94a3b8; font-weight:600; }
        table.ci-mini th.num, table.ci-mini td.num { text-align:right; }
        table.ci-mini td { padding:4px 8px; color:#334155; }

        .ci-empty { text-align:center; padding:40px; color:#94a3b8; }
    </style>

    @php $tot = $this->getTotalesGenerales(); @endphp

    <div class="ci-kpis">
        <div class="ci-kpi dark">
            <div class="ci-kpi-label">Inquilinos con deuda</div>
            <div class="ci-kpi-valor">{{ $tot['inquilinos'] }}</div>
        </div>
        <div class="ci-kpi blue">
            <div class="ci-kpi-label">Al día (gracia)</div>
            <div class="ci-kpi-valor">${{ number_format($tot['al_dia'], 0, ',', '.') }}</div>
        </div>
        <div class="ci-kpi amber">
            <div class="ci-kpi-label">1 – 30 días</div>
            <div class="ci-kpi-valor">${{ number_format($tot['b_0_30'], 0, ',', '.') }}</div>
        </div>
        <div class="ci-kpi orange">
            <div class="ci-kpi-label">31 – 60 días</div>
            <div class="ci-kpi-valor">${{ number_format($tot['b_31_60'], 0, ',', '.') }}</div>
        </div>
        <div class="ci-kpi red">
            <div class="ci-kpi-label">61 – 90 días</div>
            <div class="ci-kpi-valor">${{ number_format($tot['b_61_90'], 0, ',', '.') }}</div>
        </div>
        <div class="ci-kpi red-strong">
            <div class="ci-kpi-label">+90 días (crítica)</div>
            <div class="ci-kpi-valor">${{ number_format($tot['b_90_mas'], 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="ci-toolbar">
        <input type="text" wire:model.live.debounce.400ms="busqueda" placeholder="🔍 Buscar inquilino..." class="ci-input" style="min-width:260px;">
        <select wire:model.live="ordenarPor" class="ci-input">
            <option value="total">Ordenar por: Deuda total</option>
            <option value="dias">Ordenar por: Días de mora</option>
            <option value="facturas">Ordenar por: # de facturas</option>
        </select>
    </div>

    <div class="ci-table-wrap">
        <table class="ci-table" x-data="{ abierto: null }">
            <thead>
                <tr>
                    <th>Inquilino</th>
                    <th class="num"># Fact.</th>
                    <th class="num">Al día</th>
                    <th class="num">1-30</th>
                    <th class="num">31-60</th>
                    <th class="num">61-90</th>
                    <th class="num">+90</th>
                    <th class="num">Total</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($filas as $i => $fila)
                    <tr class="ci-row">
                        <td>
                            <div class="ci-nombre">{{ $fila['nombre'] }}</div>
                            @if($fila['celular'])<div class="ci-celular">{{ $fila['celular'] }}</div>@endif
                        </td>
                        <td class="num">{{ $fila['cantidad'] }}</td>
                        <td class="num {{ $fila['al_dia'] > 0 ? '' : 'ci-muted' }}">{{ $fila['al_dia'] > 0 ? '$'.number_format($fila['al_dia'],0,',','.') : '—' }}</td>
                        <td class="num {{ $fila['b_0_30'] > 0 ? 'ci-val-30' : 'ci-muted' }}">{{ $fila['b_0_30'] > 0 ? '$'.number_format($fila['b_0_30'],0,',','.') : '—' }}</td>
                        <td class="num {{ $fila['b_31_60'] > 0 ? 'ci-val-60' : 'ci-muted' }}">{{ $fila['b_31_60'] > 0 ? '$'.number_format($fila['b_31_60'],0,',','.') : '—' }}</td>
                        <td class="num {{ $fila['b_61_90'] > 0 ? 'ci-val-90' : 'ci-muted' }}">{{ $fila['b_61_90'] > 0 ? '$'.number_format($fila['b_61_90'],0,',','.') : '—' }}</td>
                        <td class="num {{ $fila['b_90_mas'] > 0 ? 'ci-val-90mas' : 'ci-muted' }}">{{ $fila['b_90_mas'] > 0 ? '$'.number_format($fila['b_90_mas'],0,',','.') : '—' }}</td>
                        <td class="num ci-total">${{ number_format($fila['total'],0,',','.') }}</td>
                        <td>
                            <button type="button" class="ci-toggle" @click="abierto = (abierto === {{ $i }}) ? null : {{ $i }}">
                                <span x-text="abierto === {{ $i }} ? 'Ocultar' : 'Ver detalle'"></span>
                            </button>
                        </td>
                    </tr>
                    <tr class="ci-detalle-row" x-show="abierto === {{ $i }}" x-cloak>
                        <td colspan="9">
                            <div class="ci-detalle">
                                <table class="ci-mini">
                                    <thead>
                                        <tr><th>Factura</th><th>Período</th><th>Inmueble</th><th class="num">Días mora</th><th class="num">Valor</th></tr>
                                    </thead>
                                    <tbody>
                                        @foreach($fila['facturas'] as $f)
                                            <tr>
                                                <td>{{ $f['numero'] }}</td>
                                                <td>{{ $f['periodo'] }}</td>
                                                <td style="color:#94a3b8;">{{ $f['direccion'] }}</td>
                                                <td class="num">{{ $f['dias_mora'] > 0 ? $f['dias_mora'].' d' : '—' }}</td>
                                                <td class="num" style="font-weight:600;">${{ number_format($f['valor'],0,',','.') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="ci-empty">No hay inquilinos con facturas pendientes 🎉</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-filament-panels::page>
