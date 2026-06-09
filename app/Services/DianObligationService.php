<?php

namespace App\Services;

use App\Models\AccountingEntryLine;
use App\Models\Company;
use App\Models\DianDeclaration;
use App\Models\DianObligationType;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Servicio de obligaciones DIAN para Serviarrendar S.A.S.
 * Implementa los 11 formularios de información exógena + declaraciones tributarias.
 *
 * Códigos de concepto DIAN (Resolución 000162):
 *   Pagos:   5001=Arrendamientos inmuebles, 5003=Servicios, 5004=Comisiones,
 *            5010=Honorarios, 5041=Salarios, 5047=Pensiones, 5048=Salud, 5051=Otros
 *   Retefte: 1001=Honorarios, 1003=Servicios, 1005=Arrendamientos, 1006=Otros
 *   Ingresos:4001=Ventas, 4002=Servicios, 4004=Comisiones, 4016=Arrendamientos
 *   CxC/CxP: 14=Deudores varios, 22=Cuentas comerciales, 15=Dividendos
 *
 * Tipos de documento DIAN:
 *   11=Registro civil, 12=TI, 13=CC, 21=Tarjeta extranjería, 22=CE,
 *   31=NIT, 41=Pasaporte, 91=NUIP
 */
class DianObligationService
{
    // ════════════════════════════════════════════════════════════════════════
    // FECHAS DE VENCIMIENTO SEGÚN NIT Y CALENDARIO DIAN
    // ════════════════════════════════════════════════════════════════════════

    public static function fechaVencimientoRetefte(int $anio, int $mes): Carbon
    {
        $company    = Company::first();
        $nit        = preg_replace('/[^0-9]/', '', $company?->nit ?? '0');
        $ultimosDos = (int) substr($nit, -2);

        // Tabla DIAN: últimos 2 dígitos del NIT → día de vencimiento (mes siguiente).
        $tabla = [
             1=>7,  2=>7,  3=>8,  4=>8,  5=>9,  6=>9,  7=>10,  8=>10,
             9=>11,10=>11,11=>12,12=>12,13=>13,14=>13,15=>14, 16=>14,
            17=>15,18=>15,19=>16,20=>16,21=>17,22=>17,23=>18, 24=>18,
            25=>19,26=>19,27=>20,28=>20,29=>21,30=>21,31=>22, 32=>22,
            33=>23,34=>23,35=>24,36=>24,37=>25,38=>25,39=>26, 40=>26,
            41=>7, 42=>7, 43=>8, 44=>8, 45=>9, 46=>9, 47=>10, 48=>10,
            49=>11,50=>11,51=>12,52=>12,53=>13,54=>13,55=>14, 56=>14,
            57=>15,58=>15,59=>16,60=>16,61=>17,62=>17,63=>18, 64=>18,
            65=>19,66=>19,67=>20,68=>20,69=>21,70=>21,71=>22, 72=>22,
            73=>23,74=>23,75=>24,76=>24,77=>25,78=>25,79=>26, 80=>26,
            81=>7, 82=>7, 83=>8, 84=>8, 85=>9, 86=>9, 87=>10, 88=>10,
            89=>11,90=>11,91=>12,92=>12,93=>13,94=>13,95=>14, 96=>14,
            97=>15,98=>15,99=>16, 0=>16,
        ];

        $diaVenc      = $tabla[$ultimosDos] ?? 21;
        $mesSiguiente = Carbon::create($anio, $mes, 1)->addMonth();
        $fecha        = Carbon::create($mesSiguiente->year, $mesSiguiente->month, $diaVenc);

        if ($fecha->isSaturday()) $fecha->addDays(2);
        if ($fecha->isSunday())   $fecha->addDay();

        return $fecha;
    }

    public static function fechaVencimientoIVA(int $anio, int $cuatrimestre): Carbon
    {
        [$mesVenc, $diaVenc] = match($cuatrimestre) {
            1 => [5, 12], 2 => [9, 25], 3 => [1, 15], default => [5, 12],
        };
        $anioVenc = $cuatrimestre === 3 ? $anio + 1 : $anio;
        $fecha    = Carbon::create($anioVenc, $mesVenc, $diaVenc);

        if ($fecha->isSaturday()) $fecha->addDays(2);
        if ($fecha->isSunday())   $fecha->addDay();

        return $fecha;
    }

    // ════════════════════════════════════════════════════════════════════════
    // HELPERS INTERNOS
    // ════════════════════════════════════════════════════════════════════════

    private static function rangoAnio(int $anio): array
    {
        return [
            Carbon::create($anio, 1, 1)->startOfDay(),
            Carbon::create($anio, 12, 31)->endOfDay(),
        ];
    }

    /** Convierte tipo_documento del sistema al código numérico DIAN */
    private static function tipoDocDian(string $tipo): string
    {
        return match(strtoupper($tipo)) {
            'NIT'        => '31',
            'CC'         => '13',
            'CE'         => '22',
            'PASAPORTE'  => '41',
            'TI'         => '12',
            'RC'         => '11',
            'NUIP'       => '91',
            default      => '13',
        };
    }

    /** Líneas contables filtradas por cuenta(s) y período */
    private static function lineas(int $anio, array|string $cuentas, string $tipoFiltro = 'like'): \Illuminate\Database\Eloquent\Builder
    {
        $cuentas = (array) $cuentas;

        return AccountingEntryLine::whereHas('entry', fn($q) =>
                $q->where('estado', 'contabilizado')
                  ->whereBetween('fecha', static::rangoAnio($anio))
            )
            ->whereHas('account', function ($q) use ($cuentas) {
                $q->where(function ($inner) use ($cuentas) {
                    foreach ($cuentas as $c) {
                        $inner->orWhere('codigo', 'like', $c . '%');
                    }
                });
            });
    }

