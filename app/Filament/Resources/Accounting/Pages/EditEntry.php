<?php

namespace App\Filament\Resources\Accounting\Pages;

use App\Filament\Resources\Accounting\AccountingEntryResource;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Exceptions\Halt;

class EditEntry extends EditRecord
{
    protected static string $resource = AccountingEntryResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $lineas = $data['lines'] ?? [];
        $debitos = round(array_sum(array_column($lineas, 'debito')), 2);
        $creditos = round(array_sum(array_column($lineas, 'credito')), 2);

        if (abs($debitos - $creditos) >= 0.01) {
            Notification::make()
                ->title('El comprobante no cuadra')
                ->body('Débitos $' . number_format($debitos, 0, ',', '.') . ' vs. créditos $' . number_format($creditos, 0, ',', '.') . '. No se guardó — ajusta las partidas para que sumen igual.')
                ->danger()
                ->send();

            throw new Halt();
        }

        return $data;
    }

    public function mount(int|string $record): void
    {
        parent::mount($record);

        if ($this->record->estado !== 'borrador') {
            Notification::make()
                ->title('Solo lectura')
                ->body('Los comprobantes contabilizados o anulados no se pueden editar.')
                ->warning()
                ->send();
            $this->redirect(AccountingEntryResource::getUrl('view', ['record' => $this->record]));
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('Borrar')
                ->hidden(fn() => $this->record->estado !== 'borrador'),
        ];
    }

    protected function afterSave(): void
    {
        // Recalcula totales después de que Filament guarda el Repeater de líneas
        $this->record->recalcularTotales();
    }

    protected function getRedirectUrl(): string
    {
        return AccountingEntryResource::getUrl('index');
    }
}
