<?php

namespace App\Filament\Resources\Properties\Pages;

use App\Filament\Resources\Properties\PropertyResource;
use App\Models\Property;
use Filament\Actions\Action;
use Filament\Resources\Pages\Page;

class ViewPropertyGallery extends Page
{
    protected static string $resource = PropertyResource::class;
    protected string $view = 'filament.properties.gallery';

    public Property $record;

    public function mount(Property $record): void
    {
        $this->record = $record->load(['images' => fn($q) => $q->orderBy('orden'), 'tipo', 'propietario']);
    }

    public function getTitle(): string
    {
        return 'Galería — ' . $this->record->codigo;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('editar')
                ->label('Editar inmueble')
                ->icon('heroicon-o-pencil')
                ->url(PropertyResource::getUrl('edit', ['record' => $this->record])),
        ];
    }
}
