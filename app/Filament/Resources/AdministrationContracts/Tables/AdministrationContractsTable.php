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
| Archivo: AdministrationContractsTable.php
| Fecha: CURRENT_DAY/05/2026
| Versión: v1.0
|--------------------------------------------------------------------------
*/


namespace App\Filament\Resources\AdministrationContracts\Tables;

use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AdministrationContractsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero_contrato')
                    ->label('Contrato')->searchable()->sortable()
                    ->weight('bold')->color('primary'),

                TextColumn::make('property.codigo')
                    ->label('Inmueble')
                    ->description(fn ($record) => $record->property?->direccion)
                    ->searchable(),

                TextColumn::make('propietario.nombre_completo')
                    ->label('Propietario')
                    ->description(fn ($record) => $record->propietario?->celular)
                    ->searchable(),

                TextColumn::make('canon_pactado')
                    ->label('Canon')->money('COP')->sortable(),

                TextColumn::make('comision_porcentaje')
                    ->label('Comisión')->suffix('%'),

                TextColumn::make('fecha_inicio')->label('Inicio')->date('d/m/Y'),
                TextColumn::make('fecha_fin')
                    ->label('Vence')->date('d/m/Y')
                    ->color(fn ($record) =>
                        $record->fecha_fin?->isPast() ? 'danger' :
                        ($record->fecha_fin?->diffInDays(now()) < 60 ? 'warning' : null)
                    ),

                TextColumn::make('estado')->label('Estado')->badge()
                    ->color(fn ($state) => match($state) {
                        'activo'               => 'success',
                        'firmado'              => 'primary',
                        'aprobado_gerencia'    => 'info',
                        'aprobado'             => 'info',
                        'enviado_notaria'      => 'primary',
                        'autenticado_notaria'  => 'primary',
                        'borrador'             => 'gray',
                        'en_revision'          => 'warning',
                        'enviado_propietario'  => 'warning',
                        'terminado'            => 'gray',
                        'cancelado'            => 'danger',
                        default                => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match($state) {
                        'borrador'             => 'Borrador',
                        'enviado_propietario'  => 'Enviado propietario',
                        'en_revision'          => 'En revisión',
                        'aprobado_gerencia'    => 'Aprobado gerencia',
                        'enviado_notaria'      => 'Enviado a notaría',
                        'autenticado_notaria'  => 'Autenticado notaría',
                        'firmado'              => 'Firmado',
                        'activo'               => 'Activo',
                        'terminado'            => 'Terminado',
                        'cancelado'            => 'Cancelado',
                        default                => $state,
                    }),

                TextColumn::make('clauses_editadas')
                    ->label('Edits.')
                    ->getStateUsing(fn ($record) =>
                        $record->clauses()->where('fue_editada', true)->count()
                    )
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'warning' : 'gray'),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->filters([
                SelectFilter::make('estado')->label('Estado')
                    ->options([
                        'borrador'            => 'Borrador', 'aprobado_gerencia' => 'Aprobado gerencia', 'enviado_notaria' => 'Enviado notaría', 'autenticado_notaria' => 'Autenticado notaría',
                        'enviado_propietario'=> 'Enviado al propietario',
                        'en_revision'        => 'En revisión',
                        'aprobado'           => 'Aprobado',
                        'firmado'            => 'Firmado',
                        'activo'             => 'Activo',
                        'terminado'          => 'Terminado',
                        'cancelado'          => 'Cancelado',
                    ]),
            ])
            ->recordActions([
                EditAction::make()->label('Editar')->hidden(fn ($record) => $record->isReadOnly()),
                \Filament\Actions\Action::make('ver')->label('Ver')->icon('heroicon-o-eye')->color('gray')
                    ->url(fn ($record) => \App\Filament\Resources\AdministrationContracts\AdministrationContractResource::getUrl('edit', ['record' => $record]))
                    ->visible(fn ($record) => $record->isReadOnly()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Eliminar'),
                ]),
            ]);
    }
}