    /** Agrupa líneas por tercero y devuelve info básica de cada uno */
    private static function porTercero(\Illuminate\Support\Collection $items): \Illuminate\Support\Collection
    {
        return $items->groupBy('third_id')->map(function ($grupo) {
            $tercero = $grupo->first()->third;
            return [
                'tipo_doc'  => static::tipoDocDian($tercero?->tipo_documento ?? 'CC'),
                'nit'       => preg_replace('/[^0-9]/', '', $tercero?->numero_documento ?? ''),
                'dv'        => (string)($tercero?->digito_verificacion ?? ''),
                'nombre'    => $tercero?->nombre_completo ?? '',
                'direccion' => $tercero?->direccion ?? '',
                'telefono'  => $tercero?->telefono ?? '',
                'ciudad'    => $tercero?->ciudad ?? 'Bogotá',
                'pais'      => 'CO',
                'debito'    => round($grupo->sum('debito'), 2),
                'credito'   => round($grupo->sum('credito'), 2),
            ];
        })->values();
    }

    // ════════════════════════════════════════════════════════════════════════
    // FORM 350 — RETENCIÓN EN LA FUENTE (MENSUAL)
    // ════════════════════════════════════════════════════════════════════════

    public static function calcularRetefte(int $anio, int $mes): array
    {
        $inicio = Carbon::create($anio, $mes, 1)->startOfMonth();
        $fin    = Carbon::create($anio, $mes, 1)->endOfMonth();

        $reteBase = AccountingEntryLine::whereHas('entry', fn($q) =>
                $q->where('estado', 'contabilizado')->whereBetween('fecha', [$inicio, $fin])
            )
            ->whereHas('account', fn($q) => $q->where('codigo', 'like', '236%'))
            ->with(['account', 'third'])
            ->get();

        // Detalle por concepto DIAN
        $porConcepto = $reteBase->groupBy('account.codigo')->map(fn($g) => [
            'cuenta'  => $g->first()->account?->codigo,
            'nombre'  => $g->first()->account?->nombre,
            'base'    => round($g->sum('base_retencion') ?: 0, 2),
            'neto'    => round($g->sum('credito') - $g->sum('debito'), 2),
        ])->values();

        // Autorretención (236525 crédito)
        $autoRete = AccountingEntryLine::whereHas('entry', fn($q) =>
                $q->where('estado', 'contabilizado')->whereBetween('fecha', [$inicio, $fin])
            )
            ->whereHas('account', fn($q) => $q->where('codigo', '236525'))
            ->sum('credito');

        $totalRete     = round($reteBase->sum('credito') - $reteBase->sum('debito'), 2);
        $totalAutoRete = round((float)$autoRete, 2);

        return [
            'formulario'                    => '350',
            'periodo'                       => Carbon::create($anio, $mes)->locale('es')->isoFormat('MMMM YYYY'),
            'por_concepto'                  => $porConcepto->toArray(),
            'total_retenciones_practicadas' => $totalRete,
            'total_autorretenciones'        => $totalAutoRete,
            'total_a_pagar'                 => round($totalRete + $totalAutoRete, 2),
        ];
    }

    // ════════════════════════════════════════════════════════════════════════
    // FORM 300 — IVA CUATRIMESTRAL
    // ════════════════════════════════════════════════════════════════════════

    public static function calcularIVA(int $anio, int $cuatrimestre): array
    {
        [$mesInicio, $mesFin] = match($cuatrimestre) {
            1 => [1, 4], 2 => [5, 8], 3 => [9, 12], default => [1, 4],
        };

        $inicio = Carbon::create($anio, $mesInicio, 1)->startOfMonth();
        $fin    = Carbon::create($anio, $mesFin, 1)->endOfMonth();

        $ivaGenerado = AccountingEntryLine::whereHas('entry', fn($q) =>
                $q->where('estado', 'contabilizado')->whereBetween('fecha', [$inicio, $fin])
            )
            ->whereHas('account', fn($q) => $q->where('codigo', '240805'))
            ->selectRaw('SUM(credito) as cre, SUM(debito) as deb')
            ->first();

        $baseComision = AccountingEntryLine::whereHas('entry', fn($q) =>
                $q->where('estado', 'contabilizado')->whereBetween('fecha', [$inicio, $fin])
            )
            ->whereHas('account', fn($q) => $q->where('codigo', '413505'))
            ->sum('credito');

        $generado    = round((float)($ivaGenerado?->cre ?? 0) - (float)($ivaGenerado?->deb ?? 0), 2);
        $descontable = 0; // inmobiliaria pura sin compras con IVA significativas

        return [
            'formulario'      => '300',
            'cuatrimestre'    => $cuatrimestre,
            'periodo'         => "Cuatrimestre {$cuatrimestre} ({$mesInicio}-{$mesFin}) - {$anio}",
            'base_comisiones' => round((float)$baseComision, 2),
            'iva_generado'    => $generado,
            'iva_descontable' => $descontable,
            'saldo_a_favor'   => max(0, $descontable - $generado),
            'total_a_pagar'   => max(0, $generado - $descontable),
        ];
    }

    // ════════════════════════════════════════════════════════════════════════
    // FORM 110 — RENTA ANUAL
    // ════════════════════════════════════════════════════════════════════════

