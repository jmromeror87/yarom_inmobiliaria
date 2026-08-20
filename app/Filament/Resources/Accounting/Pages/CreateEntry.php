<?php

namespace App\Filament\Resources\Accounting\Pages;

use App\Filament\Resources\Accounting\AccountingEntryResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;

class CreateEntry extends CreateRecord
{
    protected static string $resource = AccountingEntryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
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

    protected function afterCreate(): void
    {
        $this->record->recalcularTotales();
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('edit', ['record' => $this->record]);
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->label('Crear comprobante')
            ->icon('heroicon-o-check-circle')
            ->extraAttributes([
                'style' => 'background:linear-gradient(135deg,#1e3a8a,#E11D48)!important;color:#fff!important;border:none!important;box-shadow:0 4px 14px rgba(30,58,138,.3)!important;font-weight:700!important;',
            ]);
    }

    protected function getCreateAnotherFormAction(): Action
    {
        return parent::getCreateAnotherFormAction()
            ->label('Crear y agregar otro')
            ->icon('heroicon-o-plus-circle')
            ->outlined();
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->label('Cancelar')
            ->icon('heroicon-o-x-mark')
            ->outlined()
            ->color('gray');
    }

    public function getHeader(): ?\Illuminate\Contracts\View\View
    {
        return view('filament.pages.accounting.create-entry-header');
    }
}
