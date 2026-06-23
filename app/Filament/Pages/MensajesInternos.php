<?php

namespace App\Filament\Pages;

use App\Models\UserNote;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;

class MensajesInternos extends Page
{
    use WithFileUploads;
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
    public array   $notas     = [];
    public         $adjunto   = null;

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
                'id'              => $n->id,
                'texto'           => $n->texto,
                'prioridad'       => $n->prioridad,
                'categoria'       => $n->categoria,
                'completada'      => $n->completada,
                'hora'            => $n->created_at->format('d/m/Y H:i'),
                'autor'           => Auth::user()->name,
                'attachment_path' => $n->attachment_path,
                'attachment_name' => $n->attachment_name,
                'attachment_mime' => $n->attachment_mime,
            ])
            ->toArray();
    }

    public function guardar(): void
    {
        if (trim($this->texto) === '') return;

        $data = [
            'user_id'   => Auth::id(),
            'texto'     => $this->texto,
            'prioridad' => $this->prioridad,
            'categoria' => $this->categoria,
        ];

        if ($this->adjunto) {
            $this->validate(['adjunto' => 'file|mimes:pdf,jpg,jpeg,png,gif,webp|max:10240']);
            $data['attachment_path'] = $this->adjunto->store('notas-adjuntos', 'public');
            $data['attachment_name'] = $this->adjunto->getClientOriginalName();
            $data['attachment_mime'] = $this->adjunto->getMimeType();
        }

        UserNote::create($data);

        $this->texto     = '';
        $this->prioridad = 'normal';
        $this->categoria = 'nota';
        $this->adjunto   = null;

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
        $nota = UserNote::where('user_id', Auth::id())->where('id', $id)->first();
        if ($nota) {
            if ($nota->attachment_path) {
                Storage::disk('public')->delete($nota->attachment_path);
            }
            $nota->delete();
        }
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
