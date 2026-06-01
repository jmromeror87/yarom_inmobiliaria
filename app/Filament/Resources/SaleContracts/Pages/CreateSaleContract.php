<?php

namespace App\Filament\Resources\SaleContracts\Pages;

use App\Filament\Resources\SaleContracts\SaleContractResource;
use App\Filament\Resources\SaleContracts\Schemas\SaleContractForm;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Schema;

class CreateSaleContract extends CreateRecord
{
    protected static string $resource = SaleContractResource::class;

    public function form(Schema $schema): Schema
    {
        return SaleContractForm::configure($schema);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }
}
