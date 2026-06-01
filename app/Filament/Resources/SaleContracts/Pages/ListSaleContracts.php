<?php

namespace App\Filament\Resources\SaleContracts\Pages;

use App\Filament\Resources\SaleContracts\SaleContractResource;
use App\Filament\Resources\SaleContracts\Tables\SaleContractsTable;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;

class ListSaleContracts extends ListRecords
{
    protected static string $resource = SaleContractResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Nuevo contrato')];
    }

    public function table(Table $table): Table
    {
        return SaleContractsTable::configure($table);
    }
}
