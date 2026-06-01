<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\OwnerLiquidations\OwnerLiquidationResource;
use App\Filament\Resources\RentBills\RentBillResource;
use App\Models\OwnerLiquidation;
use App\Models\RentBill;
use App\Models\RentPayment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class KPIsCarteraWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $mes  = now()->month;
        $anio = now()->year;
        $mesAnt  = now()->subMonth()->month;
        $anioAnt = now()->subMonth()->year;

        $fmt = fn ($v) => '$' . number_format((float) $v, 0, ',', '.') . ' COP';

        // ── Facturado ──────────────────────────────────────────────────────
        $facturadoMes = (float) RentBill::where('mes', $mes)->where('anio', $anio)->sum('total_factura');
        $facturadoAnt = (float) RentBill::where('mes', $mesAnt)->where('anio', $anioAnt)->sum('total_factura');
        [$fIcon, $fColor, $fDiff] = self::trend($facturadoMes, $facturadoAnt, true);

        // ── Recaudado ──────────────────────────────────────────────────────
        $recaudadoMes = (float) RentPayment::whereYear('fecha_pago', $anio)->whereMonth('fecha_pago', $mes)->sum('total_pagado');
        $recaudadoAnt = (float) RentPayment::whereYear('fecha_pago', $anioAnt)->whereMonth('fecha_pago', $mesAnt)->sum('total_pagado');
        [$rIcon, $rColor, $rDiff] = self::trend($recaudadoMes, $recaudadoAnt, true);

        $efectividad = $facturadoMes > 0 ? round($recaudadoMes / $facturadoMes * 100, 1) : 0;
        $efecAnt     = $facturadoAnt > 0 ? round($recaudadoAnt / $facturadoAnt * 100, 1) : 0;
        [$eIcon, $eColor, $eDiff] = self::trend($efectividad, $efecAnt, true);

        // ── Cartera pendiente ─────────────────────────────────────────────
        $cartera    = (float) RentBill::whereIn('estado', ['pendiente', 'parcial', 'en_mora', 'vencida'])->sum('saldo_pendiente');
        $carteraAnt = (float) RentBill::whereIn('estado', ['pendiente', 'parcial', 'en_mora', 'vencida'])
            ->where(fn ($q) => $q->where('anio', $anioAnt)->where('mes', $mesAnt))->sum('saldo_pendiente');
        [$cIcon, $cColor, $cDiff] = self::trend($cartera, $carteraAnt, false); // subir = malo

        $factPendientes = RentBill::whereIn('estado', ['pendiente', 'parcial', 'en_mora', 'vencida'])->count();

        // ── Mora ──────────────────────────────────────────────────────────
        $totalMora   = (float) RentBill::where('estado', 'en_mora')->sum('mora_acumulada');
        $moraAnt     = (float) RentBill::where('estado', 'en_mora')
            ->where('anio', $anioAnt)->where('mes', $mesAnt)->sum('mora_acumulada');
        $factsMora   = RentBill::where('estado', 'en_mora')->count();
        $diasMoraProm= (int) RentBill::where('estado', 'en_mora')->avg('dias_mora');
        [$mIcon, $mColor, $mDiff] = self::trend($totalMora, $moraAnt, false); // subir = malo

        // ── Liquidaciones pendientes ──────────────────────────────────────
        $liquidPend  = (float) OwnerLiquidation::where('estado', 'pendiente')->sum('total_giro');
        $liquidCount = OwnerLiquidation::where('estado', 'pendiente')->count();
        $liquidAnt   = (float) OwnerLiquidation::where('estado', 'pendiente')
            ->whereMonth('created_at', $mesAnt)->sum('total_giro');
        [$lIcon, $lColor, $lDiff] = self::trend($liquidPend, $liquidAnt, false); // subir = malo

        // Charts recaudo últimos 6 meses
        $chartRec = collect(range(5, 0))->map(fn ($i) =>
            (float) RentPayment::whereYear('fecha_pago', now()->subMonths($i)->year)
                ->whereMonth('fecha_pago', now()->subMonths($i)->month)->sum('total_pagado')
        )->values()->toArray();

        $chartEfec = collect(range(5, 0))->map(function ($i) {
            $d = now()->subMonths($i);
            $f = (float) RentBill::where('mes', $d->month)->where('anio', $d->year)->sum('total_factura');
            $r = (float) RentPayment::whereYear('fecha_pago', $d->year)->whereMonth('fecha_pago', $d->month)->sum('total_pagado');
            return $f > 0 ? round($r / $f * 100, 0) : 0;
        })->values()->toArray();

        $baseFact  = RentBillResource::getUrl('index');
        $baseLiqui = OwnerLiquidationResource::getUrl('index');

        return [
            Stat::make('Facturado ' . now()->translatedFormat('F Y'), $fmt($facturadoMes))
                ->description($fDiff)
                ->descriptionIcon($fIcon)
                ->color($fColor)
                ->url($baseFact . '?tableFilters[estado][value]=pendiente'),

            Stat::make('Recaudado ' . now()->translatedFormat('F Y'), $fmt($recaudadoMes))
                ->description($rDiff . ' · ' . $fmt($facturadoMes - $recaudadoMes) . ' por recaudar')
                ->descriptionIcon($rIcon)
                ->color($rColor)
                ->chart($chartRec)
                ->url($baseFact . '?tableFilters[estado][value]=pagada'),

            Stat::make('Efectividad de recaudo', $efectividad . '%')
                ->description($eDiff)
                ->descriptionIcon($eIcon)
                ->color($efectividad >= 90 ? 'success' : ($efectividad >= 70 ? 'warning' : 'danger'))
                ->chart($chartEfec)
                ->url($baseFact),

            Stat::make('Cartera pendiente', $fmt($cartera))
                ->description($cDiff . ' · ' . $factPendientes . ' facturas sin cobrar')
                ->descriptionIcon($cIcon)
                ->color($cColor)
                ->url($baseFact . '?tableFilters[estado][value]=en_mora'),

            Stat::make('Mora acumulada', $fmt($totalMora))
                ->description($mDiff . ' · ' . $factsMora . ' facturas · ' . $diasMoraProm . ' días prom.')
                ->descriptionIcon($mIcon)
                ->color($mColor)
                ->url($baseFact . '?tableFilters[estado][value]=en_mora'),

            Stat::make('Liquidaciones pendientes', $fmt($liquidPend))
                ->description($lDiff . ' · ' . $liquidCount . ' pendiente(s) de girar')
                ->descriptionIcon($lIcon)
                ->color($liquidCount > 0 ? 'warning' : 'success')
                ->url($baseLiqui . '?tableFilters[estado][value]=pendiente'),
        ];
    }

    private static function trend(float $actual, float $anterior, bool $positivoEsBueno): array
    {
        if ($anterior == 0) {
            return ['heroicon-m-minus', 'gray', 'Sin datos del mes anterior'];
        }
        $diff  = round(($actual - $anterior) / $anterior * 100, 1);
        $igual = abs($diff) < 0.1;

        if ($igual) {
            return ['heroicon-m-minus', 'gray', 'Sin cambios vs mes anterior'];
        }

        $sube  = $diff > 0;
        $bueno = $positivoEsBueno ? $sube : !$sube;
        $icon  = $sube ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';
        $color = $bueno ? 'success' : 'danger';
        $signo = $sube ? '+' : '';

        return [$icon, $color, "{$signo}{$diff}% vs mes anterior"];
    }
}
