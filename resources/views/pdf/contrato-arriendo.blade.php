<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    @page { margin: 1.8cm 2.2cm 2cm; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 10pt; color: #000; line-height: 1.6; }

    .footer { position: fixed; bottom: -1.5cm; left: 0; right: 0; text-align: center; font-size: 7pt; color: #555; border-top: 0.5pt solid #ccc; padding-top: 3pt; font-style: italic; }

    .header { text-align: center; margin-bottom: 14pt; }
    .header-logo { margin-bottom: 8pt; }
    .header-titulo { font-size: 13pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.03em; }
    .header-numero { font-size: 11pt; font-weight: bold; }
    .header-empresa { font-size: 8.5pt; color: #333; margin-top: 4pt; }

    .resumen { margin-bottom: 14pt; font-size: 10pt; }
    .resumen-fila { display: flex; margin-bottom: 3pt; }
    .resumen-lbl { font-weight: bold; min-width: 200pt; }
    .resumen-val { flex: 1; }

    .intro { font-size: 10pt; text-align: justify; margin-bottom: 14pt; line-height: 1.7; }

    .clausula { margin-bottom: 10pt; text-align: justify; }
    .clausula-titulo { font-weight: bold; font-size: 10pt; }
    .clausula-body { font-size: 10pt; line-height: 1.7; }

    .firmas-section { margin-top: 40pt; }
    .firma-fecha { font-size: 10pt; margin-bottom: 40pt; text-align: justify; }
    .firmas-tabla { width: 100%; border-collapse: collapse; }
    .firma-col { width: 30%; text-align: center; vertical-align: bottom; padding: 0 6pt; }
    .firma-linea { border-top: 1pt solid #000; padding-top: 5pt; margin-top: 36pt; }
    .firma-nombre { font-weight: bold; font-size: 9.5pt; }
    .firma-dato { font-size: 9pt; }
</style>
</head>
<body>

<div class="footer">
    Cel. {{ $company?->celular ?? '318 693 4710' }} – {{ $company?->telefono ?? '5610274' }} – email: {{ $company?->email ?? 'serviarrendarltda@gmail.com' }} – RNA 05-539<br>
    {{ $company?->direccion ?? 'Carrera 13 # 11-15 oficina 103 edificio Banco de Bogotá' }} {{ $company?->municipio?->nombre ?? 'Ocaña' }} N de S
</div>

@php
    $repLegal    = mb_strtoupper($company?->rep_legal_nombre ?? 'YANETH DEL CARMEN PÉREZ ARÉVALO', 'UTF-8');
    $repDoc      = $company?->rep_legal_documento ?? '37.321.359';
    $repCiudad   = $company?->municipio?->nombre ?? 'Ocaña';
    $empresaNombre = mb_strtoupper($company?->razon_social ?? 'INMOBILIARIA SERVIARRENDAR SAS', 'UTF-8');
    $nit         = $company?->nit_completo ?? '807.005.762-4';

    $arr         = $contract->arrendatario;
    $arrNombre   = mb_strtoupper($arr?->nombre_completo ?? '', 'UTF-8');
    $arrDoc      = number_format((float)($arr?->numero_documento ?? 0), 0, ',', '.');
    $arrLugar    = $arr?->lugar_nacimiento ?? 'Ocaña';
    $arrGenero   = $arr?->genero === 'femenino' ? 'a' : 'o';

    $canonLetras = \App\Helpers\NumeroALetras::convertir((float)$contract->canon_mensual);
    $canonNum    = '$' . number_format($contract->canon_mensual, 0, ',', '.');

    $fechaContrato = $contract->fecha_contrato ?? now()->toDateString();
    $diaFirma    = (int)\Carbon\Carbon::parse($fechaContrato)->format('d');
    $mesFirma    = strtolower(\Carbon\Carbon::parse($fechaContrato)->translatedFormat('F'));
    $anioFirma   = \Carbon\Carbon::parse($fechaContrato)->format('Y');
    $diaLetras   = \App\Helpers\NumeroALetras::diaEnLetras($diaFirma);
    $diaFirmaLetras = $diaLetras . ' (' . str_pad($diaFirma, 2, '0', STR_PAD_LEFT) . ')';

    $fechaInicio = $contract->fecha_inicio
        ? $contract->fecha_inicio->translatedFormat('j \d\e F \d\e Y')
        : 'N/A';

    $duracionTexto = match($contract->duracion_meses) {
        1  => 'Un (01) mes',
        2  => 'Dos (02) meses',
        3  => 'Tres (03) meses',
        6  => 'Seis (06) meses',
        12 => 'Un (01) año',
        default => $contract->duracion_meses . ' meses',
    };

    $incrementoTexto = $contract->tipo_incremento === 'ipc_vivienda'
        ? 'el IPC para la VIVIENDA URBANA'
        : 'el ' . $contract->porcentaje_incremento . '%';

    $tipoInmueble = $contract->property?->tipo?->nombre ?? 'inmueble';
    $direccion    = $contract->property?->direccion ?? '';
    $conjunto     = $contract->property?->conjunto_edificio;
    $apto         = $contract->property?->apto_casa_oficina;
    $ciudad       = $contract->property?->municipio?->nombre ?? 'Ocaña';
    $dpto         = $contract->property?->departamento?->nombre ?? 'Norte de Santander';

    $direccionCompleta = $direccion;
    if ($conjunto) $direccionCompleta .= ' ' . $conjunto;
    if ($apto)     $direccionCompleta .= ' ' . $apto;

    $folio = $contract->folio_inmobiliario;
@endphp

{{-- ENCABEZADO --}}
<div class="header">
    @if($logoBase64)
    <div class="header-logo">
        <img src="{{ $logoBase64 }}" style="max-height:55pt;max-width:180pt;">
    </div>
    @endif
    <div class="header-titulo">Contrato de Arrendamiento {{ $contract->tipo === 'comercial' ? 'Comercial' : 'Vivienda Urbana' }} N°{{ $contract->numero_contrato }}</div>
</div>

{{-- TABLA RESUMEN (igual al modelo real) --}}
<table style="width:100%;margin-bottom:14pt;font-size:10pt;border-collapse:collapse;">
    <tr>
        <td style="font-weight:bold;width:35%;vertical-align:top;padding:2pt 4pt;">Lugar y Fecha del Contrato:</td>
        <td style="vertical-align:top;padding:2pt 4pt;">{{ ucfirst($repCiudad) }}, {{ $diaFirmaLetras }} de {{ $mesFirma }} de {{ $anioFirma }}.</td>
    </tr>
    <tr>
        <td style="font-weight:bold;vertical-align:top;padding:2pt 4pt;">Arrendador:</td>
        <td style="vertical-align:top;padding:2pt 4pt;"><strong>{{ $repLegal }}</strong> identificada con cédula de ciudadanía {{ $repDoc }} Representante legal de <strong>{{ $empresaNombre }}</strong>, con <strong>NIT</strong>. {{ $nit }}.</td>
    </tr>
    <tr>
        <td style="font-weight:bold;vertical-align:top;padding:2pt 4pt;">Arrendatario:</td>
        <td style="vertical-align:top;padding:2pt 4pt;"><strong>{{ $arrNombre }}</strong> identificad{{ $arrGenero }} con cédula de ciudadanía {{ $arrDoc }} de {{ $arrLugar }}.</td>
    </tr>
    @foreach($contract->thirds as $t)
    <tr>
        <td style="font-weight:bold;vertical-align:top;padding:2pt 4pt;">{{ ucfirst(str_replace('_',' ',$t->rol)) }}:</td>
        <td style="vertical-align:top;padding:2pt 4pt;"><strong>{{ mb_strtoupper($t->third?->nombre_completo ?? '', 'UTF-8') }}</strong> identificad{{ $t->third?->genero === 'femenino' ? 'a' : 'o' }} con cédula de ciudadanía {{ number_format((float)($t->third?->numero_documento ?? 0), 0, ',', '.') }}{{ $t->ciudad_expedicion_doc ? ' de ' . $t->ciudad_expedicion_doc : '' }}.</td>
    </tr>
    @endforeach
    <tr>
        <td style="font-weight:bold;vertical-align:top;padding:2pt 4pt;">Objeto:</td>
        <td style="vertical-align:top;padding:2pt 4pt;">Por medio del presente Contrato, el Arrendador entrega a título de arrendamiento al Arrendatario {{ $contract->tipo === 'comercial' ? 'dos locales comerciales ubicados' : 'una ' . strtolower($tipoInmueble) . ' ubicad' . ($tipoInmueble === 'Casa' ? 'a' : 'o') . ' en' }} la {{ $direccionCompleta }}{{ $folio ? ' identificado con el folio inmobiliario ' . $folio . ' de la oficina de registro de instrumentos públicos de ' . $ciudad : '' }}.</td>
    </tr>
    <tr>
        <td style="font-weight:bold;vertical-align:top;padding:2pt 4pt;">Dirección:</td>
        <td style="vertical-align:top;padding:2pt 4pt;">{{ $direccionCompleta }}– {{ $ciudad }}, {{ $dpto }}.</td>
    </tr>
    <tr>
        <td style="font-weight:bold;vertical-align:top;padding:2pt 4pt;">Canon De Arrendamiento:</td>
        <td style="vertical-align:top;padding:2pt 4pt;"><strong>{{ $canonLetras }} ({{ $canonNum }})</strong> mensuales pagaderos anticipadamente.</td>
    </tr>
    <tr>
        <td style="font-weight:bold;vertical-align:top;padding:2pt 4pt;">Término duración:</td>
        <td style="vertical-align:top;padding:2pt 4pt;">{{ $duracionTexto }}.</td>
    </tr>
    <tr>
        <td style="font-weight:bold;vertical-align:top;padding:2pt 4pt;">Fecha de iniciación:</td>
        <td style="vertical-align:top;padding:2pt 4pt;">{{ ucfirst($fechaInicio) }}.</td>
    </tr>
    <tr>
        <td style="font-weight:bold;vertical-align:top;padding:2pt 4pt;">El Inmueble consta de los servicios de:</td>
        <td style="vertical-align:top;padding:2pt 4pt;"><strong>{{ mb_strtoupper($contract->servicios_cargo_arrendatario ?? '', 'UTF-8') }}</strong> que corren por cuenta y cargo del Arrendatario.</td>
    </tr>
    <tr>
        <td style="font-weight:bold;vertical-align:top;padding:2pt 4pt;">Destinación Inmueble:</td>
        <td style="vertical-align:top;padding:2pt 4pt;"><strong>{{ mb_strtoupper($contract->destinacion ?? '', 'UTF-8') }}.</strong>{{ $contract->actividad_comercial ? ' Su actividad comercial será ' . mb_strtoupper($contract->actividad_comercial, 'UTF-8') . '.' : '' }}</td>
    </tr>
    <tr>
        <td style="font-weight:bold;vertical-align:top;padding:2pt 4pt;">Observaciones:</td>
        <td style="vertical-align:top;padding:2pt 4pt;">El incremento legal será anual, según lo establecido en la cuarta cláusula del presente contrato.</td>
    </tr>
</table>

{{-- PÁRRAFO INTRODUCTORIO --}}
<div class="intro">
    Entre <strong>{{ $empresaNombre }}</strong>, sociedad comercial constituida de conformidad con las normas colombianas,
    mediante escritura pública No 0192BIS. Otorgada en la Notaria Primera del Círculo de Ocaña y posteriormente registrada
    en la Cámara de Comercio de Ocaña bajo el número 00010462, con <strong>NIT. {{ $nit }}</strong>, representada en este acto
    por <strong>{{ $repLegal }}</strong> identificada con cédula de ciudadanía {{ $repDoc }} expedida en la ciudad en {{ $repCiudad }},
    quien para efectos del presente contrato se denominará <strong>EL ARRENDADOR</strong>, por una parte; y por la otra
    <strong>{{ $arrNombre }}</strong> identificad{{ $arrGenero }} con cédula de ciudadanía {{ $arrDoc }} de {{ $arrLugar }},
    quien en el presente contrato se denominará <strong>EL ARRENDATARIO</strong>. Además de las anteriores estipulaciones,
    el <strong>ARRENDADOR</strong> y el <strong>ARRENDATARIO</strong> convienen las siguientes clausulas y en lo estipulado
    en ellas, por lo previsto en la Ley:
</div>

{{-- CLÁUSULAS --}}
@foreach($contract->clauses as $clausula)
@php
    $num = strtoupper($clausula->numero);
    $esCuarta = str_contains($num, 'CUARTA');
@endphp
<div class="clausula">
    @if($clausula->tipo === 'paragrafo')
        <span class="clausula-titulo">Parágrafo{{ str_contains(strtoupper($clausula->titulo),'PARÁGRAFO') ? '' : ': ' . $clausula->titulo }}.</span>
        <span class="clausula-body"> {{ $clausula->contenido_actual }}</span>
    @else
        <div>
            <span class="clausula-titulo">{{ $clausula->numero }}. {{ $clausula->titulo }}.</span>
            @if($esCuarta)
            <span class="clausula-body">
                Vencido el primer año de vigencia de este contrato y así sucesivamente cada doce (12) mensualidades, en caso de prorroga
                tácita o expresa, en forma automática y sin necesidad de requerimiento alguno entre las partes, el precio mensual del
                arrendamiento se incrementará conforme a <strong>{{ $incrementoTexto }}</strong>. Al suscribir este contrato el
                <strong>ARRENDATARIO</strong> y el deudor solidario quedan plenamente notificados de todos los reajustes automáticos
                pactados en este contrato y que han de operar durante la vigencia del mismo.
            </span>
            @else
            <span class="clausula-body"> {{ $clausula->contenido_actual }}</span>
            @endif
        </div>
    @endif
</div>
@endforeach

{{-- FIRMAS --}}
<div class="firmas-section">
    <div class="firma-fecha">
        En constancia de lo anterior se firma por las partes a los <strong>{{ $diaFirmaLetras }}</strong> día del mes de <strong>{{ $mesFirma }}</strong> de {{ $anioFirma }}.
    </div>

    <table class="firmas-tabla">
        <tr>
            {{-- Arrendador --}}
            <td class="firma-col">
                <div class="firma-linea"></div>
                <div class="firma-nombre">{{ $repLegal }}</div>
                <div class="firma-dato">C.C. No. {{ $repDoc }} de {{ $repCiudad }}, Norte de Santander.</div>
                <div class="firma-dato">{{ $empresaNombre }}</div>
                <div class="firma-dato">Gerente/ Representante Legal.</div>
            </td>

            <td width="4%"></td>

            {{-- Arrendatario --}}
            <td class="firma-col">
                <div class="firma-linea"></div>
                <div class="firma-nombre">{{ $arrNombre }}</div>
                <div class="firma-dato">C.C. N°{{ $arrDoc }} de {{ $arrLugar }}.</div>
                <div class="firma-dato">&nbsp;</div>
            </td>

            @foreach($contract->thirds as $t)
            <td width="4%"></td>
            <td class="firma-col">
                <div class="firma-linea"></div>
                <div class="firma-nombre">{{ mb_strtoupper($t->third?->nombre_completo ?? '', 'UTF-8') }}</div>
                <div class="firma-dato">C.C. N° {{ number_format((float)($t->third?->numero_documento ?? 0), 0, ',', '.') }}{{ $t->ciudad_expedicion_doc ? ' de ' . $t->ciudad_expedicion_doc : '' }}</div>
                <div class="firma-dato">{{ ucfirst(str_replace('_',' ',$t->rol)) }}</div>
            </td>
            @endforeach
        </tr>
    </table>
</div>

</body>
</html>