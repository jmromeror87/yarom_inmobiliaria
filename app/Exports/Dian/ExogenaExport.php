<?php

namespace App\Exports\Dian;

use App\Models\DianDeclaration;

/**
 * Exportador CSV de información exógena (medios magnéticos) DIAN.
 *
 * Genera archivos con la estructura exacta de columnas que espera el
 * prevalidador DIAN para cada formulario. El separador es punto y coma (;)
 * y el BOM UTF-8 garantiza que Excel lo abra correctamente.
 *
 * Formularios soportados: 1001, 1003, 1005, 1006, 1007, 1008, 1009, 1010, 1011, 2276, 5247
 */
class ExogenaExport
{
    public function __construct(private DianDeclaration $declaration) {}

    public function toCsv(): string
    {
        $formulario = $this->declaration->obligationType?->formulario ?? '';
        $calculo    = $this->declaration->calculo ?? [];

        [$encabezados, $filas] = match($formulario) {
            '1001'  => $this->formato1001($calculo),
            '1003'  => $this->formato1003($calculo),
            '1005'  => $this->formato1005($calculo),
            '1006'  => $this->formato1006($calculo),
            '1007'  => $this->formato1007($calculo),
            '1008'  => $this->formato1008($calculo),
            '1009'  => $this->formato1009($calculo),
            '1010'  => $this->formato1010($calculo),
            '1011'  => $this->formato1011($calculo),
            '2276'  => $this->formato2276($calculo),
            '5247'  => $this->formato5247($calculo),
            default => [['Formulario no soportado: ' . $formulario], []],
        };

        return $this->generarCsv($encabezados, $filas);
    }

    public function nombreArchivo(): string
    {
        $form = $this->declaration->obligationType?->formulario ?? 'XXX';
        $anio = $this->declaration->anio;
        $nit  = preg_replace('/[^0-9]/', '', \App\Models\Company::first()?->nit ?? '0');
        return "DIAN_F{$form}_{$anio}_NIT{$nit}.csv";
    }

    // ── FORM 1001 — Pagos/abonos y retenciones practicadas ───────────────

    private function formato1001(array $c): array
    {
        $cols = [
            'Tipo doc.','Identificación','DV',
            'Primer apellido','Segundo apellido','Primer nombre','Otros nombres','Razón social',
            'País','Cód. departamento','Cód. municipio','Dirección','Teléfono',
            'Cód. concepto',
            'Valor pago/abono','Rete. en la fuente','Rete. IVA','Rete. ICA',
        ];

        $filas = [];
        foreach ($c['registros'] ?? [] as $r) {
            $filas[] = [
                $r['tipo_doc']        ?? '',
                $r['nit']             ?? '',
                $r['dv']              ?? '',
                $r['primer_apellido'] ?? '',
                $r['segundo_apellido']?? '',
                $r['primer_nombre']   ?? '',
                $r['otros_nombres']   ?? '',
                $r['razon_social']    ?? '',
                $r['pais']            ?? 'CO',
                $r['codigo_dpto']     ?? '11',
                $r['codigo_ciudad']   ?? '11001',
                $r['direccion']       ?? '',
                $r['telefono']        ?? '',
                $r['cod_concepto']    ?? '',
                $this->fmt($r['valor_pago']      ?? 0),
                $this->fmt($r['valor_rete_fte']  ?? 0),
                $this->fmt($r['valor_rete_iva']  ?? 0),
                $this->fmt($r['valor_rete_ica']  ?? 0),
            ];
        }

        $filas[] = $this->totalRow(count($cols), [14=>$c['total_pagos']??0, 15=>$c['total_rete']??0]);

        return [$cols, $filas];
    }

    // ── FORM 1003 — Retenciones que le practicaron al informante ─────────

    private function formato1003(array $c): array
    {
        $cols = [
            'Tipo doc. retenedor','Identificación retenedor','DV retenedor','Nombre retenedor',
            'Cód. concepto','Valor base','Tarifa (%)','Valor retención',
        ];

        $filas = [];
        foreach ($c['registros'] ?? [] as $r) {
            $filas[] = [
                $r['tipo_doc_retenedor']  ?? '',
                $r['nit_retenedor']       ?? '',
                $r['dv_retenedor']        ?? '',
                $r['nombre_retenedor']    ?? '',
                $r['cod_concepto']        ?? '1005',
                $this->fmt($r['valor_base']      ?? 0),
                $r['tarifa']              ?? 3.5,
                $this->fmt($r['valor_retencion'] ?? 0),
            ];
        }

        $filas[] = $this->totalRow(8, [5=>$c['total_retenciones']??0, 7=>$c['total_retenciones']??0]);

        return [$cols, $filas];
    }

    // ── FORM 1005 — IVA generado y descontable (resumen cuatrimestral) ───

