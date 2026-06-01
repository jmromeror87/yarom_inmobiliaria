<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8">
<style>
    @page { margin:1.5cm 1.5cm 1.8cm; size:legal landscape; }
    body { font-family:'DejaVu Sans',sans-serif; font-size:8pt; color:#000; line-height:1.4; }
    .footer-fijo { position:fixed; bottom:-1.3cm; left:0; right:0; text-align:center; font-size:7pt; color:#555; border-top:0.5pt solid #ccc; padding-top:3pt; }
    .head { background:#0A192F; padding:12pt 16pt; }
    .head-title { color:#fff; font-size:14pt; font-weight:bold; }
    .head-title span { color:#E24B4A; }
    .head-sub { color:#94a3b8; font-size:8pt; margin-top:3pt; }
    .kpi-row { background:#f1f5f9; padding:8pt 16pt; display:table; width:100%; border-collapse:collapse; }
    .kpi-cell { display:table-cell; padding:4pt 12pt; border-right:0.5pt solid #cbd5e1; text-align:center; }
    .kpi-cell:last-child { border-right:none; }
    .kpi-label { color:#64748b; font-size:7pt; text-transform:uppercase; font-weight:bold; }
    .kpi-val { color:#0f172a; font-size:11pt; font-weight:bold; margin-top:2pt; }
    table.data { width:100%; border-collapse:collapse; margin-top:12pt; font-size:7.5pt; }
    table.data th { background:#0E01A3; color:#fff; padding:5pt 6pt; text-align:left; font-size:7.5pt; }
    table.data td { padding:4pt 6pt; border-bottom:0.5pt solid #e2e8f0; }
    table.data tr:nth-child(even) td { background:#f8faff; }
    table.data tr.total td { background:#e8edff; font-weight:bold; border-top:1pt solid #0E01A3; }
    .r { text-align:right; }
    .c { text-align:center; }
    .badge-pago { background:#dcfce7; color:#166534; padding:1pt 5pt; border-radius:3pt; font-size:7pt; font-weight:bold; }
    .badge-mora { background:#fee2e2; color:#991b1b; padding:1pt 5pt; border-radius:3pt; font-size:7pt; font-weight:bold; }
    .badge-pend { background:#fef3c7; color:#92400e; padding:1pt 5pt; border-radius:3pt; font-size:7pt; font-weight:bold; }
</style>
</head>
<body>

<div class="footer-fijo">
    YarOM ERP — Reporte generado el {{ now()->format('d/m/Y H:i') }} — Confidencial
</div>

<div class="head">
    <table style="width:100%">
        <tr>
            <td>
                <div class="head-title">YAROM <span>INMO</span>BILIARIA</div>
                <div class="head-sub">Serviarrendar S.A.S — Gestión Inmobiliaria</div>
            </td>
            <td style="text-align:right;">
                <div style="color:#94a3b8;font-size:8pt;">REPORTE</div>
                <div style="color:#fff;font-size:15pt;font-weight:bold;">RECAUDO DEL MES</div>
                <div style="color:#94a3b8;font-size:8pt;">{{ strtoupper($nombreMes) }}</div>
            </td>
        </tr>
    </table>
</div>

<table class="kpi-row" style="width:100%;border-collapse:collapse;">
    <tr>
        <td class="kpi-cell" style="width:25%;display:table-cell;padding:6pt 12pt;border-right:0.5pt solid #cbd5e1;text-align:center;">
            <div class="kpi-label">Total Facturado</div>
            <div class="kpi-val">${{ number_format($totalFacturado, 0, ',', '.') }}</div>
        </td>
        <td class="kpi-cell" style="width:25%;display:table-cell;padding:6pt 12pt;border-right:0.5pt solid #cbd5e1;text-align:center;">
            <div class="kpi-label">Total Recaudado</div>
            <div class="kpi-val">${{ number_format($totalRecaudado, 0, ',', '.') }}</div>
        </td>
        <td class="kpi-cell" style="width:25%;display:table-cell;padding:6pt 12pt;border-right:0.5pt solid #cbd5e1;text-align:center;">
            <div class="kpi-label">Pendiente</div>
            <div class="kpi-val">${{ number_format($totalPendiente, 0, ',', '.') }}</div>
        </td>
        <td class="kpi-cell" style="width:25%;display:table-cell;padding:6pt 12pt;text-align:center;">
            <div class="kpi-label">Efectividad</div>
            <div class="kpi-val" style="color:{{ $efectividad >= 90 ? '#16a34a' : ($efectividad >= 70 ? '#d97706' : '#dc2626') }}">{{ $efectividad }}%</div>
        </td>
    </tr>
</table>

<table class="data">
    <thead>
        <tr>
            <th>Factura</th><th>Inmueble</th><th>Arrendatario</th>
            <th class="r">Total</th><th class="r">Pagado</th><th class="r">Saldo</th>
            <th class="c">F. Límite</th><th class="c">F. Pago</th><th class="c">Estado</th>
        </tr>
    </thead>
    <tbody>
        @foreach($bills as $b)
        <tr>
            <td>{{ $b->numero }}</td>
            <td>{{ $b->property?->codigo }} — {{ $b->property?->direccion }}</td>
            <td>{{ $b->arrendatario?->nombre_completo ?? $b->arrendatario?->razon_social }}</td>
            <td class="r">${{ number_format($b->total_factura, 0, ',', '.') }}</td>
            <td class="r">${{ number_format($b->total_pagado, 0, ',', '.') }}</td>
            <td class="r">${{ number_format($b->saldo_pendiente, 0, ',', '.') }}</td>
            <td class="c">{{ $b->fecha_limite_pago?->format('d/m/Y') }}</td>
            <td class="c">{{ $b->fecha_pago?->format('d/m/Y') ?? '—' }}</td>
            <td class="c">
                @if(in_array($b->estado, ['pagada']))
                    <span class="badge-pago">PAGADA</span>
                @elseif(in_array($b->estado, ['en_mora','vencida']))
                    <span class="badge-mora">{{ strtoupper($b->estado) }}</span>
                @else
                    <span class="badge-pend">{{ strtoupper($b->estado) }}</span>
                @endif
            </td>
        </tr>
        @endforeach
        <tr class="total">
            <td colspan="3"><strong>TOTALES</strong></td>
            <td class="r">${{ number_format($totalFacturado, 0, ',', '.') }}</td>
            <td class="r">${{ number_format($totalRecaudado, 0, ',', '.') }}</td>
            <td class="r">${{ number_format($totalPendiente, 0, ',', '.') }}</td>
            <td colspan="3"></td>
        </tr>
    </tbody>
</table>

</body>
</html>
