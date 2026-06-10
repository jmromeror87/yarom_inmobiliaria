<?php

namespace App\Filament\Widgets;

use App\Models\Third;
use Filament\Widgets\Widget;

class ThirdHeroWidget extends Widget
{
    protected static bool $isLazy = false;
    protected int | string | array $columnSpan = 'full';
    protected string $view = 'filament.widgets.third-hero';

    public function getViewData(): array
    {
        $id     = request()->route('record');
        $record = $id ? Third::with(['municipio', 'departamento'])->find($id) : null;
        return ['record' => $record];
    }
}
