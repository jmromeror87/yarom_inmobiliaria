<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Recibo {{ $payment->numero }}</title>
<style>
    @page { margin: 14pt 16pt; }
    * { box-sizing: border-box; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 8.3pt; color: #111827; margin: 0; }

    .doc { border: 0.75pt solid #94a3b8; border-radius: 4pt; }

    /* ── Encabezado ── */
    .head { padding:6pt 12pt 5pt 12pt; border-bottom:1pt solid #94a3b8; }
    .head table { width:100%; border-collapse:collapse; }
    .head-logo { color:#1e293b; font-size:14pt; font-weight:bold; letter-spacing:.01em; }
    .head-logo span { color:#E24B4A; }
    .head-company { color:#64748b; font-size:6.6pt; margin-top:2pt; line-height:1.5; }
    .head-right { text-align:right; }
    .head-doctype { color:#94a3b8; font-size:6.6pt; text-transform:uppercase; letter-spacing:.1em; }
    .head-num { color:#1e293b; font-size:14pt; font-weight:bold; }
    .head-fecha { color:#64748b; font-size:7pt; margin-top:1pt; }

    /* ── Filas de campos tipo formulario (3 o 4 columnas) ── */
    table.grid { width:100%; border-collapse:collapse; }
    table.grid th { text-align:left; font-size:6.2pt; font-weight:bold; text-transform:uppercase; letter-spacing:.06em; color:#64748b; padding:1.8pt 8pt; border-top:0.75pt solid #94a3b8; border-bottom:0.5pt solid #e2e8f0; }
    table.grid td { font-size:8.3pt; font-weight:bold; color:#0f172a; padding:2.5pt 8pt 3pt 8pt; border-bottom:0.75pt solid #94a3b8; vertical-align:top; }

    /* ── Detalle del pago ── */
    .det-title { font-size:6.4pt; font-weight:bold; text-transform:uppercase; letter-spacing:.08em; color:#64748b; padding:1.8pt 8pt; border-top:0.75pt solid #94a3b8; border-bottom:0.5pt solid #e2e8f0; }
    table.items { width:100%; border-collapse:collapse; }
    table.items th { text-align:left; font-size:6.2pt; font-weight:bold; text-transform:uppercase; letter-spacing:.05em; color:#64748b; padding:2pt 8pt; border-bottom:0.5pt solid #cbd5e1; }
    table.items th.right, table.items td.right { text-align:right; }
    table.items td { font-size:8pt; padding:1.8pt 8pt; border-bottom:0.5pt dotted #cbd5e1; }
    table.items td.num { font-weight:bold; font-variant-numeric: tabular-nums; }
    table.items tr.total td { border-top:1pt solid #94a3b8; border-bottom:1pt solid #94a3b8; font-weight:bold; font-size:9pt; color:#1e293b; padding:2.5pt 8pt; }

    .son { padding:2.5pt 12pt; font-size:7.6pt; border-top:0.5pt solid #e2e8f0; border-bottom:0.75pt solid #94a3b8; }
    .son .lbl { color:#64748b; font-size:6.4pt; text-transform:uppercase; letter-spacing:.07em; font-weight:bold; }
    .son .val { font-weight:bold; color:#0f172a; }

    /* ── Pie: QR / Observaciones / Firma ── */
    table.pie { width:100%; border-collapse:collapse; }
    table.pie th { text-align:left; font-size:6.2pt; font-weight:bold; text-transform:uppercase; letter-spacing:.06em; color:#64748b; padding:1.8pt 8pt; border-bottom:0.5pt solid #e2e8f0; }
    table.pie td { font-size:7.4pt; color:#374151; padding:3pt 8pt; vertical-align:top; height:20pt; }
    table.pie td.qr { text-align:center; }
    .qr-box { display:inline-block; width:18pt; height:18pt; border:0.75pt dashed #94a3b8; color:#cbd5e1; font-size:5.5pt; text-align:center; line-height:18pt; }
    .firma-linea { border-top:0.5pt solid #94a3b8; margin-top:8pt; padding-top:2pt; font-size:6.2pt; color:#64748b; text-transform:uppercase; letter-spacing:.04em; text-align:center; }

    .col-l { border-right:0.5pt solid #e2e8f0; }
</style>
</head>
<body>

@php
    $bill = $payment->bill;
    $money = fn ($v) => '$' . number_format((float) $v, 0, ',', '.');

    $periodoTexto = null;
    if ($bill?->periodo_inicio && $bill?->periodo_fin) {
        $periodoTexto = \Carbon\Carbon::parse($bill->periodo_inicio)->format('d/m/Y') . ' - ' . \Carbon\Carbon::parse($bill->periodo_fin)->format('d/m/Y');
    } elseif ($bill?->mes && $bill?->anio) {
        $periodoTexto = ucfirst(\Carbon\Carbon::create($bill->anio, $bill->mes, 1)->translatedFormat('F Y'));
    }
@endphp

<div class="doc">
    <div class="head">
        <table>
            <tr>
                <td>
                    @if($logoBase64)
                        <img src="{{ $logoBase64 }}" style="max-height:20pt;max-width:90pt;">
                    @else
                        <div class="head-logo">SERVIARRENDAR <span>SAS</span></div>
                    @endif
                    <div class="head-company">
                        NIT {{ $company?->nit_completo ?? $company?->nit }}
                        @if($company?->direccion) &nbsp;·&nbsp; {{ $company->direccion }}{{ $company?->municipio?->nombre ? ', ' . $company->municipio->nombre : '' }} @endif
                        <br>
                        @if($company?->celular) Tel: {{ $company->celular }} @endif
                        @if($company?->email) &nbsp;·&nbsp; {{ $company->email }} @endif
                    </div>
                </td>
                <td class="head-right">
                    <div class="head-doctype">Recibo de pago</div>
                    <div class="head-num">{{ $payment->numero }}</div>
                    <div class="head-fecha">{{ \Carbon\Carbon::parse($payment->fecha_pago)->translatedFormat('d \d\e F \d\e Y') }}</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="grid">
        <tr>
            <th style="width:34%;">Cliente</th>
            <th style="width:16%;">Documento</th>
            <th style="width:18%;">Contrato</th>
            <th style="width:16%;">Factura</th>
        </tr>
        <tr>
            <td class="col-l">{{ $payment->arrendatario?->nombre_completo }}</td>
            <td class="col-l">{{ $payment->arrendatario?->numero_documento }}</td>
            <td class="col-l">{{ $bill?->rentalContract?->numero_contrato }}</td>
            <td>{{ $bill?->numero }}</td>
        </tr>
        <tr>
            <th style="width:16%;">Inmueble</th>
            <th style="width:48%;">Dirección</th>
            <th style="width:20%;">Período</th>
        </tr>
        <tr>
            <td class="col-l">{{ $bill?->property?->codigo }}</td>
            <td class="col-l">{{ $bill?->property?->direccion }}</td>
            <td>{{ $periodoTexto }}</td>
        </tr>
        <tr>
            <th style="width:20%;">Método de pago</th>
            <th style="width:18%;">Banco</th>
            <th style="width:14%;">Referencia</th>
            <th style="width:32%;">Elaboró</th>
        </tr>
        <tr>
            <td class="col-l">{{ ucfirst($payment->forma_pago) }}</td>
            <td class="col-l">{{ $payment->bank?->nombre ?? '—' }}</td>
            <td class="col-l">{{ $payment->referencia_pago ?: '—' }}</td>
            <td>{{ $payment->registradoPor?->name ?? '—' }}</td>
        </tr>
    </table>

    <div class="det-title">Detalle del pago</div>
    <table class="items">
        <thead>
            <tr><th>Concepto</th><th class="right" style="width:26%;">Valor</th></tr>
        </thead>
        <tbody>
            @if($payment->valor_canon > 0)
            <tr><td>Canon de arriendo</td><td class="right num">{{ $money($payment->valor_canon) }}</td></tr>
            @endif
            @if($payment->valor_administracion > 0)
            <tr><td>Cuota de administración</td><td class="right num">{{ $money($payment->valor_administracion) }}</td></tr>
            @endif
            <tr><td>Recargos</td><td class="right num">{{ $money($payment->valor_mora) }}</td></tr>
            @if($payment->otros_valores > 0)
            <tr><td>Otros</td><td class="right num">{{ $money($payment->otros_valores) }}</td></tr>
            @endif
            <tr class="total"><td>TOTAL PAGADO</td><td class="right">{{ $money($payment->total_pagado) }}</td></tr>
        </tbody>
    </table>

    <div class="son">
        <span class="lbl">Son:</span>
        <span class="val">{{ \App\Helpers\NumeroALetras::convertir((float) $payment->total_pagado) }}</span>
    </div>

    <table class="pie">
        <tr>
            <th style="width:22%;">QR de validación</th>
            <th style="width:44%;">Observaciones</th>
            <th style="width:34%;">Firma y sello</th>
        </tr>
        <tr>
            <td class="qr col-l"><span class="qr-box">QR</span></td>
            <td class="col-l">{{ $payment->notas ?: ($periodoTexto ? 'Pago correspondiente al período ' . $periodoTexto : '—') }}</td>
            <td><div class="firma-linea">{{ $company?->razon_social }}</div></td>
        </tr>
    </table>
</div>

</body>
</html>
