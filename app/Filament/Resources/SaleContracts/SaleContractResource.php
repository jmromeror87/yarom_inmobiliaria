<?php

namespace App\Filament\Resources\SaleContracts;

use App\Filament\Resources\SaleContracts\Pages\CreateSaleContract;
use App\Filament\Resources\SaleContracts\Pages\EditSaleContract;
use App\Filament\Resources\SaleContracts\Pages\ListSaleContracts;
use App\Models\SaleContract;
use Filament\Resources\Resource;

class SaleContractResource extends Resource
{
    protected static ?string $model = SaleContract::class;

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-home-modern';
    protected static string|\UnitEnum|null  $navigationGroup = 'Contratación';
    protected static ?string $navigationLabel  = 'Corretaje / Venta';
    protected static ?string $modelLabel       = 'Contrato de corretaje';
    protected static ?string $pluralModelLabel = 'Corretaje y ventas';
    protected static ?int    $navigationSort   = 5;
    protected static ?string $slug             = 'corretaje-ventas';

    public static function getPages(): array
    {
        return [
            'index'  => ListSaleContracts::route('/'),
            'create' => CreateSaleContract::route('/create'),
            'edit'   => EditSaleContract::route('/{record}/edit'),
        ];
    }
}