    public static function calcularRenta(int $anio): array
    {
        [$inicio, $fin] = static::rangoAnio($anio);

        $porClase = AccountingEntryLine::whereHas('entry', fn($q) =>
                $q->where('estado', 'contabilizado')->whereBetween('fecha', [$inicio, $fin])
            )
            ->join('accounting_accounts as aa', 'accounting_entry_lines.account_id', '=', 'aa.id')
            ->selectRaw('aa.clase, SUM(accounting_entry_lines.debito) as deb, SUM(accounting_entry_lines.credito) as cre')
            ->groupBy('aa.clase')
            ->get()->keyBy('clase');

        $ingresos = (float)(($porClase['4'] ?? null)?->cre  ?? 0) - (float)(($porClase['4'] ?? null)?->deb  ?? 0);
        $gastos   = (float)(($porClase['5'] ?? null)?->deb  ?? 0) - (float)(($porClase['5'] ?? null)?->cre  ?? 0);
        $activos  = (float)(($porClase['1'] ?? null)?->deb  ?? 0) - (float)(($porClase['1'] ?? null)?->cre  ?? 0);
        $pasivos  = (float)(($porClase['2'] ?? null)?->cre  ?? 0) - (float)(($porClase['2'] ?? null)?->deb  ?? 0);
        $patrimonio = $activos - $pasivos;

        $anticipos = AccountingEntryLine::whereHas('entry', fn($q) =>
                $q->where('estado', 'contabilizado')->whereBetween('fecha', [$inicio, $fin])
            )
            ->whereHas('account', fn($q) => $q->whereIn('codigo', ['135505','136515']))
            ->sum('debito');

        return [
            'formulario'      => '110',
            'anio_gravable'   => $anio,
            'ingresos_brutos' => round($ingresos, 2),
            'gastos'          => round($gastos, 2),
            'utilidad_bruta'  => round($ingresos - $gastos, 2),
            'activos_totales' => round($activos, 2),
            'pasivos_totales' => round($pasivos, 2),
            'patrimonio'      => round($patrimonio, 2),
            'anticipos_rete'  => round((float)$anticipos, 2),
            'nota'            => 'Valores extraídos de asientos contabilizados. Verificar con contador antes de presentar.',
        ];
    }

    // ════════════════════════════════════════════════════════════════════════
    // FORM 1001 — PAGOS/ABONOS EN CUENTA Y RETENCIONES PRACTICADAS
    // Cols DIAN: tipo_doc | nit | dv | apellidos | primer_nombre | razon_social
    //            | pais | dpto | ciudad | dirección | teléfono
    //            | cod_concepto | valor_pago | valor_rete_fte | valor_rete_iva | valor_rete_ica
    // ════════════════════════════════════════════════════════════════════════

    public static function calcularExogena1001(int $anio): array
    {
        [$inicio, $fin] = static::rangoAnio($anio);

        // Pagos a propietarios: cuenta 233510 débitos (giros realizados)
        $pagos = AccountingEntryLine::whereHas('entry', fn($q) =>
                $q->where('estado', 'contabilizado')->whereBetween('fecha', [$inicio, $fin])
            )
            ->whereHas('account', fn($q) => $q->where('codigo', '233510'))
            ->whereNotNull('third_id')
            ->with(['third', 'entry'])
            ->get()
            ->groupBy('third_id')
            ->map(function ($g) {
                $t = $g->first()->third;
                return [
                    'tipo_doc'          => static::tipoDocDian($t?->tipo_documento ?? 'CC'),
                    'nit'               => preg_replace('/[^0-9]/', '', $t?->numero_documento ?? ''),
                    'dv'                => (string)($t?->digito_verificacion ?? ''),
                    'primer_apellido'   => static::primerApellido($t?->nombre_completo ?? ''),
                    'segundo_apellido'  => static::segundoApellido($t?->nombre_completo ?? ''),
                    'primer_nombre'     => static::primerNombre($t?->nombre_completo ?? ''),
                    'otros_nombres'     => '',
                    'razon_social'      => $t?->tipo_persona === 'juridica' ? ($t?->razon_social ?? $t?->nombre_completo ?? '') : '',
                    'pais'              => 'CO',
                    'codigo_dpto'       => '11', // Cundinamarca/Bogotá por defecto
                    'codigo_ciudad'     => '11001',
                    'direccion'         => $t?->direccion ?? '',
                    'telefono'          => $t?->telefono ?? '',
                    'cod_concepto'      => '5001', // Arrendamiento inmuebles
                    'valor_pago'        => round($g->sum('debito'), 2),
                    'valor_rete_fte'    => 0,
                    'valor_rete_iva'    => 0,
                    'valor_rete_ica'    => 0,
                ];
            });

        // Retenciones practicadas: cuentas 236xxx crédito neto
        $retes = AccountingEntryLine::whereHas('entry', fn($q) =>
                $q->where('estado', 'contabilizado')->whereBetween('fecha', [$inicio, $fin])
            )
            ->whereHas('account', fn($q) => $q->where('codigo', 'like', '236%'))
            ->whereNotNull('third_id')
            ->with(['third', 'account'])
            ->get()
            ->groupBy('third_id')
            ->map(function ($g) {
                $t = $g->first()->third;
                $cuentaBase = substr($g->first()->account?->codigo ?? '2365', 0, 4);
                $codConcepto = match(true) {
                    str_starts_with($cuentaBase, '2365') => '1005', // Arrendamientos
                    str_starts_with($cuentaBase, '2363') => '1001', // Honorarios
                    default                               => '1006', // Otros
                };
                return [
                    'tipo_doc'         => static::tipoDocDian($t?->tipo_documento ?? 'CC'),
                    'nit'              => preg_replace('/[^0-9]/', '', $t?->numero_documento ?? ''),
                    'dv'               => (string)($t?->digito_verificacion ?? ''),
                    'primer_apellido'  => static::primerApellido($t?->nombre_completo ?? ''),
                    'segundo_apellido' => static::segundoApellido($t?->nombre_completo ?? ''),
                    'primer_nombre'    => static::primerNombre($t?->nombre_completo ?? ''),
                    'otros_nombres'    => '',
                    'razon_social'     => $t?->tipo_persona === 'juridica' ? ($t?->razon_social ?? $t?->nombre_completo ?? '') : '',
                    'pais'             => 'CO',
                    'codigo_dpto'      => '11',
                    'codigo_ciudad'    => '11001',
                    'direccion'        => $t?->direccion ?? '',
                    'telefono'         => $t?->telefono ?? '',
                    'cod_concepto'     => $codConcepto,
                    'valor_pago'       => 0,
                    'valor_rete_fte'   => round($g->sum('credito') - $g->sum('debito'), 2),
                    'valor_rete_iva'   => 0,
                    'valor_rete_ica'   => 0,
                ];
            });

        $registros = $pagos->merge($retes)->values()->toArray();

        return [
            'formulario'   => '1001',
            'anio'         => $anio,
            'registros'    => $registros,
            'cantidad'     => count($registros),
            'total_pagos'  => round(collect($registros)->sum('valor_pago'), 2),
            'total_rete'   => round(collect($registros)->sum('valor_rete_fte'), 2),
        ];
    }

