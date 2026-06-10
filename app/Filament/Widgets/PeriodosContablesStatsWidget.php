<?php

namespace App\Filament\Widgets;

use App\Models\AccountingPeriod;
use Filament\Widgets\Widget;

class PeriodosContablesStatsWidget extends Widget
{
    protected string $view = 'filament.widgets.periodos-contables-stats';
    protected int|string|array $columnSpan = 'full';
    protected static ?int $sort = 1;
    public static function canView(): bool { return true; }

    public function getViewData(): array
    {
        $periodos = AccountingPeriod::withCount('entries')->get();

        $abiertos  = $periodos->where('estado', 'abierto')->count();
        $cerrados  = $periodos->where('estado', 'cerrado')->count();
        $total     = $periodos->count();
        $comprobantes = $periodos->sum('entries_count');

        $periodoActual = AccountingPeriod::where('anio', now()->year)
            ->where('mes', now()->month)
            ->first();

        $mesNames = [
            1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',
            5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',
            9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre',
        ];

        return compact('abiertos', 'cerrados', 'total', 'comprobantes', 'periodoActual', 'mesNames');
    }
}
