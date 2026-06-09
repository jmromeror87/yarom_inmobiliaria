<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    @page { margin: 2cm 2.2cm 2.5cm; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 9.5pt; color: #1a1a1a; line-height: 1.65; }

    .footer {
        position: fixed; bottom: -1.8cm; left: 0; right: 0;
        text-align: center; font-size: 7pt; color: #666;
        border-top: 0.5pt solid #ccc; padding-top: 3pt; font-style: italic;
    }

    /* ── Encabezado ── */
    .header { text-align: center; margin-bottom: 18pt; border-bottom: 1.5pt solid #1e3a8a; padding-bottom: 10pt; }
    .logo-row { display: flex; align-items: center; justify-content: center; margin-bottom: 6pt; }
    .logo-icon {
        width: 32pt; height: 32pt;
        background: #1e3a8a; border-radius: 6pt;
        display: inline-block; text-align: center; vertical-align: middle;
        margin-right: 8pt; line-height: 32pt;
    }
    .empresa-nombre { font-size: 14pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.05em; color: #1e3a8a; }
    .empresa-sub { font-size: 8.5pt; color: #555; margin-top: 2pt; }
    .doc-titulo { font-size: 11.5pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.04em; margin-top: 8pt; color: #1a1a1a; }
    .empresa-datos { font-size: 8pt; color: #444; margin-top: 4pt; line-height: 1.5; }

    /* ── Secciones ── */
    .seccion { margin-bottom: 14pt; }
    .seccion-titulo {
        font-size: 9pt; font-weight: bold; text-transform: uppercase;
        letter-spacing: 0.06em; color: #fff;
        background: #1e3a8a; padding: 3pt 8pt;
        margin-bottom: 7pt; border-radius: 2pt;
    }
    .seccion p { margin: 0 0 5pt 0; text-align: justify; }

    /* ── Lista numerada ── */
    ol { margin: 0 0 0 14pt; padding: 0; }
    ol li { margin-bottom: 3pt; text-align: justify; }

    /* ── Lista derechos ── */
    ul { margin: 0 0 0 14pt; padding: 0; }
    ul li { margin-bottom: 2pt; }

    /* ── Firma ── */
    .firma-section { margin-top: 20pt; }
    .firma-grid { display: table; width: 100%; }
    .firma-col { display: table-cell; width: 50%; padding-right: 10pt; vertical-align: top; }
    .firma-linea { border-bottom: 0.8pt solid #333; margin-top: 28pt; margin-bottom: 3pt; }
    .firma-label { font-size: 8pt; color: #444; }
    .campo-firma {
        border-bottom: 0.8pt solid #333;
        min-height: 14pt; margin-bottom: 3pt; margin-top: 6pt;
        font-size: 9.5pt;
    }
    .campo-label { font-size: 8pt; color: #555; margin-bottom: 1pt; }

    /* ── Casilla aceptación ── */
    .checkbox-row { display: flex; align-items: flex-start; margin-top: 8pt; }
    .checkbox-box {
        width: 10pt; height: 10pt; border: 1pt solid #333;
        display: inline-block; flex-shrink: 0; margin-right: 6pt; margin-top: 1pt;
        @if($third && $third->habeas_data_aceptado) background: #1e3a8a; @endif
    }
    .check-mark { color: #fff; font-size: 9pt; font-weight: bold; text-align: center; line-height: 10pt; }

    .alerta { background: #fef3c7; border: 0.8pt solid #d97706; border-radius: 3pt; padding: 5pt 8pt; font-size: 8.5pt; margin-top: 8pt; }
</style>
</head>
<body>

<div class="footer">
    INMOBILIARIA SERVIARRENDAR LTDA · NIT: 807.005.762-0 · Cra 13 # 11-15 Of. 103, Ocaña · serviarrendarltda@gmail.com
</div>

{{-- ENCABEZADO --}}
<div class="header">
    <div class="empresa-nombre">INMOBILIARIA SERVIARRENDAR LTDA</div>
    <div class="empresa-sub">NIT: 807.005.762-0 · Matrícula Arrendador N° 002</div>
    <div class="doc-titulo">Autorización para el Tratamiento de Datos Personales</div>
    <div class="empresa-datos">
        Cra 13 # 11-15 Of. 103, Ocaña, Norte de Santander &nbsp;|&nbsp;
        Tel: +57 318 693 4710 &nbsp;|&nbsp; serviarrendarltda@gmail.com
    </div>
</div>

{{-- IDENTIFICACIÓN EMPRESA --}}
<div class="seccion">
    <div class="seccion-titulo">Responsable del Tratamiento</div>
    <p>
        <strong>INMOBILIARIA SERVIARRENDAR LTDA</strong>, identificada con NIT 807.005.762-0,
        con domicilio en Carrera 13 # 11-15, Oficina 103, ciudad de Ocaña, Norte de Santander.
        Representante Legal: <strong>Yaneth del Carmen Pérez Arévalo</strong>, C.C. 37.321.359.
    </p>
</div>

{{-- AUTORIZACIÓN --}}
<div class="seccion">
    <div class="seccion-titulo">Autorización</div>
    <p>
        De conformidad con lo dispuesto en la <strong>Ley 1581 de 2012</strong>, el
        <strong>Decreto 1074 de 2015</strong> y demás normas que regulan la protección de datos
        personales en Colombia, autorizo de manera <em>libre, previa, expresa, voluntaria e
        informada</em> a INMOBILIARIA SERVIARRENDAR LTDA para recolectar, almacenar, usar,
        circular, actualizar, transmitir y, en general, tratar mis datos personales para las
        finalidades aquí descritas.
    </p>
</div>

{{-- FINALIDADES --}}
<div class="seccion">
    <div class="seccion-titulo">Finalidades del Tratamiento</div>
    <p>Los datos personales suministrados podrán ser utilizados para:</p>
    <ol>
        <li>Gestionar procesos de arrendamiento de inmuebles urbanos y rurales.</li>
        <li>Realizar estudios de arrendatarios, codeudores, propietarios y demás intervinientes.</li>
        <li>Verificar referencias personales, laborales, financieras y comerciales.</li>
        <li>Consultar y reportar información ante centrales de riesgo autorizadas por la ley.</li>
        <li>Elaborar contratos de arrendamiento, administración inmobiliaria y documentos relacionados.</li>
        <li>Gestionar pagos, cobros, facturación y obligaciones contractuales.</li>
        <li>Contactar a clientes, propietarios, arrendatarios, proveedores y terceros relacionados.</li>
        <li>Enviar información sobre inmuebles disponibles, promociones y novedades de la empresa.</li>
        <li>Atender peticiones, quejas, reclamos y solicitudes.</li>
        <li>Cumplir obligaciones legales, tributarias, administrativas y judiciales.</li>
        <li>Realizar actividades estadísticas, comerciales y de mejoramiento de servicios.</li>
        <li>Compartir información con aseguradoras, entidades financieras, abogados y proveedores
            tecnológicos cuando sea necesario para la ejecución de los servicios o por mandato legal.</li>
    </ol>
</div>

{{-- DERECHOS --}}
<div class="seccion">
    <div class="seccion-titulo">Derechos del Titular</div>
    <p>Como titular de la información, conozco que tengo derecho a:</p>
    <ul>
        <li>Conocer, actualizar y rectificar mis datos personales.</li>
        <li>Solicitar prueba de la autorización otorgada.</li>
        <li>Ser informado sobre el uso dado a mis datos.</li>
        <li>Presentar consultas y reclamos relacionados con el tratamiento de mis datos.</li>
        <li>Revocar la autorización y solicitar la supresión de mis datos cuando sea procedente.</li>
        <li>Acceder gratuitamente a mis datos personales.</li>
    </ul>
</div>

{{-- CANALES --}}
<div class="seccion">
    <div class="seccion-titulo">Canales de Atención</div>
    <p>Para ejercer sus derechos puede comunicarse con INMOBILIARIA SERVIARRENDAR LTDA a través de:</p>
    <ul>
        <li><strong>Correo electrónico:</strong> serviarrendarltda@gmail.com</li>
        <li><strong>Teléfono:</strong> +57 318 693 4710</li>
        <li><strong>Dirección:</strong> Carrera 13 # 11-15, Oficina 103, Ocaña, Norte de Santander</li>
    </ul>
</div>

{{-- DECLARACIÓN --}}
<div class="seccion">
    <div class="seccion-titulo">Declaración del Titular</div>
    <p>
        Declaro que he sido informado(a) sobre la Política de Tratamiento de Datos Personales de
        INMOBILIARIA SERVIARRENDAR LTDA, que conozco mis derechos como titular de la información
        y que <strong>autorizo el tratamiento de mis datos personales</strong> para las finalidades
        anteriormente descritas.
    </p>
    <p>
        Asimismo, autorizo el envío de comunicaciones relacionadas con los servicios inmobiliarios
        a través de llamadas telefónicas, mensajes de texto (SMS), WhatsApp, correo electrónico y
        demás medios electrónicos permitidos por la ley.
    </p>

    <div class="checkbox-row">
        <div class="checkbox-box">
            @if($third && $third->habeas_data_aceptado)
                <div class="check-mark">✓</div>
            @endif
        </div>
        <span style="font-size:9pt;">
            <strong>SÍ AUTORIZO</strong> el tratamiento de mis datos personales conforme a lo
            descrito en el presente documento.
        </span>
    </div>
</div>

{{-- DATOS FIRMA --}}
<div class="firma-section">
    <div class="seccion-titulo">Datos e Identificación del Titular</div>

    <div style="margin-top:8pt;">
        <div class="campo-label">Nombre completo (según cédula, sin abreviaturas):</div>
        <div class="campo-firma">{{ $third?->nombre_completo ?? '' }}</div>

        <div style="display:table;width:100%;margin-top:4pt;">
            <div style="display:table-cell;width:50%;padding-right:8pt;">
                <div class="campo-label">Tipo de documento:</div>
                <div class="campo-firma">{{ $third?->tipo_documento ?? '' }}</div>
            </div>
            <div style="display:table-cell;width:50%;">
                <div class="campo-label">Número de documento:</div>
                <div class="campo-firma">{{ $third?->numero_documento ?? '' }}</div>
            </div>
        </div>

        <div style="display:table;width:100%;margin-top:4pt;">
            <div style="display:table-cell;width:50%;padding-right:8pt;">
                <div class="campo-label">Lugar de expedición:</div>
                <div class="campo-firma">{{ $third?->lugar_expedicion ?? '' }}</div>
            </div>
            <div style="display:table-cell;width:50%;">
                <div class="campo-label">Fecha de expedición:</div>
                <div class="campo-firma">{{ $third?->fecha_expedicion?->format('d/m/Y') ?? '' }}</div>
            </div>
        </div>

        <div style="margin-top:4pt;">
            <div class="campo-label">Dirección de residencia:</div>
            <div class="campo-firma">{{ $third?->direccion_residencia ?? '' }}</div>
        </div>

        <div style="display:table;width:100%;margin-top:4pt;">
            <div style="display:table-cell;width:50%;padding-right:8pt;">
                <div class="campo-label">Teléfono / Celular:</div>
                <div class="campo-firma">{{ $third?->celular ?? '' }}</div>
            </div>
            <div style="display:table-cell;width:50%;">
                <div class="campo-label">Correo electrónico:</div>
                <div class="campo-firma">{{ $third?->email ?? '' }}</div>
            </div>
        </div>
    </div>

    {{-- Firma y fecha --}}
    <div style="display:table;width:100%;margin-top:22pt;">
        <div style="display:table-cell;width:55%;padding-right:20pt;vertical-align:bottom;">
            <div class="firma-linea"></div>
            <div class="firma-label">Firma del titular</div>
        </div>
        <div style="display:table-cell;width:45%;vertical-align:bottom;">
            <div style="display:table;width:100%;">
                <div style="display:table-cell;width:33%;text-align:center;padding:0 4pt;">
                    <div class="firma-linea"></div>
                    <div class="firma-label">Día</div>
                </div>
                <div style="display:table-cell;width:34%;text-align:center;padding:0 4pt;">
                    <div class="firma-linea"></div>
                    <div class="firma-label">Mes</div>
                </div>
                <div style="display:table-cell;width:33%;text-align:center;padding:0 4pt;">
                    <div class="firma-linea"></div>
                    <div class="firma-label">Año</div>
                </div>
            </div>
            <div class="firma-label" style="text-align:center;margin-top:2pt;">Fecha</div>
        </div>
    </div>

    <div class="alerta" style="margin-top:14pt;">
        ⚠️ Este documento tiene validez legal una vez sea firmado por el titular. La inmobiliaria
        conservará una copia física o digital según el método de firma indicado. Ref.: Ley 1581/2012 —
        Decreto 1074/2015.
    </div>
</div>

</body>
</html>
