<?php

namespace Database\Seeders;

use App\Models\AccountingAccount;
use Illuminate\Database\Seeder;

class PucSeeder extends Seeder
{
    public function run(): void
    {
        // nivel 1 = Clase, 2 = Grupo, 3 = Cuenta, 4 = Subcuenta
        // acepta_movimiento = true solo en nivel 4 (auxiliares)
        $cuentas = [

            // ══════════════════════════════════════════════════════
            // CLASE 1 — ACTIVOS
            // ══════════════════════════════════════════════════════
            ['codigo'=>'1',      'nombre'=>'ACTIVOS',              'nivel'=>1,'clase'=>'1','naturaleza'=>'debito'],

            // Grupo 11 – Disponible
            ['codigo'=>'11',     'nombre'=>'DISPONIBLE',           'nivel'=>2,'clase'=>'1','naturaleza'=>'debito'],
            ['codigo'=>'1105',   'nombre'=>'CAJA',                 'nivel'=>3,'clase'=>'1','naturaleza'=>'debito'],
            ['codigo'=>'110505', 'nombre'=>'Caja general',         'nivel'=>4,'clase'=>'1','naturaleza'=>'debito','acepta_movimiento'=>true],
            ['codigo'=>'110510', 'nombre'=>'Cajas menores',        'nivel'=>4,'clase'=>'1','naturaleza'=>'debito','acepta_movimiento'=>true],
            ['codigo'=>'1110',   'nombre'=>'BANCOS',               'nivel'=>3,'clase'=>'1','naturaleza'=>'debito'],
            ['codigo'=>'111005', 'nombre'=>'Bancolombia cta. cte.','nivel'=>4,'clase'=>'1','naturaleza'=>'debito','acepta_movimiento'=>true],
            ['codigo'=>'111010', 'nombre'=>'Davivienda cta. cte.', 'nivel'=>4,'clase'=>'1','naturaleza'=>'debito','acepta_movimiento'=>true],
            ['codigo'=>'111015', 'nombre'=>'Banco Agrario',        'nivel'=>4,'clase'=>'1','naturaleza'=>'debito','acepta_movimiento'=>true],
            ['codigo'=>'1115',   'nombre'=>'REMESAS EN TRÁNSITO',  'nivel'=>3,'clase'=>'1','naturaleza'=>'debito'],
            ['codigo'=>'111505', 'nombre'=>'Remesas en tránsito',  'nivel'=>4,'clase'=>'1','naturaleza'=>'debito','acepta_movimiento'=>true],

            // Grupo 12 – Inversiones
            ['codigo'=>'12',     'nombre'=>'INVERSIONES',          'nivel'=>2,'clase'=>'1','naturaleza'=>'debito'],
            ['codigo'=>'1205',   'nombre'=>'ACCIONES',             'nivel'=>3,'clase'=>'1','naturaleza'=>'debito'],
            ['codigo'=>'120505', 'nombre'=>'Acciones ordinarias',  'nivel'=>4,'clase'=>'1','naturaleza'=>'debito','acepta_movimiento'=>true],

            // Grupo 13 – Deudores
            ['codigo'=>'13',     'nombre'=>'DEUDORES',             'nivel'=>2,'clase'=>'1','naturaleza'=>'debito','requiere_tercero'=>true],
            ['codigo'=>'1305',   'nombre'=>'CLIENTES',             'nivel'=>3,'clase'=>'1','naturaleza'=>'debito','requiere_tercero'=>true],
            ['codigo'=>'130505', 'nombre'=>'Arrendatarios',        'nivel'=>4,'clase'=>'1','naturaleza'=>'debito','acepta_movimiento'=>true,'requiere_tercero'=>true],
            ['codigo'=>'130510', 'nombre'=>'Compradores',          'nivel'=>4,'clase'=>'1','naturaleza'=>'debito','acepta_movimiento'=>true,'requiere_tercero'=>true],
            ['codigo'=>'130515', 'nombre'=>'Otros clientes',       'nivel'=>4,'clase'=>'1','naturaleza'=>'debito','acepta_movimiento'=>true,'requiere_tercero'=>true],
            ['codigo'=>'1330',   'nombre'=>'ANTICIPOS Y AVANCES',  'nivel'=>3,'clase'=>'1','naturaleza'=>'debito'],
            ['codigo'=>'133005', 'nombre'=>'A proveedores',        'nivel'=>4,'clase'=>'1','naturaleza'=>'debito','acepta_movimiento'=>true,'requiere_tercero'=>true],
            ['codigo'=>'133010', 'nombre'=>'A trabajadores',       'nivel'=>4,'clase'=>'1','naturaleza'=>'debito','acepta_movimiento'=>true,'requiere_tercero'=>true],
            ['codigo'=>'1355',   'nombre'=>'ANTICIPO IMPUESTOS Y CONTRIBUCIONES','nivel'=>3,'clase'=>'1','naturaleza'=>'debito'],
            ['codigo'=>'135505', 'nombre'=>'Anticipo retención en la fuente','nivel'=>4,'clase'=>'1','naturaleza'=>'debito','acepta_movimiento'=>true],
            ['codigo'=>'135510', 'nombre'=>'Anticipo IVA',         'nivel'=>4,'clase'=>'1','naturaleza'=>'debito','acepta_movimiento'=>true],
            ['codigo'=>'1380',   'nombre'=>'DEUDORES VARIOS',      'nivel'=>3,'clase'=>'1','naturaleza'=>'debito'],
            ['codigo'=>'138005', 'nombre'=>'Depósitos en garantía','nivel'=>4,'clase'=>'1','naturaleza'=>'debito','acepta_movimiento'=>true,'requiere_tercero'=>true],

            // Grupo 15 – Propiedad, Planta y Equipo
            ['codigo'=>'15',     'nombre'=>'PROPIEDAD, PLANTA Y EQUIPO','nivel'=>2,'clase'=>'1','naturaleza'=>'debito'],
            ['codigo'=>'1504',   'nombre'=>'EQUIPO DE OFICINA',    'nivel'=>3,'clase'=>'1','naturaleza'=>'debito'],
            ['codigo'=>'150405', 'nombre'=>'Muebles y enseres',    'nivel'=>4,'clase'=>'1','naturaleza'=>'debito','acepta_movimiento'=>true],
            ['codigo'=>'1528',   'nombre'=>'EQUIPO DE CÓMPUTO',    'nivel'=>3,'clase'=>'1','naturaleza'=>'debito'],
            ['codigo'=>'152805', 'nombre'=>'Computadores y servidores','nivel'=>4,'clase'=>'1','naturaleza'=>'debito','acepta_movimiento'=>true],
            ['codigo'=>'1592',   'nombre'=>'DEPRECIACIÓN ACUMULADA','nivel'=>3,'clase'=>'1','naturaleza'=>'credito'],
            ['codigo'=>'159205', 'nombre'=>'Dep. acum. equipo de oficina','nivel'=>4,'clase'=>'1','naturaleza'=>'credito','acepta_movimiento'=>true],
            ['codigo'=>'159228', 'nombre'=>'Dep. acum. equipo de cómputo','nivel'=>4,'clase'=>'1','naturaleza'=>'credito','acepta_movimiento'=>true],

            // Grupo 17 – Diferidos (activo)
            ['codigo'=>'17',     'nombre'=>'DIFERIDOS',            'nivel'=>2,'clase'=>'1','naturaleza'=>'debito'],
            ['codigo'=>'1705',   'nombre'=>'GASTOS PAGADOS POR ANTICIPADO','nivel'=>3,'clase'=>'1','naturaleza'=>'debito'],
            ['codigo'=>'170505', 'nombre'=>'Seguros pagados anticipado','nivel'=>4,'clase'=>'1','naturaleza'=>'debito','acepta_movimiento'=>true],
            ['codigo'=>'170510', 'nombre'=>'Arrendamientos pagados anticipado','nivel'=>4,'clase'=>'1','naturaleza'=>'debito','acepta_movimiento'=>true],

            // ══════════════════════════════════════════════════════
            // CLASE 2 — PASIVOS
            // ══════════════════════════════════════════════════════
            ['codigo'=>'2',      'nombre'=>'PASIVOS',              'nivel'=>1,'clase'=>'2','naturaleza'=>'credito'],

            // Grupo 21 – Obligaciones financieras
            ['codigo'=>'21',     'nombre'=>'OBLIGACIONES FINANCIERAS','nivel'=>2,'clase'=>'2','naturaleza'=>'credito'],
            ['codigo'=>'2105',   'nombre'=>'BANCOS NACIONALES',    'nivel'=>3,'clase'=>'2','naturaleza'=>'credito'],
            ['codigo'=>'210505', 'nombre'=>'Crédito bancario',     'nivel'=>4,'clase'=>'2','naturaleza'=>'credito','acepta_movimiento'=>true,'requiere_tercero'=>true],

            // Grupo 22 – Proveedores
            ['codigo'=>'22',     'nombre'=>'PROVEEDORES',          'nivel'=>2,'clase'=>'2','naturaleza'=>'credito','requiere_tercero'=>true],
            ['codigo'=>'2205',   'nombre'=>'NACIONALES',           'nivel'=>3,'clase'=>'2','naturaleza'=>'credito','requiere_tercero'=>true],
            ['codigo'=>'220505', 'nombre'=>'Proveedores nacionales','nivel'=>4,'clase'=>'2','naturaleza'=>'credito','acepta_movimiento'=>true,'requiere_tercero'=>true],

            // Grupo 23 – Cuentas por pagar
            ['codigo'=>'23',     'nombre'=>'CUENTAS POR PAGAR',    'nivel'=>2,'clase'=>'2','naturaleza'=>'credito'],
            ['codigo'=>'2335',   'nombre'=>'COSTOS Y GASTOS POR PAGAR','nivel'=>3,'clase'=>'2','naturaleza'=>'credito'],
            ['codigo'=>'233505', 'nombre'=>'Honorarios por pagar', 'nivel'=>4,'clase'=>'2','naturaleza'=>'credito','acepta_movimiento'=>true,'requiere_tercero'=>true],
            ['codigo'=>'233510', 'nombre'=>'Arrendamientos a propietarios','nivel'=>4,'clase'=>'2','naturaleza'=>'credito','acepta_movimiento'=>true,'requiere_tercero'=>true],
            ['codigo'=>'2360',   'nombre'=>'DIVIDENDOS O PARTICIPACIONES POR PAGAR','nivel'=>3,'clase'=>'2','naturaleza'=>'credito'],
            ['codigo'=>'236005', 'nombre'=>'Dividendos por pagar', 'nivel'=>4,'clase'=>'2','naturaleza'=>'credito','acepta_movimiento'=>true],
            ['codigo'=>'2365',   'nombre'=>'RETENCIÓN EN LA FUENTE','nivel'=>3,'clase'=>'2','naturaleza'=>'credito'],
            ['codigo'=>'236505', 'nombre'=>'Retefuente servicios 4%','nivel'=>4,'clase'=>'2','naturaleza'=>'credito','acepta_movimiento'=>true],
            ['codigo'=>'236510', 'nombre'=>'Retefuente honorarios 10%','nivel'=>4,'clase'=>'2','naturaleza'=>'credito','acepta_movimiento'=>true],
            ['codigo'=>'236515', 'nombre'=>'Retefuente arrendamientos 3.5%','nivel'=>4,'clase'=>'2','naturaleza'=>'credito','acepta_movimiento'=>true],
            ['codigo'=>'236520', 'nombre'=>'Retefuente otros',     'nivel'=>4,'clase'=>'2','naturaleza'=>'credito','acepta_movimiento'=>true],
            ['codigo'=>'2367',   'nombre'=>'IVA RETENIDO',         'nivel'=>3,'clase'=>'2','naturaleza'=>'credito'],
            ['codigo'=>'236705', 'nombre'=>'IVA retenido 15%',     'nivel'=>4,'clase'=>'2','naturaleza'=>'credito','acepta_movimiento'=>true],
            ['codigo'=>'2368',   'nombre'=>'IMPUESTO INDUSTRIA Y COMERCIO RETENIDO','nivel'=>3,'clase'=>'2','naturaleza'=>'credito'],
            ['codigo'=>'236805', 'nombre'=>'Reteica Ocaña',        'nivel'=>4,'clase'=>'2','naturaleza'=>'credito','acepta_movimiento'=>true],

            // Grupo 24 – Impuestos, gravámenes y tasas
            ['codigo'=>'24',     'nombre'=>'IMPUESTOS, GRAVÁMENES Y TASAS','nivel'=>2,'clase'=>'2','naturaleza'=>'credito'],
            ['codigo'=>'2408',   'nombre'=>'IVA POR PAGAR',        'nivel'=>3,'clase'=>'2','naturaleza'=>'credito'],
            ['codigo'=>'240805', 'nombre'=>'IVA generado 19%',     'nivel'=>4,'clase'=>'2','naturaleza'=>'credito','acepta_movimiento'=>true],
            ['codigo'=>'2412',   'nombre'=>'IMPUESTO INDUSTRIA Y COMERCIO','nivel'=>3,'clase'=>'2','naturaleza'=>'credito'],
            ['codigo'=>'241205', 'nombre'=>'ICA por pagar',        'nivel'=>4,'clase'=>'2','naturaleza'=>'credito','acepta_movimiento'=>true],
            ['codigo'=>'2416',   'nombre'=>'IMPUESTO SOBRE LAS VENTAS','nivel'=>3,'clase'=>'2','naturaleza'=>'credito'],
            ['codigo'=>'241605', 'nombre'=>'IVA descontable',      'nivel'=>4,'clase'=>'2','naturaleza'=>'debito','acepta_movimiento'=>true],

            // Grupo 25 – Obligaciones laborales
            ['codigo'=>'25',     'nombre'=>'OBLIGACIONES LABORALES','nivel'=>2,'clase'=>'2','naturaleza'=>'credito'],
            ['codigo'=>'2505',   'nombre'=>'SALARIOS POR PAGAR',   'nivel'=>3,'clase'=>'2','naturaleza'=>'credito'],
            ['codigo'=>'250505', 'nombre'=>'Nómina por pagar',     'nivel'=>4,'clase'=>'2','naturaleza'=>'credito','acepta_movimiento'=>true],
            ['codigo'=>'2510',   'nombre'=>'CESANTÍAS CONSOLIDADAS','nivel'=>3,'clase'=>'2','naturaleza'=>'credito'],
            ['codigo'=>'251005', 'nombre'=>'Cesantías',            'nivel'=>4,'clase'=>'2','naturaleza'=>'credito','acepta_movimiento'=>true],
            ['codigo'=>'2515',   'nombre'=>'INTERESES SOBRE CESANTÍAS','nivel'=>3,'clase'=>'2','naturaleza'=>'credito'],
            ['codigo'=>'251505', 'nombre'=>'Intereses cesantías',  'nivel'=>4,'clase'=>'2','naturaleza'=>'credito','acepta_movimiento'=>true],
            ['codigo'=>'2520',   'nombre'=>'PRIMA DE SERVICIOS',   'nivel'=>3,'clase'=>'2','naturaleza'=>'credito'],
            ['codigo'=>'252005', 'nombre'=>'Prima de servicios',   'nivel'=>4,'clase'=>'2','naturaleza'=>'credito','acepta_movimiento'=>true],
            ['codigo'=>'2525',   'nombre'=>'VACACIONES CONSOLIDADAS','nivel'=>3,'clase'=>'2','naturaleza'=>'credito'],
            ['codigo'=>'252505', 'nombre'=>'Vacaciones',           'nivel'=>4,'clase'=>'2','naturaleza'=>'credito','acepta_movimiento'=>true],

            // Grupo 26 – Pasivos estimados
            ['codigo'=>'26',     'nombre'=>'PASIVOS ESTIMADOS Y PROVISIONES','nivel'=>2,'clase'=>'2','naturaleza'=>'credito'],
            ['codigo'=>'2610',   'nombre'=>'PARA OBLIGACIONES LABORALES','nivel'=>3,'clase'=>'2','naturaleza'=>'credito'],
            ['codigo'=>'261005', 'nombre'=>'Provisión prestaciones sociales','nivel'=>4,'clase'=>'2','naturaleza'=>'credito','acepta_movimiento'=>true],

            // Grupo 28 – Otros pasivos
            ['codigo'=>'28',     'nombre'=>'OTROS PASIVOS',        'nivel'=>2,'clase'=>'2','naturaleza'=>'credito'],
            ['codigo'=>'2815',   'nombre'=>'DEPÓSITOS RECIBIDOS',  'nivel'=>3,'clase'=>'2','naturaleza'=>'credito'],
            ['codigo'=>'281505', 'nombre'=>'Depósitos de arrendatarios','nivel'=>4,'clase'=>'2','naturaleza'=>'credito','acepta_movimiento'=>true,'requiere_tercero'=>true],
            ['codigo'=>'2820',   'nombre'=>'INGRESOS RECIBIDOS POR ANTICIPADO','nivel'=>3,'clase'=>'2','naturaleza'=>'credito'],
            ['codigo'=>'282005', 'nombre'=>'Arrendamientos anticipados','nivel'=>4,'clase'=>'2','naturaleza'=>'credito','acepta_movimiento'=>true,'requiere_tercero'=>true],

            // ══════════════════════════════════════════════════════
            // CLASE 3 — PATRIMONIO
            // ══════════════════════════════════════════════════════
            ['codigo'=>'3',      'nombre'=>'PATRIMONIO',           'nivel'=>1,'clase'=>'3','naturaleza'=>'credito'],
            ['codigo'=>'31',     'nombre'=>'CAPITAL SOCIAL',       'nivel'=>2,'clase'=>'3','naturaleza'=>'credito'],
            ['codigo'=>'3105',   'nombre'=>'CAPITAL SUSCRITO Y PAGADO','nivel'=>3,'clase'=>'3','naturaleza'=>'credito'],
            ['codigo'=>'310505', 'nombre'=>'Capital suscrito y pagado','nivel'=>4,'clase'=>'3','naturaleza'=>'credito','acepta_movimiento'=>true],
            ['codigo'=>'33',     'nombre'=>'RESERVAS',             'nivel'=>2,'clase'=>'3','naturaleza'=>'credito'],
            ['codigo'=>'3305',   'nombre'=>'RESERVA LEGAL',        'nivel'=>3,'clase'=>'3','naturaleza'=>'credito'],
            ['codigo'=>'330505', 'nombre'=>'Reserva legal',        'nivel'=>4,'clase'=>'3','naturaleza'=>'credito','acepta_movimiento'=>true],
            ['codigo'=>'36',     'nombre'=>'RESULTADOS DEL EJERCICIO','nivel'=>2,'clase'=>'3','naturaleza'=>'credito'],
            ['codigo'=>'3605',   'nombre'=>'UTILIDAD DEL EJERCICIO','nivel'=>3,'clase'=>'3','naturaleza'=>'credito'],
            ['codigo'=>'360505', 'nombre'=>'Utilidad del ejercicio','nivel'=>4,'clase'=>'3','naturaleza'=>'credito','acepta_movimiento'=>true],
            ['codigo'=>'3610',   'nombre'=>'PÉRDIDA DEL EJERCICIO','nivel'=>3,'clase'=>'3','naturaleza'=>'debito'],
            ['codigo'=>'361005', 'nombre'=>'Pérdida del ejercicio','nivel'=>4,'clase'=>'3','naturaleza'=>'debito','acepta_movimiento'=>true],
            ['codigo'=>'37',     'nombre'=>'RESULTADOS EJERCICIOS ANTERIORES','nivel'=>2,'clase'=>'3','naturaleza'=>'credito'],
            ['codigo'=>'3705',   'nombre'=>'UTILIDADES ACUMULADAS','nivel'=>3,'clase'=>'3','naturaleza'=>'credito'],
            ['codigo'=>'370505', 'nombre'=>'Utilidades acumuladas','nivel'=>4,'clase'=>'3','naturaleza'=>'credito','acepta_movimiento'=>true],
            ['codigo'=>'3710',   'nombre'=>'PÉRDIDAS ACUMULADAS',  'nivel'=>3,'clase'=>'3','naturaleza'=>'debito'],
            ['codigo'=>'371005', 'nombre'=>'Pérdidas acumuladas',  'nivel'=>4,'clase'=>'3','naturaleza'=>'debito','acepta_movimiento'=>true],

            // ══════════════════════════════════════════════════════
            // CLASE 4 — INGRESOS
            // ══════════════════════════════════════════════════════
            ['codigo'=>'4',      'nombre'=>'INGRESOS',             'nivel'=>1,'clase'=>'4','naturaleza'=>'credito'],
            ['codigo'=>'41',     'nombre'=>'OPERACIONALES',        'nivel'=>2,'clase'=>'4','naturaleza'=>'credito'],
            ['codigo'=>'4135',   'nombre'=>'SERVICIOS',            'nivel'=>3,'clase'=>'4','naturaleza'=>'credito'],
            ['codigo'=>'413505', 'nombre'=>'Comisión de arrendamiento','nivel'=>4,'clase'=>'4','naturaleza'=>'credito','acepta_movimiento'=>true],
            ['codigo'=>'413510', 'nombre'=>'Administración de inmuebles','nivel'=>4,'clase'=>'4','naturaleza'=>'credito','acepta_movimiento'=>true],
            ['codigo'=>'413515', 'nombre'=>'Comisión de ventas',   'nivel'=>4,'clase'=>'4','naturaleza'=>'credito','acepta_movimiento'=>true],
            ['codigo'=>'413520', 'nombre'=>'Estudio de crédito',   'nivel'=>4,'clase'=>'4','naturaleza'=>'credito','acepta_movimiento'=>true],
            ['codigo'=>'413525', 'nombre'=>'Elaboración de contratos','nivel'=>4,'clase'=>'4','naturaleza'=>'credito','acepta_movimiento'=>true],
            ['codigo'=>'413530', 'nombre'=>'Otros servicios inmobiliarios','nivel'=>4,'clase'=>'4','naturaleza'=>'credito','acepta_movimiento'=>true],
            ['codigo'=>'42',     'nombre'=>'NO OPERACIONALES',     'nivel'=>2,'clase'=>'4','naturaleza'=>'credito'],
            ['codigo'=>'4210',   'nombre'=>'FINANCIEROS',          'nivel'=>3,'clase'=>'4','naturaleza'=>'credito'],
            ['codigo'=>'421005', 'nombre'=>'Intereses bancarios',  'nivel'=>4,'clase'=>'4','naturaleza'=>'credito','acepta_movimiento'=>true],
            ['codigo'=>'421010', 'nombre'=>'Intereses de mora',    'nivel'=>4,'clase'=>'4','naturaleza'=>'credito','acepta_movimiento'=>true],
            ['codigo'=>'4215',   'nombre'=>'ARRENDAMIENTOS',       'nivel'=>3,'clase'=>'4','naturaleza'=>'credito'],
            ['codigo'=>'421505', 'nombre'=>'Arrendamiento oficina','nivel'=>4,'clase'=>'4','naturaleza'=>'credito','acepta_movimiento'=>true],
            ['codigo'=>'4295',   'nombre'=>'DIVERSOS',             'nivel'=>3,'clase'=>'4','naturaleza'=>'credito'],
            ['codigo'=>'429505', 'nombre'=>'Recuperaciones',       'nivel'=>4,'clase'=>'4','naturaleza'=>'credito','acepta_movimiento'=>true],
            ['codigo'=>'429510', 'nombre'=>'Ingresos varios',      'nivel'=>4,'clase'=>'4','naturaleza'=>'credito','acepta_movimiento'=>true],

            // ══════════════════════════════════════════════════════
            // CLASE 5 — GASTOS
            // ══════════════════════════════════════════════════════
            ['codigo'=>'5',      'nombre'=>'GASTOS',               'nivel'=>1,'clase'=>'5','naturaleza'=>'debito'],
            ['codigo'=>'51',     'nombre'=>'OPERACIONALES DE ADMINISTRACIÓN','nivel'=>2,'clase'=>'5','naturaleza'=>'debito'],
            ['codigo'=>'5105',   'nombre'=>'GASTOS DE PERSONAL',   'nivel'=>3,'clase'=>'5','naturaleza'=>'debito'],
            ['codigo'=>'510505', 'nombre'=>'Sueldos',              'nivel'=>4,'clase'=>'5','naturaleza'=>'debito','acepta_movimiento'=>true],
            ['codigo'=>'510510', 'nombre'=>'Horas extras y recargos','nivel'=>4,'clase'=>'5','naturaleza'=>'debito','acepta_movimiento'=>true],
            ['codigo'=>'510515', 'nombre'=>'Auxilio de transporte','nivel'=>4,'clase'=>'5','naturaleza'=>'debito','acepta_movimiento'=>true],
            ['codigo'=>'510520', 'nombre'=>'Cesantías',            'nivel'=>4,'clase'=>'5','naturaleza'=>'debito','acepta_movimiento'=>true],
            ['codigo'=>'510525', 'nombre'=>'Intereses sobre cesantías','nivel'=>4,'clase'=>'5','naturaleza'=>'debito','acepta_movimiento'=>true],
            ['codigo'=>'510530', 'nombre'=>'Prima de servicios',   'nivel'=>4,'clase'=>'5','naturaleza'=>'debito','acepta_movimiento'=>true],
            ['codigo'=>'510535', 'nombre'=>'Vacaciones',           'nivel'=>4,'clase'=>'5','naturaleza'=>'debito','acepta_movimiento'=>true],
            ['codigo'=>'5110',   'nombre'=>'HONORARIOS',           'nivel'=>3,'clase'=>'5','naturaleza'=>'debito'],
            ['codigo'=>'511005', 'nombre'=>'Honorarios contadores','nivel'=>4,'clase'=>'5','naturaleza'=>'debito','acepta_movimiento'=>true,'requiere_tercero'=>true],
            ['codigo'=>'511010', 'nombre'=>'Honorarios abogados',  'nivel'=>4,'clase'=>'5','naturaleza'=>'debito','acepta_movimiento'=>true,'requiere_tercero'=>true],
            ['codigo'=>'511015', 'nombre'=>'Honorarios revisores', 'nivel'=>4,'clase'=>'5','naturaleza'=>'debito','acepta_movimiento'=>true,'requiere_tercero'=>true],
            ['codigo'=>'5115',   'nombre'=>'IMPUESTOS',            'nivel'=>3,'clase'=>'5','naturaleza'=>'debito'],
            ['codigo'=>'511505', 'nombre'=>'Impuesto de industria y comercio','nivel'=>4,'clase'=>'5','naturaleza'=>'debito','acepta_movimiento'=>true],
            ['codigo'=>'511510', 'nombre'=>'Impuesto predial',     'nivel'=>4,'clase'=>'5','naturaleza'=>'debito','acepta_movimiento'=>true],
            ['codigo'=>'511515', 'nombre'=>'GMF (4×1000)',         'nivel'=>4,'clase'=>'5','naturaleza'=>'debito','acepta_movimiento'=>true],
            ['codigo'=>'5120',   'nombre'=>'ARRENDAMIENTOS',       'nivel'=>3,'clase'=>'5','naturaleza'=>'debito'],
            ['codigo'=>'512005', 'nombre'=>'Arrendamiento oficina','nivel'=>4,'clase'=>'5','naturaleza'=>'debito','acepta_movimiento'=>true,'requiere_tercero'=>true],
            ['codigo'=>'5130',   'nombre'=>'SEGUROS',              'nivel'=>3,'clase'=>'5','naturaleza'=>'debito'],
            ['codigo'=>'513005', 'nombre'=>'Seguros de vida',      'nivel'=>4,'clase'=>'5','naturaleza'=>'debito','acepta_movimiento'=>true],
            ['codigo'=>'513010', 'nombre'=>'Seguros de bienes',    'nivel'=>4,'clase'=>'5','naturaleza'=>'debito','acepta_movimiento'=>true],
            ['codigo'=>'5135',   'nombre'=>'SERVICIOS',            'nivel'=>3,'clase'=>'5','naturaleza'=>'debito'],
            ['codigo'=>'513505', 'nombre'=>'Energía eléctrica',    'nivel'=>4,'clase'=>'5','naturaleza'=>'debito','acepta_movimiento'=>true],
            ['codigo'=>'513510', 'nombre'=>'Acueducto y alcantarillado','nivel'=>4,'clase'=>'5','naturaleza'=>'debito','acepta_movimiento'=>true],
            ['codigo'=>'513515', 'nombre'=>'Teléfono e internet',  'nivel'=>4,'clase'=>'5','naturaleza'=>'debito','acepta_movimiento'=>true],
            ['codigo'=>'513520', 'nombre'=>'Correo y mensajería',  'nivel'=>4,'clase'=>'5','naturaleza'=>'debito','acepta_movimiento'=>true],
            ['codigo'=>'513525', 'nombre'=>'Aseo y vigilancia',    'nivel'=>4,'clase'=>'5','naturaleza'=>'debito','acepta_movimiento'=>true],
            ['codigo'=>'5140',   'nombre'=>'GASTOS LEGALES',       'nivel'=>3,'clase'=>'5','naturaleza'=>'debito'],
            ['codigo'=>'514005', 'nombre'=>'Notariales y registro','nivel'=>4,'clase'=>'5','naturaleza'=>'debito','acepta_movimiento'=>true],
            ['codigo'=>'514010', 'nombre'=>'Cámara de comercio',   'nivel'=>4,'clase'=>'5','naturaleza'=>'debito','acepta_movimiento'=>true],
            ['codigo'=>'514015', 'nombre'=>'Trámites y licencias', 'nivel'=>4,'clase'=>'5','naturaleza'=>'debito','acepta_movimiento'=>true],
            ['codigo'=>'5145',   'nombre'=>'MANTENIMIENTO Y REPARACIONES','nivel'=>3,'clase'=>'5','naturaleza'=>'debito'],
            ['codigo'=>'514505', 'nombre'=>'Mantenimiento equipos','nivel'=>4,'clase'=>'5','naturaleza'=>'debito','acepta_movimiento'=>true],
            ['codigo'=>'514510', 'nombre'=>'Reparaciones locativas','nivel'=>4,'clase'=>'5','naturaleza'=>'debito','acepta_movimiento'=>true],
            ['codigo'=>'5195',   'nombre'=>'DIVERSOS',             'nivel'=>3,'clase'=>'5','naturaleza'=>'debito'],
            ['codigo'=>'519505', 'nombre'=>'Útiles y papelería',   'nivel'=>4,'clase'=>'5','naturaleza'=>'debito','acepta_movimiento'=>true],
            ['codigo'=>'519510', 'nombre'=>'Publicidad y propaganda','nivel'=>4,'clase'=>'5','naturaleza'=>'debito','acepta_movimiento'=>true],
            ['codigo'=>'519515', 'nombre'=>'Gastos de representación','nivel'=>4,'clase'=>'5','naturaleza'=>'debito','acepta_movimiento'=>true],
            ['codigo'=>'519520', 'nombre'=>'Gastos de viaje',      'nivel'=>4,'clase'=>'5','naturaleza'=>'debito','acepta_movimiento'=>true],
            ['codigo'=>'519525', 'nombre'=>'Otros gastos',         'nivel'=>4,'clase'=>'5','naturaleza'=>'debito','acepta_movimiento'=>true],
            ['codigo'=>'5199',   'nombre'=>'PROVISIONES',          'nivel'=>3,'clase'=>'5','naturaleza'=>'debito'],
            ['codigo'=>'519905', 'nombre'=>'Provisión deudores',   'nivel'=>4,'clase'=>'5','naturaleza'=>'debito','acepta_movimiento'=>true],

            // Clase 5 – Gastos no operacionales
            ['codigo'=>'53',     'nombre'=>'NO OPERACIONALES',     'nivel'=>2,'clase'=>'5','naturaleza'=>'debito'],
            ['codigo'=>'5305',   'nombre'=>'FINANCIEROS',          'nivel'=>3,'clase'=>'5','naturaleza'=>'debito'],
            ['codigo'=>'530505', 'nombre'=>'Gastos bancarios',     'nivel'=>4,'clase'=>'5','naturaleza'=>'debito','acepta_movimiento'=>true],
            ['codigo'=>'530510', 'nombre'=>'Comisiones bancarias', 'nivel'=>4,'clase'=>'5','naturaleza'=>'debito','acepta_movimiento'=>true],
            ['codigo'=>'530515', 'nombre'=>'Intereses bancarios',  'nivel'=>4,'clase'=>'5','naturaleza'=>'debito','acepta_movimiento'=>true],
            ['codigo'=>'5395',   'nombre'=>'GASTOS EXTRAORDINARIOS','nivel'=>3,'clase'=>'5','naturaleza'=>'debito'],
            ['codigo'=>'539505', 'nombre'=>'Pérdidas en ventas de activos','nivel'=>4,'clase'=>'5','naturaleza'=>'debito','acepta_movimiento'=>true],
            ['codigo'=>'539510', 'nombre'=>'Multas y sanciones',   'nivel'=>4,'clase'=>'5','naturaleza'=>'debito','acepta_movimiento'=>true],

            // Impuesto de renta
            ['codigo'=>'54',     'nombre'=>'IMPUESTO DE RENTA Y COMPLEMENTARIOS','nivel'=>2,'clase'=>'5','naturaleza'=>'debito'],
            ['codigo'=>'5405',   'nombre'=>'IMPUESTO DE RENTA Y COMPLEMENTARIOS','nivel'=>3,'clase'=>'5','naturaleza'=>'debito'],
            ['codigo'=>'540505', 'nombre'=>'Impuesto de renta corriente','nivel'=>4,'clase'=>'5','naturaleza'=>'debito','acepta_movimiento'=>true],

            // ══════════════════════════════════════════════════════
            // CLASE 6 — COSTOS DE PRODUCCIÓN (no aplica para inmobiliaria)
            ['codigo'=>'6',      'nombre'=>'COSTOS DE PRODUCCIÓN', 'nivel'=>1,'clase'=>'6','naturaleza'=>'debito'],

            // ══════════════════════════════════════════════════════
            // CLASE 7 — COSTOS DE VENTAS
            // ══════════════════════════════════════════════════════
            ['codigo'=>'7',      'nombre'=>'COSTOS DE VENTAS Y DE PRESTACIÓN DE SERVICIOS','nivel'=>1,'clase'=>'7','naturaleza'=>'debito'],
            ['codigo'=>'71',     'nombre'=>'COSTOS DE VENTAS',     'nivel'=>2,'clase'=>'7','naturaleza'=>'debito'],
            ['codigo'=>'7105',   'nombre'=>'COSTOS INMOBILIARIOS', 'nivel'=>3,'clase'=>'7','naturaleza'=>'debito'],
            ['codigo'=>'710505', 'nombre'=>'Costo de captación',   'nivel'=>4,'clase'=>'7','naturaleza'=>'debito','acepta_movimiento'=>true],
            ['codigo'=>'710510', 'nombre'=>'Comisiones pagadas a asesores','nivel'=>4,'clase'=>'7','naturaleza'=>'debito','acepta_movimiento'=>true,'requiere_tercero'=>true],
        ];

        $this->insertarCuentas($cuentas);

        $this->command->info('✅ PUC colombiano sembrado: ' . AccountingAccount::count() . ' cuentas.');
    }

