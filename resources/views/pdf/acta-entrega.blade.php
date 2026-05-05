<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    @page { margin: 1.8cm 2.2cm 2cm; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 10pt; color: #000; line-height: 1.6; }
    .footer { position:fixed; bottom:-1.5cm; left:0; right:0; text-align:center; font-size:7pt; color:#555; border-top:0.5pt solid #ccc; padding-top:3pt; }
    .header { text-align:center; margin-bottom:14pt; border-bottom:1.5pt solid #000; padding-bottom:10pt; }
    h2 { font-size:12pt; text-transform:uppercase; margin:12pt 0 6pt; }
    table { width:100%; border-collapse:collapse; margin-bottom:10pt; font-size:9.5pt; }
    th { background:#f1f5f9; font-weight:bold; padding:5pt 6pt; text-align:left; border:0.5pt solid #cbd5e1; }
    td { padding:4pt 6pt; border:0.5pt solid #cbd5e1; vertical-align:top; }
    .badge { display:inline-block; padding:1pt 6pt; border-radius:3pt; font-size:8pt; font-weight:bold; }
    .badge-excelente { background:#f0fdf4; color:#15803d; }
    .badge-bueno { background:#eff6ff; color:#2563eb; }
    .badge-regular { background:#fffbeb; color:#d97706; }
    .badge-malo { background:#fef2f2; color:#dc2626; }
    .firmas { margin-top:40pt; }
    .firma-col { width:30%; text-align:center; display:inline-block; vertical-align:top; padding:0 10pt; }
    .firma-linea { border-top:1pt solid #000; margin-top:30pt; padding-top:4pt; font-size:9pt; }
</style>
</head>
<body>

<div class="footer">
    {{ $company?->razon_social ?? 'Inmobiliaria Serviarrendar S.A.S' }} | {{ $company?->direccion ?? 'Carrera 13 # 11-15 Ofc 103' }}, Ocaña N de S | {{ $company?->email ?? 'serviarrendarltda@gmail.com' }}
</div>

<div class="header">
    @if($logoBase64)<div style="margin-bottom:8pt;"><img src="{{ $logoBase64 }}" style="max-height:50pt;max-width:160pt;"></div>@endif
    <div style="font-size:13pt;font-weight:bold;text-transform:uppercase;">
        Acta de {{ $handover->tipo === 'devolucion' ? 'Devolución' : 'Entrega' }} de Inmueble
    </div>
    <div style="font-size:11pt;font-weight:bold;">N° {{ $handover->numero }}</div>
    <div style="font-size:9pt;color:#555;margin-top:4pt;">
        {{ mb_strtoupper($company?->razon_social ?? 'INMOBILIARIA SERVIARRENDAR SAS', 'UTF-8') }} · NIT {{ $company?->nit_completo ?? '807.005.762-4' }}
    </div>
</div>

{{-- Datos generales --}}
<table>
    <tr><td style="font-weight:bold;width:35%;">Fecha del acta:</td><td>{{ $handover->fecha_acta?->translatedFormat('j \d\e F \d\e Y') }} {{ $handover->hora_acta ? 'a las ' . $handover->hora_acta : '' }}</td></tr>
    <tr><td style="font-weight:bold;">Inmueble:</td><td>{{ $handover->property?->tipo?->nombre }} — {{ $handover->property?->codigo }} | {{ $handover->lugar_acta }}</td></tr>
    <tr><td style="font-weight:bold;">Arrendatario:</td><td>{{ mb_strtoupper($handover->arrendatario?->nombre_completo ?? '', 'UTF-8') }} — CC {{ number_format((float)($handover->arrendatario?->numero_documento ?? 0), 0, ',', '.') }}</td></tr>
    <tr><td style="font-weight:bold;">Contrato:</td><td>{{ $handover->rentalContract?->numero_contrato }} — Inicio: {{ $handover->rentalContract?->fecha_inicio?->format('d/m/Y') }} · Vence: {{ $handover->rentalContract?->fecha_fin?->format('d/m/Y') }}</td></tr>
    <tr><td style="font-weight:bold;">Estado general:</td><td><span class="badge badge-{{ $handover->estado_general }}">{{ strtoupper($handover->estado_general) }}</span></td></tr>
</table>

{{-- Medidores --}}
<h2>Lecturas de Medidores</h2>
<table>
    <tr>
        <th>Servicio</th><th>Lectura</th>
    </tr>
    <tr><td>💧 Agua (m³)</td><td>{{ $handover->lectura_agua ?? 'N/A' }}</td></tr>
    <tr><td>⚡ Energía (kWh)</td><td>{{ $handover->lectura_energia ?? 'N/A' }}</td></tr>
    <tr><td>🔥 Gas (m³)</td><td>{{ $handover->lectura_gas ?? 'N/A' }}</td></tr>
</table>

{{-- Llaves --}}
<h2>Llaves Entregadas</h2>
<table>
    <tr><th>Tipo</th><th>Cantidad</th></tr>
    <tr><td>🔑 Llaves inmueble</td><td>{{ $handover->llaves_entregadas }}</td></tr>
    <tr><td>📟 Controles de acceso</td><td>{{ $handover->llaves_control_acceso }}</td></tr>
    <tr><td>🚗 Parqueadero</td><td>{{ $handover->llaves_parqueadero }}</td></tr>
    <tr><td>📦 Depósito</td><td>{{ $handover->llaves_deposito }}</td></tr>
    @if($handover->notas_llaves)<tr><td colspan="2"><em>{{ $handover->notas_llaves }}</em></td></tr>@endif
</table>

{{-- Inventario por ambientes --}}
<h2>Inventario por Ambientes</h2>
@php
$ambienteLabels = [
    'sala'=>'Sala','comedor'=>'Comedor','cocina'=>'Cocina',
    'habitacion_principal'=>'Habitación Principal','habitacion_2'=>'Habitación 2',
    'habitacion_3'=>'Habitación 3','bano_principal'=>'Baño Principal',
    'bano_secundario'=>'Baño Secundario','bano_social'=>'Baño Social',
    'garaje'=>'Garaje','deposito'=>'Depósito','patio'=>'Patio',
    'balcon'=>'Balcón','zona_lavanderia'=>'Zona Lavandería',
    'estudio'=>'Estudio','otro'=>'Otro',
];
$porAmbiente = $handover->items->groupBy('ambiente');
@endphp
@foreach($porAmbiente as $ambiente => $items)
<div style="font-weight:bold;font-size:10pt;margin:8pt 0 4pt;text-transform:uppercase;color:#0369a1;">
    {{ $ambienteLabels[$ambiente] ?? ucfirst($ambiente) }}
</div>
<table>
    <tr><th style="width:40%;">Elemento</th><th style="width:15%;">Estado</th><th>Observaciones</th></tr>
    @foreach($items as $item)
    <tr>
        <td>{{ $item->elemento }}</td>
        <td><span class="badge badge-{{ $item->estado }}">{{ strtoupper($item->estado) }}</span></td>
        <td>{{ $item->descripcion ?? '—' }}</td>
    </tr>
    @endforeach
</table>
@endforeach

@if($handover->observaciones_generales)
<h2>Observaciones Generales</h2>
<div style="background:#f8fafc;border:0.5pt solid #e2e8f0;border-radius:4pt;padding:10pt;font-size:10pt;line-height:1.6;">
    {{ $handover->observaciones_generales }}
</div>
@endif

{{-- Firmas --}}
<div class="firmas">
    <table style="width:100%;border:none;" cellpadding="0" cellspacing="0">
        <tr>
            <td style="width:33%;text-align:center;border:none;padding:0 10pt;">
                <br><br><br>
                <div style="border-top:1pt solid #000;padding-top:4pt;">
                    <div style="font-weight:bold;font-size:9.5pt;">{{ mb_strtoupper($company?->rep_legal_nombre ?? 'YANETH DEL CARMEN PÉREZ ARÉVALO', 'UTF-8') }}</div>
                    <div style="font-size:9pt;">{{ $company?->razon_social ?? 'Inmobiliaria Serviarrendar S.A.S' }}</div>
                    <div style="font-size:9pt;">Representante Legal</div>
                </div>
            </td>
            <td style="width:33%;text-align:center;border:none;padding:0 10pt;">
                @if($handover->firma_digital_arrendatario)
                <img src="{{ $handover->firma_digital_arrendatario }}" style="max-height:60pt;max-width:140pt;display:block;margin:0 auto;">
                @else
                <br><br><br>
                @endif
                <div style="border-top:1pt solid #000;padding-top:4pt;">
                    <div style="font-weight:bold;font-size:9.5pt;">{{ mb_strtoupper($handover->firmado_arrendatario ?? '', 'UTF-8') }}</div>
                    <div style="font-size:9pt;">CC {{ number_format((float)($handover->arrendatario?->numero_documento ?? 0), 0, ',', '.') }}</div>
                    <div style="font-size:9pt;">Arrendatario</div>
                </div>
            </td>
            <td style="width:33%;text-align:center;border:none;padding:0 10pt;">
                @if($handover->firma_digital_asesor)
                <img src="{{ $handover->firma_digital_asesor }}" style="max-height:60pt;max-width:140pt;display:block;margin:0 auto;">
                @else
                <br><br><br>
                @endif
                <div style="border-top:1pt solid #000;padding-top:4pt;">
                    <div style="font-weight:bold;font-size:9.5pt;">{{ mb_strtoupper($handover->firmado_asesor ?? '', 'UTF-8') }}</div>
                    <div style="font-size:9pt;">Asesor Inmobiliario</div>
                    <div style="font-size:9pt;">{{ $company?->razon_social ?? 'Serviarrendar S.A.S' }}</div>
                </div>
            </td>
        </tr>
    </table>
    <div style="text-align:center;font-size:9pt;margin-top:20pt;color:#64748b;">
        Firmado en {{ $handover->lugar_acta ?? 'Ocaña' }}, el {{ $handover->fecha_firma?->translatedFormat('j \d\e F \d\e Y') ?? now()->translatedFormat('j \d\e F \d\e Y') }}
    </div>
</div>

</body>
</html>
