<?php

namespace App\Livewire;

use Filament\Widgets\ChartWidget;

class IngresosGastosWidget extends ChartWidget
{
    protected ?string $heading = 'Ingresos Gastos Widget';

    protected function getData(): array
    {
        return [
            //
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
