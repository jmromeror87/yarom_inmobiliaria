<?php

namespace App\Filament\Resources\OwnerLiquidations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class OwnerLiquidationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero')
                    ->searchable(),
                TextColumn::make('rentalContract.id')
                    ->searchable(),
                TextColumn::make('property.id')
                    ->searchable(),
                TextColumn::make('propietario.id')
                    ->searchable(),
                TextColumn::make('mes')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('anio')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('periodo_inicio')
                    ->date()
                    ->sortable(),
                TextColumn::make('periodo_fin')
                    ->date()
                    ->sortable(),
                TextColumn::make('canon_cobrado')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('comision_porcentaje')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('comision_valor')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('iva_comision')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('otros_descuentos')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_giro')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('estado')
                    ->badge(),
                TextColumn::make('fecha_giro')
                    ->date()
                    ->sortable(),
                TextColumn::make('forma_giro')
                    ->badge(),
                TextColumn::make('referencia_giro')
                    ->searchable(),
                TextColumn::make('comprobante_giro_path')
                    ->searchable(),
                IconColumn::make('wap_enviado')
                    ->boolean(),
                TextColumn::make('wap_enviado_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
