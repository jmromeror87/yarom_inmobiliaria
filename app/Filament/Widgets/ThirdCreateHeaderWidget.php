<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class ThirdCreateHeaderWidget extends Widget
{
    protected static bool $isLazy = false;
    protected int | string | array $columnSpan = 'full';
    protected string $view = 'filament.widgets.third-create-header';
}
