<?php

namespace App\Filament\Resources\RentBills\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RentBillsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero')
                    ->label('N° Factura')->searchable()->sortable()
                    ->weight('bold')->color('primary'),

                TextColumn::make('rentalContract.numero_contrato')
                    ->label('Contrato')->searchable(),

                TextColumn::make('arrendatario.nombre_completo')
                    ->label('Arrendatario')
                    ->description(fn ($record) => $record->property?->codigo . ' — ' . $record->property?->direccion)
                    ->searchable(),

                TextColumn::make('mes')
                    ->label('Periodo')
                    ->formatStateUsing(fn ($record) =>
                        \Carbon\Carbon::create($record->anio, $record->mes, 1)->translatedFormat('F Y')
                    ),

                TextColumn::make('total_factura')
                    ->label('Total')->money('COP')->sortable(),

                TextColumn::make('mora_acumulada')
                    ->label('Mora')->money('COP')
                    ->color(fn ($state) => $state > 0 ? 'danger' : null),

                TextColumn::make('saldo_pendiente')
                    ->label('Saldo')->money('COP')
                    ->color(fn ($state) => $state > 0 ? 'warning' : 'success'),

                TextColumn::make('fecha_limite_pago')
                    ->label('Vence')->date('d/m/Y')->sortable()
                    ->color(fn ($record) => $record->estaEnMora() ? 'danger' : null),

                TextColumn::make('estado')->label('Estado')->badge()
                    ->color(fn ($state) => match($state) {
                        'pagada'    => 'success',
                        'parcial'   => 'warning',
                        'en_mora'   => 'danger',
                        'vencida'   => 'danger',
                        'anulada'   => 'gray',
                        default     => 'info',
                    })
                    ->formatStateUsing(fn ($state) => match($state) {
                        'pendiente' => '⏳ Pendiente',
                        'parcial'   => '🔶 Parcial',
                        'pagada'    => '✅ Pagada',
                        'en_mora'   => '🔴 En mora',
                        'vencida'   => '⚠️ Vencida',
                        'anulada'   => '❌ Anulada',
                        default     => $state,
                    }),

                TextColumn::make('tipo_documento')->label('Doc.')
                    ->badge()->color('gray')
                    ->formatStateUsing(fn ($state) => $state === 'factura_electronica' ? '🧾 FE' : '📄 DE'),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->filters([
                SelectFilter::make('estado')->label('Estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'pagada'    => 'Pagada',
                        'en_mora'   => 'En mora',
                        'parcial'   => 'Parcial',
                    ]),
                SelectFilter::make('tipo_documento')->label('Tipo doc.')
                    ->options([
                        'documento_equivalente' => 'Doc. equivalente',
                        'factura_electronica'   => 'Factura electrónica',
                    ]),
            ])
            ->actions([
                EditAction::make()->label('Ver / Pagar'),
            ]);
    }
}
