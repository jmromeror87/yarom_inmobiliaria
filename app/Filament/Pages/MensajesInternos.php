<?php

namespace App\Filament\Pages;

use App\Models\UserNote;
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

    public function mount(): void
    {
        $this->cargarNotas();
    }

    private function cargarNotas(): void
    {
        $this->notas = UserNote::where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($n) => [
                'id'         => $n->id,
                'texto'      => $n->texto,
                'prioridad'  => $n->prioridad,
                'categoria'  => $n->categoria,
                'completada' => $n->completada,
                'hora'       => $n->created_at->format('d/m/Y H:i'),
                'autor'      => Auth::user()->name,
            ])
            ->toArray();
    }

    public function guardar(): void
    {
        if (trim($this->texto) === '') return;

        UserNote::create([
            'user_id'   => Auth::id(),
            'texto'     => $this->texto,
            'prioridad' => $this->prioridad,
            'categoria' => $this->categoria,
        ]);

        $this->texto     = '';
        $this->prioridad = 'normal';
        $this->categoria = 'nota';

        $this->cargarNotas();
    }

    public function toggleCompletar(int $id): void
    {
        $nota = UserNote::where('user_id', Auth::id())->find($id);
        if ($nota) {
            $nota->update(['completada' => !$nota->completada]);
        }
        $this->cargarNotas();
    }

    public function eliminar(int $id): void
    {
        UserNote::where('user_id', Auth::id())->where('id', $id)->delete();
        $this->cargarNotas();
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
