<?php

namespace App\Filament\Widgets;

use App\Models\Third;
use Filament\Widgets\Widget;

class ThirdsStatsWidget extends Widget
{
    protected static bool $isLazy = false;
    protected int | string | array $columnSpan = 'full';
    protected string $view = 'filament.widgets.thirds-stats';

    public function filterTable(string $filter, string $value): void
    {
        $this->dispatch('thirds-filter', filter: $filter, value: $value);
    }

    public function clearFilter(): void
    {
        $this->dispatch('thirds-filter-clear');
    }

    public function getViewData(): array
    {
        $total         = Third::count();
        $propietarios  = Third::where('es_propietario', true)->count();
        $arrendatarios = Third::where('es_arrendatario', true)->count();
        $aprobados     = Third::where('estado_crediticio', 'aprobado')->count();
        $pendientes    = Third::where('estado_crediticio', 'sin_evaluar')->count();
        $activos       = Third::where('is_active', true)->count();
        $inactivos     = $total - $activos;
        $compradores   = Third::where('es_cliente_compra', true)->count();
        $fiadores      = Third::where('es_fiador', true)->count();
        $proveedores   = Third::where('es_proveedor', true)->count();

        return compact(
            'total','propietarios','arrendatarios',
            'aprobados','pendientes','activos','inactivos',
            'compradores','fiadores','proveedores'
        );
    }
}
