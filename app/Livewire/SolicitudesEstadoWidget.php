<?php

namespace App\Livewire;

use Filament\Widgets\ChartWidget;

class SolicitudesEstadoWidget extends ChartWidget
{
    protected ?string $heading = 'Solicitudes Estado Widget';

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
