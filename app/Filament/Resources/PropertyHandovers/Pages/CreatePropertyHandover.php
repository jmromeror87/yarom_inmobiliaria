<?php
namespace App\Filament\Resources\PropertyHandovers\Pages;
use App\Filament\Resources\PropertyHandovers\PropertyHandoverResource;
use Filament\Resources\Pages\CreateRecord;
class CreatePropertyHandover extends CreateRecord
{
    protected static string $resource = PropertyHandoverResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }
}
