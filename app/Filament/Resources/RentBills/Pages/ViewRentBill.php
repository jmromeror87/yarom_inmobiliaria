<?php

namespace App\Filament\Resources\RentBills\Pages;

use App\Filament\Resources\RentBills\RentBillResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewRentBill extends ViewRecord
{
    protected static string $resource = RentBillResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('imprimir')
                ->label('Imprimir / PDF')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->extraAttributes(['onclick' => 'window.print()']),
            Action::make('editar')
                ->label('Editar')
                ->icon('heroicon-o-pencil')
                ->color('primary')
                ->url(fn () => RentBillResource::getUrl('edit', ['record' => $this->record])),
        ];
    }

    public function getView(): string
    {
        return 'filament.resources.rent-bills.pages.view-rent-bill';
    }
}
