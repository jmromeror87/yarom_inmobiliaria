<?php

namespace App\Filament\Widgets;

use App\Models\Property;
use Filament\Widgets\Widget;

class PropertyHeroWidget extends Widget
{
    protected static bool $isLazy = false;
    protected int|string|array $columnSpan = 'full';
    protected string $view = 'filament.widgets.property-hero';

    public function getViewData(): array
    {
        $id = request()->route('record');
        $record = $id ? Property::with(['tipo', 'propietario', 'municipio'])->find($id) : null;
        return ['record' => $record];
    }
}
