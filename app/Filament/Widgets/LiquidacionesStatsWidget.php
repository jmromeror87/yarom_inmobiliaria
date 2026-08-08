<?php

namespace App\Filament\Widgets;

use App\Models\OwnerLiquidation;
use Filament\Widgets\Widget;

class LiquidacionesStatsWidget extends Widget
{
    protected string $view = 'filament.widgets.liquidaciones-stats';
    protected int|string|array $columnSpan = 'full';
    protected static ?int $sort = 1;
    public static function canView(): bool { return true; }

    public function getViewData(): array
    {
        $mes  = now()->month;
        $anio = now()->year;
        $fmt  = fn ($v) => '$' . number_format((float) $v, 0, ',', '.') . ' COP';

        $pendienteCount = OwnerLiquidation::where('estado', 'pendiente')->count();
        $pendienteSuma  = (float) OwnerLiquidation::where('estado', 'pendiente')->sum('total_giro');

        $aprobadaCount = OwnerLiquidation::where('estado', 'aprobada')->count();
        $aprobadaSuma  = (float) OwnerLiquidation::where('estado', 'aprobada')->sum('total_giro');

        $pagadaMesCount = OwnerLiquidation::where('estado', 'pagada')->where('mes', $mes)->where('anio', $anio)->count();
        $pagadaMesSuma  = (float) OwnerLiquidation::where('estado', 'pagada')->where('mes', $mes)->where('anio', $anio)->sum('total_giro');

        $anuladaCount = OwnerLiquidation::where('estado', 'anulada')->count();

        $porGirar = $pendienteSuma + $aprobadaSuma;
        $periodoLabel = now()->translatedFormat('F Y');

        return compact(
            'pendienteCount', 'pendienteSuma',
            'aprobadaCount', 'aprobadaSuma',
            'pagadaMesCount', 'pagadaMesSuma',
            'anuladaCount',
            'porGirar', 'periodoLabel', 'fmt'
        );
    }
}
