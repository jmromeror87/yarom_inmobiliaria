<?php

namespace App\Filament\Resources\Cartera\Pages;

use App\Filament\Resources\Cartera\CarteraResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCartera extends CreateRecord
{
    protected static string $resource = CarteraResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
