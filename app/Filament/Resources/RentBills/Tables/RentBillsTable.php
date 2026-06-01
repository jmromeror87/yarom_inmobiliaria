<?php

namespace App\Filament\Resources\RentBills\Tables;

use App\Models\AccountingEntry;
use App\Services\ContabilidadService;
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
            ->recordActions([
                Action::make('send_payment_link')
                    ->label('Enviar link')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
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

                EditAction::make()->label('Ver / Pagar'),
            ]);
    }
}
