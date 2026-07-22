<?php

namespace App\Services\Reports;

use App\Models\AccountingEntryLine;
use App\Models\Company;
use App\Models\RentBill;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Motor central de cálculo para todos los informes contables.
 * Extrae datos desde asientos contabilizados en la tabla accounting_entry_lines.
 *
 * Clasificación PUC colombiana usada:
 *   Clase 1 = Activos        Clase 2 = Pasivos       Clase 3 = Patrimonio
 *   Clase 4 = Ingresos       Clase 5 = Gastos        Clase 6 = Costo producción
 *   Clase 8 = Orden deudoras Clase 9 = Orden acreed.
 */
class ReportingService
{
    // ══════════════════════════════════════════════════════════════════════
    // HELPERS INTERNOS
    // ══════════════════════════════════════════════════════════════════════

    private static function lineasPeriodo(Carbon $desde, Carbon $hasta): \Illuminate\Database\Eloquent\Builder
    {
        return AccountingEntryLine::whereHas('entry', fn($q) =>
            $q->where('estado', 'contabilizado')
              ->whereBetween('fecha', [$desde->startOfDay(), $hasta->endOfDay()])
        )->with(['account', 'entry', 'third']);
    }

    private static function lineasHasta(Carbon $hasta): \Illuminate\Database\Eloquent\Builder
    {
        return AccountingEntryLine::whereHas('entry', fn($q) =>
            $q->where('estado', 'contabilizado')
              ->where('fecha', '<=', $hasta->endOfDay())
        )->with(['account']);
    }

    /** Saldo neto de una cuenta (débitos - créditos) según su naturaleza */
    private static function saldoCuenta(string $codigoLike, Carbon $hasta, bool $esDebito = true): float
    {
        $row = static::lineasHasta($hasta)
            ->whereHas('account', fn($q) => $q->where('codigo', 'like', $codigoLike . '%'))
            ->selectRaw('SUM(debito) as deb, SUM(credito) as cre')
            ->first();

        $deb = (float)($row?->deb ?? 0);
        $cre = (float)($row?->cre ?? 0);

        return $esDebito ? ($deb - $cre) : ($cre - $deb);
    }