    // ════════════════════════════════════════════════════════════════════════
    // FORM 1003 — RETENCIONES QUE LE PRACTICARON AL INFORMANTE
    // Cols DIAN: tipo_doc_retenedor | nit_retenedor | dv | nombre_retenedor
    //            | cod_concepto | valor_base | tarifa | valor_retencion
    // ════════════════════════════════════════════════════════════════════════

    public static function calcularExogena1003(int $anio): array
    {
        [$inicio, $fin] = static::rangoAnio($anio);

        // Retenciones A FAVOR de la inmobiliaria: cuentas 136xxx débito
        $items = AccountingEntryLine::whereHas('entry', fn($q) =>
                $q->where('estado', 'contabilizado')->whereBetween('fecha', [$inicio, $fin])
            )
            ->whereHas('account', fn($q) => $q->where('codigo', 'like', '136%'))
            ->whereNotNull('third_id')
            ->with(['third', 'account'])
            ->get()
            ->groupBy('third_id')
            ->map(function ($g) {
                $t = $g->first()->third;
                $valorBase = round((float)($g->max('base_retencion') ?: $g->sum('debito') / 0.035), 2);
                return [
                    'tipo_doc_retenedor'  => static::tipoDocDian($t?->tipo_documento ?? 'CC'),
                    'nit_retenedor'       => preg_replace('/[^0-9]/', '', $t?->numero_documento ?? ''),
                    'dv_retenedor'        => (string)($t?->digito_verificacion ?? ''),
                    'nombre_retenedor'    => $t?->nombre_completo ?? '',
                    'cod_concepto'        => '1005', // Retefte sobre arrendamientos
                    'valor_base'          => $valorBase,
                    'tarifa'              => 3.5,
                    'valor_retencion'     => round($g->sum('debito'), 2),
                ];
            })->values()->toArray();

        return [
            'formulario'        => '1003',
            'anio'              => $anio,
            'registros'         => $items,
            'cantidad'          => count($items),
            'total_retenciones' => round(collect($items)->sum('valor_retencion'), 2),
        ];
    }

    // ════════════════════════════════════════════════════════════════════════
    // FORM 1005 — IVA GENERADO Y DESCONTABLE (RESUMEN ANUAL)
    // Cols DIAN: periodo | iva_generado | iva_descontable | saldo_a_pagar | saldo_a_favor
    // ════════════════════════════════════════════════════════════════════════

    public static function calcularExogena1005(int $anio): array
    {
        $periodos = [];
        $totalGenerado    = 0;
        $totalDescontable = 0;

        // IVA cuatrimestral
        foreach ([1, 2, 3] as $c) {
            $calc = static::calcularIVA($anio, $c);
            $periodos[] = [
                'periodo'         => $calc['periodo'],
                'iva_generado'    => $calc['iva_generado'],
                'iva_descontable' => $calc['iva_descontable'],
                'saldo_a_pagar'   => $calc['total_a_pagar'],
                'saldo_a_favor'   => $calc['saldo_a_favor'],
            ];
            $totalGenerado    += $calc['iva_generado'];
            $totalDescontable += $calc['iva_descontable'];
        }

        return [
            'formulario'       => '1005',
            'anio'             => $anio,
            'periodos'         => $periodos,
            'total_generado'   => round($totalGenerado, 2),
            'total_descontable'=> round($totalDescontable, 2),
            'total_a_pagar'    => round(max(0, $totalGenerado - $totalDescontable), 2),
            'total_a_favor'    => round(max(0, $totalDescontable - $totalGenerado), 2),
        ];
    }

    // ════════════════════════════════════════════════════════════════════════
    // FORM 1006 — SALDOS EN CUENTAS BANCARIAS A 31-DIC
    // Cols DIAN: entidad_bancaria | nit_banco | tipo_cuenta | numero_cuenta | saldo
    // ════════════════════════════════════════════════════════════════════════

    public static function calcularExogena1006(int $anio): array
    {
        $fin = Carbon::create($anio, 12, 31)->endOfDay();

        // Saldo cuenta bancos (111005) a 31 de diciembre
        $saldoBancos = AccountingEntryLine::whereHas('entry', fn($q) =>
                $q->where('estado', 'contabilizado')->where('fecha', '<=', $fin)
            )
            ->whereHas('account', fn($q) => $q->where('codigo', 'like', '111%'))
            ->selectRaw('SUM(debito) as deb, SUM(credito) as cre')
            ->first();

        $saldo = round((float)($saldoBancos?->deb ?? 0) - (float)($saldoBancos?->cre ?? 0), 2);

        // Estructura base — Serviarrendar debe completar los datos bancarios reales
        $registros = [
            [
                'entidad_bancaria' => 'Por completar — ver extractos bancarios',
                'nit_banco'        => '',
                'tipo_cuenta'      => 'Ahorros',
                'numero_cuenta'    => 'Por completar',
                'saldo'            => max(0, $saldo),
                'nota'             => 'Saldo calculado desde cuenta contable 111005. Verificar con extracto bancario.',
            ],
        ];

        return [
            'formulario'     => '1006',
            'anio'           => $anio,
            'registros'      => $registros,
            'saldo_contable' => $saldo,
            'nota'           => 'Complete la entidad bancaria y número de cuenta con los datos reales.',
        ];
    }

