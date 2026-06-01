<?php

namespace App\Forms\Components;

use Filament\Forms\Components\TextInput;

class MapboxAddressInput extends TextInput
{
    protected string $view = 'forms.components.mapbox-address-input';

    public string $latField = '';
    public string $lngField = '';

    public function latField(string $field): static
    {
        $this->latField = $field;
        return $this;
    }

    public function lngField(string $field): static
    {
        $this->lngField = $field;
        return $this;
    }

    public function getLatField(): string { return $this->latField; }
    public function getLngField(): string { return $this->lngField; }
}
