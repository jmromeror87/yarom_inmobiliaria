<x-filament-panels::page>

@php
    $periodos  = $this->getPeriodos();
    $data      = $this->getBalanceData();
    $fmt       = fn($v) => $v > 0 ? '$' . number_format($v, 2, ',', '.') : '';

    $sumDeb    = $data->sum('debito');
    $sumCre    = $data->sum('credito');
    $sumSalDeb = $data->sum('saldo_deb');
    $sumSalCre = $data->sum('saldo_cre');
    $cuadrado  = abs($sumDeb - $sumCre) < 0.01 && abs($sumSalDeb - $sumSalCre) < 0.01;

    $clasesLabels = ['1'=>'Activos','2'=>'Pasivos','3'=>'Patrimonio','4'=>'Ingresos','5'=>'Gastos','6'=>'C.Prod','7'=>'C.Ventas'];
@endphp

<style>
.acc-filter{display:flex;gap:12px;align-items:center;flex-wrap:wrap;margin-bottom:20px;}
.acc-filter select{padding:8px 14px;border:1px solid #cbd5e1;border-radius:10px;font-size:13px;font-weight:600;color:#0f172a;background:#fff;}
.acc-filter label{display:flex;align-items:center;gap:6px;font-size:13px;font-weight:600;color:#0f172a;cursor:pointer;}
.bp-table{width:100%;border-collapse:collapse;font-size:12.5px;}
.bp-table th{background:#0f172a;color:#fff;padding:10px 14px;text-align:right;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:0.08em;}
.bp-table th:first-child,.bp-table th:nth-child(2){text-align:left;}
.bp-table td{padding:8px 14px;border-bottom:1px solid #f1f5f9;text-align:right;}
.bp-table td:first-child{text-align:left;font-family:monospace;font-weight:700;color:#2563eb;}
.bp-table td:nth-child(2){text-align:left;color:#0f172a;}
.bp-table tr:hover td{background:#f8fafc;}
.bp-table tr.group-header td{background:#f1f5f9;font-weight:800;font-size:11px;text-transform:uppercase;letter-spacing:0.08em;color:#64748b;padding:6px 14px;}
.bp-table tr.total-row td{background:#0f172a;color:#fff;font-weight:900;font-family:monospace;border:none;}
.bp-table tr.total-row td:first-child,.bp-table tr.total-row td:nth-child(2){color:#94a3b8;}
.num-pos{color:#16a34a;font-family:monospace;}
.num-neg{color:#dc2626;font-family:monospace;}
.num-zero{color:#94a3b8;}
.cuadre-ok{background:#f0fdf4;border:1.5px solid #16a34a;border-radius:10px;padding:14px 20px;color:#15803d;font-weight:900;font-size:13px;display:flex;align-items:center;gap:10px;margin-top:16px;}
.cuadre-err{background:#fef2f2;border:1.5px solid #dc2626;border-radius:10px;padding:14px 20px;color:#dc2626;font-weight:900;font-size:13px;display:flex;align-items:center;gap:10px;margin-top:16px;}
</style>

<div>
    {{-- Filtros --}}
    <div class="acc-filter">
        <label style="font-size:12px;font-weight:700;color:#64748b;">Período:</label>
        <select wire:model.live="periodo_id">
            <option value="">— Acumulado todos los períodos —</option>
            @foreach($periodos as $id => $nombre)
            <option value="{{ $id }}" @selected($this->periodo_id == $id)>{{ $nombre }}</option>
            @endforeach
        </select>

        <label>
            <input type="checkbox" wire:model.live="solo_con_movimiento">
            Solo cuentas con movimiento
        </label>

        <span style="font-size:12px;color:#94a3b8;">{{ $data->count() }} cuentas</span>
    </div>

    @if($data->isEmpty())
    <div style="text-align:center;padding:48px;color:#94a3b8;">
        <div style="font-size:36px;margin-bottom:8px;">⚖️</div>
        <div style="font-weight:700;">No hay movimientos contabilizados.</div>
    </div>
    @else

    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden;">
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
                    <td>{{ $row['codigo'] }}</td>
                    <td style="text-align:left;">{{ $row['nombre'] }}</td>
                    <td class="{{ $row['debito'] > 0 ? 'num-pos' : 'num-zero' }}">{{ $fmt($row['debito']) }}</td>
                    <td class="{{ $row['credito'] > 0 ? 'num-pos' : 'num-zero' }}">{{ $fmt($row['credito']) }}</td>
                    <td class="{{ $row['saldo_deb'] > 0 ? 'num-pos' : 'num-zero' }}">{{ $fmt($row['saldo_deb']) }}</td>
                    <td class="{{ $row['saldo_cre'] > 0 ? 'num-pos' : 'num-zero' }}">{{ $fmt($row['saldo_cre']) }}</td>
                </tr>
                @endforeach

                {{-- Fila de totales --}}
                <tr class="total-row">
                    <td colspan="2" style="text-align:left;font-size:12px;letter-spacing:0.08em;padding:14px 14px;">TOTALES</td>
                    <td>${{ number_format($sumDeb, 2, ',', '.') }}</td>
                    <td>${{ number_format($sumCre, 2, ',', '.') }}</td>
                    <td>${{ number_format($sumSalDeb, 2, ',', '.') }}</td>
                    <td>${{ number_format($sumSalCre, 2, ',', '.') }}</td>
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