    // ════════════════════════════════════════════════════════════════════════
    // FORM 1007 — INGRESOS RECIBIDOS POR TERCERO
    // Cols DIAN: tipo_doc | nit | dv | apellidos | primer_nombre | razon_social
    //            | pais | cod_concepto | valor_ingreso
    // ════════════════════════════════════════════════════════════════════════

    public static function calcularExogena1007(int $anio): array
    {
        [$inicio, $fin] = static::rangoAnio($anio);

        // Ingresos por comisión (413505) y otros ingresos (413510, 421010) por tercero
        $items = AccountingEntryLine::whereHas('entry', fn($q) =>
                $q->where('estado', 'contabilizado')->whereBetween('fecha', [$inicio, $fin])
            )
            ->whereHas('account', fn($q) =>
                $q->whereIn('codigo', ['413505','413510','421010'])
            )
            ->whereNotNull('third_id')
            ->with(['third', 'account'])
            ->get()
            ->groupBy(['third_id', 'account.codigo'])
            ->flatMap(function ($porTercero, $thirdId) {
                return $porTercero->map(function ($g, $cuenta) use ($thirdId) {
                    $t = $g->first()->third;
                    $codConcepto = match($cuenta) {
                        '413505' => '4004', // Comisiones
                        '413510' => '4002', // Servicios de administración
                        '421010' => '4016', // Intereses y mora
                        default  => '4002',
                    };
                    return [
                        'tipo_doc'        => static::tipoDocDian($t?->tipo_documento ?? 'CC'),
                        'nit'             => preg_replace('/[^0-9]/', '', $t?->numero_documento ?? ''),
                        'dv'              => (string)($t?->digito_verificacion ?? ''),
                        'primer_apellido' => static::primerApellido($t?->nombre_completo ?? ''),
                        'segundo_apellido'=> static::segundoApellido($t?->nombre_completo ?? ''),
                        'primer_nombre'   => static::primerNombre($t?->nombre_completo ?? ''),
                        'otros_nombres'   => '',
                        'razon_social'    => $t?->tipo_persona === 'juridica' ? ($t?->razon_social ?? $t?->nombre_completo ?? '') : '',
                        'pais'            => 'CO',
                        'cod_concepto'    => $codConcepto,
                        'valor_ingreso'   => round($g->sum('credito') - $g->sum('debito'), 2),
                    ];
                });
            })->filter(fn($r) => $r['valor_ingreso'] > 0)->values()->toArray();

        return [
            'formulario'     => '1007',
            'anio'           => $anio,
            'registros'      => $items,
            'cantidad'       => count($items),
            'total_ingresos' => round(collect($items)->sum('valor_ingreso'), 2),
        ];
    }

    // ════════════════════════════════════════════════════════════════════════
    // FORM 1008 — SALDO IVA A 31-DIC
    // Cols DIAN: tipo_saldo | valor_saldo
    // ════════════════════════════════════════════════════════════════════════

    public static function calcularExogena1008(int $anio): array
    {
        $fin = Carbon::create($anio, 12, 31)->endOfDay();

        $iva = AccountingEntryLine::whereHas('entry', fn($q) =>
                $q->where('estado', 'contabilizado')->where('fecha', '<=', $fin)
            )
            ->whereHas('account', fn($q) => $q->where('codigo', '240805'))
            ->selectRaw('SUM(credito) as cre, SUM(debito) as deb')
            ->first();

        $saldo = round((float)($iva?->cre ?? 0) - (float)($iva?->deb ?? 0), 2);

        return [
            'formulario'    => '1008',
            'anio'          => $anio,
            'saldo'         => abs($saldo),
            'tipo_saldo'    => $saldo > 0 ? 'A_PAGAR' : 'A_FAVOR',
            'registros'     => [[
                'tipo_saldo'  => $saldo > 0 ? 'Saldo a pagar' : 'Saldo a favor',
                'valor_saldo' => abs($saldo),
            ]],
        ];
    }

    // ════════════════════════════════════════════════════════════════════════
    // FORM 1009 — SALDO CUENTAS POR PAGAR A 31-DIC
    // Cols DIAN: tipo_doc | nit | dv | apellidos | primer_nombre | razon_social
    //            | cod_concepto | saldo_a_31dic
    // ════════════════════════════════════════════════════════════════════════

    public static function calcularExogena1009(int $anio): array
    {
        $fin = Carbon::create($anio, 12, 31)->endOfDay();

        // CxP propietarios 233510, IVA 240805, Retenciones 236xxx, Depósitos 281505
        $items = AccountingEntryLine::whereHas('entry', fn($q) =>
                $q->where('estado', 'contabilizado')->where('fecha', '<=', $fin)
            )
            ->whereHas('account', fn($q) =>
                $q->where(fn($i) =>
                    $i->where('codigo', 'like', '233%')
                      ->orWhere('codigo', 'like', '236%')
                      ->orWhere('codigo', 'like', '240%')
                      ->orWhere('codigo', 'like', '281%')
                )
            )
            ->whereNotNull('third_id')
            ->with(['third', 'account'])
            ->get()
            ->groupBy(fn($l) => $l->third_id . '|' . $l->account?->codigo)
            ->map(function ($g) {
                $t       = $g->first()->third;
                $cuenta  = $g->first()->account?->codigo ?? '';
                $concepto = match(true) {
                    str_starts_with($cuenta, '233') => '22', // Acreedores comerciales (propietarios)
                    str_starts_with($cuenta, '236') => '17', // Retenciones y aportes de nómina
                    str_starts_with($cuenta, '240') => '14', // IVA por pagar
                    str_starts_with($cuenta, '281') => '22', // Depósitos recibidos
                    default                          => '22',
                };
                $saldo = round($g->sum('credito') - $g->sum('debito'), 2);
                if ($saldo <= 0) return null;
                return [
                    'tipo_doc'        => static::tipoDocDian($t?->tipo_documento ?? 'CC'),
                    'nit'             => preg_replace('/[^0-9]/', '', $t?->numero_documento ?? ''),
                    'dv'              => (string)($t?->digito_verificacion ?? ''),
                    'primer_apellido' => static::primerApellido($t?->nombre_completo ?? ''),
                    'segundo_apellido'=> static::segundoApellido($t?->nombre_completo ?? ''),
                    'primer_nombre'   => static::primerNombre($t?->nombre_completo ?? ''),
                    'otros_nombres'   => '',
                    'razon_social'    => $t?->tipo_persona === 'juridica' ? ($t?->razon_social ?? $t?->nombre_completo ?? '') : '',
                    'cod_concepto'    => $concepto,
                    'saldo'           => $saldo,
                    'cuenta'          => $cuenta,
                ];
            })
            ->filter()->values()->toArray();

        return [
            'formulario'  => '1009',
            'anio'        => $anio,
            'registros'   => $items,
            'cantidad'    => count($items),
            'total_saldo' => round(collect($items)->sum('saldo'), 2),
        ];
    }