    private function formato1005(array $c): array
    {
        $cols = [
            'Período','IVA generado','IVA descontable','Saldo a pagar','Saldo a favor',
        ];

        $filas = [];
        foreach ($c['periodos'] ?? [] as $p) {
            $filas[] = [
                $p['periodo']         ?? '',
                $this->fmt($p['iva_generado']    ?? 0),
                $this->fmt($p['iva_descontable'] ?? 0),
                $this->fmt($p['saldo_a_pagar']   ?? 0),
                $this->fmt($p['saldo_a_favor']   ?? 0),
            ];
        }

        $filas[] = ['TOTAL',
            $this->fmt($c['total_generado']    ?? 0),
            $this->fmt($c['total_descontable'] ?? 0),
            $this->fmt($c['total_a_pagar']     ?? 0),
            $this->fmt($c['total_a_favor']     ?? 0),
        ];

        return [$cols, $filas];
    }

    // ── FORM 1006 — Saldos en cuentas bancarias ──────────────────────────

    private function formato1006(array $c): array
    {
        $cols = [
            'Entidad bancaria','NIT banco','Tipo de cuenta','Número de cuenta','Saldo a 31-dic','Nota',
        ];

        $filas = [];
        foreach ($c['registros'] ?? [] as $r) {
            $filas[] = [
                $r['entidad_bancaria'] ?? '',
                $r['nit_banco']        ?? '',
                $r['tipo_cuenta']      ?? '',
                $r['numero_cuenta']    ?? '',
                $this->fmt($r['saldo'] ?? 0),
                $r['nota']             ?? '',
            ];
        }

        return [$cols, $filas];
    }

    // ── FORM 1007 — Ingresos recibidos ───────────────────────────────────

    private function formato1007(array $c): array
    {
        $cols = [
            'Tipo doc.','Identificación','DV',
            'Primer apellido','Segundo apellido','Primer nombre','Otros nombres','Razón social',
            'País','Cód. concepto','Valor ingreso',
        ];

        $filas = [];
        foreach ($c['registros'] ?? [] as $r) {
            $filas[] = [
                $r['tipo_doc']         ?? '',
                $r['nit']              ?? '',
                $r['dv']               ?? '',
                $r['primer_apellido']  ?? '',
                $r['segundo_apellido'] ?? '',
                $r['primer_nombre']    ?? '',
                $r['otros_nombres']    ?? '',
                $r['razon_social']     ?? '',
                $r['pais']             ?? 'CO',
                $r['cod_concepto']     ?? '',
                $this->fmt($r['valor_ingreso'] ?? 0),
            ];
        }

        $filas[] = $this->totalRow(11, [10 => $c['total_ingresos'] ?? 0]);

        return [$cols, $filas];
    }

    // ── FORM 1008 — Saldo IVA ────────────────────────────────────────────

    private function formato1008(array $c): array
    {
        $cols = ['Tipo de saldo','Valor'];

        $filas = [];
        foreach ($c['registros'] ?? [] as $r) {
            $filas[] = [
                $r['tipo_saldo']  ?? '',
                $this->fmt($r['valor_saldo'] ?? 0),
            ];
        }

        return [$cols, $filas];
    }

    // ── FORM 1009 — Cuentas por pagar ────────────────────────────────────

    private function formato1009(array $c): array
    {
        $cols = [
            'Tipo doc.','Identificación','DV',
            'Primer apellido','Segundo apellido','Primer nombre','Otros nombres','Razón social',
            'Cód. concepto','Cuenta contable','Saldo a 31-dic',
        ];

        $filas = [];
        foreach ($c['registros'] ?? [] as $r) {
            $filas[] = [
                $r['tipo_doc']         ?? '',
                $r['nit']              ?? '',
                $r['dv']               ?? '',
                $r['primer_apellido']  ?? '',
                $r['segundo_apellido'] ?? '',
                $r['primer_nombre']    ?? '',
                $r['otros_nombres']    ?? '',
                $r['razon_social']     ?? '',
                $r['cod_concepto']     ?? '',
                $r['cuenta']           ?? '',
                $this->fmt($r['saldo'] ?? 0),
            ];
        }

        $filas[] = $this->totalRow(11, [10 => $c['total_saldo'] ?? 0]);

        return [$cols, $filas];
    }

    // ── FORM 1010 — Cuentas por cobrar ───────────────────────────────────

    private function formato1010(array $c): array
    {
        $cols = [
            'Tipo doc.','Identificación','DV',
            'Primer apellido','Segundo apellido','Primer nombre','Otros nombres','Razón social',
            'Cód. concepto','Cuenta contable','Saldo a 31-dic',
        ];

        $filas = [];
        foreach ($c['registros'] ?? [] as $r) {
            $filas[] = [
                $r['tipo_doc']         ?? '',
                $r['nit']              ?? '',
                $r['dv']               ?? '',
                $r['primer_apellido']  ?? '',
                $r['segundo_apellido'] ?? '',
                $r['primer_nombre']    ?? '',
                $r['otros_nombres']    ?? '',
                $r['razon_social']     ?? '',
                $r['cod_concepto']     ?? '',
                $r['cuenta']           ?? '',
                $this->fmt($r['saldo'] ?? 0),
            ];
        }

        $filas[] = $this->totalRow(11, [10 => $c['total_saldo'] ?? 0]);

        return [$cols, $filas];
    }

