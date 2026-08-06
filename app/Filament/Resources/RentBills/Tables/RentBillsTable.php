<?php

namespace App\Filament\Resources\RentBills\Tables;

use App\Models\AccountingEntry;
use App\Models\ElectronicInvoice;
use App\Services\ContabilidadService;
use App\Services\FacturacionElectronicaService;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class RentBillsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero')
                    ->label('N° Factura')->searchable()->sortable()
                    ->weight('bold')->color('primary'),


                TextColumn::make('arrendatario.nombre_completo')
                    ->label('Arrendatario')
                    ->searchable()
                    ->description(fn ($record) => $record->rentalContract?->en_revision ? '⚠️ Contrato en revisión' : null),

                TextColumn::make('origen')
                    ->label('Origen')
                    ->badge()
                    ->getStateUsing(fn ($record) => $record->rentalContract?->property?->businessOrigin?->nombre ?? '—')
                    ->color(fn ($state) => match(true) {
                        str_contains(strtolower($state), 'victoria') => 'warning',
                        str_contains(strtolower($state), 'serviarrendar') => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('mes')
                    ->label('Periodo')
                    ->formatStateUsing(fn ($record) =>
                        \Carbon\Carbon::create($record->anio, $record->mes, 1)->translatedFormat('F Y')
                    )
                    ->sortable(['periodo_inicio']),

                TextColumn::make('total_factura')
                    ->label('Total')->money('COP')->sortable(),

                TextColumn::make('dias_mora')
                    ->label('Días mora')->sortable()->alignCenter()
                    ->badge()
                    ->color(fn ($state, $record) => $state > 0 ? ($record->estado === 'pagada' ? 'success' : 'danger') : 'gray')
                    ->formatStateUsing(fn ($state, $record) => $state > 0
                        ? $state . ' día' . ($state == 1 ? '' : 's') . ($record->estado === 'pagada' ? ' (ya pagada)' : '')
                        : '—'),

                TextColumn::make('mora_acumulada')
                    ->label('Mora')->money('COP')
                    ->color(fn ($state, $record) => $state > 0 && $record->estado !== 'pagada' ? 'danger' : null),

                TextColumn::make('saldo_pendiente')
                    ->label('Saldo')->money('COP')
                    ->color(fn ($state) => $state > 0 ? 'warning' : 'success')
                    ->description(fn ($record) => $record->saldo_anterior_arrastrado > 0
                        ? 'Incluye $' . number_format($record->saldo_anterior_arrastrado, 0, ',', '.') . ' arrastrado'
                        : null),

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
                        'pendiente' => 'Pendiente',
                        'parcial'   => 'Parcial',
                        'pagada'    => 'Pagada',
                        'en_mora'   => 'En mora',
                        'vencida'   => 'Vencida',
                        'anulada'   => 'Anulada',
                        default     => $state,
                    })
                    ->tooltip(fn ($record) => $record->estado === 'anulada'
                        ? "Anulada por {$record->anuladoPor?->name} el {$record->anulado_en?->format('d/m/Y h:i A')}\nMotivo: {$record->motivo_anulacion}"
                        : null),

                TextColumn::make('tipo_documento')->label('Doc.')
                    ->badge()->color('gray')
                    ->formatStateUsing(fn ($state) => $state === 'factura_electronica' ? 'FE' : 'DE')
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('contabilizado')
                    ->label('Cont.')
                    ->tooltip(fn ($record) => $record->contabilizado_via_historico
                        ? 'Contabilizado vía histórico Siinmob: ' . $record->referencia_historico
                        : '¿Tiene asiento contable?')
                    ->getStateUsing(fn ($record) => $record->contabilizado_via_historico
                        || AccountingEntry::where('referencia_id', $record->id)
                            ->where('referencia_tipo', 'factura_rent_bill')->exists())
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor(fn ($record) => $record->contabilizado_via_historico ? 'info' : 'success')
                    ->falseColor('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('fe_estado')
                    ->label('FE DIAN')
                    ->badge()
                    ->getStateUsing(fn ($record) => $record->electronicInvoice?->estado ?? 'sin_fe')
                    ->color(fn ($state) => match($state) {
                        'aceptada', 'aceptada_con_notificacion' => 'success',
                        'rechazada', 'error' => 'danger',
                        'enviada'    => 'info',
                        'anulada'    => 'warning',
                        'pendiente'  => 'gray',
                        default      => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match($state) {
                        'aceptada'                   => '✅ Aceptada',
                        'aceptada_con_notificacion'  => '✅ Aceptada*',
                        'rechazada'                  => '❌ Rechazada',
                        'error'                      => '⚠️ Error',
                        'enviada'                    => '🕐 Enviada',
                        'anulada'                    => '🚫 Anulada',
                        'pendiente'                  => '⏳ Pendiente',
                        default                      => '—',
                    })
                    ->visible(fn () => \App\Models\Company::first()?->factura_electronica_activa ?? false),
            ])
            ->defaultSort('periodo_inicio', 'asc')
            ->striped()
            ->filters([
                SelectFilter::make('periodo')
                    ->label('Período')
                    ->options(function () {
                        $opciones = [];
                        $cursor = now()->startOfMonth();
                        for ($i = 0; $i < 13; $i++) {
                            $opciones[$cursor->year . '-' . $cursor->month] = ucfirst($cursor->translatedFormat('F Y'));
                            $cursor->subMonth();
                        }
                        return $opciones;
                    })
                    ->query(function ($query, array $data) {
                        if (empty($data['value'])) return $query;
                        [$anio, $mes] = explode('-', $data['value']);
                        return $query->where('anio', $anio)->where('mes', $mes);
                    }),
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
                SelectFilter::make('origen')
                    ->label('Origen')
                    ->options(\App\Models\BusinessOrigin::pluck('nombre', 'id'))
                    ->query(function ($query, array $data) {
                        if (empty($data['value'])) return $query;
                        return $query->whereHas('rentalContract.property', fn ($q) =>
                            $q->where('business_origin_id', $data['value'])
                        );
                    }),
            ])
            ->headerActions([
                Action::make('send_all_payment_links')
                    ->label('Enviar todos los links pendientes')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->modalHeading('Enviar links de pago masivamente')
                    ->modalDescription(function () {
                        $count = self::elegiblesParaEnvioMasivo()->count();
                        return "Se enviará el link de pago por WhatsApp a {$count} arrendatario(s) con factura pendiente. Se excluyen automáticamente los inmuebles de origen Victoria (sin celular registrado) y las facturas que ya tienen el link enviado.";
                    })
                    ->requiresConfirmation()
                    ->action(function () {
                        $bills = self::elegiblesParaEnvioMasivo()->get();

                        $enviados = 0;
                        $fallidos = 0;

                        foreach ($bills as $record) {
                            try {
                                $token = $record->generatePaymentToken();
                                $url   = route('payment.show', ['token' => $token]);

                                $msg = "💳 *Link de pago — {$record->numero}*\n\n"
                                    . "Hola {$record->arrendatario->nombre_completo},\n\n"
                                    . "Su factura de arrendamiento está lista para pago en línea.\n\n"
                                    . "📋 *Factura:* {$record->numero}\n"
                                    . "💰 *Valor:* \$" . number_format($record->saldo_pendiente, 0, ',', '.') . " COP\n"
                                    . "📅 *Vence:* {$record->fecha_limite_pago->format('d/m/Y')}\n\n"
                                    . "🔗 *Pagar aquí:*\n{$url}\n\n"
                                    . "Puede pagar con PSE, Nequi, tarjeta débito/crédito o en nuestra oficina.\n"
                                    . "— Serviarrendar S.A.S";

                                $resultado = app(\App\Services\WhatsAppService::class)->enviar($record->arrendatario->celular, $msg);

                                if ($resultado['ok'] ?? false) {
                                    $record->update(['wap_enviado' => true, 'wap_enviado_at' => now()]);
                                    $enviados++;
                                } else {
                                    $fallidos++;
                                }
                            } catch (\Throwable $e) {
                                $fallidos++;
                            }
                        }

                        Notification::make()
                            ->title('Envío masivo completado')
                            ->body("{$enviados} link(s) enviados correctamente" . ($fallidos > 0 ? " · {$fallidos} fallaron" : ''))
                            ->success()
                            ->send();
                    }),
            ])
            ->recordActions([
                Action::make('send_payment_link')
                    // El tooltip (hover) no funciona en mobile, así que la
                    // fecha de envío va directo en el label del botón —
                    // visible sin necesidad de pasar el mouse.
                    ->label(fn ($record) => $record->wap_enviado && $record->wap_enviado_at
                        ? 'Enviado ' . $record->wap_enviado_at->format('d/m/y h:i A')
                        : 'Enviar link')
                    ->icon(fn ($record) => $record->wap_enviado ? 'heroicon-o-arrow-path' : 'heroicon-o-paper-airplane')
                    ->color(fn ($record) => $record->wap_enviado ? 'gray' : 'success')
                    ->outlined()
                    ->tooltip(fn ($record) => $record->wap_enviado && $record->wap_enviado_at
                        ? 'Ya enviado el ' . $record->wap_enviado_at->format('d/m/Y h:i A') . ' — clic para reenviar'
                        : null)
                    ->visible(fn ($record) => in_array($record->estado, ['pendiente', 'en_mora', 'parcial', 'vencida']))
                    ->requiresConfirmation()
                    ->modalHeading(fn ($record) => $record->wap_enviado ? 'Reenviar link de pago por WhatsApp' : 'Enviar link de pago por WhatsApp')
                    ->modalDescription(function ($record) {
                        $base = "Se enviará el link de pago a {$record->arrendatario?->nombre_completo} al número {$record->arrendatario?->celular}.";
                        if ($record->wap_enviado && $record->wap_enviado_at) {
                            $base = "⚠️ Ya se envió el " . $record->wap_enviado_at->format('d/m/Y \\a \\l\\a\\s h:i A') . ". " . $base;
                        }
                        return $base;
                    })
                    ->action(function ($record) {
                        if (!$record->arrendatario?->celular) {
                            Notification::make()
                                ->title('Sin número de celular')
                                ->body('El arrendatario no tiene celular registrado.')
                                ->danger()->send();
                            return;
                        }

                        $token = $record->generatePaymentToken();
                        $url   = route('payment.show', ['token' => $token]);

                        $msg = "💳 *Link de pago — {$record->numero}*\n\n"
                            . "Hola {$record->arrendatario->nombre_completo},\n\n"
                            . "Su factura de arrendamiento está lista para pago en línea.\n\n"
                            . "📋 *Factura:* {$record->numero}\n"
                            . "💰 *Valor:* \$" . number_format($record->saldo_pendiente, 0, ',', '.') . " COP\n"
                            . "📅 *Vence:* {$record->fecha_limite_pago->format('d/m/Y')}\n\n"
                            . "🔗 *Pagar aquí:*\n{$url}\n\n"
                            . "Puede pagar con PSE, Nequi, tarjeta débito/crédito o en nuestra oficina.\n"
                            . "— Serviarrendar S.A.S";

                        $resultado = app(\App\Services\WhatsAppService::class)->enviar($record->arrendatario->celular, $msg);

                        // El botón solo cambia a "Enviado ..." si esto queda
                        // en true — antes la notificación decía "enviado" con
                        // éxito SIEMPRE, aunque el envío real hubiera fallado,
                        // así que parecía que el botón "no se actualizaba"
                        // cuando en realidad nunca se marcó como enviado.
                        if ($resultado['ok'] ?? false) {
                            $record->update(['wap_enviado' => true, 'wap_enviado_at' => now()]);

                            Notification::make()
                                ->title('✅ Link enviado')
                                ->body("Link de pago enviado a {$record->arrendatario->celular}")
                                ->success()->send();
                        } else {
                            Notification::make()
                                ->title('❌ No se pudo enviar el WhatsApp')
                                ->body($resultado['error'] ?? 'El servicio de WhatsApp no respondió correctamente. Intenta de nuevo en un momento.')
                                ->danger()->send();
                        }
                    }),
                Action::make('recontabilizar')
                    ->label('Recontabilizar')
                    ->icon('heroicon-o-calculator')
                    ->color('gray')
                    ->outlined()
                    ->tooltip('Generar asiento contable manualmente')
                    ->visible(fn ($record) => !$record->contabilizado_via_historico
                        && !AccountingEntry::where('referencia_id', $record->id)
                            ->where('referencia_tipo', 'factura_rent_bill')->exists())
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        try {
                            $entry = ContabilidadService::generarParaFactura($record);
                            if ($entry) {
                                Notification::make()->title('Asiento generado')->success()->send();
                            } else {
                                Notification::make()->title('Ya existe o faltan cuentas PUC')->warning()->send();
                            }
                        } catch (\Throwable $e) {
                            Notification::make()->title('Error: ' . $e->getMessage())->danger()->send();
                        }
                    }),

                Action::make('emitir_fe')
                    ->label('Emitir FE')
                    ->icon('heroicon-o-document-check')
                    ->color('info')
                    ->outlined()
                    ->tooltip('Emitir factura electrónica ante la DIAN')
                    ->visible(fn ($record) =>
                        $record->tipo_documento === 'factura_electronica' &&
                        (\App\Models\Company::first()?->factura_electronica_activa ?? false) &&
                        !ElectronicInvoice::where('rent_bill_id', $record->id)
                            ->whereIn('estado', ['aceptada', 'aceptada_con_notificacion', 'enviada'])
                            ->exists()
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Emitir Factura Electrónica')
                    ->modalDescription('Se enviará la factura al operador autorizado DIAN. ¿Desea continuar?')
                    ->action(function ($record) {
                        try {
                            $fe = FacturacionElectronicaService::emitir($record);
                            if (!$fe) {
                                Notification::make()->title('FE no aplica o ya existe')->warning()->send();
                                return;
                            }
                            if ($fe->es_aceptada) {
                                Notification::make()->title('✅ Factura electrónica aceptada por la DIAN')->success()->send();
                            } else {
                                Notification::make()->title('FE procesada — estado: ' . $fe->estado_label)->warning()->send();
                            }
                        } catch (\Throwable $e) {
                            Notification::make()->title('Error FE: ' . $e->getMessage())->danger()->send();
                        }
                    }),

                EditAction::make()
                    ->label('Ver / Pagar')
                    ->icon('heroicon-o-eye')
                    ->outlined(),

                Action::make('anular_factura')
                    ->label('Anular')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->button()
                    ->visible(fn ($record) => $record->estado !== 'anulada')
                    ->requiresConfirmation()
                    ->modalHeading('¿Deseas anular esta factura?')
                    ->modalDescription(fn ($record) => "Factura {$record->numero} — {$record->arrendatario?->nombre_completo} — \$" . number_format($record->total_factura, 0, ',', '.') . '. Esta acción reversa automáticamente todos los asientos contables ligados (factura, mora, pagos y, si aplica, la liquidación al propietario) y queda registrada con tu usuario y la fecha de hoy.')
                    ->modalSubmitActionLabel('Sí, anular factura')
                    ->schema([
                        Textarea::make('motivo')
                            ->label('Motivo de la anulación')
                            ->placeholder('Ej: factura duplicada, error en el valor del contrato, contrato terminado anticipadamente...')
                            ->required()
                            ->minLength(10)
                            ->rows(3),
                    ])
                    ->action(function ($record, array $data) {
                        try {
                            $record->anularConReversion($data['motivo'], Auth::id());
                            Notification::make()
                                ->title('Factura anulada')
                                ->body("Se reversaron los asientos contables de {$record->numero}.")
                                ->warning()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()->title('Error al anular: ' . $e->getMessage())->danger()->send();
                        }
                    }),
            ]);
    }

    private static function elegiblesParaEnvioMasivo()
    {
        return \App\Models\RentBill::query()
            ->whereIn('estado', ['pendiente', 'en_mora', 'parcial', 'vencida'])
            ->where('wap_enviado', false)
            ->whereHas('arrendatario', fn ($q) => $q->whereNotNull('celular')->where('celular', '!=', ''))
            ->whereDoesntHave('rentalContract.property.businessOrigin', fn ($q) => $q->where('nombre', 'Victoria'))
            ->with('arrendatario');
    }
}
