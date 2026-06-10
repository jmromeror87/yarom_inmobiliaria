<?php

namespace App\Filament\Widgets;

use App\Models\AccountingEntry;
use App\Models\AccountingPeriod;
use Filament\Widgets\Widget;

class ComprobantesStatsWidget extends Widget
{
    protected string $view = 'filament.widgets.comprobantes-stats';
    protected int|string|array $columnSpan = 'full';
    protected static ?int $sort = 1;
    public static function canView(): bool { return true; }

    public function getViewData(): array
    {
        $total         = AccountingEntry::count();
        $contabilizados = AccountingEntry::where('estado', 'contabilizado')->count();
        $borradores    = AccountingEntry::where('estado', 'borrador')->count();
        $anulados      = AccountingEntry::where('estado', 'anulado')->count();

        $periodoActual = AccountingPeriod::actual();
        $enPeriodoActual = $periodoActual
            ? AccountingEntry::where('period_id', $periodoActual->id)->count()
            : 0;

        $totalDebitos = AccountingEntry::where('estado', 'contabilizado')
            ->sum('total_debitos');

        return compact(
            'total', 'contabilizados', 'borradores', 'anulados',
            'periodoActual', 'enPeriodoActual', 'totalDebitos'
        );
    }
}
