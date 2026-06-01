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
    table.data th { background:#0E01A3; color:#fff; padding:5pt 6pt; text-align:left; font-size:7.5pt; }
    table.data td { padding:4pt 6pt; border-bottom:0.5pt solid #e2e8f0; }
    .arr { background:#f0fdf4; }
    .disp { background:#eff6ff; }
    .badge-arr { background:#dcfce7; color:#166534; padding:1pt 5pt; border-radius:3pt; font-size:7pt; font-weight:bold; }
    .badge-disp { background:#dbeafe; color:#1e40af; padding:1pt 5pt; border-radius:3pt; font-size:7pt; font-weight:bold; }
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
                <div style="color:#fff;font-size:15pt;font-weight:bold;">ESTADO DEL PORTAFOLIO</div>
                <div style="color:#94a3b8;font-size:8pt;">{{ now()->format('d/m/Y') }}</div>
            </td>
        </tr>
    </table>
</div>

@php
    $arrendados  = $properties->filter(fn($p) => $p->rentalContracts->isNotEmpty())->count();
    $disponibles = $properties->count() - $arrendados;
    $pctOcup     = $properties->count() > 0 ? round($arrendados / $properties->count() * 100, 1) : 0;
    $canonTotal  = $properties->sum(fn($p) => $p->rentalContracts->first()?->canon ?? 0);
@endphp

<table style="width:100%;border-collapse:collapse;background:#f1f5f9;margin-top:1pt;">
    <tr>
        <td style="padding:6pt 16pt;border-right:0.5pt solid #cbd5e1;text-align:center;width:25%;">
            <div style="color:#64748b;font-size:7pt;text-transform:uppercase;font-weight:bold;">Total Inmuebles</div>
            <div style="color:#0f172a;font-size:13pt;font-weight:bold;">{{ $properties->count() }}</div>
        </td>
        <td style="padding:6pt 16pt;border-right:0.5pt solid #cbd5e1;text-align:center;width:25%;">
            <div style="color:#64748b;font-size:7pt;text-transform:uppercase;font-weight:bold;">Arrendados</div>
            <div style="color:#16a34a;font-size:13pt;font-weight:bold;">{{ $arrendados }}</div>
        </td>
        <td style="padding:6pt 16pt;border-right:0.5pt solid #cbd5e1;text-align:center;width:25%;">
            <div style="color:#64748b;font-size:7pt;text-transform:uppercase;font-weight:bold;">Disponibles</div>
            <div style="color:#2563eb;font-size:13pt;font-weight:bold;">{{ $disponibles }}</div>
        </td>
        <td style="padding:6pt 16pt;text-align:center;width:25%;">
            <div style="color:#64748b;font-size:7pt;text-transform:uppercase;font-weight:bold;">Ocupación / Canon Total</div>
            <div style="color:#0f172a;font-size:13pt;font-weight:bold;">{{ $pctOcup }}% / ${{ number_format($canonTotal, 0, ',', '.') }}</div>
        </td>
    </tr>
</table>

<table class="data">
    <thead>
        <tr>
            <th>Código</th><th>Dirección</th><th>Ciudad</th><th>Tipo</th>
            <th>Propietario</th><th class="c">Estado</th><th>Arrendatario</th>
            <th class="r">Canon</th><th class="c">Inicio</th><th class="c">Vence</th>
        </tr>
    </thead>
    <tbody>
        @foreach($properties as $p)
        @php $contrato = $p->rentalContracts->first(); @endphp
        <tr class="{{ $contrato ? 'arr' : 'disp' }}">
            <td>{{ $p->codigo }}</td>
            <td>{{ $p->direccion }}</td>
            <td>{{ $p->ciudad ?? '—' }}</td>
            <td>{{ $p->tipo ?? '—' }}</td>
            <td>{{ $p->propietario?->nombre_completo ?? $p->propietario?->razon_social ?? '—' }}</td>
            <td class="c">
                @if($contrato)
                    <span class="badge-arr">ARRENDADO</span>
                @else
                    <span class="badge-disp">DISPONIBLE</span>
                @endif
            </td>
            <td>{{ $contrato?->arrendatario?->nombre_completo ?? $contrato?->arrendatario?->razon_social ?? '—' }}</td>
            <td class="r">{{ $contrato ? '$'.number_format($contrato->canon, 0, ',', '.') : '—' }}</td>
            <td class="c">{{ $contrato?->fecha_inicio?->format('d/m/Y') ?? '—' }}</td>
            <td class="c">{{ $contrato?->fecha_fin?->format('d/m/Y') ?? '—' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>
