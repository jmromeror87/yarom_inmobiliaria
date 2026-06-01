<x-filament-panels::page>

@php
    $cuentas    = $this->getCuentas();
    $periodos   = $this->getPeriodos();
    $cuenta     = $this->getCuentaActual();
    $movs       = $this->getMovimientos();
    $fmt        = fn($v) => '$' . number_format($v, 2, ',', '.');

    $totalDeb   = $movs->sum('debito');
    $totalCre   = $movs->sum('credito');
    $saldoFinal = $cuenta?->naturaleza === 'debito'
        ? ($totalDeb - $totalCre)
        : ($totalCre - $totalDeb);

    $saldoAcum  = 0;
@endphp

<style>
.acc-filter{display:flex;gap:12px;align-items:center;flex-wrap:wrap;margin-bottom:20px;}
.acc-filter select{padding:8px 14px;border:1px solid #cbd5e1;border-radius:10px;font-size:13px;font-weight:600;color:#0f172a;background:#fff;cursor:pointer;min-width:280px;}
.acc-table{width:100%;border-collapse:collapse;font-size:13px;}
.acc-table th{background:#f8fafc;padding:10px 14px;text-align:left;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:0.08em;color:#64748b;border-bottom:2px solid #e2e8f0;}
.acc-table td{padding:10px 14px;border-bottom:1px solid #f1f5f9;}
.acc-table tr:hover td{background:#f8fafc;}
.acc-num{font-family:monospace;font-weight:700;}
.acc-deb{color:#2563eb;font-family:monospace;}
.acc-cre{color:#16a34a;font-family:monospace;}
.acc-saldo-pos{color:#16a34a;font-family:monospace;font-weight:700;}
.acc-saldo-neg{color:#dc2626;font-family:monospace;font-weight:700;}
.cuenta-header{background:linear-gradient(135deg,#0f172a,#2563eb);border-radius:14px;padding:20px 24px;margin-bottom:16px;color:#fff;}
</style>

<div>
    {{-- Filtros --}}
    <div class="acc-filter">
        <label style="font-size:12px;font-weight:700;color:#64748b;">Cuenta PUC:</label>
        <select wire:model.live="account_id">
            <option value="">— Seleccione una cuenta —</option>
            @foreach($cuentas as $id => $nombre)
            <option value="{{ $id }}" @selected($this->account_id == $id)>{{ $nombre }}</option>
            @endforeach
        </select>

        <label style="font-size:12px;font-weight:700;color:#64748b;">Período:</label>
        <select wire:model.live="periodo_id">
            <option value="">— Todos —</option>
            @foreach($periodos as $id => $nombre)
            <option value="{{ $id }}" @selected($this->periodo_id == $id)>{{ $nombre }}</option>
            @endforeach
        </select>
    </div>

    @if(!$this->account_id)
    <div style="text-align:center;padding:48px;color:#94a3b8;">
        <div style="font-size:36px;margin-bottom:8px;">📚</div>
        <div style="font-weight:700;">Seleccione una cuenta para ver sus movimientos.</div>
    </div>
    @else

    {{-- Header de la cuenta --}}
    <div class="cuenta-header">
        <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;opacity:0.7;margin-bottom:6px;">
            Cuenta PUC
        </div>
        <div style="display:flex;justify-content:space-between;align-items:flex-end;">
            <div>
                <div style="font-size:22px;font-weight:900;font-family:monospace;">{{ $cuenta?->codigo }}</div>
                <div style="font-size:16px;font-weight:700;margin-top:4px;">{{ $cuenta?->nombre }}</div>
                <div style="font-size:12px;opacity:0.7;margin-top:2px;">
                    Naturaleza: {{ ucfirst($cuenta?->naturaleza ?? '') }}
                    · Clase {{ $cuenta?->clase }} — {{ $cuenta?->claseLabel }}
                </div>
            </div>
            <div style="text-align:right;">
                <div style="font-size:11px;opacity:0.7;text-transform:uppercase;">Saldo final</div>
                <div style="font-size:24px;font-weight:900;font-family:monospace;{{ $saldoFinal >= 0 ? 'color:#86efac' : 'color:#fca5a5' }}">
                    {{ $fmt(abs($saldoFinal)) }}
                    {{ $saldoFinal >= 0 ? ($cuenta?->naturaleza === 'debito' ? '(D)' : '(C)') : '(NEGATIVO)' }}
                </div>
            </div>
        </div>
    </div>

    @if($movs->isEmpty())
    <div style="text-align:center;padding:36px;color:#94a3b8;background:#f8fafc;border-radius:14px;">
        <div style="font-size:28px;margin-bottom:8px;">📭</div>
        <div style="font-weight:700;">Sin movimientos en este período.</div>
    </div>
    @else

    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden;">
        <table class="acc-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Comprobante</th>
                    <th>Descripción</th>
                    <th>Tercero</th>
                    <th style="text-align:right;">Débito</th>
                    <th style="text-align:right;">Crédito</th>
                    <th style="text-align:right;">Saldo</th>
                </tr>
            </thead>
            <tbody>
                @php $saldoAcum = 0; @endphp
                @foreach($movs as $mov)
                @php
                    if ($cuenta->naturaleza === 'debito') {
                        $saldoAcum += $mov->debito - $mov->credito;
                    } else {
                        $saldoAcum += $mov->credito - $mov->debito;
                    }
                @endphp
                <tr>
                    <td class="acc-num">{{ $mov->entry?->fecha?->format('d/m/Y') }}</td>
                    <td>
                        <span class="acc-num" style="color:#2563eb;font-size:12px;">{{ $mov->entry?->numero }}</span>
                    </td>
                    <td style="font-size:12px;color:#475569;">
                        {{ $mov->descripcion ?: $mov->entry?->descripcion }}
                    </td>
                    <td style="font-size:12px;color:#64748b;">{{ $mov->third?->nombre_completo ?? '—' }}</td>
                    <td class="acc-deb" style="text-align:right;">{{ $mov->debito > 0 ? $fmt($mov->debito) : '' }}</td>
                    <td class="acc-cre" style="text-align:right;">{{ $mov->credito > 0 ? $fmt($mov->credito) : '' }}</td>
                    <td style="text-align:right;" class="{{ $saldoAcum >= 0 ? 'acc-saldo-pos' : 'acc-saldo-neg' }}">
                        {{ $fmt(abs($saldoAcum)) }}
                    </td>
                </tr>
                @endforeach

                {{-- Totales --}}
                <tr style="background:#f8fafc;font-weight:900;border-top:2px solid #e2e8f0;">
                    <td colspan="4" style="padding:12px 14px;font-size:12px;text-transform:uppercase;letter-spacing:0.05em;color:#0f172a;">TOTALES DEL PERÍODO</td>
                    <td class="acc-deb" style="text-align:right;padding:12px 14px;">{{ $fmt($totalDeb) }}</td>
                    <td class="acc-cre" style="text-align:right;padding:12px 14px;">{{ $fmt($totalCre) }}</td>
                    <td class="{{ $saldoFinal >= 0 ? 'acc-saldo-pos' : 'acc-saldo-neg' }}" style="text-align:right;padding:12px 14px;font-size:14px;">
                        {{ $fmt(abs($saldoFinal)) }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    @endif
    @endif
</div>

</x-filament-panels::page>
