<?php

namespace App\Filament\Pages\Accounting;

class ComprobanteEgreso extends ComprobanteRapidoBase
{
    protected static ?string $title = 'Comprobante de Egreso';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-up-circle';
    protected static string|\UnitEnum|null $navigationGroup = 'Contabilidad';
    protected static ?string $navigationLabel = 'Comp. de Egreso';
    protected static ?int $navigationSort = 3;

    public function tipo(): string
    {
        return 'CE';
    }
}
