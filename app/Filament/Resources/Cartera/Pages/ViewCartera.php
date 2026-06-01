<?php

namespace App\Filament\Resources\Cartera\Pages;

use App\Filament\Resources\Cartera\CarteraResource;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewCartera extends ViewRecord
{
    protected static string $resource = CarteraResource::class;

    protected string $view = 'filament.pages.cartera.view-cartera';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('registrarAbono')
                ->label('Registrar abono')
                ->icon('heroicon-o-plus-circle')
                ->color('success')
                ->visible(fn () => $this->record->estado !== 'pagado' && $this->record->estado !== 'castigada')
                ->form([
                    TextInput::make('valor')
                        ->label('Valor del abono ($)')
                        ->numeric()->prefix('$')->required()
                        ->minValue(0.01)
                        ->maxValue(fn () => $this->record->saldo),

                    Select::make('forma_pago')
                        ->label('Forma de pago')
                        ->options([
                            'transferencia' => 'Transferencia bancaria',
                            'efectivo'      => 'Efectivo',
                            'cheque'        => 'Cheque',
                            'pse'           => 'PSE',
                        ])->required()->default('transferencia'),

                    TextInput::make('referencia')
                        ->label('Referencia / N° transacción'),

                    Textarea::make('notas')
                        ->label('Notas')->rows(2),
                ])
                ->action(function (array $data): void {
                    $this->record->registrarAbono(
                        valor:      (float) $data['valor'],
                        formaPago:  $data['forma_pago'],
                        referencia: $data['referencia'] ?? '',
                        userId:     auth()->user()?->getKey(),
                        notas:      $data['notas'] ?? '',
                    );

                    Notification::make()
                        ->title('Abono registrado correctamente')
                        ->success()
                        ->send();

                    $this->refreshFormData(['valor_pagado','saldo','estado']);
                }),

            Action::make('castigar')
                ->label('Castigar cartera')
                ->icon('heroicon-o-x-circle')
                ->color('gray')
                ->visible(fn () => in_array($this->record->estado, ['pendiente','parcial']))
                ->requiresConfirmation()
                ->modalDescription('Marcar esta cuenta como incobrable (castigada). El saldo pasará a pérdida. ¿Desea continuar?')
                ->action(function (): void {
                    $this->record->update(['estado' => 'castigada']);
                    Notification::make()->title('Cartera castigada')->warning()->send();
                }),
        ];
    }
}
