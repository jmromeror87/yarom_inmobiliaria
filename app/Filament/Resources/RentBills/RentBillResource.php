<?php

namespace App\Filament\Resources\RentBills;

use App\Filament\Resources\RentBills\Pages\CreateRentBill;
use App\Filament\Resources\RentBills\Pages\EditRentBill;
use App\Filament\Resources\RentBills\Pages\ViewRentBill;
use App\Filament\Resources\RentBills\Pages\ListRentBills;
use App\Filament\Resources\RentBills\Schemas\RentBillForm;
use App\Filament\Resources\RentBills\Tables\RentBillsTable;
use App\Models\RentBill;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class RentBillResource extends Resource
{
    protected static ?string $model = RentBill::class;
    protected static ?string $navigationLabel = 'Facturación';
    protected static ?string $modelLabel = 'Factura';
    protected static ?string $pluralModelLabel = 'Facturación';
    protected static ?string $slug = 'facturacion';
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'numero';

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-document-currency-dollar';
    }
    

    public static function getNavigationGroup(): ?string
    {
        return 'Cobros';
    }

    public static function form(Schema $schema): Schema
    {
        return RentBillForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RentBillsTable::configure($table);
    }

    public static function getRelations(): array { return []; }

    public static function getPages(): array
    {
        return [
            'index'  => ListRentBills::route('/'),
            'create' => CreateRentBill::route('/create'),
            'view'   => ViewRentBill::route('/{record}'),
            'edit'   => EditRentBill::route('/{record}/edit'),
        ];
    }
}
