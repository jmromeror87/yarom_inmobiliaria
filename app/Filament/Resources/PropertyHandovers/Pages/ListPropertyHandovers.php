<?php
namespace App\Filament\Resources\PropertyHandovers\Pages;
use App\Filament\Resources\PropertyHandovers\PropertyHandoverResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
class ListPropertyHandovers extends ListRecords
{
    protected static string $resource = PropertyHandoverResource::class;
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Crear acta de entrega')
                ->icon('heroicon-o-plus')
                ->extraAttributes([
                    'style' => 'background:linear-gradient(135deg,#1e3a8a,#E11D48)!important;color:#fff!important;border:none!important;box-shadow:0 4px 14px rgba(30,58,138,.35)!important;font-weight:700!important;',
                ]),
        ];
    }
}
