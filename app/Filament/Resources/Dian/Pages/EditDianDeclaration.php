<?php

namespace App\Filament\Resources\Dian\Pages;

use App\Filament\Resources\Dian\DianDeclarationResource;
use App\Services\DianObligationService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditDianDeclaration extends EditRecord
{
    protected static string $resource = DianDeclarationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('recalcular')
                ->label('Recalcular desde contabilidad')
                ->icon('heroicon-o-calculator')->color('warning')
                ->requiresConfirmation()
                ->action(function () {
                    try {
                        $decl = DianObligationService::recalcular($this->record);
                        Notification::make()
                            ->title('Recalculado — Valor: $' . number_format($decl->valor_a_pagar, 0, ',', '.'))
                            ->success()->send();
                        $this->record = $decl;
                        $this->fillForm();
                    } catch (\Throwable $e) {
                        Notification::make()->title('Error: ' . $e->getMessage())->danger()->send();
                    }
                }),
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Declaración actualizada';
    }

    protected function afterSave(): void
    {
        $record = $this->record;

        // Marcar usuario que presentó o pagó
        $changes = [];
        if ($record->wasChanged('estado')) {
            if ($record->estado === 'presentada' && !$record->presentado_por) {
                $changes['presentado_por'] = \Illuminate\Support\Facades\Auth::id();
            }
            if ($record->estado === 'pagada' && !$record->pagado_por) {
                $changes['pagado_por'] = \Illuminate\Support\Facades\Auth::id();
            }
        }

        if (!empty($changes)) {
            $record->updateQuietly($changes);
        }
    }
}
