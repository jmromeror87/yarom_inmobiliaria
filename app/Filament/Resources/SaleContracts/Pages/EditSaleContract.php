<?php

namespace App\Filament\Resources\SaleContracts\Pages;

use App\Filament\Resources\SaleContracts\SaleContractResource;
use App\Filament\Resources\SaleContracts\Schemas\SaleContractForm;
use App\Models\SaleContract;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;

class EditSaleContract extends EditRecord
{
    protected static string $resource = SaleContractResource::class;

    public function form(Schema $schema): Schema
    {
        return SaleContractForm::configure($schema);
    }

    protected function getHeaderActions(): array
    {
        $record = $this->record;
        $cerrado = $record->estado === 'cancelado';

        $acciones = [];

        // Badge de estado
        $acciones[] = Action::make('badge_estado')
            ->label(SaleContract::ESTADOS[$record->estado] ?? $record->estado)
            ->color(match($record->estado) {
                'entregado'  => 'success',
                'registrado' => 'info',
                'cancelado'  => 'danger',
                default      => 'warning',
            })
            ->disabled();

        // Avanzar etapa
        $siguienteEstado = match($record->estado) {
            'promesa'       => ['escrituracion', 'Pasar a escrituración'],
            'escrituracion' => ['registrado',    'Registrado en notaría'],
            'registrado'    => ['entregado',     'Marcar como entregado'],
            default         => null,
        };

        if ($siguienteEstado && !$cerrado) {
            [$nuevoEstado, $label] = $siguienteEstado;
            $acciones[] = Action::make('avanzar_estado')
                ->label($label)
                ->icon('heroicon-o-arrow-right-circle')
                ->color('success')
                ->requiresConfirmation()
                ->action(function () use ($nuevoEstado): void {
                    $this->record->update(['estado' => $nuevoEstado]);
                    Notification::make()->title("Estado actualizado: {$nuevoEstado}")->success()->send();
                    $this->redirect(static::getResource()::getUrl('edit', ['record' => $this->record]));
                });
        }

        // Registrar pago de comisión
        if ($record->estado_comision !== 'pagada') {
            $acciones[] = Action::make('registrar_pago_comision')
                ->label('💰 Registrar pago comisión')
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->form([
                    TextInput::make('valor_pago')
                        ->label('Valor recibido ($)')
                        ->numeric()->prefix('$')->required()
                        ->helperText('Saldo pendiente: $' . number_format($record->saldo_comision, 0, ',', '.')),
                    Select::make('forma_pago')
                        ->label('Forma de pago')
                        ->options([
                            'transferencia' => 'Transferencia',
                            'efectivo'      => 'Efectivo',
                            'cheque'        => 'Cheque',
                        ])->default('transferencia')->required(),
                    TextInput::make('referencia')->label('Referencia / N° transacción'),
                    Textarea::make('notas')->label('Notas')->rows(2),
                ])
                ->action(function (array $data): void {
                    $nuevoPagado = $this->record->comision_pagada + (float) $data['valor_pago'];
                    $nuevoEstado = $nuevoPagado >= ($this->record->valor_comision ?? 0)
                        ? 'pagada' : 'parcial';

                    $this->record->update([
                        'comision_pagada'  => $nuevoPagado,
                        'estado_comision'  => $nuevoEstado,
                    ]);

                    Notification::make()
                        ->title('Pago de comisión registrado')
                        ->body('$' . number_format($data['valor_pago'], 0, ',', '.') . ' · ' . $data['forma_pago'])
                        ->success()->send();

                    $this->refreshFormData(['comision_pagada','estado_comision']);
                });
        }

        if (!$cerrado) {
            $acciones[] = Action::make('cancelar_contrato')
                ->label('Cancelar negocio')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalDescription('¿Cancelar este contrato de corretaje? Esta acción no elimina el registro.')
                ->action(function (): void {
                    $this->record->update(['estado' => 'cancelado']);
                    Notification::make()->title('Contrato cancelado')->warning()->send();
                    $this->redirect(static::getResource()::getUrl('edit', ['record' => $this->record]));
                });

            $acciones[] = DeleteAction::make()->label('Eliminar');
        }

        return $acciones;
    }
}
