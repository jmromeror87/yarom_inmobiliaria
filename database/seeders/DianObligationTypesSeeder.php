<?php

namespace Database\Seeders;

use App\Models\DianObligationType;
use Illuminate\Database\Seeder;

class DianObligationTypesSeeder extends Seeder
{
    public function run(): void
    {
        $obligaciones = [
            // ── Declaraciones tributarias ──────────────────────────────────
            [
                'codigo'       => 'retefte',
                'nombre'       => 'Retención en la Fuente',
                'formulario'   => '350',
                'periodicidad' => 'mensual',
                'orden'        => 1,
                'descripcion'  => 'Declaración mensual de retenciones en la fuente practicadas a arrendatarios, proveedores y autorretenciones sobre comisiones propias. Vence entre el 7 y 21 del mes siguiente según últimos 2 dígitos del NIT.',
            ],
            [
                'codigo'       => 'reteica',
                'nombre'       => 'ReteICA',
                'formulario'   => 'ReteICA',
                'periodicidad' => 'mensual',
                'orden'        => 2,
                'descripcion'  => 'Retención del Impuesto de Industria y Comercio practicada en pagos a proveedores y arrendadores. Periodicidad y formulario según el municipio donde opera la empresa.',
            ],
            [
                'codigo'       => 'iva',
                'nombre'       => 'IVA — Impuesto sobre las Ventas',
                'formulario'   => '300',
                'periodicidad' => 'cuatrimestral',
                'orden'        => 3,
                'descripcion'  => 'Declaración cuatrimestral del IVA generado en comisiones de administración (19%). Períodos: ene-abr (vence mayo), may-ago (vence sep), sep-dic (vence ene siguiente).',
            ],
            [
                'codigo'       => 'renta',
                'nombre'       => 'Renta y Complementarios',
                'formulario'   => '110',
                'periodicidad' => 'anual',
                'orden'        => 4,
                'descripcion'  => 'Declaración anual de renta de personas jurídicas (sociedades). Se presenta entre abril y mayo del año siguiente al gravable.',
            ],
            [
                'codigo'       => 'ica',
                'nombre'       => 'ICA — Industria y Comercio',
                'formulario'   => 'D-500',
                'periodicidad' => 'anual',
                'orden'        => 5,
                'descripcion'  => 'Impuesto de Industria y Comercio municipal. Para Bogotá, formulario D-500. Base: ingresos brutos en el municipio por actividades gravadas (código CIIU inmobiliario).',
            ],

            // ── Información exógena (medios magnéticos) ───────────────────
            [
                'codigo'       => 'exogena_1001',
                'nombre'       => 'Exógena — Form. 1001: Pagos/abonos y retenciones practicadas',
                'formulario'   => '1001',
                'periodicidad' => 'anual',
                'orden'        => 6,
                'descripcion'  => 'Reporte de todos los pagos o abonos en cuenta realizados en el año a cada tercero (propietarios, proveedores) y las retenciones en la fuente practicadas, clasificados por código de concepto DIAN.',
            ],
            [
                'codigo'       => 'exogena_1003',
                'nombre'       => 'Exógena — Form. 1003: Retenciones que le practicaron al informante',
                'formulario'   => '1003',
                'periodicidad' => 'anual',
                'orden'        => 7,
                'descripcion'  => 'Retenciones en la fuente que TERCEROS le practicaron a Serviarrendar S.A.S durante el año (ej. arrendatarios jurídicos que retienen sobre el canon de arrendamiento pagado a la inmobiliaria).',
            ],
            [
                'codigo'       => 'exogena_1005',
                'nombre'       => 'Exógena — Form. 1005: IVA generado y descontable',
                'formulario'   => '1005',
                'periodicidad' => 'anual',
                'orden'        => 8,
                'descripcion'  => 'IVA generado en ventas/servicios e IVA descontable en compras, discriminado por período (bimestre o cuatrimestre) y tarifa. Basado en declaraciones del Form. 300.',
            ],
            [
                'codigo'       => 'exogena_1006',
                'nombre'       => 'Exógena — Form. 1006: Saldos en cuentas bancarias',
                'formulario'   => '1006',
                'periodicidad' => 'anual',
                'orden'        => 9,
                'descripcion'  => 'Saldos de todas las cuentas corrientes y de ahorros a 31 de diciembre del año gravable, discriminadas por entidad bancaria, número de cuenta y tipo.',
            ],
            [
                'codigo'       => 'exogena_1007',
                'nombre'       => 'Exógena — Form. 1007: Ingresos recibidos',
                'formulario'   => '1007',
                'periodicidad' => 'anual',
                'orden'        => 10,
                'descripcion'  => 'Ingresos recibidos de cada tercero durante el año (arrendatarios, otros pagadores), clasificados por código de concepto. Incluye comisiones, administración, mora y otros ingresos.',
            ],
            [
                'codigo'       => 'exogena_1008',
                'nombre'       => 'Exógena — Form. 1008: Saldo IVA por pagar o a favor',
                'formulario'   => '1008',
                'periodicidad' => 'anual',
                'orden'        => 11,
                'descripcion'  => 'Saldo del impuesto sobre las ventas (IVA) a 31 de diciembre, indicando si es saldo a pagar o saldo a favor de la empresa.',
            ],
            [
                'codigo'       => 'exogena_1009',
                'nombre'       => 'Exógena — Form. 1009: Saldo de cuentas por pagar',
                'formulario'   => '1009',
                'periodicidad' => 'anual',
                'orden'        => 12,
                'descripcion'  => 'Saldo de las cuentas por pagar a 31 de diciembre discriminado por tercero: propietarios (CxP neto a girar), proveedores, retenciones por pagar, IVA por pagar.',
            ],
            [
                'codigo'       => 'exogena_1010',
                'nombre'       => 'Exógena — Form. 1010: Saldo de cuentas por cobrar',
                'formulario'   => '1010',
                'periodicidad' => 'anual',
                'orden'        => 13,
                'descripcion'  => 'Saldo de las cuentas por cobrar a 31 de diciembre por tercero: arrendatarios con facturas pendientes, anticipos de impuestos (retenciones a favor), otras.',
            ],
            [
                'codigo'       => 'exogena_1011',
                'nombre'       => 'Exógena — Form. 1011: Socios, accionistas y/o asociados',
                'formulario'   => '1011',
                'periodicidad' => 'anual',
                'orden'        => 14,
                'descripcion'  => 'Información de socios o accionistas de Serviarrendar S.A.S: identificación, porcentaje de participación, valor del aporte y dividendos/participaciones distribuidos o decretados en el año.',
            ],
            [
                'codigo'       => 'exogena_2276',
                'nombre'       => 'Exógena — Form. 2276: Deudores de créditos activos',
                'formulario'   => '2276',
                'periodicidad' => 'anual',
                'orden'        => 15,
                'descripcion'  => 'Información de arrendatarios u otros terceros con cartera activa al 31 de diciembre: saldo de capital, intereses corrientes, intereses de mora. Aplica cuando hay cartera vencida en libros.',
            ],
            [
                'codigo'       => 'exogena_5247',
                'nombre'       => 'Exógena — Form. 5247: IVA generado y descontable por operaciones',
                'formulario'   => '5247',
                'periodicidad' => 'anual',
                'orden'        => 16,
                'descripcion'  => 'Detalle del IVA generado y descontable clasificado por tipo de operación, tarifa y período. Complementa el Form. 1005 con mayor granularidad por operación.',
            ],

            // ── Obligaciones adicionales ───────────────────────────────────
            [
                'codigo'       => 'cert_retencion',
                'nombre'       => 'Certificados de Retención en la Fuente',
                'formulario'   => 'Cert.',
                'periodicidad' => 'anual',
                'orden'        => 17,
                'descripcion'  => 'Expedición anual de certificados de retención a cada arrendatario persona jurídica que practicó retención sobre el canon pagado a la inmobiliaria. Plazo: 31 de marzo del año siguiente.',
            ],
        ];

        foreach ($obligaciones as $obl) {
            DianObligationType::updateOrCreate(
                ['codigo' => $obl['codigo']],
                array_merge($obl, ['activa' => true]),
            );
        }

        $this->command->info('Tipos de obligaciones DIAN: ' . count($obligaciones) . ' registros.');
    }
}
