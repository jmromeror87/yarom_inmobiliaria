<?php

namespace App\Filament\Resources\RentBills\Tables;

use App\Models\AccountingEntry;
use App\Models\ElectronicInvoice;
use App\Services\ContabilidadService;
use App\Services\FacturacionElectronicaService;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Filament\Tables\Columns\IconColumn;
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
                    ),

                TextColumn::make('total_factura')
                    ->label('Total')->money('COP')->sortable(),

                TextColumn::make('dias_mora')
                    ->label('Días mora')->sortable()->alignCenter()
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'gray')
                    ->formatStateUsing(fn ($state) => $state > 0 ? $state . ' día' . ($state == 1 ? '' : 's') : '—'),

                TextColumn::make('mora_acumulada')
                    ->label('Mora')->money('COP')
                    ->color(fn ($state) => $state > 0 ? 'danger' : null),

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
                    }),

                TextColumn::make('tipo_documento')->label('Doc.')
                    ->badge()->color('gray')
                    ->formatStateUsing(fn ($state) => $state === 'factura_electronica' ? 'FE' : 'DE'),

                IconColumn::make('contabilizado')
                    ->label('Cont.')
                    ->tooltip('¿Tiene asiento contable?')
                    ->getStateUsing(fn ($record) => AccountingEntry::where('referencia_id', $record->id)
                        ->where('referencia_tipo', 'factura_rent_bill')->exists())
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),

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
            ->recordActions([
                Action::make('send_payment_link')
                    ->label('Enviar link')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->outlined()
                    ->visible(fn ($record) => in_array($record->estado, ['pendiente', 'en_mora', 'parcial', 'vencida']))
                    ->requiresConfirmation()
                    ->modalHeading('Enviar link de pago por WhatsApp')
                    ->modalDescription(fn ($record) => "Se enviará el link de pago a {$record->arrendatario?->nombre_completo} al número {$record->arrendatario?->celular}.")
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

                        app(\App\Services\WhatsAppService::class)->enviar($record->arrendatario->celular, $msg);

                        Notification::make()
                            ->title('Link enviado')
                            ->body("Link de pago enviado a {$record->arrendatario->celular}")
                            ->success()->send();
                    }),
                Action::make('recontabilizar')
                    ->label('Recontabilizar')
                    ->icon('heroicon-o-calculator')
                    ->color('gray')
                    ->outlined()
                    ->tooltip('Generar asiento contable manualmente')
                    ->visible(fn ($record) => !AccountingEntry::where('referencia_id', $record->id)
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
            ]);
    }
}
