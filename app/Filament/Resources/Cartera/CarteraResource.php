<?php

namespace App\Filament\Resources\Cartera;

use App\Filament\Resources\Cartera\Pages\ListCartera;
use App\Filament\Resources\Cartera\Pages\ViewCartera;
use App\Models\CuentaPorCobrar;
use Filament\Resources\Resource;

class CarteraResource extends Resource
{
    protected static ?string $model = CuentaPorCobrar::class;

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Cartera';
    protected static string|\UnitEnum|null  $navigationGroup = 'Cobros';
    protected static ?int    $navigationSort  = 3;
    protected static ?string $modelLabel      = 'Cuenta por cobrar';
    protected static ?string $pluralModelLabel = 'Cartera';
    protected static ?string $slug            = 'cartera';

    public static function getPages(): array
    {
        return [
            'index' => ListCartera::route('/'),
            'view'  => ViewCartera::route('/{record}'),
        ];
    }
}
