<?php

namespace App\Filament\Widgets;

use App\Models\Property;
use Filament\Widgets\Widget;

class PropertiesStatsWidget extends Widget
{
    protected static bool $isLazy = false;
    protected int|string|array $columnSpan = 'full';
    protected string $view = 'filament.widgets.properties-stats';

    public function filterTable(string $filter, string $value): void
    {
        $this->dispatch('properties-filter', filter: $filter, value: $value);
    }

    public function clearFilter(): void
    {
        $this->dispatch('properties-filter-clear');
    }

    public function getViewData(): array
    {
        $total       = Property::count();
        $disponibles = Property::where('estado', 'disponible')->count();
        $arrendados  = Property::where('estado', 'arrendado')->count();
        $enVenta     = Property::where('estado', 'en_venta')->count();
        $captacion   = Property::where('estado', 'en_captacion')->count();
        $mantenimiento = Property::where('estado', 'en_mantenimiento')->count();
        $enVenta2    = Property::where('disponible_venta', true)->count();
        $canonTotal  = Property::where('estado', 'arrendado')->sum('canon_arriendo');
        $ocupacion   = $total > 0 ? round(($arrendados / $total) * 100) : 0;

        return compact(
            'total', 'disponibles', 'arrendados', 'enVenta',
            'captacion', 'mantenimiento', 'enVenta2', 'canonTotal', 'ocupacion'
        );
    }
}
