<?php

namespace App\Filament\Widgets;

use App\Models\RentBill;
use Filament\Actions\Action;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class FacturasEnMoraWidget extends BaseWidget
{
    protected static ?string $heading            = '🔴 Facturas en Mora — Casos Críticos';
    protected static ?int $sort = 4;
    protected int|string|array $columnSpan = 'full';
    protected static ?int $defaultPaginationPageOption = 5;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                RentBill::query()
                    ->whereIn('estado', ['en_mora', 'vencida'])
                    ->where('saldo_pendiente', '>', 0)
                    ->with(['arrendatario', 'property'])
                    ->orderByDesc('dias_mora')
            )
            ->columns([
                Tables\Columns\TextColumn::make('arrendatario.nombre_completo')
                    ->label('Arrendatario')
                    ->searchable()
                    ->limit(22)
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('property.direccion')
                    ->label('Inmueble')
                    ->limit(25)
                    ->description(fn ($record) => $record->property?->codigo),

                Tables\Columns\TextColumn::make('numero')
                    ->label('Factura')
                    ->color('primary')
                    ->searchable(),

                Tables\Columns\TextColumn::make('saldo_pendiente')
                    ->label('Saldo')
                    ->money('COP')
                    ->alignRight()
                    ->color('danger'),

                Tables\Columns\TextColumn::make('dias_mora')
                    ->label('Días mora')
                    ->alignCenter()
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state > 90  => 'danger',
                        $state > 30  => 'warning',
                        default      => 'info',
                    })
                    ->formatStateUsing(fn ($state) => $state . ' días'),

                Tables\Columns\TextColumn::make('mora_acumulada')
                    ->label('Mora')
                    ->money('COP')
                    ->alignRight()
                    ->color('danger'),

                Tables\Columns\TextColumn::make('total_a_pagar')
                    ->label('Total a pagar')
                    ->state(fn ($record) => $record->saldo_pendiente + $record->mora_acumulada)
                    ->money('COP')
                    ->alignRight()
                    ->weight('bold')
                    ->color('danger'),
            ])
            ->actions([
                Action::make('whatsapp')
                    ->label('')
                    ->tooltip('Enviar aviso por WhatsApp')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->color('success')
                    ->action(function (RentBill $record) {
                        if (!$record->arrendatario?->celular) return;
                        $token   = $record->generatePaymentToken();
                        $url     = route('payment.show', ['token' => $token]);
                        $saldo   = '$' . number_format($record->saldo_pendiente, 0, ',', '.');
                        $mora    = '$' . number_format($record->mora_acumulada, 0, ',', '.');
                        $total   = '$' . number_format($record->saldo_pendiente + $record->mora_acumulada, 0, ',', '.');
                        $msg = "⚠️ *Recordatorio de pago en mora*\n\n"
                            . "Estimad@ {$record->arrendatario->nombre_completo},\n\n"
                            . "Su factura *{$record->numero}* lleva *{$record->dias_mora} día(s)* de mora.\n\n"
                            . "💰 Saldo: {$saldo} COP\n"
                            . "📈 Mora: {$mora} COP\n"
                            . "💵 *Total: {$total} COP*\n\n"
                            . "🔗 Pagar en línea:\n{$url}";
                        app(\App\Services\WhatsAppService::class)->enviar($record->arrendatario->celular, $msg);
                        \Filament\Notifications\Notification::make()
                            ->title('Aviso enviado por WhatsApp')->success()->send();
                    }),
            ])
            ->emptyStateHeading('Sin facturas en mora')
            ->emptyStateIcon('heroicon-o-check-circle')
            ->emptyStateDescription('Todas las facturas están al día.')
            ->paginated([5, 10, 25]);
    }
}
