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
| Archivo: PropertiesTable.php
| Fecha: CURRENT_DAY/05/2026
| Versión: v1.0
|--------------------------------------------------------------------------
*/
        

namespace App\Filament\Resources\Properties\Tables;

use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PropertiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
               TextColumn::make('codigo')
    ->label('Código / Tipo')
    ->description(fn ($record) => $record->tipo?->nombre)
    ->searchable()->sortable()
    ->weight('bold')->color('primary'),


                TextColumn::make('businessOrigin.nombre')
                    ->label('Origen')
                    ->badge()
                    ->color(fn ($record) => $record->businessOrigin?->color ? \Filament\Support\Colors\Color::hex($record->businessOrigin->color) : 'gray')
                    ->toggleable(),

                TextColumn::make('propietario.nombre_completo')
                    ->label('Propietario')
                    ->description(fn ($record) => $record->propietario?->numero_documento)
                    ->searchable(),

                TextColumn::make('direccion')
                    ->label('Dirección')
                    ->description(fn ($record) => $record->barrio . ' — ' . $record->municipio?->nombre)
                    ->limit(30),

                TextColumn::make('canon_arriendo')
                    ->label('Canon')
                    ->money('COP')->sortable(),

                TextColumn::make('porcentaje_documentos')
                    ->label('Docs.')
                    ->getStateUsing(fn ($record) => $record->porcentaje_documentos . '%')
                    ->badge()
                    ->color(fn ($record) => match(true) {
                        $record->porcentaje_documentos == 100 => 'success',
                        $record->porcentaje_documentos >= 50  => 'warning',
                        default                               => 'danger',
                    }),

                TextColumn::make('estado')
                    ->label('Estado')->badge()
                    ->color(fn ($state) => match($state) {
                        'disponible'           => 'success',
                        'arrendado'            => 'primary',
                        'en_venta'             => 'info',
                        'vendido'              => 'gray',
                        'en_captacion'         => 'warning',
                        'documentos_pendientes'=> 'danger',
                        'en_mantenimiento'     => 'warning',
                        'inactivo'             => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match($state) {
                        'en_captacion'         => 'En captación',
                        'documentos_pendientes'=> 'Docs. pendientes',
                        'disponible'           => 'Disponible',
                        'arrendado'            => 'Arrendado',
                        'en_venta'             => 'En venta',
                        'vendido'              => 'Vendido',
                        'en_mantenimiento'     => 'Mantenimiento',
                        'inactivo'             => 'Inactivo',
                        default                => $state,
                    }),

                TextColumn::make('disponibilidad')
                    ->label('Disponible para')
                    ->getStateUsing(fn ($record) => match(true) {
                        $record->disponible_arriendo && $record->disponible_venta => '🔑 Arriendo + 🏷️ Venta',
                        $record->disponible_arriendo => '🔑 Arriendo',
                        $record->disponible_venta    => '🏷️ Venta',
                        default                      => '—',
                    }),

                TextColumn::make('ctl_alerta')
                    ->label('CTL')
                    ->getStateUsing(fn ($record) => match(true) {
                        $record->ctl_tiene_limitacion => '🚫 Bloqueado',
                        !$record->doc_certificado_libertad => '—',
                        $record->doc_certificado_libertad_fecha?->diffInDays(now()) > 30 => '⚠️ Vencido',
                        default => '✅ OK',
                    })
                    ->badge()
                    ->color(fn ($record) => match(true) {
                        $record->ctl_tiene_limitacion => 'danger',
                        !$record->doc_certificado_libertad => 'gray',
                        $record->doc_certificado_libertad_fecha?->diffInDays(now()) > 30 => 'warning',
                        default => 'success',
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->filters([
                SelectFilter::make('estado')->label('Estado')
                    ->options([
                        'en_captacion'         => 'En captación',
                        'documentos_pendientes'=> 'Docs. pendientes',
                        'disponible'           => 'Disponible',
                        'arrendado'            => 'Arrendado',
                        'en_venta'             => 'En venta',
                        'vendido'              => 'Vendido',
                        'en_mantenimiento'     => 'Mantenimiento',
                        'inactivo'             => 'Inactivo',
                    ]),
                SelectFilter::make('property_type_id')
                    ->label('Tipo de inmueble')
                    ->relationship('tipo', 'nombre'),
                SelectFilter::make('business_origin_id')
                    ->label('Origen')
                    ->relationship('businessOrigin', 'nombre'),
            ])
            ->recordActions([
                EditAction::make()->label('Editar'),
                \Filament\Actions\Action::make('dashboard')->label('Expediente')->icon('heroicon-o-clipboard-document-list')->color('primary')
                    ->url(fn ($record) => \App\Filament\Resources\Properties\PropertyResource::getUrl('dashboard', ['record' => $record])),
                \Filament\Actions\Action::make('galeria')->label('Galería')->icon('heroicon-o-photo')->color('info')
                    ->url(fn ($record) => \App\Filament\Resources\Properties\PropertyResource::getUrl('gallery', ['record' => $record])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Eliminar seleccionados')
                        ->requiresConfirmation()
                        ->modalHeading('¿Eliminar inmuebles seleccionados?')
                        ->modalDescription('Solo se eliminarán registros sin contratos activos. Esta acción la pueden realizar únicamente administradores.')
                        ->modalSubmitActionLabel('Sí, eliminar')
                        ->visible(fn () => auth()->user()?->hasAnyRole(['super_admin', 'admin'])),
                ]),
            ]);
    }
}
