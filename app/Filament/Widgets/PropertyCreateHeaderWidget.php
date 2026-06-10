<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class PropertyCreateHeaderWidget extends Widget
{
    protected static bool $isLazy = false;
    protected int|string|array $columnSpan = 'full';
    protected string $view = 'filament.widgets.property-create-header';
}
