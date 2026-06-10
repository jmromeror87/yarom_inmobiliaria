<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class MensajesInternos extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel          = 'Notas y Tareas';
    protected static ?string $title                    = 'Notas y Tareas';
    protected static bool    $shouldRegisterNavigation = false;

    public function getView(): string
    {
        return 'filament.pages.mensajes-internos';
    }

    public string $texto     = '';
    public string $prioridad = 'normal';
    public string $categoria = 'nota';
    public string $filtro    = 'todas';
    public array  $notas     = [];

    private function key(): string
    {
        return 'yarom_inmo_notas_' . Auth::id();
    }

    public function mount(): void
    {
        $this->notas = session($this->key(), []);
    }

    public function guardar(): void
    {
        if (trim($this->texto) === '') return;

        $lista   = session($this->key(), []);
        $lista[] = [
            'id'         => uniqid(),
            'texto'      => $this->texto,
            'prioridad'  => $this->prioridad,
            'categoria'  => $this->categoria,
            'completada' => false,
            'hora'       => now()->format('d/m/Y H:i'),
            'autor'      => Auth::user()->name,
        ];
        session([$this->key() => $lista]);
        $this->notas     = $lista;
        $this->texto     = '';
        $this->prioridad = 'normal';
        $this->categoria = 'nota';
    }

    public function toggleCompletar(int $i): void
    {
        $lista = session($this->key(), []);
        if (isset($lista[$i])) {
            $lista[$i]['completada'] = !$lista[$i]['completada'];
            session([$this->key() => $lista]);
            $this->notas = $lista;
        }
    }

    public function eliminar(int $i): void
    {
        $lista = session($this->key(), []);
        unset($lista[$i]);
        $lista = array_values($lista);
        session([$this->key() => $lista]);
        $this->notas = $lista;
    }

    public function getNotasFiltradas(): array
    {
        return collect($this->notas)->filter(function ($n) {
            return match ($this->filtro) {
                'pendientes'  => !$n['completada'],
                'completadas' => $n['completada'],
                'alta'        => $n['prioridad'] === 'alta',
                'tarea'       => $n['categoria'] === 'tarea',
                default       => true,
            };
        })->values()->all();
    }
}
