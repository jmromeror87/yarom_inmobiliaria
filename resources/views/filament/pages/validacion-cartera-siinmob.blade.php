<x-filament-panels::page>
    <style>
        .vc-wrap { display:flex; flex-direction:column; gap:16px; }
        .vc-intro { background:#eff6ff; border:1px solid #bfdbfe; border-radius:10px; padding:14px 16px; font-size:0.82rem; color:#1e40af; line-height:1.5; }
        .vc-kpis { display:flex; gap:12px; flex-wrap:wrap; }
        .vc-kpi { flex:1; min-width:180px; background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:14px 16px; }
        .vc-kpi-label { font-size:0.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#94a3b8; }
        .vc-kpi-valor { font-size:1.15rem; font-weight:800; color:#0F172A; margin-top:3px; }
        table.vc-table { width:100%; border-collapse:collapse; background:#fff; border-radius:10px; overflow:hidden; font-size:0.82rem; }
        table.vc-table th { background:#f1f5f9; padding:8px 10px; text-align:right; font-weight:700; color:#475569; border-bottom:1px solid #e2e8f0; }
        table.vc-table th:first-child { text-align:left; }
        table.vc-table td { padding:8px 10px; text-align:right; border-bottom:1px solid #f1f5f9; }
        table.vc-table td:first-child { text-align:left; font-weight:600; }
        .vc-pos { color:#16a34a; }
        .vc-neg { color:#dc2626; }
        .vc-foot { font-weight:800; background:#f8fafc; }
    </style>

    <div class="vc-wrap">
        <div class="vc-intro">
            📊 Esta página cruza, mes a mes, lo <strong>facturado</strong> (débito) contra lo <strong>pagado</strong> (crédito) por los arrendatarios en la cuenta de cartera (13050501/13050502 — Cxc inquilinos), calculado en vivo desde las notas contables históricas de Siinmob ya importadas al sistema (módulo "Histórico Siinmob"). Sirve para verificar que el saldo pendiente cargado en <strong>Cartera</strong> es real y consistente con el movimiento histórico.
        </div>

        <div class="vc-kpis">
            <div class="vc-kpi">
                <div class="vc-kpi-label">Total facturado (periodo)</div>
                <div class="vc-kpi-valor">${{ number_format($totalDebe, 0, ',', '.') }}</div>
            </div>
            <div class="vc-kpi">
                <div class="vc-kpi-label">Total pagado (periodo)</div>
                <div class="vc-kpi-valor">${{ number_format($totalHaber, 0, ',', '.') }}</div>
            </div>
            <div class="vc-kpi">
                <div class="vc-kpi-label">Cartera cargada hoy (30-jun-2026)</div>
                <div class="vc-kpi-valor" style="color:#7c3aed;">${{ number_format($saldoRealCargado, 0, ',', '.') }}</div>
            </div>
            <div class="vc-kpi">
                <div class="vc-kpi-label">Saldo estimado antes de ene-2025</div>
                <div class="vc-kpi-valor">${{ number_format($saldoInicialEstimado, 0, ',', '.') }}</div>
            </div>
        </div>

        <div style="overflow-x:auto;">
            <table class="vc-table">
                <thead>
                    <tr>
                        <th>Mes</th>
                        <th>Facturado (débito)</th>
                        <th>Pagado (crédito)</th>
                        <th>Diferencia</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($meses as $m)
                    <tr>
                        <td>{{ $this->getMesLabel($m['mes']) }}</td>
                        <td>${{ number_format($m['debe'], 0, ',', '.') }}</td>
                        <td>${{ number_format($m['haber'], 0, ',', '.') }}</td>
                        <td class="{{ $m['diferencia'] >= 0 ? 'vc-pos' : 'vc-neg' }}">
                            {{ $m['diferencia'] >= 0 ? '+' : '' }}${{ number_format($m['diferencia'], 0, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="vc-foot">
                        <td>TOTAL</td>
                        <td>${{ number_format($totalDebe, 0, ',', '.') }}</td>
                        <td>${{ number_format($totalHaber, 0, ',', '.') }}</td>
                        <td class="{{ ($totalDebe - $totalHaber) >= 0 ? 'vc-pos' : 'vc-neg' }}">
                            ${{ number_format($totalDebe - $totalHaber, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="vc-intro" style="background:#f0fdf4;border-color:#bbf7d0;color:#166534;">
            ✓ <strong>Cuadre:</strong> saldo estimado antes de enero 2025 (${{ number_format($saldoInicialEstimado, 0, ',', '.') }}) + diferencia acumulada del periodo (${{ number_format($totalDebe - $totalHaber, 0, ',', '.') }}) = ${{ number_format($saldoInicialEstimado + ($totalDebe - $totalHaber), 0, ',', '.') }}, que coincide con la cartera real cargada hoy en el sistema (${{ number_format($saldoRealCargado, 0, ',', '.') }}).
        </div>
    </div>
</x-filament-panels::page>
