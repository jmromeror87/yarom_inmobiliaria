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
            Action::make('expediente')
                ->label('Expediente')
                ->icon('heroicon-o-clipboard-document-list')
                ->url(PropertyResource::getUrl('dashboard', ['record' => $this->record]))
                ->extraAttributes([
                    'style' => 'background:linear-gradient(135deg,#334155,#1e3a8a)!important;border:none!important;color:#fff!important;font-weight:700!important;border-radius:10px!important;box-shadow:0 4px 14px rgba(30,58,138,.28)!important;transition:transform .12s!important;',
                    'onmouseover' => "this.style.transform='translateY(-1px)'",
                    'onmouseout'  => "this.style.transform=''",
                ]),
            Action::make('editar')
                ->label('Editar inmueble')
                ->icon('heroicon-o-pencil')
                ->url(PropertyResource::getUrl('edit', ['record' => $this->record]))
                ->extraAttributes([
                    'style' => 'background:linear-gradient(135deg,#1e3a8a,#E11D48)!important;border:none!important;color:#fff!important;font-weight:700!important;border-radius:10px!important;box-shadow:0 4px 14px rgba(225,29,72,.32)!important;transition:transform .12s,box-shadow .12s!important;',
                    'onmouseover' => "this.style.transform='translateY(-1px)';this.style.boxShadow='0 6px 20px rgba(225,29,72,.45)'",
                    'onmouseout'  => "this.style.transform='';this.style.boxShadow='0 4px 14px rgba(225,29,72,.32)'",
                ]),
        ];
    }
}
