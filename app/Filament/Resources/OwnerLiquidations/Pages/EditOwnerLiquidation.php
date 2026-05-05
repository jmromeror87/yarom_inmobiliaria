<?php
namespace App\Filament\Resources\OwnerLiquidations\Pages;

use App\Filament\Resources\OwnerLiquidations\OwnerLiquidationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOwnerLiquidation extends EditRecord
{
    protected static string $resource = OwnerLiquidationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->visible(fn() => $this->record->estado === 'pendiente'),
            Actions\RestoreAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function mount(int|string $record): void
    {
        parent::mount($record);
        if (in_array($this->record->estado, ['pagada','anulada'])) {
            \Filament\Notifications\Notification::make()
                ->title('Solo lectura')
                ->body('Las liquidaciones pagadas o anuladas no se pueden editar.')
                ->warning()->send();
            $this->redirect($this->getResource()::getUrl('index'));
        }
    }
}