    private function insertarCuentas(array $cuentas): void
    {
        // Primero insertar todos sin parent_id para obtener IDs
        $idMap = [];

        foreach ($cuentas as $c) {
            $nivel  = $c['nivel'];
            $codigo = $c['codigo'];

            // Determinar parent_id por código
            $parentCodigo = null;
            if ($nivel === 2) $parentCodigo = substr($codigo, 0, 1);
            if ($nivel === 3) $parentCodigo = substr($codigo, 0, 2);
            if ($nivel === 4) $parentCodigo = substr($codigo, 0, 4);

            $cuenta = AccountingAccount::updateOrCreate(
                ['codigo' => $codigo],
                [
                    'nombre'                => $c['nombre'],
                    'nivel'                 => $nivel,
                    'clase'                 => $c['clase'],
                    'naturaleza'            => $c['naturaleza'],
                    'acepta_movimiento'     => $c['acepta_movimiento'] ?? false,
                    'requiere_tercero'      => $c['requiere_tercero'] ?? false,
                    'requiere_centro_costo' => $c['requiere_centro_costo'] ?? false,
                    'estado'                => 'activo',
                    'parent_id'             => $parentCodigo ? ($idMap[$parentCodigo] ?? null) : null,
                ]
            );

            $idMap[$codigo] = $cuenta->id;
        }
    }
}