    // ════════════════════════════════════════════════════════════════════════
    // FORM 1010 — SALDO CUENTAS POR COBRAR A 31-DIC
    // Cols DIAN: tipo_doc | nit | dv | apellidos | primer_nombre | razon_social
    //            | cod_concepto | saldo_a_31dic
    // ════════════════════════════════════════════════════════════════════════

    public static function calcularExogena1010(int $anio): array
    {
        $fin = Carbon::create($anio, 12, 31)->endOfDay();

        // CxC arrendatarios 130505, Anticipos impuestos 135xxx/136xxx, Provisión 139905
        $items = AccountingEntryLine::whereHas('entry', fn($q) =>
                $q->where('estado', 'contabilizado')->where('fecha', '<=', $fin)
            )
            ->whereHas('account', fn($q) =>
                $q->where(fn($i) =>
                    $i->where('codigo', 'like', '130%')
                      ->orWhere('codigo', 'like', '135%')
                      ->orWhere('codigo', 'like', '136%')
                )
            )
            ->whereNotNull('third_id')
            ->with(['third', 'account'])
            ->get()
            ->groupBy(fn($l) => $l->third_id . '|' . $l->account?->codigo)
            ->map(function ($g) {
                $t      = $g->first()->third;
                $cuenta = $g->first()->account?->codigo ?? '';
                $concepto = match(true) {
                    str_starts_with($cuenta, '130') => '14', // Deudores por arrendamiento
                    str_starts_with($cuenta, '135') => '14', // Anticipos impuestos
                    str_starts_with($cuenta, '136') => '14', // Retenciones a favor
                    default                          => '14',
                };
                $saldo = round($g->sum('debito') - $g->sum('credito'), 2);
                if ($saldo <= 0) return null;
                return [
                    'tipo_doc'        => static::tipoDocDian($t?->tipo_documento ?? 'CC'),
                    'nit'             => preg_replace('/[^0-9]/', '', $t?->numero_documento ?? ''),
                    'dv'              => (string)($t?->digito_verificacion ?? ''),
                    'primer_apellido' => static::primerApellido($t?->nombre_completo ?? ''),
                    'segundo_apellido'=> static::segundoApellido($t?->nombre_completo ?? ''),
                    'primer_nombre'   => static::primerNombre($t?->nombre_completo ?? ''),
                    'otros_nombres'   => '',
                    'razon_social'    => $t?->tipo_persona === 'juridica' ? ($t?->razon_social ?? $t?->nombre_completo ?? '') : '',
                    'cod_concepto'    => $concepto,
                    'saldo'           => $saldo,
                    'cuenta'          => $cuenta,
                ];
            })
            ->filter()->values()->toArray();

        return [
            'formulario'  => '1010',
            'anio'        => $anio,
            'registros'   => $items,
            'cantidad'    => count($items),
            'total_saldo' => round(collect($items)->sum('saldo'), 2),
        ];
    }

    // ════════════════════════════════════════════════════════════════════════
    // FORM 1011 — SOCIOS, ACCIONISTAS O ASOCIADOS
    // Cols DIAN: tipo_doc | nit | dv | apellidos | primer_nombre | razon_social
    //            | pais | porcentaje_participacion | valor_patrimonio
    //            | valor_dividendos_decretados | valor_dividendos_pagados
    // ════════════════════════════════════════════════════════════════════════

    public static function calcularExogena1011(int $anio): array
    {
        [$inicio, $fin] = static::rangoAnio($anio);

        // Patrimonio (cuentas 3xxx crédito neto a 31-dic)
        $finAnio = Carbon::create($anio, 12, 31)->endOfDay();
        $patrimonio = AccountingEntryLine::whereHas('entry', fn($q) =>
                $q->where('estado', 'contabilizado')->where('fecha', '<=', $finAnio)
            )
            ->whereHas('account', fn($q) => $q->where('codigo', 'like', '3%'))
            ->selectRaw('SUM(credito) as cre, SUM(debito) as deb')
            ->first();

        $valorPatrimonio = round((float)($patrimonio?->cre ?? 0) - (float)($patrimonio?->deb ?? 0), 2);

        // Dividendos/participaciones decretados (cuenta 3605 u otras distribución de utilidades)
        $dividendos = AccountingEntryLine::whereHas('entry', fn($q) =>
                $q->where('estado', 'contabilizado')->whereBetween('fecha', [$inicio, $fin])
            )
            ->whereHas('account', fn($q) => $q->where('codigo', 'like', '360%'))
            ->sum('debito');

        return [
            'formulario'       => '1011',
            'anio'             => $anio,
            'valor_patrimonio' => $valorPatrimonio,
            'dividendos'       => round((float)$dividendos, 2),
            'registros'        => [], // Completar manualmente con los socios de Serviarrendar
            'nota'             => 'Complete con los datos de cada socio de Serviarrendar S.A.S: nombre, NIT, % participación. El valor del patrimonio y dividendos se calcula desde contabilidad.',
        ];
    }

