<?php

namespace App\Livewire;

use App\Services\AgenteService;
use Livewire\Component;

class InmoChat extends Component
{
    public bool   $abierto  = false;
    public string $input    = '';
    public array  $mensajes = [];

    public array $sugerencias = [
        'Resumen del sistema',
        'Arrendatarios morosos',
        'Contratos por vencer',
        'Inmuebles disponibles',
    ];

    public function toggle(): void
    {
        $this->abierto = !$this->abierto;
    }

    public function enviar(): void
    {
        $texto = trim($this->input);
        if (empty($texto)) return;

        $this->mensajes[] = ['rol' => 'user', 'texto' => $texto];
        $this->input      = '';
        $this->dispatch('inmo-scroll');

        $historial = collect($this->mensajes)
            ->map(fn($m) => [
                'role'    => $m['rol'] === 'user' ? 'user' : 'assistant',
                'content' => $m['texto'],
            ])->values()->toArray();

        try {
            $resultado = app(AgenteService::class)->chat($historial);
            $this->mensajes[] = [
                'rol'          => 'bot',
                'texto'        => $resultado['texto'],
                'herramientas' => $resultado['herramientas_usadas'] ?? [],
            ];
        } catch (\Throwable $e) {
            $this->mensajes[] = ['rol' => 'bot', 'texto' => 'Error al procesar: ' . $e->getMessage(), 'herramientas' => []];
        }

        $this->dispatch('inmo-done');
        $this->dispatch('inmo-scroll');
    }

    public function sugerir(string $texto): void
    {
        $this->input = $texto;
        $this->enviar();
    }

    public function limpiar(): void
    {
        $this->mensajes = [];
    }

    public function render()
    {
        return view('livewire.inmo-chat');
    }
}
