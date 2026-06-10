<?php

namespace App\Filament\Widgets;

use App\Models\RentBill;
use Filament\Widgets\Widget;

class FacturacionStatsWidget extends Widget
{
    protected string $view = 'filament.widgets.facturacion-stats';
    protected int|string|array $columnSpan = 'full';
    protected static ?int $sort = 1;
    public static function canView(): bool { return true; }

    public function getViewData(): array
    {
        $mes  = now()->month;
        $anio = now()->year;
        $fmt  = fn ($v) => '$' . number_format((float) $v, 0, ',', '.') . ' COP';

        $totalFacturado = (float) RentBill::where('mes', $mes)->where('anio', $anio)->sum('total_factura');
        $totalRecaudado = (float) RentBill::where('mes', $mes)->where('anio', $anio)->where('estado', 'pagada')->sum('total_factura');
        $totalMora      = (float) RentBill::where('estado', 'en_mora')->sum('mora_acumulada');
        $pendientes     = RentBill::whereIn('estado', ['pendiente', 'parcial', 'en_mora'])->count();
        $efectividad    = $totalFacturado > 0 ? round($totalRecaudado / $totalFacturado * 100, 1) : 0;
        $periodoLabel   = now()->translatedFormat('F Y');

        return compact('totalFacturado', 'totalRecaudado', 'totalMora', 'pendientes', 'efectividad', 'periodoLabel', 'fmt');
    }
}
