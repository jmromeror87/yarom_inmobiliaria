<?php

namespace App\Filament\Pages;

use App\Models\UserNote;
use App\Models\UserNoteAttachment;
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
    public array  $notas     = [];
    /** @var array<int, \Livewire\Features\SupportFileUploads\TemporaryUploadedFile> */
    public array $adjuntos = [];

    public function mount(): void
    {
        $this->cargarNotas();
    }

    private function cargarNotas(): void
    {
        $this->notas = UserNote::where('user_id', Auth::id())
            ->with('attachments')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($n) => [
                'id'          => $n->id,
                'texto'       => $n->texto,
                'prioridad'   => $n->prioridad,
                'categoria'   => $n->categoria,
                'completada'  => $n->completada,
                'hora'        => $n->created_at->format('d/m/Y H:i'),
                'attachments' => $n->attachments->map(fn ($a) => [
                    'id'     => $a->id,
                    'nombre' => $a->nombre,
                    'mime'   => $a->mime,
                    'size'   => $a->size,
                    'path'   => $a->path,
                ])->toArray(),
            ])
            ->toArray();
    }

    public function guardar(): void
    {
        if (trim($this->texto) === '') return;

        $this->validate([
            'adjuntos.*' => 'file|mimes:pdf,jpg,jpeg,png,gif,webp|max:10240',
        ]);

        $nota = UserNote::create([
            'user_id'   => Auth::id(),
            'texto'     => $this->texto,
            'prioridad' => $this->prioridad,
            'categoria' => $this->categoria,
        ]);

        foreach ($this->adjuntos as $archivo) {
            $path = $archivo->store('notas-adjuntos', 'public');
            UserNoteAttachment::create([
                'user_note_id' => $nota->id,
                'path'         => $path,
                'nombre'       => $archivo->getClientOriginalName(),
                'mime'         => $archivo->getMimeType(),
                'size'         => $archivo->getSize(),
            ]);
        }

        $this->texto     = '';
        $this->prioridad = 'normal';
        $this->categoria = 'nota';
        $this->adjuntos  = [];

        $this->cargarNotas();
    }

    public function eliminarAdjunto(int $adjuntoId): void
    {
        $adjunto = UserNoteAttachment::whereHas('nota', fn ($q) => $q->where('user_id', Auth::id()))
            ->find($adjuntoId);
        if ($adjunto) {
            Storage::disk('public')->delete($adjunto->path);
            $adjunto->delete();
        }
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
        $nota = UserNote::where('user_id', Auth::id())->with('attachments')->find($id);
        if ($nota) {
            foreach ($nota->attachments as $a) {
                Storage::disk('public')->delete($a->path);
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
