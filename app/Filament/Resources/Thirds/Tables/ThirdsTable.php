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

use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
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
                    ->color(fn ($state, $record) => match(true) {
                        $record->es_propietario && !$record->es_arrendatario => 'gray',
                        $state === 'aprobado'    => 'success',
                        $state === 'rechazado'   => 'danger',
                        $state === 'condicional' => 'warning',
                        $state === 'en_proceso'  => 'info',
                        default                  => 'gray',
                    })
                    ->formatStateUsing(fn ($state, $record) => match(true) {
                        $record->es_propietario && !$record->es_arrendatario => '— No aplica',
                        $state === 'sin_evaluar' => '⏳ Pendiente',
                        $state === 'en_proceso'  => '🔍 En estudio',
                        $state === 'aprobado'    => '✓ Aprobado',
                        $state === 'condicional' => '⚠ Condicional',
                        $state === 'rechazado'   => '✕ Rechazado',
                        default                  => $state,
                    }),

                // ── Tipo de persona ─────────────────────────────
                TextColumn::make('tipo_persona')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn ($state) => $state === 'juridica' ? 'info' : 'gray')
                    ->formatStateUsing(fn ($state) => $state === 'juridica' ? 'Jurídica' : 'Natural'),

                // ── Estado expediente (solo propietarios) ───────
                TextColumn::make('estado_expediente')
                    ->label('Expediente')
                    ->badge()
                    ->visible(fn ($record) => $record?->es_propietario ?? true)
                    ->color(fn ($state) => match($state) {
                        'completo'   => 'success',
                        'bloqueado'  => 'danger',
                        default      => 'warning',
                    })
                    ->formatStateUsing(fn ($state) => match($state) {
                        'completo'   => '✓ Completo',
                        'bloqueado'  => '🚫 Bloqueado',
                        default      => '⏳ Incompleto',
                    }),

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
            ->recordActions([
                \Filament\Actions\Action::make('expediente')
                    ->label('Expediente')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->color('primary')
                    ->url(fn($record) => \App\Filament\Resources\Thirds\ThirdResource::getUrl('expediente', ['record' => $record])),

                \Filament\Actions\Action::make('portal_propietario')
                    ->label(fn ($record) => $record->portal_activo ? 'Portal ✓' : 'Portal')
                    ->icon('heroicon-o-link')
                    ->color(fn ($record) => $record->portal_activo ? 'success' : 'gray')
                    ->visible(fn ($record) => $record->es_propietario)
                    ->modalHeading(fn ($record) => 'Portal — ' . $record->nombre_completo)
                    ->modalWidth('lg')
                    ->modalDescription(fn ($record) => $record->portal_activo
                        ? 'El link está activo. Puede reenviarlo o revocarlo.'
                        : 'Este propietario aún no tiene acceso al portal.')
                    ->form(fn ($record) => $record->portal_activo ? [
                        \Filament\Forms\Components\Placeholder::make('url_actual')
                            ->label('Link activo')
                            ->content(fn () => $record->portal_url),
                        \Filament\Forms\Components\Textarea::make('mensaje_wap')
                            ->label('Mensaje a enviar por WhatsApp')
                            ->rows(4)
                            ->default(fn () =>
                                "Hola {$record->primer_nombre}, le compartimos su portal de propietario donde puede ver sus inmuebles, contratos y liquidaciones:\n\n"
                                . $record->portal_url
                                . "\n\nEste enlace es personal. Cualquier duda estamos a su disposición."),
                    ] : [
                        \Filament\Forms\Components\Placeholder::make('info')
                            ->label('')
                            ->content('Se generará un link único. Si el propietario tiene celular registrado, se enviará automáticamente por WhatsApp.'),
                        \Filament\Forms\Components\Textarea::make('mensaje_wap')
                            ->label('Mensaje de bienvenida (WhatsApp)')
                            ->rows(4)
                            ->default(fn () =>
                                "Hola {$record->primer_nombre}, le damos la bienvenida a su portal de propietario. Pronto recibirá el link de acceso para consultar sus inmuebles, contratos y liquidaciones en línea. ¡Cualquier duda estamos disponibles!"),
                    ])
                    ->modalSubmitActionLabel(fn ($record) => $record->portal_activo ? '📱 Reenviar por WhatsApp' : '🔗 Generar y enviar link')
                    ->action(function ($record, array $data): void {
                        // Generar o regenerar token
                        $token = $record->generarPortalToken();
                        $url   = route('portal.propietario', ['token' => $token]);

                        $enviado = false;
                        if ($record->celular) {
                            $wap     = app(\App\Services\WhatsAppService::class);
                            $mensaje = $data['mensaje_wap']
                                ?? "Hola {$record->primer_nombre}, su portal de propietario: {$url}";

                            // Insertar URL real en el mensaje si no está ya
                            if (! str_contains($mensaje, $url)) {
                                $mensaje .= "\n\n🔗 {$url}";
                            }

                            $resultado = $wap->enviar($record->celular, $mensaje);
                            $enviado   = $resultado['ok'] ?? false;
                        }

                        \Filament\Notifications\Notification::make()
                            ->title($enviado
                                ? '✅ Link generado y enviado por WhatsApp'
                                : '🔗 Link generado' . ($record->celular ? ' (WhatsApp no disponible — copie el link)' : ' — el propietario no tiene celular registrado'))
                            ->body($url)
                            ->success()
                            ->send();
                    })
                    ->extraModalFooterActions(fn ($action) => $action->getRecord()?->portal_activo ? [
                        \Filament\Actions\Action::make('revocar_portal')
                            ->label('Revocar acceso')
                            ->color('danger')
                            ->requiresConfirmation()
                            ->action(function () use ($action): void {
                                $action->getRecord()->revocarPortalToken();
                                \Filament\Notifications\Notification::make()
                                    ->title('Acceso al portal revocado')
                                    ->warning()->send();
                            }),
                    ] : []),

                \Filament\Actions\Action::make('habeas_data_pdf')
                    ->label('Habeas Data')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('info')
                    ->action(function ($record) {
                        $pdf  = Pdf::loadView('pdf.habeas-data', ['third' => $record])
                                   ->setPaper('letter', 'portrait');
                        $nombre = 'HabeasData_' . str_replace(' ', '_', $record->nombre_completo ?: 'tercero') . '.pdf';
                        return response()->streamDownload(fn () => print($pdf->output()), $nombre);
                    }),

                EditAction::make()->label('Editar')->icon('heroicon-o-pencil'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Eliminar'),
                ]),
            ]);
    }
}
