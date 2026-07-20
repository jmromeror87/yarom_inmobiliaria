<?php

namespace App\Filament\Pages\Accounting;

class ComprobanteIngreso extends ComprobanteRapidoBase
{
    protected static ?string $title = 'Comprobante de Ingreso';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-down-circle';
    protected static string|\UnitEnum|null $navigationGroup = 'Contabilidad';
    protected static ?string $navigationLabel = 'Comp. de Ingreso';
    protected static ?int $navigationSort = 3;
    protected static bool $shouldRegisterNavigation = false;

    public function tipo(): string
    {
        return 'CI';
    }
}
