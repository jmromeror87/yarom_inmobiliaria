<?php

namespace App\Filament\Resources\Cartera\Pages;

use App\Filament\Resources\Cartera\CarteraResource;
use App\Filament\Resources\Cartera\Tables\CarteraTable;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;

class ListCartera extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = CarteraResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function table(Table $table): Table
    {
        return CarteraTable::configure($table);
    }
}
