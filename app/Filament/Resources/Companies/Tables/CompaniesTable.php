<?php
/*
|--------------------------------------------------------------------------
| YarOM ERP - Soluciones de Gestión
|--------------------------------------------------------------------------
| Proyecto privado desarrollado por:
| Ingeniero Jhoan Romero Rivera
| LinkedIn: https://linkedin.com/in/jmromeror87
|
| Módulo: \1
| Archivo: CompaniesTable.php
| Fecha: CURRENT_DAY/05/2026
| Versión: v1.0
|--------------------------------------------------------------------------
*/


namespace App\Filament\Resources\Companies\Tables;

use Filament\Tables;
use Filament\Tables\Table;

class CompaniesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('razon_social')
                    ->label('Razón social')
                    ->icon('heroicon-m-building-office-2')
                    ->iconColor('gray')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->color('gray-900'),

                Tables\Columns\TextColumn::make('nit_completo')
                    ->label('NIT'),
               Tables\Columns\TextColumn::make('regimen_fiscal')
                    ->label('Régimen')
                    ->badge() 
                    ->color('info')
                    ->formatStateUsing(fn ($state) => match($state) {
                        'simple_tributacion' => 'Simple',
                        'ordinario'          => 'Ordinario',
                        'especial'           => 'Especial',
                        default              => $state,
                    }),
                Tables\Columns\TextColumn::make('municipio.nombre')
                    ->label('Ciudad'),
                Tables\Columns\TextColumn::make('telefono')
    ->label('Teléfono')
    ->icon('heroicon-m-phone')
    ->iconColor('gray')
    ->color('gray-600')
    // En v3 para que sea un link directo con icono usamos este método:
    ->action(
        \Filament\Actions\Action::make('whatsapp')
            ->url(fn ($state) => $state ? "https://wa.me/" . preg_replace('/[^0-9]/', '', $state) : null, true)
    )
    ->icon('heroicon-m-chat-bubble-left-right') // Icono que se muestra al lado
    ->iconColor('success') // Color verde WhatsApp
                ,
                Tables\Columns\IconColumn::make('responsable_iva')
                    ->label('IVA')->boolean(),
                Tables\Columns\IconColumn::make('factura_electronica_activa')
                    ->label('Fact. elect.')->boolean(),
            ])
            ->actions([
                \Filament\Actions\EditAction::make()->label('Editar'),
            ]);
    }
}
