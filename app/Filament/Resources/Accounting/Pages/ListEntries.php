<?php

namespace App\Filament\Resources\Accounting\Pages;

use App\Filament\Pages\Accounting\BalancePrueba;
use App\Filament\Pages\Accounting\LibroDiario;
use App\Filament\Pages\Accounting\LibroMayor;
use App\Filament\Resources\Accounting\AccountingEntryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEntries extends ListRecords
{
    protected static string $resource = AccountingEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('libro_diario')
                ->label('📖 Libro Diario')
                ->color('gray')
                ->url(fn() => LibroDiario::getUrl()),

            \Filament\Actions\Action::make('libro_mayor')
                ->label('📚 Libro Mayor')
                ->color('gray')
                ->url(fn() => LibroMayor::getUrl()),

            \Filament\Actions\Action::make('balance_prueba')
                ->label('⚖️ Balance de Prueba')
                ->color('gray')
                ->url(fn() => BalancePrueba::getUrl()),

            CreateAction::make()->label('Nuevo comprobante'),
        ];
    }
}
