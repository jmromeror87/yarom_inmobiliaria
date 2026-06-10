<?php

namespace App\Filament\Resources\SaleContracts\Tables;

use App\Filament\Resources\SaleContracts\SaleContractResource;
use App\Models\SaleContract;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SaleContractsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero_contrato')
                    ->label('N° Contrato')->searchable()->sortable()->weight('bold'),

                TextColumn::make('property.direccion')
                    ->label('Inmueble')->searchable()->limit(35),

                TextColumn::make('vendedor.nombre_completo')
                    ->label('Vendedor')->searchable()->limit(25),

                TextColumn::make('comprador.nombre_completo')
                    ->label('Comprador')->searchable()->limit(25),

                TextColumn::make('precio_venta')
                    ->label('Precio')->money('COP')->sortable(),

                TextColumn::make('valor_comision')
                    ->label('Comisión')->money('COP')
                    ->color('success'),

                TextColumn::make('estado_comision')
                    ->label('Comisión')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'pagada'    => 'success',
                        'parcial'   => 'info',
                        'pendiente' => 'warning',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match($state) {
                        'pagada'    => 'Cobrada',
                        'parcial'   => 'Parcial',
                        'pendiente' => 'Pendiente',
                        default     => ucfirst($state),
                    }),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'entregado'     => 'success',
                        'registrado'    => 'info',
                        'escrituracion' => 'warning',
                        'promesa'       => 'gray',
                        'cancelado'     => 'danger',
                        default         => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => SaleContract::ESTADOS[$state] ?? ucfirst($state)),

                TextColumn::make('created_at')
                    ->label('Creado')->date('d/m/Y')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('estado')
                    ->options(SaleContract::ESTADOS),
                SelectFilter::make('estado_comision')
                    ->label('Estado comisión')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'parcial'   => 'Parcial',
                        'pagada'    => 'Pagada',
                    ]),
            ])
            ->recordActions([
                EditAction::make()->label('Editar')->outlined(),
            ])
            ->toolbarActions([
                \Filament\Actions\BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
