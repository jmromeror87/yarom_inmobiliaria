<?php

namespace App\Filament\Resources\PropertyHandovers\Tables;

use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PropertyHandoversTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero')
                    ->label('N° Acta')->searchable()->sortable()
                    ->weight('bold')->color('primary'),

                TextColumn::make('tipo')->label('Tipo')->badge()
                    ->color(fn ($state) => $state === 'entrega' ? 'success' : 'warning')
                    ->formatStateUsing(fn ($state) => $state === 'entrega' ? '🔑 Entrega' : '🔄 Devolución'),

                TextColumn::make('rentalContract.numero_contrato')
                    ->label('Contrato')->searchable(),

                TextColumn::make('property.codigo')
                    ->label('Inmueble')
                    ->description(fn ($record) => $record->property?->direccion),

                TextColumn::make('arrendatario.nombre_completo')
                    ->label('Arrendatario')->searchable(),

                TextColumn::make('fecha_acta')
                    ->label('Fecha acta')->date('d/m/Y')->sortable(),

                TextColumn::make('estado_general')->label('Estado inmueble')->badge()
                    ->color(fn ($state) => match($state) {
                        'excelente' => 'success', 'bueno' => 'info',
                        'regular'   => 'warning',  'malo'  => 'danger',
                        default     => 'gray',
                    }),

                TextColumn::make('items_count')->label('Ítems')
                    ->counts('items')->badge()->color('gray'),

                TextColumn::make('estado')->label('Estado acta')->badge()
                    ->color(fn ($state) => match($state) {
                        'cerrada' => 'success', 'firmada' => 'primary',
                        'en_proceso' => 'warning', default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match($state) {
                        'borrador'   => '📝 Borrador',
                        'en_proceso' => '🔄 En proceso',
                        'firmada'    => '✍️ Firmada',
                        'cerrada'    => '✅ Cerrada',
                        default      => $state,
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->filters([
                SelectFilter::make('tipo')->label('Tipo')
                    ->options(['entrega'=>'Entrega','devolucion'=>'Devolución']),
                SelectFilter::make('estado')->label('Estado')
                    ->options(['borrador'=>'Borrador','firmada'=>'Firmada','cerrada'=>'Cerrada']),
            ])
            ->actions([EditAction::make()->label('Editar')])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