    // ── FORM 1011 — Socios / accionistas ─────────────────────────────────

    private function formato1011(array $c): array
    {
        $cols = [
            'Tipo doc.','Identificación','DV',
            'Primer apellido','Segundo apellido','Primer nombre','Otros nombres','Razón social',
            'País','% Participación','Valor patrimonio líquido',
            'Dividendos decretados','Dividendos pagados',
        ];

        $filas = [];
        foreach ($c['registros'] ?? [] as $r) {
            $filas[] = [
                $r['tipo_doc']             ?? '',
                $r['nit']                  ?? '',
                $r['dv']                   ?? '',
                $r['primer_apellido']      ?? '',
                $r['segundo_apellido']     ?? '',
                $r['primer_nombre']        ?? '',
                $r['otros_nombres']        ?? '',
                $r['razon_social']         ?? '',
                $r['pais']                 ?? 'CO',
                $r['porcentaje']           ?? '',
                $this->fmt($r['valor_patrimonio']    ?? 0),
                $this->fmt($r['dividendos_decretados'] ?? 0),
                $this->fmt($r['dividendos_pagados']    ?? 0),
            ];
        }

        if (empty($c['registros'])) {
            $filas[] = ['PENDIENTE: Completar con datos de socios de Serviarrendar S.A.S',
                '','','','','','','','','',
                $this->fmt($c['valor_patrimonio'] ?? 0),
                $this->fmt($c['dividendos'] ?? 0), '0',
            ];
        }

        return [$cols, $filas];
    }

    // ── FORM 2276 — Deudores de créditos activos ─────────────────────────

    private function formato2276(array $c): array
    {
        $cols = [
            'Tipo doc.','Identificación','DV',
            'Primer apellido','Segundo apellido','Primer nombre','Otros nombres','Razón social',
            'Saldo capital','Saldo intereses corrientes','Saldo intereses mora','Fecha último pago',
        ];

        $filas = [];
        foreach ($c['registros'] ?? [] as $r) {
            $filas[] = [
                $r['tipo_doc']          ?? '',
                $r['nit']               ?? '',
                $r['dv']                ?? '',
                $r['primer_apellido']   ?? '',
                $r['segundo_apellido']  ?? '',
                $r['primer_nombre']     ?? '',
                $r['otros_nombres']     ?? '',
                $r['razon_social']      ?? '',
                $this->fmt($r['saldo_capital']    ?? 0),
                $this->fmt($r['saldo_intereses']  ?? 0),
                $this->fmt($r['saldo_mora']       ?? 0),
                $r['fecha_ultimo_pago'] ?? '',
            ];
        }

        $filas[] = $this->totalRow(12, [
            8 => $c['total_capital'] ?? 0,
            10 => $c['total_mora']   ?? 0,
        ]);

        return [$cols, $filas];
    }

    // ── FORM 5247 — IVA por operaciones (detallado) ───────────────────────

    private function formato5247(array $c): array
    {
        $cols = [
            'Tipo operación','Tarifa IVA (%)','Base gravable','IVA generado','IVA descontable',
        ];

        $filas = [];
        foreach ($c['registros'] ?? [] as $r) {
            $filas[] = [
                $r['tipo_operacion']  ?? '',
                $r['tarifa_iva']      ?? 19,
                $this->fmt($r['base_gravable']   ?? 0),
                $this->fmt($r['iva_generado']    ?? 0),
                $this->fmt($r['iva_descontable'] ?? 0),
            ];
        }

        $filas[] = ['TOTAL','',
            $this->fmt(collect($c['registros'] ?? [])->sum('base_gravable')),
            $this->fmt($c['total_generado']    ?? 0),
            $this->fmt($c['total_descontable'] ?? 0),
        ];

        return [$cols, $filas];
    }

    // ── HELPERS ───────────────────────────────────────────────────────────

    /** Formatea número como string sin separador de miles (DIAN usa punto decimal) */
    private function fmt(float|int|string $valor): string
    {
        return number_format((float)$valor, 2, '.', '');
    }

    /** Genera fila de totales con columnas en blanco excepto las indicadas */
    private function totalRow(int $numCols, array $totalesPorIndice): array
    {
        $fila = array_fill(0, $numCols, '');
        $fila[0] = 'TOTAL';
        foreach ($totalesPorIndice as $idx => $valor) {
            $fila[$idx] = $this->fmt($valor);
        }
        return $fila;
    }

    /** Genera el string CSV con BOM UTF-8 y separador ; */
    private function generarCsv(array $encabezados, array $filas): string
    {
        $output = fopen('php://memory', 'r+');
        fputs($output, "\xEF\xBB\xBF"); // BOM UTF-8

        fputcsv($output, $encabezados, ';');
        foreach ($filas as $fila) {
            fputcsv($output, $fila, ';');
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }
}