    // ════════════════════════════════════════════════════════════════════════
    // FORM 2276 — DEUDORES DE CRÉDITOS ACTIVOS (CARTERA)
    // Cols DIAN: tipo_doc | nit | dv | apellidos | primer_nombre | razon_social
    //            | saldo_capital | saldo_intereses | saldo_mora | fecha_ultimo_pago
    // ════════════════════════════════════════════════════════════════════════

    public static function calcularExogena2276(int $anio): array
    {
        // Arrendatarios con cartera activa al 31-dic (saldo pendiente > 0)
        $fin = Carbon::create($anio, 12, 31)->endOfDay();

        $cartera = \App\Models\RentBill::whereIn('estado', ['pendiente','parcial','en_mora','vencida'])
            ->whereDate('periodo_fin', '<=', $fin)
            ->with('arrendatario')
            ->get()
            ->groupBy('arrendatario_id')
            ->map(function ($bills) {
                $t = $bills->first()->arrendatario;
                $saldoCapital    = round($bills->sum('saldo_pendiente'), 2);
                $saldoMora       = round($bills->sum('mora_acumulada'), 2);
                $ultimoPago      = $bills->max('fecha_pago');

                return [
                    'tipo_doc'        => static::tipoDocDian($t?->tipo_documento ?? 'CC'),
                    'nit'             => preg_replace('/[^0-9]/', '', $t?->numero_documento ?? ''),
                    'dv'              => (string)($t?->digito_verificacion ?? ''),
                    'primer_apellido' => static::primerApellido($t?->nombre_completo ?? ''),
                    'segundo_apellido'=> static::segundoApellido($t?->nombre_completo ?? ''),
                    'primer_nombre'   => static::primerNombre($t?->nombre_completo ?? ''),
                    'otros_nombres'   => '',
                    'razon_social'    => $t?->tipo_persona === 'juridica' ? ($t?->razon_social ?? $t?->nombre_completo ?? '') : '',
                    'saldo_capital'   => $saldoCapital,
                    'saldo_intereses' => 0, // intereses corrientes (no mora)
                    'saldo_mora'      => $saldoMora,
                    'fecha_ultimo_pago' => $ultimoPago ? \Carbon\Carbon::parse($ultimoPago)->format('d/m/Y') : '',
                ];
            })
            ->filter(fn($r) => $r['saldo_capital'] > 0)
            ->values()->toArray();

        return [
            'formulario'      => '2276',
            'anio'            => $anio,
            'registros'       => $cartera,
            'cantidad'        => count($cartera),
            'total_capital'   => round(collect($cartera)->sum('saldo_capital'), 2),
            'total_mora'      => round(collect($cartera)->sum('saldo_mora'), 2),
        ];
    }

    // ════════════════════════════════════════════════════════════════════════
    // FORM 5247 — IVA GENERADO Y DESCONTABLE POR OPERACIONES (DETALLADO)
    // Cols DIAN: tipo_operacion | tarifa_iva | base_gravable | iva_generado | iva_descontable
    // ════════════════════════════════════════════════════════════════════════

    public static function calcularExogena5247(int $anio): array
    {
        [$inicio, $fin] = static::rangoAnio($anio);

        // IVA generado en comisiones (tarifa 19%)
        $ivaComision = AccountingEntryLine::whereHas('entry', fn($q) =>
                $q->where('estado', 'contabilizado')->whereBetween('fecha', [$inicio, $fin])
            )
            ->whereHas('account', fn($q) => $q->where('codigo', '240805'))
            ->selectRaw('SUM(credito) as cre, SUM(debito) as deb')
            ->first();

        $baseComision = AccountingEntryLine::whereHas('entry', fn($q) =>
                $q->where('estado', 'contabilizado')->whereBetween('fecha', [$inicio, $fin])
            )
            ->whereHas('account', fn($q) => $q->where('codigo', '413505'))
            ->sum('credito');

        $ivaGenerado = round((float)($ivaComision?->cre ?? 0) - (float)($ivaComision?->deb ?? 0), 2);

        $registros = [
            [
                'tipo_operacion'  => 'Venta de servicios — Comisiones de administración',
                'tarifa_iva'      => 19,
                'base_gravable'   => round((float)$baseComision, 2),
                'iva_generado'    => $ivaGenerado,
                'iva_descontable' => 0,
            ],
        ];

        return [
            'formulario'        => '5247',
            'anio'              => $anio,
            'registros'         => $registros,
            'total_generado'    => $ivaGenerado,
            'total_descontable' => 0,
            'neto'              => $ivaGenerado,
        ];
    }

    // ════════════════════════════════════════════════════════════════════════
    // RECÁLCULO CENTRALIZADO
    // ════════════════════════════════════════════════════════════════════════

    public static function recalcular(DianDeclaration $decl): DianDeclaration
    {
        $codigo  = $decl->obligationType?->codigo;
        $anio    = $decl->anio;
        $periodo = $decl->periodo;

        $calculo = match($codigo) {
            'retefte'         => static::calcularRetefte($anio, $periodo),
            'reteica'         => static::calcularRetefte($anio, $periodo),
            'iva'             => static::calcularIVA($anio, $periodo),
            'renta'           => static::calcularRenta($anio),
            'ica'             => static::calcularICA($anio),
            'exogena_1001'    => static::calcularExogena1001($anio),
            'exogena_1003'    => static::calcularExogena1003($anio),
            'exogena_1005'    => static::calcularExogena1005($anio),
            'exogena_1006'    => static::calcularExogena1006($anio),
            'exogena_1007'    => static::calcularExogena1007($anio),
            'exogena_1008'    => static::calcularExogena1008($anio),
            'exogena_1009'    => static::calcularExogena1009($anio),
            'exogena_1010'    => static::calcularExogena1010($anio),
            'exogena_1011'    => static::calcularExogena1011($anio),
            'exogena_2276'    => static::calcularExogena2276($anio),
            'exogena_5247'    => static::calcularExogena5247($anio),
            'cert_retencion'  => static::calcularExogena1003($anio),
            default           => [],
        };

        $valorPagar = (float)(
            $calculo['total_a_pagar'] ??
            $calculo['valor_ica']     ??
            $calculo['neto']          ??
            0
        );

        $decl->update([
            'calculo'       => $calculo,
            'valor_a_pagar' => $valorPagar,
            'total_declarado'=> $valorPagar,
            'calculado_por' => Auth::id(),
            'calculado_en'  => now(),
        ]);

        return $decl->fresh();
    }

