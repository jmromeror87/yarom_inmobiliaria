<?php
namespace App\Filament\Resources\OwnerLiquidations\Pages;

use App\Filament\Resources\OwnerLiquidations\OwnerLiquidationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOwnerLiquidation extends CreateRecord
{
    protected static string $resource = OwnerLiquidationResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
