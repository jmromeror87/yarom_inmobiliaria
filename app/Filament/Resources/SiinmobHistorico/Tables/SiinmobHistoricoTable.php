<?php

namespace App\Filament\Resources\SiinmobHistorico\Tables;

use App\Models\SiinmobHistoricoNota;
use Filament\Actions\Action as TableAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SiinmobHistoricoTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('fecha', 'desc')
            ->columns([
                TextColumn::make('fecha')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state) => $state === 'NC' ? 'info' : 'gray')
                    ->formatStateUsing(fn (string $state) => $state === 'NC' ? 'Manual' : 'Automática'),

                TextColumn::make('detalle')
                    ->label('Descripción')
                    ->searchable()
                    ->limit(60)
                    ->wrap(),

                TextColumn::make('creada_por')
                    ->label('Creada por')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('total_debito')
                    ->label('Débito')
                    ->money('COP', divideBy: 1)
                    ->sortable(),

                TextColumn::make('total_credito')
                    ->label('Crédito')
                    ->money('COP', divideBy: 1)
                    ->sortable(),

                IconColumn::make('cuadra')
                    ->label('Cuadra')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-exclamation-triangle')
                    ->trueColor('success')
                    ->falseColor('warning'),
            ])
            ->filters([
                SelectFilter::make('tipo')
                    ->label('Tipo')
                    ->options(['NC' => 'Manual (NC)', 'NA' => 'Automática (NA)']),

                Filter::make('fecha')
                    ->schema([
                        \Filament\Forms\Components\DatePicker::make('desde')->label('Desde'),
                        \Filament\Forms\Components\DatePicker::make('hasta')->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['desde'] ?? null, fn ($q, $v) => $q->whereDate('fecha', '>=', $v))
                            ->when($data['hasta'] ?? null, fn ($q, $v) => $q->whereDate('fecha', '<=', $v));
                    }),
            ])
            ->recordActions([
                TableAction::make('ver_detalle')
                    ->label('Ver detalle')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalHeading(fn (SiinmobHistoricoNota $record) => 'Detalle — ' . $record->detalle)
                    ->modalContent(fn (SiinmobHistoricoNota $record) => view(
                        'filament.modals.siinmob-historico-detalle',
                        ['nota' => $record, 'lineas' => $record->lineas]
                    ))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar'),
            ]);
    }
}
