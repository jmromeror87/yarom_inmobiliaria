<?php

namespace App\Filament\Resources\Properties\Pages;

use App\Filament\Resources\Properties\PropertyResource;
use App\Models\Property;
use Filament\Actions\Action;
use Filament\Resources\Pages\Page;

class PropertyDashboard extends Page
{
    protected static string $resource = PropertyResource::class;
    protected string $view = 'filament.properties.dashboard';

    public Property $record;

    public function mount(Property $record): void
    {
        $this->record = $record->load([
            'tipo',
            'propietario',
            'municipio.departamento',
            'asesor',
            'images'        => fn ($q) => $q->orderBy('orden'),
            'documents',
            'administrationContracts.statusHistory',
            'administrationContracts.propietario',
        ]);
    }

    public function getTitle(): string
    {
        return 'Expediente — ' . $this->record->codigo;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('editar')
                ->label('Editar inmueble')
                ->icon('heroicon-o-pencil')
                ->url(PropertyResource::getUrl('edit', ['record' => $this->record])),

            Action::make('galeria')
                ->label('Galería')
                ->icon('heroicon-o-photo')
                ->color('info')
                ->url(PropertyResource::getUrl('gallery', ['record' => $this->record])),
        ];
    }
}
