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
| Archivo: RequestsTable.php
| Fecha: CURRENT_DAY/05/2026
| Versión: v1.0
|--------------------------------------------------------------------------
*/
            

namespace App\Filament\Resources\Requests\Tables;

use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero')
                    ->label('N° Solicitud')->searchable()->sortable()
                    ->weight('bold')->color('primary'),

                TextColumn::make('tipo')
                    ->label('Tipo')->badge()
                    ->color(fn ($state) => match($state) {
                        'estudio_propietario'  => 'info',
                        'estudio_arrendatario' => 'success',
                        'estudio_comprador'    => 'warning',
                        default                => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match($state) {
                        'estudio_propietario'  => '🏠 Propietario',
                        'estudio_arrendatario' => '🔑 Arrendatario',
                        'estudio_comprador'    => '🛒 Comprador',
                        default                => $state,
                    }),

                TextColumn::make('property.codigo')
                    ->label('Inmueble')
                    ->description(fn ($record) => $record->property?->direccion)
                    ->searchable(),

                TextColumn::make('thirds_count')
                    ->label('Terceros')
                    ->counts('thirds')
                    ->badge()->color('gray'),

                TextColumn::make('fecha_radicacion')
                    ->label('Radicada')->date('d/m/Y')->sortable(),

                TextColumn::make('estado')
                    ->label('Estado')->badge()
                    ->color(fn ($state) => match($state) {
                        'aprobada'    => 'success',
                        'rechazada'   => 'danger',
                        'condicional' => 'warning',
                        'en_estudio'  => 'info',
                        'radicada'    => 'gray',
                        'desistida'   => 'gray',
                        default       => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match($state) {
                        'radicada'    => '📋 Radicada',
                        'en_estudio'  => '🔍 En estudio',
                        'aprobada'    => '✅ Aprobada',
                        'condicional' => '⚠️ Condicional',
                        'rechazada'   => '❌ Rechazada',
                        'desistida'   => '🚫 Desistida',
                        default       => $state,
                    }),


                TextColumn::make('asesor.name')
                    ->label('Asesor')->searchable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->filters([
                SelectFilter::make('tipo')->label('Tipo')
                    ->options([
                        'estudio_propietario'  => 'Estudio propietario',
                        'estudio_arrendatario' => 'Estudio arrendatario',
                        'estudio_comprador'    => 'Estudio comprador',
                    ]),
                SelectFilter::make('estado')->label('Estado')
                    ->options([
                        'radicada'    => 'Radicada',
                        'en_estudio'  => 'En estudio',
                        'aprobada'    => 'Aprobada',
                        'condicional' => 'Condicional',
                        'rechazada'   => 'Rechazada',
                        'desistida'   => 'Desistida',
                    ]),
            ])
            ->actions([
                EditAction::make()->label('Editar')->hidden(fn ($record) => in_array($record->estado, ['aprobada','desistida'])), \Filament\Actions\Action::make('ver')->label('Ver')->icon('heroicon-o-eye')->color('gray')->url(fn ($record) => \App\Filament\Resources\Requests\RequestResource::getUrl('edit', ['record' => $record]))->visible(fn ($record) => in_array($record->estado, ['aprobada','desistida'])),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Eliminar'),
                ]),
            ]);
    }
}
