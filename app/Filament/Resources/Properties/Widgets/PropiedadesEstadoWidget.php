<?php

namespace App\Filament\Resources\Properties\Widgets;

use Filament\Widgets\ChartWidget;

class PropiedadesEstadoWidget extends ChartWidget
{
    protected ?string $heading = 'Propiedades Estado Widget';

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
