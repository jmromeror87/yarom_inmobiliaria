<?php

namespace App\Filament\Resources\RentalContracts\Tables;

use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RentalContractsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero_contrato')
                    ->label('N° Contrato')->searchable()->sortable()
                    ->weight('bold')->color('primary'),

                TextColumn::make('tipo')
                    ->label('Tipo')->badge()
                    ->color(fn ($state) => $state === 'comercial' ? 'warning' : 'info')
                    ->formatStateUsing(fn ($state) => $state === 'comercial' ? '🏢 Comercial' : '🏠 Vivienda'),

                TextColumn::make('property.codigo')
                    ->label('Inmueble')
                    ->description(fn ($record) => $record->property?->direccion)
                    ->searchable(),

                TextColumn::make('arrendatario.nombre_completo')
                    ->label('Arrendatario')
                    ->description(fn ($record) => $record->arrendatario?->numero_documento)
                    ->searchable(),

                TextColumn::make('canon_mensual')
                    ->label('Canon')->money('COP')->sortable(),

                TextColumn::make('fecha_inicio')
                    ->label('Inicio')->date('d/m/Y')->sortable(),

                TextColumn::make('fecha_fin')
                    ->label('Vence')->date('d/m/Y')->sortable()
                    ->color(fn ($record) => $record->estaVencido() ? 'danger' : ($record->estaProximoAVencer() ? 'warning' : null)),

                TextColumn::make('estado')
                    ->label('Estado')->badge()
                    ->color(fn ($state) => match($state) {
                        'activo'    => 'success',
                        'firmado'   => 'primary',
                        'terminado' => 'gray',
                        'cancelado' => 'danger',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match($state) {
                        'borrador'             => '📝 Borrador',
                        'enviado_arrendatario' => '📤 Enviado',
                        'aprobado'             => '✅ Aprobado',
                        'firmado'              => '✍️ Firmado',
                        'activo'               => '🟢 Activo',
                        'terminado'            => '🔴 Terminado',
                        'cancelado'            => '❌ Cancelado',
                        default                => $state,
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->filters([
                SelectFilter::make('tipo')->label('Tipo')
                    ->options(['vivienda_urbana'=>'Vivienda','comercial'=>'Comercial']),
                SelectFilter::make('estado')->label('Estado')
                    ->options([
                        'borrador'=>'Borrador','activo'=>'Activo',
                        'firmado'=>'Firmado','terminado'=>'Terminado','cancelado'=>'Cancelado',
                    ]),
            ])
            ->actions([
                EditAction::make()->label('Editar')
                    ->hidden(fn ($record) => $record->isReadOnly()),
                \Filament\Actions\Action::make('ver')
                    ->label('Ver')->icon('heroicon-o-eye')->color('gray')
                    ->url(fn ($record) => \App\Filament\Resources\RentalContracts\RentalContractResource::getUrl('edit', ['record' => $record]))
                    ->visible(fn ($record) => $record->isReadOnly()),
            ])
            ->bulkActions([
                BulkActionGroup::make([DeleteBulkAction::make()->label('Eliminar')]),
            ]);
    }
}
