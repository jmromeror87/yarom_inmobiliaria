<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    @page { margin: 1.8cm 2.2cm 2cm; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 10pt; color: #000; line-height: 1.65; }
    .header { text-align: center; margin-bottom: 18pt; border-bottom: 1.5pt solid #000; padding-bottom: 10pt; }
    .header-empresa { font-size: 13pt; font-weight: bold; }
    .header-datos { font-size: 8pt; color: #333; margin-top: 3pt; }
    .intro { font-size: 10pt; text-align: justify; margin-bottom: 14pt; line-height: 1.7; }
    .clausula { margin-bottom: 12pt; page-break-inside: avoid; }
    .clausula-titulo { font-weight: bold; font-size: 10pt; text-transform: uppercase; margin-bottom: 3pt; }
    .clausula-body { font-size: 10pt; text-align: justify; line-height: 1.7; }
    .clausula-editada { border-left: 2pt solid #888; padding-left: 6pt; }
    .firmas-section { margin-top: 36pt; page-break-inside: avoid; }
    .firma-fecha { font-size: 10pt; margin-bottom: 36pt; }
    .firmas-tabla { width: 100%; }
    .firma-celda { width: 48%; vertical-align: bottom; }
    .firma-linea { border-top: 1pt solid #000; margin-bottom: 4pt; padding-top: 4pt; }
    .firma-nombre { font-weight: bold; font-size: 10pt; }
    .firma-cargo { font-size: 9.5pt; }
    .footer { position: fixed; bottom: -1.5cm; left: 0; right: 0; text-align: center; font-size: 7pt; color: #666; border-top: 0.5pt solid #ccc; padding-top: 3pt; }
    .nota { font-size: 9pt; font-style: italic; margin-top: 10pt; }
</style>
</head>
<body>

<div class="footer">
    {{ $empresa?->razon_social ?? 'Inmobiliaria Serviarrendar S.A.S' }} — NIT {{ $empresa?->nit_completo ?? '807.005.762-4' }} |
    {{ $empresa?->direccion ?? 'Carrera 13 # 11-15 Ofc 103' }}, Ocaña Norte de Santander |
    {{ $empresa?->email ?? 'serviarrendarltda@gmail.com' }}
</div>

<div class="header">
    @if($logoBase64)
    <div style="margin-bottom:8pt;text-align:center;">
       @if($logoBase64)
<div style="text-align:center;margin-bottom:8pt;">
    <img src="{{ $logoBase64 }}" style="max-height:55pt;max-width:180pt;">
</div>
@endif
    </div>
    @endif
    <div class="header-empresa">CONTRATO DE ADMINISTRACION DE INMUEBLE DESTINADO A {{ mb_strtoupper($contrato->property->tipo?->nombre ?? 'INMUEBLE', 'UTF-8') }}</div>
    <div class="header-datos">{{ mb_strtoupper($empresa?->razon_social ?? 'INMOBILIARIA SERVIARRENDAR SAS', 'UTF-8') }} · NIT {{ $empresa?->nit_completo ?? '807.005.762-4' }}</div>
    <div class="header-datos">{{ $empresa?->direccion ?? 'Carrera 13 # 11-15 Ofc 103 Centro' }} · {{ $empresa?->municipio?->nombre ?? 'Ocaña' }}, {{ $empresa?->departamento?->nombre ?? 'Norte de Santander' }}</div>
</div>

@php
    $propNombre    = mb_strtoupper($contrato->propietario->nombre_completo, 'UTF-8');
    $repLegal      = mb_strtoupper($empresa?->rep_legal_nombre ?? 'YANETH DEL CARMEN PÉREZ ARÉVALO', 'UTF-8');
    $empresaNombre = mb_strtoupper($empresa?->razon_social ?? 'INMOBILIARIA SERVIARRENDAR SAS', 'UTF-8');
    $genero        = $contrato->propietario->genero === 'femenino' ? 'a' : 'o';
    $tipoDoc       = $contrato->propietario->tipo_documento === 'NIT' ? 'NIT' : 'cédula de ciudadanía';
    $numDoc        = number_format((float)$contrato->propietario->numero_documento, 0, ',', '.');
    $lugarExp      = $contrato->propietario->lugar_nacimiento ? 'expedida en ' . $contrato->propietario->lugar_nacimiento : '';
    $ciudad        = $empresa?->municipio?->nombre ?? 'Ocaña';
    $dpto          = $empresa?->departamento?->nombre ?? 'Norte de Santander';
    $diaFirma      = $contrato->fecha_firma ? (int)$contrato->fecha_firma->format('d') : (int)now()->format('d');
    $mesFirma      = strtolower(($contrato->fecha_firma ?? now())->translatedFormat('F'));
    $anioFirma     = ($contrato->fecha_firma ?? now())->format('Y');
    $canonLetras   = \App\Helpers\NumeroALetras::convertir((float)$contrato->canon_pactado);
    $diaLetras     = \App\Helpers\NumeroALetras::diaEnLetras($diaFirma);
    $inm = $contrato->property;
    $inmDesc = '';
    if ($inm->porcentaje_propiedad && $inm->porcentaje_propiedad < 100) $inmDesc .= $inm->porcentaje_propiedad . '% del ';
    $inmDesc .= $inm->tipo?->nombre ?? 'inmueble';
    $inmDesc .= ' ubicado en ' . $inm->direccion;
    if ($inm->conjunto_edificio) $inmDesc .= ', ' . $inm->conjunto_edificio;
    if ($inm->apto_casa_oficina) $inmDesc .= ', ' . $inm->apto_casa_oficina;
    $inmDesc .= ', <strong>' . ($inm->municipio?->nombre ?? 'Ocaña') . '-' . ($inm->departamento?->nombre ?? 'Norte de Santander') . '</strong>';
    if ($inm->area_construida_m2) $inmDesc .= ', con área construida de ' . $inm->area_construida_m2 . ' M2';
    if ($inm->coeficiente_copropiedad) $inmDesc .= ', con coeficiente de copropiedad de ' . $inm->coeficiente_copropiedad . '%';
    if ($inm->escritura_ph_numero) $inmDesc .= ', con escritura de propiedad horizontal No. ' . $inm->escritura_ph_numero;
    $inmDesc .= '.';
    if ($inm->servicios_publicos) $inmDesc .= '<br><br>' . $inm->servicios_publicos;
@endphp

<div class="intro">
Entre los suscritos a saber, <strong>{{ $propNombre }}</strong>
identificad{{ $genero }} con {{ $tipoDoc }} No. {{ $numDoc }} {{ $lugarExp }},
actuando como propietari{{ $genero }} del {{ strtolower($inm->tipo?->nombre ?? 'inmueble') }}
ubicado en {{ $inm->direccion }}{{ $inm->barrio ? ', ' . $inm->barrio : '' }},
{{ $inm->municipio?->nombre ?? 'Ocaña' }} {{ $inm->departamento?->nombre ?? 'Norte de Santander' }}
y quien en adelante y para todos los efectos del presente contrato se llamará EL PROPIETARIO.
</div>

@foreach($contrato->clauses as $clausula)
@php
    $num = strtoupper($clausula->numero);
    $esPrimero    = $num === 'PRIMERO';
    $esSegundo    = $num === 'SEGUNDO';
    $esSexta      = $num === 'SEXTA';
    $esSeptima    = in_array($num, ['SÉPTIMA', 'SEPTIMA']);
    $esDeciNovena = str_contains($num, 'DÉCIMA NOVENA') || str_contains($num, 'DECIMA NOVENA');
@endphp
<div class="clausula {{ $clausula->fue_editada ? 'clausula-editada' : '' }}">
    @if($clausula->tipo === 'paragrafo')
        <div class="clausula-titulo">Parágrafo: {{ preg_replace('/^[Pp]arágrafo:\\s*/u', '', $clausula->titulo) }}.</div>
    @else
        <div class="clausula-titulo">{{ $clausula->numero }}: {{ $clausula->titulo }}. —</div>
    @endif

    @if($esPrimero)
    <div class="clausula-body">
        Confiero a la sociedad <strong>{{ $empresaNombre }}</strong>,
        con NIT {{ $empresa?->nit_completo ?? '807.005.762-4' }} con domicilio en {{ $ciudad }}
        y representada legalmente por <strong>{{ $repLegal }}</strong>
        mayor de edad y vecina de {{ $ciudad }}, identificada con la cédula de ciudadanía No.
        {{ $empresa?->rep_legal_documento ?? '37.321.359' }} expedida en {{ $ciudad }},
        poder para administrar por conducto de su departamento, los inmuebles que se identifican
        al encabezado de este documento y quien en adelante se llamara EL ADMINISTRADOR,
        se ha celebrado el presente <strong>Contrato De Administración De Inmueble</strong>,
        el cual se regirá por las disposiciones legales Art. 1973 y siguientes del Código Civil
        Art.515 y siguiente del código de comercio, Art 518,519,520,521,522,523 y 864 del código de comercio.
    </div>
    @elseif($esSegundo)
    <div class="clausula-body">
        A través del presente contrato EL PROPIETARIO entrega para su ADMINISTRACION,
        de manera real y material, AL ADMINISTRADOR el bien inmueble de su propiedad distinguido así:<br><br>
        {!! $inmDesc !!}
    </div>
    @elseif($esSexta)
    <div class="clausula-body">
        La comisión por los servicios de arrendamiento que pacte EL ADMINISTRADOR,
        de acuerdo con el presente contrato será de (<strong>{{ number_format($contrato->comision_porcentaje, 0) }}%</strong>)
        del valor del canon mensual de arrendamiento.
    </div>
    @elseif($esSeptima)
    <div class="clausula-body">
        El precio acordado con EL PROPIETARIO, por el cual se arrendarán los inmuebles será de
        <strong>{{ $canonLetras }}</strong>
        (<strong>${{ number_format((float)$contrato->canon_pactado, 2, ',', '.') }}</strong>).
    </div>
    @elseif($esDeciNovena)
    <div class="clausula-body">
        Para efectos judiciales y extra judiciales las partes se notificarán en las siguientes direcciones:
        ADMINISTRADOR: <strong>{{ $empresa?->direccion ?? 'Carrera 13 # 11-15 Ofc 103 Centro, Ocaña' }}</strong>
        o al correo electrónico {{ $empresa?->email ?? 'serviarrendarltda@gmail.com' }}.
        EL PROPIETARIO: {{ $contrato->propietario->direccion_residencia ?? $inm->direccion }}
        {{ $contrato->propietario->email ? 'o al correo electrónico ' . $contrato->propietario->email : '' }}.
        En caso de cambio de dirección, las partes se comprometen a informar a la otra en las
        instalaciones de {{ $empresa?->razon_social ?? 'la inmobiliaria Serviarrendar SAS' }}
        en {{ $ciudad }} {{ $dpto }}.
    </div>
    @else
    <div class="clausula-body">{{ $clausula->contenido_actual }}</div>
    @endif
</div>
@endforeach

@if($contrato->notas)
<div class="nota">Nota aclaratoria: {{ $contrato->notas }}</div>
@endif

<div class="firmas-section">
    <div class="firma-fecha">
        Como constancia del acuerdo de voluntades se firma el presente contrato en
        {{ $ciudad }} a los <strong>{{ $diaLetras }} ({{ $diaFirma }})</strong>
        días del mes de <strong>{{ $mesFirma }}</strong> de {{ $anioFirma }}.
    </div>
    <table class="firmas-tabla" cellpadding="0" cellspacing="0">
        <tr>
            <td class="firma-celda">
                <br><br><br>
                <div class="firma-linea"></div>
                <div class="firma-nombre">{{ $repLegal }}</div>
                <div class="firma-cargo">{{ $empresa?->razon_social ?? 'Inmobiliaria Serviarrendar S.A.S' }}</div>
                <div class="firma-cargo">Gerente/ Representante Legal.</div>
            </td>
            <td width="4%"></td>
            <td class="firma-celda">
                <br><br><br>
                <div class="firma-linea"></div>
                <div class="firma-nombre">{{ $propNombre }}</div>
                <div class="firma-cargo">C.C. No. {{ $numDoc }}{{ $contrato->propietario->lugar_nacimiento ? ' de ' . $contrato->propietario->lugar_nacimiento : '' }}</div>
                <div class="firma-cargo">Propietario</div>
            </td>
        </tr>
    </table>
</div>

</body>
</html>