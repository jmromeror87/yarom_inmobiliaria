<?php

namespace App\Livewire;

use Livewire\Component;

class MapboxDireccion extends Component
{
    public string $statePath  = '';
    public string $latPath    = '';
    public string $lngPath    = '';
    public string $query      = '';

    public function mount(
        string $statePath = '',
        string $latPath   = '',
        string $lngPath   = '',
        string $state     = '',
    ): void {
        $this->statePath = $statePath;
        $this->latPath   = $latPath;
        $this->lngPath   = $lngPath;
        $this->query     = $state;
    }

    public function render()
    {
        return view('livewire.mapbox-direccion', [
            'apiKey' => config('services.mapbox.key'),
        ]);
    }
}
