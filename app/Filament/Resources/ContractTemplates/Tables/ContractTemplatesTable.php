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
| Archivo: ContractTemplatesTable.php
| Fecha: CURRENT_DAY/05/2026
| Versión: v1.0
|--------------------------------------------------------------------------
*/
        
namespace App\Filament\Resources\ContractTemplates\Tables;

use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ContractTemplatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->label('Plantilla')->searchable()->sortable()
                    ->weight('bold')->color('primary'),

                TextColumn::make('tipo_contrato')
                    ->label('Tipo')->badge()
                    ->color(fn ($state) => match($state) {
                        'administracion_arriendo' => 'success',
                        'administracion_venta'    => 'warning',
                        'arrendamiento_vivienda'  => 'info',
                        'arrendamiento_comercial' => 'primary',
                        default                   => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match($state) {
                        'administracion_arriendo' => 'Adm. Arriendo',
                        'administracion_venta'    => 'Adm. Venta',
                        'arrendamiento_vivienda'  => 'Arrend. Vivienda',
                        'arrendamiento_comercial' => 'Arrend. Comercial',
                        default                   => $state,
                    }),

                TextColumn::make('clauses_count')
                    ->label('Cláusulas')
                    ->counts('clauses')
                    ->badge()->color('gray'),

                IconColumn::make('is_default')
                    ->label('Por defecto')->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->trueColor('warning'),

                IconColumn::make('is_active')
                    ->label('Activa')->boolean(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('tipo_contrato')->label('Tipo')
                    ->options([
                        'administracion_arriendo' => 'Adm. Arriendo',
                        'administracion_venta'    => 'Adm. Venta',
                        'arrendamiento_vivienda'  => 'Arrend. Vivienda',
                        'arrendamiento_comercial' => 'Arrend. Comercial',
                    ]),
            ])
            ->actions([
                EditAction::make()->label('Editar'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Eliminar'),
                ]),
            ]);
    }
}