    // ════════════════════════════════════════════════════════════════════════
    // ICA
    // ════════════════════════════════════════════════════════════════════════

    public static function calcularICA(int $anio): array
    {
        $company   = Company::first();
        $tarifa    = (float)($company?->tarifa_reteica ?? 6.9);
        $renta     = static::calcularRenta($anio);
        $base      = $renta['ingresos_brutos'];
        $valorIca  = round($base * ($tarifa / 1000), 2);

        return [
            'formulario'     => 'D-500',
            'anio'           => $anio,
            'base_ingresos'  => $base,
            'tarifa_por_mil' => $tarifa,
            'valor_ica'      => $valorIca,
            'municipio'      => 'Bogotá D.C.',
            'total_a_pagar'  => $valorIca,
        ];
    }

    // ════════════════════════════════════════════════════════════════════════
    // GENERACIÓN DE PERÍODOS
    // ════════════════════════════════════════════════════════════════════════

    public static function generarPeriodosAnio(int $anio): int
    {
        $creadas = 0;
        $tipos   = DianObligationType::where('activa', true)->get();

        foreach ($tipos as $tipo) {
            foreach (static::periodosDelAnio($tipo->periodicidad, $anio) as $periodo) {
                $existe = DianDeclaration::where('obligation_type_id', $tipo->id)
                    ->where('anio', $anio)
                    ->where('periodo', $periodo['numero'])
                    ->exists();

                if ($existe) continue;

                DianDeclaration::create([
                    'obligation_type_id'   => $tipo->id,
                    'anio'                 => $anio,
                    'periodo'              => $periodo['numero'],
                    'periodo_label'        => $periodo['label'],
                    'fecha_inicio_periodo' => $periodo['inicio'],
                    'fecha_fin_periodo'    => $periodo['fin'],
                    'fecha_vencimiento'    => static::calcularFechaVencimiento($tipo, $anio, $periodo['numero']),
                    'estado'               => 'pendiente',
                    'valor_a_pagar'        => 0,
                    'total_declarado'      => 0,
                ]);

                $creadas++;
            }
        }

        return $creadas;
    }

    // ════════════════════════════════════════════════════════════════════════
    // HELPERS PRIVADOS
    // ════════════════════════════════════════════════════════════════════════

    private static function periodosDelAnio(string $periodicidad, int $anio): array
    {
        return match($periodicidad) {
            'mensual' => collect(range(1, 12))->map(fn($m) => [
                'numero' => $m,
                'label'  => Carbon::create($anio, $m)->locale('es')->isoFormat('MMMM YYYY'),
                'inicio' => Carbon::create($anio, $m, 1)->startOfMonth()->toDateString(),
                'fin'    => Carbon::create($anio, $m, 1)->endOfMonth()->toDateString(),
            ])->toArray(),

            'bimestral' => collect(range(1, 6))->map(fn($b) => [
                'numero' => $b,
                'label'  => "Bimestre {$b} - {$anio}",
                'inicio' => Carbon::create($anio, ($b - 1) * 2 + 1, 1)->startOfMonth()->toDateString(),
                'fin'    => Carbon::create($anio, $b * 2, 1)->endOfMonth()->toDateString(),
            ])->toArray(),

            'cuatrimestral' => collect(range(1, 3))->map(fn($c) => [
                'numero' => $c,
                'label'  => "Cuatrimestre {$c} - {$anio}",
                'inicio' => Carbon::create($anio, ($c - 1) * 4 + 1, 1)->startOfMonth()->toDateString(),
                'fin'    => Carbon::create($anio, $c * 4, 1)->endOfMonth()->toDateString(),
            ])->toArray(),

            'anual' => [[
                'numero' => 0,
                'label'  => "Año gravable {$anio}",
                'inicio' => Carbon::create($anio, 1, 1)->toDateString(),
                'fin'    => Carbon::create($anio, 12, 31)->toDateString(),
            ]],

            default => [],
        };
    }

    private static function calcularFechaVencimiento(DianObligationType $tipo, int $anio, int $periodo): string
    {
        return match($tipo->codigo) {
            'retefte','reteica'  => static::fechaVencimientoRetefte($anio, $periodo)->toDateString(),
            'iva'                => static::fechaVencimientoIVA($anio, $periodo)->toDateString(),
            'renta'              => Carbon::create($anio + 1, 4, 15)->toDateString(),
            'ica'                => Carbon::create($anio + 1, 3, 31)->toDateString(),
            'cert_retencion'     => Carbon::create($anio + 1, 3, 31)->toDateString(),
            // Exógena: vence en abril del año siguiente (resolución DIAN varía por año)
            default              => Carbon::create($anio + 1, 4, 30)->toDateString(),
        };
    }

    /** Extrae el primer apellido de un nombre completo */
    private static function primerApellido(string $nombre): string
    {
        $partes = explode(' ', trim($nombre));
        return count($partes) >= 3 ? $partes[0] : '';
    }

    private static function segundoApellido(string $nombre): string
    {
        $partes = explode(' ', trim($nombre));
        return count($partes) >= 4 ? $partes[1] : (count($partes) === 3 ? '' : '');
    }

    private static function primerNombre(string $nombre): string
    {
        $partes = explode(' ', trim($nombre));
        if (count($partes) >= 4) return $partes[2];
        if (count($partes) === 3) return $partes[1];
        return $partes[0] ?? '';
    }
}
