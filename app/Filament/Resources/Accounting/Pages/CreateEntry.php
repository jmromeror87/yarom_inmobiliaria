<?php

namespace App\Filament\Resources\Accounting\Pages;

use App\Filament\Resources\Accounting\AccountingEntryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEntry extends CreateRecord
{
    protected static string $resource = AccountingEntryResource::class;

    protected function afterCreate(): void
    {
        // Recalcula totales después de que Filament guarda el Repeater de líneas
        $this->record->recalcularTotales();
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('edit', ['record' => $this->record]);
    }
}
