<?php
namespace App\Filament\Resources\PropertyHandovers\Pages;
use App\Filament\Resources\PropertyHandovers\PropertyHandoverResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
class ListPropertyHandovers extends ListRecords
{
    protected static string $resource = PropertyHandoverResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()->label('Crear acta de entrega')]; }
}