    /** Saldos agrupados por código de cuenta (primer N dígitos) */
    private static function saldosPorCuenta(
        string $clasePrefix,
        Carbon $desde,
        Carbon $hasta,
        bool   $acumulado = false
    ): Collection {
        $query = $acumulado
            ? static::lineasHasta($hasta)
            : static::lineasPeriodo($desde, $hasta);

        return $query->whereHas('account', fn($q) => $q->where('codigo', 'like', $clasePrefix . '%'))
            ->join('accounting_accounts as aa', 'accounting_entry_lines.account_id', '=', 'aa.id')
            ->selectRaw('aa.codigo, aa.nombre, aa.naturaleza, aa.clase,
                         SUM(accounting_entry_lines.debito) as deb,
                         SUM(accounting_entry_lines.credito) as cre')
            ->groupBy('aa.codigo', 'aa.nombre', 'aa.naturaleza', 'aa.clase')
            ->orderBy('aa.codigo')
            ->get()
            ->map(fn($r) => [
                'codigo'    => $r->codigo,
                'nombre'    => $r->nombre,
                'naturaleza'=> $r->naturaleza,
                'clase'     => $r->clase,
                'debito'    => round((float)$r->deb, 2),
                'credito'   => round((float)$r->cre, 2),
                'saldo'     => $r->naturaleza === 'debito'
                    ? round((float)$r->deb - (float)$r->cre, 2)
                    : round((float)$r->cre - (float)$r->deb, 2),
            ]);
    }

    // ══════════════════════════════════════════════════════════════════════
    // 1. ESTADO DE RESULTADOS (P&G)
    // ══════════════════════════════════════════════════════════════════════

    public static function estadoResultados(Carbon $desde, Carbon $hasta): array
    {
        $company = Company::first();

        // Ingresos operacionales (clase 4)
        $ingresos = static::saldosPorCuenta('4', $desde, $hasta);
        $totalIngresos = $ingresos->sum('saldo');

        // Costos (clase 6 y 7 si existen)
        $costos = static::saldosPorCuenta('6', $desde, $hasta);
        $totalCostos = $costos->sum('saldo');

        // Gastos operacionales (clase 5)
        $gastos = static::saldosPorCuenta('5', $desde, $hasta);
        $totalGastos = $gastos->sum('saldo');

        $utilidadBruta      = $totalIngresos - $totalCostos;
        $utilidadOperacional = $utilidadBruta - $totalGastos;

        // KPIs
        $margenBruto       = $totalIngresos > 0 ? round(($utilidadBruta / $totalIngresos) * 100, 2) : 0;
        $margenOperacional = $totalIngresos > 0 ? round(($utilidadOperacional / $totalIngresos) * 100, 2) : 0;

        // Agrupación por subtítulos de 4 dígitos
        $ingresosGrupo = $ingresos->groupBy(fn($r) => substr($r['codigo'], 0, 4));
        $gastosGrupo   = $gastos->groupBy(fn($r) => substr($r['codigo'], 0, 4));

        return [
            'tipo'               => 'estado_resultados',
            'titulo'             => 'Estado de Resultados',
            'empresa'            => $company?->razon_social ?? 'Serviarrendar S.A.S',
            'nit'                => $company?->nit_completo ?? '',
            'desde'              => $desde->toDateString(),
            'hasta'              => $hasta->toDateString(),
            'periodo_label'      => $desde->locale('es')->isoFormat('D MMM YYYY') . ' — ' . $hasta->locale('es')->isoFormat('D MMM YYYY'),
            'ingresos'           => $ingresos->toArray(),
            'ingresos_grupo'     => $ingresosGrupo->toArray(),
            'costos'             => $costos->toArray(),
            'gastos'             => $gastos->toArray(),
            'gastos_grupo'       => $gastosGrupo->toArray(),
            'total_ingresos'     => round($totalIngresos, 2),
            'total_costos'       => round($totalCostos, 2),
            'total_gastos'       => round($totalGastos, 2),
            'utilidad_bruta'     => round($utilidadBruta, 2),
            'utilidad_operacional' => round($utilidadOperacional, 2),
            'margen_bruto'       => $margenBruto,
            'margen_operacional' => $margenOperacional,
            'kpis'               => [
                ['label' => 'Ingresos totales',        'valor' => $totalIngresos,      'color' => 'green',  'icon' => '📈'],
                ['label' => 'Gastos operacionales',    'valor' => $totalGastos,        'color' => 'red',    'icon' => '📉'],
                ['label' => 'Utilidad operacional',    'valor' => $utilidadOperacional,'color' => $utilidadOperacional >= 0 ? 'blue' : 'red', 'icon' => '💼'],
                ['label' => 'Margen operacional',      'valor' => $margenOperacional . '%', 'color' => $margenOperacional >= 0 ? 'emerald' : 'red', 'icon' => '📊', 'es_pct' => true],
            ],
        ];
    }

    // ══════════════════════════════════════════════════════════════════════
    // 2. BALANCE GENERAL
    // ══════════════════════════════════════════════════════════════════════

    public static function balanceGeneral(Carbon $hasta): array
    {
        $company = Company::first();

        // Activos (clase 1, naturaleza débito)
        $activos  = static::saldosPorCuenta('1', Carbon::create(2000), $hasta, true)
            ->filter(fn($r) => $r['saldo'] != 0);

        // Pasivos (clase 2, naturaleza crédito)
        $pasivos  = static::saldosPorCuenta('2', Carbon::create(2000), $hasta, true)
            ->filter(fn($r) => $r['saldo'] != 0);

        // Patrimonio (clase 3, naturaleza crédito)
        $patrimonio = static::saldosPorCuenta('3', Carbon::create(2000), $hasta, true)
            ->filter(fn($r) => $r['saldo'] != 0);

        // Utilidad del ejercicio acumulada (ingresos - costos - gastos, clases 4/5/6
        // desde el inicio hasta la fecha del corte). Sin esto, Activos = Pasivos +
        // Patrimonio nunca cuadra: la comisión que ya está en la cartera/cxp
        // (activos/pasivos) tiene su contrapartida en cuentas de resultado que jamás
        // se cierran contra el patrimonio.
        $ingresosAcum = static::saldosPorCuenta('4', Carbon::create(2000), $hasta, true)->sum('saldo');
        $costosAcum   = static::saldosPorCuenta('6', Carbon::create(2000), $hasta, true)->sum('saldo');
        $gastosAcum   = static::saldosPorCuenta('5', Carbon::create(2000), $hasta, true)->sum('saldo');
        $utilidadEjercicio = round($ingresosAcum - $costosAcum - $gastosAcum, 2);

        if ($utilidadEjercicio != 0) {
            $patrimonio->push([
                'codigo'     => '3705',
                'nombre'     => 'Utilidad del ejercicio',
                'naturaleza' => 'credito',
                'clase'      => '3',
                'debito'     => 0,
                'credito'    => $utilidadEjercicio,
                'saldo'      => $utilidadEjercicio,
            ]);
        }

        $totalActivos   = $activos->sum('saldo');
        $totalPasivos   = $pasivos->sum('saldo');
        $totalPatrimonio= $patrimonio->sum('saldo');
        $ecuacion       = round(abs($totalActivos - ($totalPasivos + $totalPatrimonio)), 2);

        // Activos corrientes (11xx, 12xx, 13xx, 14xx) vs no corrientes (15xx+)
        $activosCorrientes    = $activos->filter(fn($r) => (int)substr($r['codigo'],0,2) <= 19 && (int)substr($r['codigo'],0,2) >= 11 && (int)substr($r['codigo'],0,2) <= 16);
        $activosNoCorrientes  = $activos->filter(fn($r) => (int)substr($r['codigo'],0,2) > 16);

        // Pasivos corrientes (21xx-24xx) vs largo plazo (25xx+)
        $pasivosCorrientes   = $pasivos->filter(fn($r) => (int)substr($r['codigo'],0,2) <= 24);
        $pasivosLargoPlazo   = $pasivos->filter(fn($r) => (int)substr($r['codigo'],0,2) > 24);

        $liquidez = $activosCorrientes->sum('saldo') > 0 && $pasivosCorrientes->sum('saldo') > 0
            ? round($activosCorrientes->sum('saldo') / max($pasivosCorrientes->sum('saldo'), 1), 2)
            : 0;

        return [
            'tipo'                    => 'balance_general',
            'titulo'                  => 'Balance General',
            'empresa'                 => $company?->razon_social ?? 'Serviarrendar S.A.S',
            'nit'                     => $company?->nit_completo ?? '',
            'hasta'                   => $hasta->toDateString(),
            'hasta_label'             => $hasta->locale('es')->isoFormat('D [de] MMMM [de] YYYY'),
            'activos'                 => $activos->values()->toArray(),
            'activos_corrientes'      => $activosCorrientes->values()->toArray(),
            'activos_no_corrientes'   => $activosNoCorrientes->values()->toArray(),
            'pasivos'                 => $pasivos->values()->toArray(),
            'pasivos_corrientes'      => $pasivosCorrientes->values()->toArray(),
            'pasivos_largo_plazo'     => $pasivosLargoPlazo->values()->toArray(),
            'patrimonio'              => $patrimonio->values()->toArray(),
            'total_activos'           => round($totalActivos, 2),
            'total_activos_corrientes'=> round($activosCorrientes->sum('saldo'), 2),
            'total_pasivos'           => round($totalPasivos, 2),
            'total_pasivos_corrientes'=> round($pasivosCorrientes->sum('saldo'), 2),
            'total_patrimonio'        => round($totalPatrimonio, 2),
            'ecuacion_cuadra'         => $ecuacion < 1,
            'diferencia'              => $ecuacion,
            'kpis'                    => [
                ['label' => 'Total activos',    'valor' => $totalActivos,    'color' => 'blue',    'icon' => '🏢'],
                ['label' => 'Total pasivos',    'valor' => $totalPasivos,    'color' => 'red',     'icon' => '📋'],
                ['label' => 'Patrimonio neto',  'valor' => $totalPatrimonio, 'color' => 'green',   'icon' => '💎'],
                ['label' => 'Razón corriente',  'valor' => $liquidez . 'x',  'color' => $liquidez >= 1 ? 'emerald' : 'orange', 'icon' => '⚖️', 'es_pct' => true],
            ],
        ];
    }

    // ══════════════════════════════════════════════════════════════════════
    // 3. FLUJO DE EFECTIVO (MÉTODO DIRECTO SIMPLIFICADO)
    // ══════════════════════════════════════════════════════════════════════

    public static function flujoEfectivo(Carbon $desde, Carbon $hasta): array
    {
        $company = Company::first();

        // Entradas: débitos a caja/bancos (111xxx) — recaudos reales
        $entradas = static::lineasPeriodo($desde, $hasta)
            ->whereHas('account', fn($q) => $q->where('codigo', 'like', '111%'))
            ->selectRaw('SUM(debito) as total_ent, SUM(credito) as total_sal')
            ->first();

        $totalEntradas = round((float)($entradas?->total_ent ?? 0), 2);
        $totalSalidas  = round((float)($entradas?->total_sal ?? 0), 2);
        $flujoNeto     = $totalEntradas - $totalSalidas;

        // Detalle por concepto de entrada
        $detalleEntradas = static::lineasPeriodo($desde, $hasta)
            ->whereHas('account', fn($q) => $q->where('codigo', 'like', '111%'))
            ->where('debito', '>', 0)
            ->with(['entry'])
            ->get()
            ->groupBy(fn($l) => substr($l->entry?->descripcion ?? 'Otros', 0, 40))
            ->map(fn($g) => ['concepto' => $g->first()->entry?->descripcion ?? 'Recaudo', 'valor' => round($g->sum('debito'), 2)])
            ->sortByDesc('valor')->take(10)->values();

        $detalleSalidas = static::lineasPeriodo($desde, $hasta)
            ->whereHas('account', fn($q) => $q->where('codigo', 'like', '111%'))
            ->where('credito', '>', 0)
            ->with(['entry'])
            ->get()
            ->groupBy(fn($l) => substr($l->entry?->descripcion ?? 'Otros', 0, 40))
            ->map(fn($g) => ['concepto' => $g->first()->entry?->descripcion ?? 'Pago', 'valor' => round($g->sum('credito'), 2)])
            ->sortByDesc('valor')->take(10)->values();

        // Saldo inicial (bancos antes del período)
        $saldoInicial = static::saldoCuenta('111', $desde->copy()->subDay(), true);
        $saldoFinal   = $saldoInicial + $flujoNeto;

        return [
            'tipo'             => 'flujo_efectivo',
            'titulo'           => 'Flujo de Caja',
            'empresa'          => $company?->razon_social ?? 'Serviarrendar S.A.S',
            'nit'              => $company?->nit_completo ?? '',
            'desde'            => $desde->toDateString(),
            'hasta'            => $hasta->toDateString(),
            'periodo_label'    => $desde->locale('es')->isoFormat('D MMM YYYY') . ' — ' . $hasta->locale('es')->isoFormat('D MMM YYYY'),
            'saldo_inicial'    => $saldoInicial,
            'total_entradas'   => $totalEntradas,
            'total_salidas'    => $totalSalidas,
            'flujo_neto'       => $flujoNeto,
            'saldo_final'      => $saldoFinal,
            'detalle_entradas' => $detalleEntradas->toArray(),
            'detalle_salidas'  => $detalleSalidas->toArray(),
            'kpis'             => [
                ['label' => 'Saldo inicial',  'valor' => $saldoInicial,  'color' => 'gray',  'icon' => '🏦'],
                ['label' => 'Entradas',       'valor' => $totalEntradas, 'color' => 'green', 'icon' => '⬆️'],
                ['label' => 'Salidas',        'valor' => $totalSalidas,  'color' => 'red',   'icon' => '⬇️'],
                ['label' => 'Saldo final',    'valor' => $saldoFinal,    'color' => $saldoFinal >= 0 ? 'blue' : 'red', 'icon' => '💰'],
            ],
        ];
    }

    // ══════════════════════════════════════════════════════════════════════
    // 4. BALANCE DE PRUEBA DETALLADO
    // ══════════════════════════════════════════════════════════════════════

    public static function balancePrueba(Carbon $desde, Carbon $hasta, bool $soloConMovimiento = true): array
    {
        $company = Company::first();

        $lineas = AccountingEntryLine::whereHas('entry', fn($q) =>
                $q->where('estado', 'contabilizado')
                  ->whereBetween('fecha', [$desde->startOfDay(), $hasta->endOfDay()])
            )
            ->join('accounting_accounts as aa', 'accounting_entry_lines.account_id', '=', 'aa.id')
            ->selectRaw('aa.codigo, aa.nombre, aa.naturaleza, aa.clase,
                         SUM(accounting_entry_lines.debito) as deb,
                         SUM(accounting_entry_lines.credito) as cre')
            ->groupBy('aa.codigo', 'aa.nombre', 'aa.naturaleza', 'aa.clase')
            ->orderBy('aa.codigo')
            ->get()
            ->map(fn($r) => [
                'codigo'   => $r->codigo,
                'nombre'   => $r->nombre,
                'clase'    => $r->clase,
                'debito'   => round((float)$r->deb, 2),
                'credito'  => round((float)$r->cre, 2),
                'saldo_db' => max(0, round((float)$r->deb - (float)$r->cre, 2)),
                'saldo_cr' => max(0, round((float)$r->cre - (float)$r->deb, 2)),
            ])
            ->when($soloConMovimiento, fn($c) => $c->filter(fn($r) => $r['debito'] > 0 || $r['credito'] > 0))
            ->values();

        $totalDeb = $lineas->sum('debito');
        $totalCre = $lineas->sum('credito');
        $cuadra   = abs($totalDeb - $totalCre) < 1;

        return [
            'tipo'          => 'balance_prueba',
            'titulo'        => 'Balance de Prueba',
            'empresa'       => $company?->razon_social ?? 'Serviarrendar S.A.S',
            'nit'           => $company?->nit_completo ?? '',
            'desde'         => $desde->toDateString(),
            'hasta'         => $hasta->toDateString(),
            'periodo_label' => $desde->locale('es')->isoFormat('D MMM YYYY') . ' — ' . $hasta->locale('es')->isoFormat('D MMM YYYY'),
            'cuentas'       => $lineas->toArray(),
            'total_debitos' => round($totalDeb, 2),
            'total_creditos'=> round($totalCre, 2),
            'cuadra'        => $cuadra,
            'diferencia'    => round(abs($totalDeb - $totalCre), 2),
            'kpis'          => [
                ['label' => 'Total débitos',   'valor' => $totalDeb, 'color' => 'blue',   'icon' => '📥'],
                ['label' => 'Total créditos',  'valor' => $totalCre, 'color' => 'purple', 'icon' => '📤'],
                ['label' => 'Cuadre',          'valor' => $cuadra ? '✅ CUADRA' : '❌ DESCUADRE', 'color' => $cuadra ? 'green' : 'red', 'icon' => '⚖️', 'es_pct' => true],
                ['label' => 'Cuentas activas', 'valor' => $lineas->count(), 'color' => 'gray', 'icon' => '📋', 'es_pct' => true],
            ],
        ];
    }

    // ══════════════════════════════════════════════════════════════════════
    // 4B. LIBRO MAYOR — saldo inicial + movimiento del período + saldo final
    // ══════════════════════════════════════════════════════════════════════

    public static function libroMayor(Carbon $desde, Carbon $hasta, bool $soloConMovimiento = true): array
    {
        $company = Company::first();

        // Saldo inicial: todo lo contabilizado ANTES de $desde
        $inicial = static::lineasHasta($desde->copy()->subDay())
            ->join('accounting_accounts as aa', 'accounting_entry_lines.account_id', '=', 'aa.id')
            ->selectRaw('aa.codigo, SUM(accounting_entry_lines.debito) as deb, SUM(accounting_entry_lines.credito) as cre')
            ->groupBy('aa.codigo')
            ->get()
            ->keyBy('codigo');

        // Movimiento del período + datos de la cuenta
        $periodo = static::lineasPeriodo($desde, $hasta)
            ->join('accounting_accounts as aa', 'accounting_entry_lines.account_id', '=', 'aa.id')
            ->selectRaw('aa.codigo, aa.nombre, aa.naturaleza, aa.clase,
                         SUM(accounting_entry_lines.debito) as deb, SUM(accounting_entry_lines.credito) as cre')
            ->groupBy('aa.codigo', 'aa.nombre', 'aa.naturaleza', 'aa.clase')
            ->orderBy('aa.codigo')
            ->get();

        // Incluir también cuentas con saldo inicial pero SIN movimiento en el período
        // (si no se pide "solo con movimiento") para que el saldo final sea completo
        $codigosConPeriodo = $periodo->pluck('codigo')->all();
        $cuentasSoloConInicial = collect();
        if (!$soloConMovimiento) {
            $cuentasSoloConInicial = \App\Models\AccountingAccount::where('acepta_movimiento', true)
                ->whereIn('codigo', $inicial->keys()->diff($codigosConPeriodo))
                ->get()
                ->map(fn ($a) => (object) ['codigo' => $a->codigo, 'nombre' => $a->nombre, 'naturaleza' => $a->naturaleza, 'clase' => $a->clase, 'deb' => 0, 'cre' => 0]);
        }

        $cuentas = $periodo->concat($cuentasSoloConInicial)->sortBy('codigo')->map(function ($r) use ($inicial) {
            $ini = $inicial->get($r->codigo);
            $iniDeb = (float) ($ini->deb ?? 0);
            $iniCre = (float) ($ini->cre ?? 0);
            $saldoInicial = $r->naturaleza === 'debito' ? ($iniDeb - $iniCre) : ($iniCre - $iniDeb);

            $movDeb = round((float) $r->deb, 2);
            $movCre = round((float) $r->cre, 2);
            $saldoFinal = $r->naturaleza === 'debito'
                ? $saldoInicial + $movDeb - $movCre
                : $saldoInicial + $movCre - $movDeb;

            return [
                'codigo'        => $r->codigo,
                'nombre'        => $r->nombre,
                'clase'         => $r->clase,
                'naturaleza'    => $r->naturaleza,
                'saldo_inicial' => round($saldoInicial, 2),
                'debito'        => $movDeb,
                'credito'       => $movCre,
                'saldo_final'   => round($saldoFinal, 2),
            ];
        })
        ->when($soloConMovimiento, fn ($c) => $c->filter(fn ($r) => $r['debito'] > 0 || $r['credito'] > 0 || abs($r['saldo_inicial']) > 0.01))
        ->values();

        $totalDeb = $cuentas->sum('debito');
        $totalCre = $cuentas->sum('credito');
        $cuadra = abs($totalDeb - $totalCre) < 1;

        return [
            'tipo'          => 'libro_mayor',
            'titulo'        => 'Libro Mayor',
            'empresa'       => $company?->razon_social ?? 'Serviarrendar S.A.S',
            'nit'           => $company?->nit_completo ?? '',
            'desde'         => $desde->toDateString(),
            'hasta'         => $hasta->toDateString(),
            'periodo_label' => $desde->locale('es')->isoFormat('D MMM YYYY') . ' — ' . $hasta->locale('es')->isoFormat('D MMM YYYY'),
            'cuentas'       => $cuentas->toArray(),
            'total_debitos' => round($totalDeb, 2),
            'total_creditos'=> round($totalCre, 2),
            'cuadra'        => $cuadra,
            'diferencia'    => round(abs($totalDeb - $totalCre), 2),
            'kpis'          => [
                ['label' => 'Mov. débitos',    'valor' => $totalDeb, 'color' => 'blue',   'icon' => '📥'],
                ['label' => 'Mov. créditos',   'valor' => $totalCre, 'color' => 'purple', 'icon' => '📤'],
                ['label' => 'Cuadre',          'valor' => $cuadra ? '✅ CUADRA' : '❌ DESCUADRE', 'color' => $cuadra ? 'green' : 'red', 'icon' => '⚖️', 'es_pct' => true],
                ['label' => 'Cuentas activas', 'valor' => $cuentas->count(), 'color' => 'gray', 'icon' => '📋', 'es_pct' => true],
            ],
        ];
    }

    // ══════════════════════════════════════════════════════════════════════
    // 5. ANÁLISIS DE CARTERA POR ANTIGÜEDAD
    // ══════════════════════════════════════════════════════════════════════

    public static function analisisCartera(Carbon $hasta): array
    {
        $company = Company::first();

        $facturas = RentBill::whereIn('estado', ['pendiente','parcial','en_mora','vencida'])
            ->where('saldo_pendiente', '>', 0)
            ->with(['arrendatario', 'rentalContract.property'])
            ->get();

        $rangos = [
            '0-30 días'   => ['min' => 0,   'max' => 30,  'pct_prov' => 0,   'color' => '#16a34a'],
            '31-60 días'  => ['min' => 31,  'max' => 60,  'pct_prov' => 5,   'color' => '#d97706'],
            '61-90 días'  => ['min' => 61,  'max' => 90,  'pct_prov' => 10,  'color' => '#f59e0b'],
            '91-180 días' => ['min' => 91,  'max' => 180, 'pct_prov' => 15,  'color' => '#ef4444'],
            '181-360 días'=> ['min' => 181, 'max' => 360, 'pct_prov' => 33,  'color' => '#dc2626'],
            'Más de 360'  => ['min' => 361, 'max' => 9999,'pct_prov' => 100, 'color' => '#7f1d1d'],
        ];

        $porRango = collect($rangos)->map(fn($r, $label) => [
            'rango'        => $label,
            'pct_provision'=> $r['pct_prov'],
            'color'        => $r['color'],
            'facturas'     => [],
            'total_saldo'  => 0,
            'total_prov'   => 0,
        ])->toArray();

        foreach ($facturas as $bill) {
            $diasVencida = max(0, (int) $hasta->diffInDays($bill->fecha_limite_pago, false) * -1);
            $saldo       = (float) $bill->saldo_pendiente;

            foreach ($rangos as $label => $r) {
                if ($diasVencida >= $r['min'] && $diasVencida <= $r['max']) {
                    $prov = round($saldo * ($r['pct_prov'] / 100), 2);
                    $porRango[$label]['facturas'][] = [
                        'numero'       => $bill->numero,
                        'arrendatario' => $bill->arrendatario?->nombre_completo,
                        'inmueble'     => $bill->rentalContract?->property?->codigo,
                        'dias'         => $diasVencida,
                        'saldo'        => $saldo,
                        'mora'         => (float) $bill->mora_acumulada,
                        'provision'    => $prov,
                    ];
                    $porRango[$label]['total_saldo'] += $saldo;
                    $porRango[$label]['total_prov']  += $prov;
                    break;
                }
            }
        }

        $totalSaldo = array_sum(array_column($porRango, 'total_saldo'));
        $totalProv  = array_sum(array_column($porRango, 'total_prov'));

        return [
            'tipo'          => 'analisis_cartera',
            'titulo'        => 'Análisis de Cartera por Antigüedad',
            'empresa'       => $company?->razon_social ?? 'Serviarrendar S.A.S',
            'hasta'         => $hasta->toDateString(),
            'hasta_label'   => $hasta->locale('es')->isoFormat('D [de] MMMM [de] YYYY'),
            'por_rango'     => array_values($porRango),
            'total_saldo'   => round($totalSaldo, 2),
            'total_provision'=> round($totalProv, 2),
            'total_facturas'=> $facturas->count(),
            'kpis'          => [
                ['label' => 'Total cartera',   'valor' => $totalSaldo,         'color' => 'blue',   'icon' => '💰'],
                ['label' => 'Provisión req.',  'valor' => $totalProv,          'color' => 'orange', 'icon' => '🛡️'],
                ['label' => 'N° facturas',     'valor' => $facturas->count(),  'color' => 'gray',   'icon' => '📄', 'es_pct' => true],
                ['label' => '% Provisionado',  'valor' => $totalSaldo > 0 ? round(($totalProv/$totalSaldo)*100, 1).'%' : '0%', 'color' => 'red', 'icon' => '📊', 'es_pct' => true],
            ],
        ];
    }

    // ══════════════════════════════════════════════════════════════════════
    // 6. INFORME DE RETENCIONES
    // ══════════════════════════════════════════════════════════════════════

    public static function informeRetenciones(Carbon $desde, Carbon $hasta): array
    {
        $company = Company::first();

        // Retenciones practicadas (236xxx crédito)
        $practicadas = static::lineasPeriodo($desde, $hasta)
            ->whereHas('account', fn($q) => $q->where('codigo', 'like', '236%'))
            ->with(['third', 'account'])
            ->get()
            ->groupBy(fn($l) => $l->third_id . '|' . ($l->account?->codigo ?? ''))
            ->map(fn($g) => [
                'tercero'     => $g->first()->third?->nombre_completo ?? 'Sin tercero',
                'nit'         => $g->first()->third?->numero_documento ?? '',
                'cuenta'      => $g->first()->account?->codigo,
                'cuenta_nom'  => $g->first()->account?->nombre,
                'valor'       => round($g->sum('credito') - $g->sum('debito'), 2),
                'base'        => round((float)$g->max('base_retencion'), 2),
            ])
            ->filter(fn($r) => $r['valor'] > 0)
            ->sortByDesc('valor')->values();

        // Retenciones a favor (136xxx débito)
        $aFavor = static::lineasPeriodo($desde, $hasta)
            ->whereHas('account', fn($q) => $q->where('codigo', 'like', '136%'))
            ->with(['third', 'account'])
            ->get()
            ->groupBy('third_id')
            ->map(fn($g) => [
                'tercero' => $g->first()->third?->nombre_completo ?? 'Sin tercero',
                'nit'     => $g->first()->third?->numero_documento ?? '',
                'valor'   => round($g->sum('debito'), 2),
                'base'    => round((float)$g->max('base_retencion'), 2),
            ])
            ->filter(fn($r) => $r['valor'] > 0)
            ->sortByDesc('valor')->values();

        $totalPracticadas = $practicadas->sum('valor');
        $totalAFavor      = $aFavor->sum('valor');
        $neto             = $totalPracticadas - $totalAFavor;

        return [
            'tipo'               => 'informe_retenciones',
            'titulo'             => 'Informe de Retenciones',
            'empresa'            => $company?->razon_social ?? 'Serviarrendar S.A.S',
            'desde'              => $desde->toDateString(),
            'hasta'              => $hasta->toDateString(),
            'periodo_label'      => $desde->locale('es')->isoFormat('D MMM YYYY') . ' — ' . $hasta->locale('es')->isoFormat('D MMM YYYY'),
            'practicadas'        => $practicadas->toArray(),
            'a_favor'            => $aFavor->toArray(),
            'total_practicadas'  => round($totalPracticadas, 2),
            'total_a_favor'      => round($totalAFavor, 2),
            'neto'               => round($neto, 2),
            'kpis'               => [
                ['label' => 'Rete. practicadas', 'valor' => $totalPracticadas, 'color' => 'red',    'icon' => '📤'],
                ['label' => 'Rete. a favor',      'valor' => $totalAFavor,     'color' => 'green',  'icon' => '📥'],
                ['label' => 'Neto a pagar',       'valor' => $neto,            'color' => $neto >= 0 ? 'blue' : 'green', 'icon' => '⚖️'],
                ['label' => 'N° terceros',        'valor' => $practicadas->count(), 'color' => 'gray', 'icon' => '👥', 'es_pct' => true],
            ],
        ];
    }

    // ══════════════════════════════════════════════════════════════════════
    // 7. INFORME DE COMISIONES E INGRESOS
    // ══════════════════════════════════════════════════════════════════════

    public static function informeComisiones(Carbon $desde, Carbon $hasta): array
    {
        $company = Company::first();

        $ingresos = static::lineasPeriodo($desde, $hasta)
            ->whereHas('account', fn($q) => $q->whereIn('codigo', ['413505','413510','421010']))
            ->get();

        $porCuenta = $ingresos->groupBy('account.codigo')->map(fn($g) => [
            'cuenta'   => $g->first()->account?->codigo,
            'nombre'   => $g->first()->account?->nombre,
            'total'    => round($g->sum('credito') - $g->sum('debito'), 2),
            'cantidad' => $g->count(),
        ])->values();

        $porMes = $ingresos->groupBy(fn($l) => Carbon::parse($l->entry?->fecha)->format('Y-m'))
            ->map(fn($g, $mes) => [
                'mes'   => Carbon::parse($mes . '-01')->locale('es')->isoFormat('MMM YY'),
                'total' => round($g->sum('credito') - $g->sum('debito'), 2),
            ])->sortKeys()->values();

        $totalComision = $ingresos
            ->filter(fn($l) => $l->account?->codigo === '413505')
            ->sum(fn($l) => $l->credito - $l->debito);

        $totalAdmon = $ingresos
            ->filter(fn($l) => $l->account?->codigo === '413510')
            ->sum(fn($l) => $l->credito - $l->debito);

        $totalMora = $ingresos
            ->filter(fn($l) => $l->account?->codigo === '421010')
            ->sum(fn($l) => $l->credito - $l->debito);

        $totalIngresos = $totalComision + $totalAdmon + $totalMora;

        return [
            'tipo'            => 'informe_comisiones',
            'titulo'          => 'Informe de Comisiones e Ingresos',
            'empresa'         => $company?->razon_social ?? 'Serviarrendar S.A.S',
            'desde'           => $desde->toDateString(),
            'hasta'           => $hasta->toDateString(),
            'periodo_label'   => $desde->locale('es')->isoFormat('D MMM YYYY') . ' — ' . $hasta->locale('es')->isoFormat('D MMM YYYY'),
            'por_cuenta'      => $porCuenta->toArray(),
            'por_mes'         => $porMes->toArray(),
            'total_comision'  => round((float)$totalComision, 2),
            'total_admon'     => round((float)$totalAdmon, 2),
            'total_mora'      => round((float)$totalMora, 2),
            'total_ingresos'  => round($totalIngresos, 2),
            'kpis'            => [
                ['label' => 'Comisiones arrend.',  'valor' => $totalComision,  'color' => 'blue',   'icon' => '🏠'],
                ['label' => 'Ingr. administración','valor' => $totalAdmon,     'color' => 'indigo', 'icon' => '📋'],
                ['label' => 'Intereses mora',       'valor' => $totalMora,     'color' => 'orange', 'icon' => '⏰'],
                ['label' => 'Total ingresos',       'valor' => $totalIngresos, 'color' => 'green',  'icon' => '💰'],
            ],
        ];
    }

    // ══════════════════════════════════════════════════════════════════════
    // 8. CONCILIACIÓN DE IVA
    // ══════════════════════════════════════════════════════════════════════

    public static function conciliacionIVA(Carbon $desde, Carbon $hasta): array
    {
        $company = Company::first();

        $ivaGenerado = static::lineasPeriodo($desde, $hasta)
            ->whereHas('account', fn($q) => $q->where('codigo', '240805'))
            ->selectRaw('SUM(credito) as cre, SUM(debito) as deb')
            ->first();

        $baseComision = static::lineasPeriodo($desde, $hasta)
            ->whereHas('account', fn($q) => $q->where('codigo', '413505'))
            ->sum('credito');

        $generado    = round((float)($ivaGenerado?->cre ?? 0) - (float)($ivaGenerado?->deb ?? 0), 2);
        $descontable = 0;
        $saldo       = $generado - $descontable;

        // Detalle mensual
        $porMes = static::lineasPeriodo($desde, $hasta)
            ->whereHas('account', fn($q) => $q->where('codigo', '240805'))
            ->join('accounting_entries as ae', 'accounting_entry_lines.entry_id', '=', 'ae.id')
            ->selectRaw("DATE_FORMAT(ae.fecha, '%Y-%m') as mes,
                         SUM(accounting_entry_lines.credito) as iva_gen,
                         SUM(accounting_entry_lines.debito)  as iva_des")
            ->groupBy('mes')
            ->orderBy('mes')
            ->get()
            ->map(fn($r) => [
                'mes'          => Carbon::parse($r->mes . '-01')->locale('es')->isoFormat('MMM YYYY'),
                'iva_generado' => round((float)$r->iva_gen, 2),
                'iva_descontable' => 0,
                'saldo'        => round((float)$r->iva_gen, 2),
            ]);

        return [
            'tipo'             => 'conciliacion_iva',
            'titulo'           => 'Conciliación de IVA',
            'empresa'          => $company?->razon_social ?? 'Serviarrendar S.A.S',
            'desde'            => $desde->toDateString(),
            'hasta'            => $hasta->toDateString(),
            'periodo_label'    => $desde->locale('es')->isoFormat('D MMM YYYY') . ' — ' . $hasta->locale('es')->isoFormat('D MMM YYYY'),
            'base_comisiones'  => round((float)$baseComision, 2),
            'iva_generado'     => $generado,
            'iva_descontable'  => $descontable,
            'saldo'            => $saldo,
            'por_mes'          => $porMes->toArray(),
            'kpis'             => [
                ['label' => 'Base comisiones',  'valor' => $baseComision, 'color' => 'gray',   'icon' => '📊'],
                ['label' => 'IVA generado 19%', 'valor' => $generado,    'color' => 'blue',   'icon' => '📈'],
                ['label' => 'IVA descontable',  'valor' => $descontable, 'color' => 'green',  'icon' => '📉'],
                ['label' => 'Saldo a pagar',    'valor' => $saldo,       'color' => 'red',    'icon' => '💸'],
            ],
        ];
    }
}
