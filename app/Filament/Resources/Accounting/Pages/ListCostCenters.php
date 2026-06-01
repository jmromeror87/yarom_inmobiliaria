<?php

namespace App\Filament\Resources\Accounting\Pages;

use App\Filament\Resources\Accounting\AccountingCostCenterResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCostCenters extends ListRecords
{
    protected static string $resource = AccountingCostCenterResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Nuevo centro de costo')];
    }
}
