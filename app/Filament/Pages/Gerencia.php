<?php

namespace App\Filament\Pages;

use App\Models\AccountingEntry;
use App\Models\AccountingEntryLine;
use App\Models\OwnerLiquidation;
use App\Models\Property;
use App\Models\RentBill;
use App\Models\RentPayment;
use App\Models\RentalContract;
use Filament\Pages\Page;

class Gerencia extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static ?string $navigationLabel = 'Indicadores';

    protected static string|\UnitEnum|null $navigationGroup = 'Gerencia';

    protected static ?string $title = 'Panel Gerencial';

    protected static ?int $navigationSort = -10;

    protected string $view = 'filament.pages.gerencia';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['gerente', 'super_admin']) ?? false;
    }

    public function getViewData(): array
    {
        $hoy = now()->startOfDay();
        $mes = now()->month;
        $anio = now()->year;

        // ── Hoy: entra / sale ──────────────────────────────────────
        $ingresoHoy = (float) RentPayment::whereDate('fecha_pago', $hoy)->sum('total_pagado');
        $giroHoy    = (float) OwnerLiquidation::whereDate('fecha_giro', $hoy)->where('estado', 'pagada')->sum('total_giro');

        $gastosHoy = (float) AccountingEntryLine::whereHas('entry', fn ($q) => $q->where('tipo', 'CE')->whereDate('fecha', $hoy))
            ->whereHas('account', fn ($q) => $q->where('codigo', 'like', '5%'))
            ->sum('debito');

        // ── Mes: facturado / recaudado / efectividad ────────────────
        $facturadoMes = (float) RentBill::where('mes', $mes)->where('anio', $anio)->sum('total_factura');
        $recaudadoMes = (float) RentPayment::whereYear('fecha_pago', $anio)->whereMonth('fecha_pago', $mes)->sum('total_pagado');
        $efectividad  = $facturadoMes > 0 ? round($recaudadoMes / $facturadoMes * 100, 1) : 0;

        // ── Cartera / mora ──────────────────────────────────────────
        $carteraPendiente = (float) RentBill::whereIn('estado', ['pendiente', 'parcial', 'en_mora', 'vencida'])->sum('saldo_pendiente');
        $facturasEnMora   = RentBill::where('estado', 'en_mora')->count();

        // ── Inquilinos que deben (mes actual) ────────────────────────
        $inquilinosDeben = RentBill::with(['arrendatario', 'property'])
            ->where('mes', $mes)->where('anio', $anio)
            ->whereIn('estado', ['pendiente', 'parcial', 'en_mora', 'vencida'])
            ->orderByDesc('saldo_pendiente')
            ->limit(8)
            ->get();
        $totalInquilinosDeben = RentBill::where('mes', $mes)->where('anio', $anio)
            ->whereIn('estado', ['pendiente', 'parcial', 'en_mora', 'vencida'])->count();

        // ── Inquilinos que pagaron (mes actual) ──────────────────────
        $inquilinosPagaron = RentPayment::with(['arrendatario', 'bill.property'])
            ->whereYear('fecha_pago', $anio)->whereMonth('fecha_pago', $mes)
            ->orderByDesc('fecha_pago')
            ->limit(8)
            ->get();
        $totalInquilinosPagaron = RentPayment::whereYear('fecha_pago', $anio)->whereMonth('fecha_pago', $mes)
            ->distinct('arrendatario_id')->count('arrendatario_id');

        // ── Propietarios: pagados / pendientes (mes actual) ──────────
        $propietariosPagados = OwnerLiquidation::with('propietario')
            ->whereYear('fecha_giro', $anio)->whereMonth('fecha_giro', $mes)
            ->where('estado', 'pagada')
            ->orderByDesc('fecha_giro')
            ->limit(8)->get();
        $totalPropietariosPagados = OwnerLiquidation::whereYear('fecha_giro', $anio)->whereMonth('fecha_giro', $mes)
            ->where('estado', 'pagada')->count();
        $montoPropietariosPagados = (float) OwnerLiquidation::whereYear('fecha_giro', $anio)->whereMonth('fecha_giro', $mes)
            ->where('estado', 'pagada')->sum('total_giro');

        $propietariosPendientes = OwnerLiquidation::with('propietario')
            ->whereIn('estado', ['pendiente', 'aprobada'])
            ->orderByDesc('total_giro')
            ->limit(8)->get();
        $totalPropietariosPendientes = OwnerLiquidation::whereIn('estado', ['pendiente', 'aprobada'])->count();
        $montoPropietariosPendientes = (float) OwnerLiquidation::whereIn('estado', ['pendiente', 'aprobada'])->sum('total_giro');

        // ── Utilidad neta / margen (comisión ganada - gastos) ────────
        $comisionMes = (float) OwnerLiquidation::whereYear('created_at', $anio)->whereMonth('created_at', $mes)->sum('comision_valor');
        $ivaComisionMes = (float) OwnerLiquidation::whereYear('created_at', $anio)->whereMonth('created_at', $mes)->sum('iva_comision');
        $ingresoOperativoMes = $comisionMes; // ingreso propio de la inmobiliaria (sin IVA, que es un pasivo)

        $gastosMes = (float) AccountingEntryLine::whereHas('entry', fn ($q) => $q->where('tipo', 'CE')->whereYear('fecha', $anio)->whereMonth('fecha', $mes))
            ->whereHas('account', fn ($q) => $q->where('codigo', 'like', '5%'))
            ->sum('debito');

        $utilidadNeta = $ingresoOperativoMes - $gastosMes;
        $margen = $ingresoOperativoMes > 0 ? round($utilidadNeta / $ingresoOperativoMes * 100, 1) : 0;

        // ── Gastos del mes (detalle) ──────────────────────────────────
        $gastosDetalle = \App\Models\AccountingEntry::where('tipo', 'CE')
            ->whereYear('fecha', $anio)->whereMonth('fecha', $mes)
            ->whereIn('estado', ['contabilizado', 'aprobado'])
            ->with('third')
            ->orderByDesc('fecha')
            ->limit(8)
            ->get();
        $totalGastosMes = AccountingEntry::where('tipo', 'CE')
            ->whereYear('fecha', $anio)->whereMonth('fecha', $mes)
            ->whereIn('estado', ['contabilizado', 'aprobado'])
            ->count();

        // ── Ocupación general ─────────────────────────────────────────
        $totalInm = Property::count();
        $arrendados = Property::where('estado', 'arrendado')->count();
        $ocupacion = $totalInm > 0 ? round($arrendados / $totalInm * 100) : 0;

        $contratosActivos = RentalContract::where('estado', 'activo')->count();
        $porVencer30 = RentalContract::where('estado', 'activo')
            ->whereBetween('fecha_fin', [now(), now()->addDays(30)])->count();

        return compact(
            'ingresoHoy', 'giroHoy', 'gastosHoy',
            'facturadoMes', 'recaudadoMes', 'efectividad',
            'carteraPendiente', 'facturasEnMora',
            'inquilinosDeben', 'totalInquilinosDeben',
            'inquilinosPagaron', 'totalInquilinosPagaron',
            'propietariosPagados', 'totalPropietariosPagados', 'montoPropietariosPagados',
            'propietariosPendientes', 'totalPropietariosPendientes', 'montoPropietariosPendientes',
            'comisionMes', 'ivaComisionMes', 'gastosMes', 'gastosDetalle', 'totalGastosMes', 'utilidadNeta', 'margen',
            'totalInm', 'arrendados', 'ocupacion', 'contratosActivos', 'porVencer30',
        );
    }
}
