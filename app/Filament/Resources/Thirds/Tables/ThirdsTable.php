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
| Archivo: ThirdsTable.php
| Fecha: CURRENT_DAY/05/2026
| Versión: v1.0
|--------------------------------------------------------------------------
*/

namespace App\Filament\Resources\Thirds\Tables;

use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ThirdsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                // ── Documento + Nombre ──────────────────────────
                TextColumn::make('nombre_completo')
                    ->label('Tercero')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) =>
                        $record->tipo_documento . ' · ' . $record->numero_documento
                    )
                    ->icon('heroicon-o-user-circle')
                    ->iconColor('primary')
                    ->weight('bold'),

                // ── Roles con badges ────────────────────────────
                TextColumn::make('roles_display')
                    ->label('Roles')
                    ->getStateUsing(function ($record) {
                        $roles = [];
                        if ($record->es_propietario)    $roles[] = '🏠 Propietario';
                        if ($record->es_arrendatario)   $roles[] = '🔑 Arrendatario';
                        if ($record->es_cliente_compra) $roles[] = '🛒 Comprador';
                        if ($record->es_fiador)         $roles[] = '🤝 Fiador';
                        if ($record->es_proveedor)      $roles[] = '🔧 Proveedor';
                        return implode("\n", $roles) ?: '—';
                    })
                    ->html()
                    ->wrap(),

                // ── Contacto: celular + email ───────────────────
                TextColumn::make('celular')
                    ->label('Contacto')
                    ->description(fn ($record) => $record->email ?? '—')
                    ->icon('heroicon-o-phone')
                    ->iconColor('success')
                    ->searchable(),

                // ── Ciudad ─────────────────────────────────────
                TextColumn::make('municipio.nombre')
                    ->label('Ciudad')
                    ->description(fn ($record) => $record->departamento?->nombre)
                    ->icon('heroicon-o-map-pin')
                    ->iconColor('info'),

                // ── Estado crediticio ───────────────────────────
                TextColumn::make('estado_crediticio')
                    ->label('Crédito')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'aprobado'    => 'success',
                        'rechazado'   => 'danger',
                        'condicional' => 'warning',
                        'en_proceso'  => 'info',
                        default       => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match($state) {
                        'sin_evaluar' => 'Sin evaluar',
                        'en_proceso'  => 'En proceso',
                        'aprobado'    => '✓ Aprobado',
                        'condicional' => '⚠ Condicional',
                        'rechazado'   => '✕ Rechazado',
                        default       => $state,
                    }),

                // ── Tipo de persona ─────────────────────────────
                TextColumn::make('tipo_persona')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn ($state) => $state === 'juridica' ? 'info' : 'gray')
                    ->formatStateUsing(fn ($state) => $state === 'juridica' ? 'Jurídica' : 'Natural'),

                // ── Activo ──────────────────────────────────────
                IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->filters([
                TernaryFilter::make('es_propietario')->label('Propietarios'),
                TernaryFilter::make('es_arrendatario')->label('Arrendatarios'),
                TernaryFilter::make('es_cliente_compra')->label('Compradores'),
                TernaryFilter::make('es_fiador')->label('Fiadores'),
                SelectFilter::make('estado_crediticio')
                    ->label('Estado crediticio')
                    ->options([
                        'sin_evaluar' => 'Sin evaluar',
                        'en_proceso'  => 'En proceso',
                        'aprobado'    => 'Aprobado',
                        'condicional' => 'Condicional',
                        'rechazado'   => 'Rechazado',
                    ]),
                SelectFilter::make('tipo_persona')
                    ->label('Tipo persona')
                    ->options(['natural' => 'Natural', 'juridica' => 'Jurídica']),
            ])
            ->actions([
                EditAction::make()->label('Editar')->icon('heroicon-o-pencil'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Eliminar'),
                ]),
            ]);
    }
}
