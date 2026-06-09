<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: DejaVu Sans, sans-serif; font-size:10px; color:#0f172a; background:#fff; }
.header { background:#0f172a; color:#fff; padding:14px 20px; display:table; width:100%; }
.header-left  { display:table-cell; vertical-align:middle; }
.header-right { display:table-cell; vertical-align:middle; text-align:right; }
.header h1 { font-size:16px; font-weight:700; margin-bottom:2px; }
.header p  { font-size:9px; color:rgba(255,255,255,.65); }
.kpis { display:table; width:100%; margin:10px 0; border-collapse:separate; border-spacing:6px; }
.kpi  { display:table-cell; background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; padding:8px 12px; text-align:center; }
.kpi-label { font-size:8px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#64748b; }
.kpi-val   { font-size:14px; font-weight:900; margin-top:2px; }
table.data { width:100%; border-collapse:collapse; margin-top:8px; }
table.data th { background:#1e3a8a; color:#fff; padding:6px 8px; text-align:left; font-size:9px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; }
table.data td { padding:5px 8px; font-size:9px; border-bottom:1px solid #f1f5f9; vertical-align:middle; }
table.data tr:nth-child(even) td { background:#f8fafc; }
.estado { display:inline-block; padding:2px 6px; border-radius:4px; font-size:8px; font-weight:700; }
.est-pendiente { background:#fef9c3; color:#854d0e; }
.est-parcial   { background:#dbeafe; color:#1e40af; }
.est-en_mora   { background:#fee2e2; color:#991b1b; }
.est-vencida   { background:#fce7f3; color:#9d174d; }
.tfoot td { font-weight:900; background:#0f172a; color:#fff; padding:7px 8px; font-size:10px; }
.footer { margin-top:14px; font-size:8px; color:#94a3b8; text-align:center; border-top:1px solid #e2e8f0; padding-top:6px; }
.mora-badge { color:#dc2626; font-weight:700; }
</style>
</head>
<body>

{{-- HEADER --}}
<div class="header">
    <div class="header-left">
        <h1>CARTERA GENERAL — CUENTAS PENDIENTES</h1>
        <p>Inmobiliaria · Saldos vigentes al {{ now()->format('d/m/Y H:i') }}</p>
    </div>
    <div class="header-right">
        <p style="font-size:11px;font-weight:700;color:#fff">{{ now()->format('d/m/Y') }}</p>
        <p style="color:rgba(255,255,255,.6)">{{ $bills->count() }} facturas</p>
    </div>
</div>

{{-- KPIs --}}
@php
    $totalFactura  = $bills->sum('total_factura');
    $totalPagado   = $bills->sum('total_pagado');
    $totalSaldo    = $bills->sum('saldo_pendiente');
    $totalMora     = $bills->sum('mora_acumulada');
    $fmt = fn($v) => '$' . number_format((float)$v, 0, ',', '.');
@endphp
<div class="kpis">
    <div class="kpi">
        <div class="kpi-label">Total facturado</div>
        <div class="kpi-val" style="color:#0f172a">{{ $fmt($totalFactura) }}</div>
    </div>
    <div class="kpi">
        <div class="kpi-label">Total recaudado</div>
        <div class="kpi-val" style="color:#15803d">{{ $fmt($totalPagado) }}</div>
    </div>
    <div class="kpi">
        <div class="kpi-label">Saldo pendiente</div>
        <div class="kpi-val" style="color:#dc2626">{{ $fmt($totalSaldo) }}</div>
    </div>
    <div class="kpi">
        <div class="kpi-label">Intereses de mora</div>
        <div class="kpi-val" style="color:#d97706">{{ $fmt($totalMora) }}</div>
    </div>
    <div class="kpi">
        <div class="kpi-label">N° facturas</div>
        <div class="kpi-val" style="color:#1e3a8a">{{ $bills->count() }}</div>
    </div>
</div>

{{-- TABLA --}}
<table class="data">
    <thead>
        <tr>
            <th>Factura</th>
            <th>Inmueble</th>
            <th>Dirección</th>
            <th>Arrendatario</th>
            <th>Período</th>
            <th style="text-align:right">Total Fact.</th>
            <th style="text-align:right">Pagado</th>
            <th style="text-align:right">Saldo</th>
            <th style="text-align:right">Mora</th>
            <th>Días mora</th>
            <th>Estado</th>
        </tr>
    </thead>
    <tbody>
        @foreach($bills as $b)
        @php
            $dias = $b->fecha_limite_pago ? max(0, (int) now()->diffInDays($b->fecha_limite_pago, false) * -1) : 0;
        @endphp
        <tr>
            <td style="font-family:monospace;font-weight:700">{{ $b->numero }}</td>
            <td style="font-weight:700">{{ $b->property?->codigo ?? '—' }}</td>
            <td>{{ Str::limit($b->property?->direccion ?? '—', 28) }}</td>
            <td>{{ Str::limit($b->arrendatario?->nombre_completo ?? $b->arrendatario?->razon_social ?? '—', 24) }}</td>
            <td style="font-family:monospace">{{ str_pad($b->mes,2,'0',STR_PAD_LEFT) }}/{{ $b->anio }}</td>
            <td style="text-align:right;font-family:monospace">{{ $fmt($b->total_factura) }}</td>
            <td style="text-align:right;font-family:monospace;color:#15803d">{{ $fmt($b->total_pagado) }}</td>
            <td style="text-align:right;font-family:monospace;font-weight:700;color:#dc2626">{{ $fmt($b->saldo_pendiente) }}</td>
            <td style="text-align:right;font-family:monospace;color:#d97706">{{ $b->mora_acumulada > 0 ? $fmt($b->mora_acumulada) : '—' }}</td>
            <td style="text-align:center" class="{{ $dias > 0 ? 'mora-badge' : '' }}">{{ $dias > 0 ? $dias.'d' : 'Al día' }}</td>
            <td>
                <span class="estado est-{{ $b->estado }}">{{ strtoupper($b->estado) }}</span>
            </td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="5">TOTALES ({{ $bills->count() }} facturas)</td>
            <td style="text-align:right;font-family:monospace">{{ $fmt($totalFactura) }}</td>
            <td style="text-align:right;font-family:monospace;color:#86efac">{{ $fmt($totalPagado) }}</td>
            <td style="text-align:right;font-family:monospace;color:#fca5a5">{{ $fmt($totalSaldo) }}</td>
            <td style="text-align:right;font-family:monospace;color:#fde047">{{ $fmt($totalMora) }}</td>
            <td colspan="2"></td>
        </tr>
    </tfoot>
</table>

<div class="footer">
    Generado automáticamente por YarOM ERP · {{ now()->format('d/m/Y H:i:s') }} ·
    Efectividad de recaudo: {{ $totalFactura > 0 ? round(($totalPagado/$totalFactura)*100,1) : 0 }}%
</div>

</body>
</html>
