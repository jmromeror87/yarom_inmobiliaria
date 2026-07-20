<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\RentBills\RentBillResource;
use App\Models\RentBill;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class CarteraActivaWidget extends BaseWidget
{
    protected static ?int $sort = 4;
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                RentBill::query()
                    ->whereIn('estado', ['pendiente', 'parcial', 'en_mora', 'vencida'])
                    ->where('saldo_pendiente', '>', 0)
            )
            ->heading('Cartera Activa — Sistema Nuevo')
            ->description('Facturas pendientes de cobro generadas desde julio 2026 en adelante')
            ->columns([
                TextColumn::make('numero')
                    ->label('N° Factura')->searchable()->sortable()->weight('bold')->color('primary'),

                TextColumn::make('arrendatario.nombre_completo')
                    ->label('Deudor')->searchable(),

                TextColumn::make('mes')
                    ->label('Periodo')
                    ->formatStateUsing(fn ($record) => \Carbon\Carbon::create($record->anio, $record->mes, 1)->translatedFormat('F Y')),

                TextColumn::make('total_factura')->label('Valor original')->money('COP')->sortable(),
                TextColumn::make('total_pagado')->label('Pagado')->money('COP')->sortable()->color('success'),

                TextColumn::make('saldo_pendiente')
                    ->label('Saldo pendiente')->money('COP')->sortable()
                    ->weight('bold')->color(fn ($state) => $state > 0 ? 'danger' : 'success'),

                TextColumn::make('estado')->label('Estado')->badge()
                    ->color(fn ($state) => match ($state) {
                        'parcial'  => 'info',
                        'en_mora'  => 'danger',
                        'vencida'  => 'danger',
                        default    => 'warning',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pendiente' => 'Pendiente',
                        'parcial'   => 'Pago parcial',
                        'en_mora'   => 'En mora',
                        'vencida'   => 'Vencida',
                        default     => ucfirst($state),
                    }),

                TextColumn::make('fecha_limite_pago')
                    ->label('Vencimiento')->date('d/m/Y')->sortable()
                    ->color(fn ($record) => $record->fecha_limite_pago?->isPast() ? 'danger' : null),
            ])
            ->defaultSort('fecha_limite_pago', 'asc')
            ->recordUrl(fn ($record) => RentBillResource::getUrl('edit', ['record' => $record]))
            ->paginated([10, 25, 50]);
    }
}
