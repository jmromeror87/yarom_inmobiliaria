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
    table.data { width:100%; border-collapse:collapse; margin-top:14pt; font-size:7.5pt; }
    table.data th { background:#D97706; color:#fff; padding:5pt 6pt; text-align:left; font-size:7.5pt; }
    table.data td { padding:4pt 6pt; border-bottom:0.5pt solid #e2e8f0; }
    table.data tr:nth-child(even) td { background:#fffbeb; }
    table.data tr.total td { background:#fef3c7; font-weight:bold; border-top:1pt solid #D97706; }
    .badge-girada { background:#dcfce7; color:#166534; padding:1pt 5pt; border-radius:3pt; font-size:7pt; font-weight:bold; }
    .badge-pend { background:#fef3c7; color:#92400e; padding:1pt 5pt; border-radius:3pt; font-size:7pt; font-weight:bold; }
    .r { text-align:right; }
    .c { text-align:center; }
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
                <div style="color:#fff;font-size:15pt;font-weight:bold;">LIQUIDACIONES PROPIETARIOS</div>
                <div style="color:#94a3b8;font-size:8pt;">{{ strtoupper($nombreMes) }}</div>
            </td>
        </tr>
    </table>
</div>

<table style="width:100%;border-collapse:collapse;background:#fffbeb;margin-top:1pt;">
    <tr>
        <td style="padding:6pt 16pt;border-right:0.5pt solid #d97706;text-align:center;width:20%;">
            <div style="color:#64748b;font-size:7pt;text-transform:uppercase;font-weight:bold;">Canon Total</div>
            <div style="color:#0f172a;font-size:12pt;font-weight:bold;">${{ number_format($totalCanon, 0, ',', '.') }}</div>
        </td>
        <td style="padding:6pt 16pt;border-right:0.5pt solid #d97706;text-align:center;width:20%;">
            <div style="color:#64748b;font-size:7pt;text-transform:uppercase;font-weight:bold;">Comisión</div>
            <div style="color:#dc2626;font-size:12pt;font-weight:bold;">${{ number_format($totalComision, 0, ',', '.') }}</div>
        </td>
        <td style="padding:6pt 16pt;border-right:0.5pt solid #d97706;text-align:center;width:20%;">
            <div style="color:#64748b;font-size:7pt;text-transform:uppercase;font-weight:bold;">IVA + ReteFuente</div>
            <div style="color:#dc2626;font-size:12pt;font-weight:bold;">${{ number_format($totalIva + $totalReteFuente, 0, ',', '.') }}</div>
        </td>
        <td style="padding:6pt 16pt;text-align:center;width:40%;">
            <div style="color:#64748b;font-size:7pt;text-transform:uppercase;font-weight:bold;">TOTAL A GIRAR</div>
            <div style="color:#16a34a;font-size:16pt;font-weight:bold;">${{ number_format($totalGiro, 0, ',', '.') }}</div>
        </td>
    </tr>
</table>

<table class="data">
    <thead>
        <tr>
            <th>N° Liquid.</th><th>Inmueble</th><th>Dirección</th><th>Propietario</th>
            <th class="r">Canon</th><th class="r">Comisión</th><th class="r">IVA</th>
            <th class="r">ReteFte</th><th class="r">Otros Desc.</th><th class="r">Total Giro</th><th class="c">Estado</th>
        </tr>
    </thead>
    <tbody>
        @foreach($liquidaciones as $l)
        <tr>
            <td>{{ $l->numero }}</td>
            <td>{{ $l->property?->codigo }}</td>
            <td>{{ $l->property?->direccion }}</td>
            <td>{{ $l->propietario?->nombre_completo ?? $l->propietario?->razon_social }}</td>
            <td class="r">${{ number_format($l->canon_cobrado, 0, ',', '.') }}</td>
            <td class="r">${{ number_format($l->comision_valor, 0, ',', '.') }}</td>
            <td class="r">${{ number_format($l->iva_comision, 0, ',', '.') }}</td>
            <td class="r">${{ number_format($l->retefuente_valor, 0, ',', '.') }}</td>
            <td class="r">${{ number_format($l->otros_descuentos, 0, ',', '.') }}</td>
            <td class="r"><strong>${{ number_format($l->total_giro, 0, ',', '.') }}</strong></td>
            <td class="c">
                @if($l->estado === 'pagada')
                    <span class="badge-girada">GIRADA</span>
                @elseif($l->estado === 'aprobada')
                    <span style="background:#dbeafe;color:#1e40af;padding:1pt 5pt;border-radius:3pt;font-size:7pt;font-weight:bold;">APROBADA</span>
                @elseif($l->estado === 'anulada')
                    <span style="background:#fee2e2;color:#991b1b;padding:1pt 5pt;border-radius:3pt;font-size:7pt;font-weight:bold;">ANULADA</span>
                @else
                    <span class="badge-pend">PENDIENTE</span>
                @endif
            </td>
        </tr>
        @endforeach
        <tr class="total">
            <td colspan="4"><strong>TOTALES</strong></td>
            <td class="r">${{ number_format($totalCanon, 0, ',', '.') }}</td>
            <td class="r">${{ number_format($totalComision, 0, ',', '.') }}</td>
            <td class="r">${{ number_format($totalIva, 0, ',', '.') }}</td>
            <td class="r">${{ number_format($totalReteFuente, 0, ',', '.') }}</td>
            <td class="r"></td>
            <td class="r"><strong>${{ number_format($totalGiro, 0, ',', '.') }}</strong></td>
            <td></td>
        </tr>
    </tbody>
</table>

</body>
</html>
