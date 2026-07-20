<?php

namespace App\Filament\Resources\Cartera\Tables;

use App\Filament\Resources\Cartera\CarteraResource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CarteraTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->heading('Heredado Siinmob y Otros Conceptos')
            ->description('Deuda migrada del sistema anterior, depósitos, daños y otros cobros manuales')
            ->columns([
                TextColumn::make('numero')
                    ->label('N° Cuenta')
                    ->searchable()->sortable()
                    ->weight('bold'),

                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'deposito_arriendo'     => 'Depósito arriendo',
                        'mora'                  => 'Mora',
                        'dano'                  => 'Daño inmueble',
                        'saldo_inicial_siinmob' => 'Heredado Siinmob',
                        default                 => ucfirst($state),
                    })
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'deposito_arriendo'     => 'info',
                        'mora'                  => 'danger',
                        'dano'                  => 'warning',
                        'saldo_inicial_siinmob' => 'purple',
                        default                 => 'gray',
                    }),

                TextColumn::make('third.nombre_completo')
                    ->label('Deudor')->searchable(),

                TextColumn::make('rentalContract.numero_contrato')
                    ->label('Contrato')->searchable(),

                TextColumn::make('valor_original')
                    ->label('Valor original')
                    ->money('COP')->sortable(),

                TextColumn::make('valor_pagado')
                    ->label('Pagado')
                    ->money('COP')->sortable()
                    ->color('success'),

                TextColumn::make('saldo')
                    ->label('Saldo pendiente')
                    ->money('COP')->sortable()
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'success')
                    ->weight('bold'),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'pendiente' => 'warning',
                        'parcial'   => 'info',
                        'pagado'    => 'success',
                        'castigada' => 'gray',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pendiente' => 'Pendiente',
                        'parcial'   => 'Pago parcial',
                        'pagado'    => 'Pagado',
                        'castigada' => 'Castigada',
                        default     => ucfirst($state),
                    }),

                TextColumn::make('fecha_origen')
                    ->label('Fecha origen')->date('d/m/Y')->sortable(),

                TextColumn::make('fecha_vencimiento')
                    ->label('Vencimiento')->date('d/m/Y')
                    ->color(fn ($record) => $record->fecha_vencimiento?->isPast() && $record->estado !== 'pagado' ? 'danger' : null),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'parcial'   => 'Pago parcial',
                        'pagado'    => 'Pagado',
                        'castigada' => 'Castigada',
                    ]),
                SelectFilter::make('tipo')
                    ->options([
                        'deposito_arriendo'     => 'Depósito arriendo',
                        'mora'                  => 'Mora',
                        'dano'                  => 'Daño',
                        'otro'                  => 'Otro',
                        'saldo_inicial_siinmob' => 'Heredado Siinmob',
                    ]),
            ])
            ->recordUrl(fn ($record) => CarteraResource::getUrl('view', ['record' => $record]));
    }
}
