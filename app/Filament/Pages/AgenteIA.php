<?php

namespace App\Filament\Pages;

use App\Services\AgenteService;
use Filament\Pages\Page;
use Livewire\Attributes\On;

class AgenteIA extends Page
{
    protected string $view = 'filament.pages.agente-ia';

    protected static bool $shouldRegisterNavigation = false;

    public static function getNavigationLabel(): string  { return 'Agente IA'; }
    public function getTitle(): string                   { return 'Agente Inteligente — YarOM'; }

    public array  $mensajes    = [];
    public string $input       = '';
    public bool   $cargando    = false;
    public array  $sugerencias = [
        '¿Cuántos contratos vencen este mes?',
        'Lista los arrendatarios morosos',
        'Muéstrame los inmuebles disponibles',
        'Servicios pendientes de esta semana',
        'Resumen general del sistema',
        'Notifica por WhatsApp a los morosos de más de 5 días',
    ];

    public function enviar(): void
    {
        $texto = trim($this->input);
        if (empty($texto) || $this->cargando) return;

        $this->mensajes[] = ['rol' => 'user', 'texto' => $texto, 'herramientas' => []];
        $this->input      = '';
        $this->cargando   = true;

        $this->dispatch('scroll-bottom');

        // Construir historial para Claude
        $historial = collect($this->mensajes)
            ->filter(fn($m) => in_array($m['rol'], ['user', 'assistant']))
            ->map(fn($m) => [
                'role'    => $m['rol'] === 'user' ? 'user' : 'assistant',
                'content' => $m['texto'],
            ])->values()->toArray();

        try {
            $agente    = app(AgenteService::class);
            $resultado = $agente->chat($historial);

            $this->mensajes[] = [
                'rol'         => 'assistant',
                'texto'       => $resultado['texto'],
                'herramientas'=> $resultado['herramientas_usadas'] ?? [],
            ];
        } catch (\Throwable $e) {
            $this->mensajes[] = [
                'rol'         => 'assistant',
                'texto'       => 'Error: ' . $e->getMessage(),
                'herramientas'=> [],
            ];
        }

        $this->cargando = false;
        $this->dispatch('scroll-bottom');
    }

    public function usarSugerencia(string $sugerencia): void
    {
        $this->input = $sugerencia;
        $this->enviar();
    }

    public function limpiarChat(): void
    {
        $this->mensajes = [];
        $this->input    = '';
    }
}
