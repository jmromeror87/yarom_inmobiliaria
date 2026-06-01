<?php

namespace App\Filament\Resources\Accounting\Pages;

use App\Filament\Resources\Accounting\AccountingEntryResource;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditEntry extends EditRecord
{
    protected static string $resource = AccountingEntryResource::class;

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

    protected function getRedirectUrl(): string
    {
        return AccountingEntryResource::getUrl('index');
    }
}
