<?php

namespace App\Filament\Resources\Thirds\Tables;

use Barryvdh\DomPDF\Facade\Pdf;
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

                // ── Avatar + Nombre + Doc ────────────────────────
                TextColumn::make('nombre_completo')
                    ->label('Tercero')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) =>
                        ($record->tipo_documento ?? 'CC') . ' · ' . ($record->numero_documento ?? '—')
                    )
                    ->icon('heroicon-o-user-circle')
                    ->iconColor('primary')
                    ->weight('bold')
                    ->grow(),

                // ── Roles con badges de color ────────────────────
                TextColumn::make('roles_display')
                    ->label('Roles')
                    ->html()
                    ->getStateUsing(function ($record) {
                        $badges = [];
                        if ($record->es_propietario)    $badges[] = '<span style="display:inline-flex;align-items:center;gap:3px;background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;border-radius:99px;padding:2px 8px;font-size:10px;font-weight:700;white-space:nowrap;">🏠 Propietario</span>';
                        if ($record->es_arrendatario)   $badges[] = '<span style="display:inline-flex;align-items:center;gap:3px;background:#fef2f2;color:#991b1b;border:1px solid #fecaca;border-radius:99px;padding:2px 8px;font-size:10px;font-weight:700;white-space:nowrap;">🔑 Arrendatario</span>';
                        if ($record->es_cliente_compra) $badges[] = '<span style="display:inline-flex;align-items:center;gap:3px;background:#f0fdf4;color:#166534;border:1px solid #bbf7d0;border-radius:99px;padding:2px 8px;font-size:10px;font-weight:700;white-space:nowrap;">🛒 Comprador</span>';
                        if ($record->es_fiador)         $badges[] = '<span style="display:inline-flex;align-items:center;gap:3px;background:#fdf4ff;color:#7e22ce;border:1px solid #e9d5ff;border-radius:99px;padding:2px 8px;font-size:10px;font-weight:700;white-space:nowrap;">🤝 Fiador</span>';
                        if ($record->es_proveedor)      $badges[] = '<span style="display:inline-flex;align-items:center;gap:3px;background:#fffbeb;color:#92400e;border:1px solid #fde68a;border-radius:99px;padding:2px 8px;font-size:10px;font-weight:700;white-space:nowrap;">🔧 Proveedor</span>';
                        return $badges ? '<div style="display:flex;flex-wrap:wrap;gap:4px;">' . implode('', $badges) . '</div>' : '<span style="color:#94a3b8;font-size:12px;">—</span>';
                    }),

                // ── Contacto ─────────────────────────────────────
                TextColumn::make('celular')
                    ->label('Contacto')
                    ->description(fn ($record) => $record->email ?? '—')
                    ->icon('heroicon-o-phone')
                    ->iconColor('success')
                    ->searchable()
                    ->copyable(),

                // ── Crédito ───────────────────────────────────────
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

                // ── Archivo físico ────────────────────────────────
                TextColumn::make('ubicacion_archivo')
                    ->label('Archivo')
                    ->icon('heroicon-o-archive-box')
                    ->iconColor('warning')
                    ->searchable()
                    ->placeholder('—')
                    ->toggleable(),

                // ── Expediente ────────────────────────────────────
                TextColumn::make('estado_expediente')
                    ->label('Expediente')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'completo'  => 'success',
                        'bloqueado' => 'danger',
                        default     => 'warning',
                    })
                    ->formatStateUsing(fn ($state) => match($state) {
                        'completo'  => '✓ Completo',
                        'bloqueado' => '🚫 Bloqueado',
                        default     => '⏳ Incompleto',
                    }),

            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TernaryFilter::make('is_active')->label('Activos'),
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
                        $token = $record->generarPortalToken();
                        $url   = route('portal.propietario', ['token' => $token]);
                        $enviado = false;
                        if ($record->celular) {
                            $wap     = app(\App\Services\WhatsAppService::class);
                            $mensaje = $data['mensaje_wap'] ?? "Hola {$record->primer_nombre}, su portal de propietario: {$url}";
                            if (! str_contains($mensaje, $url)) { $mensaje .= "\n\n🔗 {$url}"; }
                            $resultado = $wap->enviar($record->celular, $mensaje);
                            $enviado   = $resultado['ok'] ?? false;
                        }
                        \Filament\Notifications\Notification::make()
                            ->title($enviado ? '✅ Link generado y enviado por WhatsApp' : '🔗 Link generado')
                            ->body($url)->success()->send();
                    })
                    ->extraModalFooterActions(fn ($action) => $action->getRecord()?->portal_activo ? [
                        \Filament\Actions\Action::make('revocar_portal')
                            ->label('Revocar acceso')->color('danger')->requiresConfirmation()
                            ->action(function () use ($action): void {
                                $action->getRecord()->revocarPortalToken();
                                \Filament\Notifications\Notification::make()->title('Acceso al portal revocado')->warning()->send();
                            }),
                    ] : []),

                \Filament\Actions\Action::make('habeas_data_pdf')
                    ->label('Habeas Data')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('info')
                    ->action(function ($record) {
                        $pdf    = Pdf::loadView('pdf.habeas-data', ['third' => $record])->setPaper('letter', 'portrait');
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
