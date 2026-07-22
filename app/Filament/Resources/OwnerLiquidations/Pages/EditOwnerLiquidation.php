<?php
namespace App\Filament\Resources\OwnerLiquidations\Pages;

use App\Filament\Resources\OwnerLiquidations\OwnerLiquidationResource;
use Filament\Actions;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditOwnerLiquidation extends EditRecord
{
    protected static string $resource = OwnerLiquidationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('anular')
                ->label('Anular liquidación')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->outlined()
                ->visible(fn () => in_array($this->record->estado, ['pendiente', 'aprobada']))
                ->requiresConfirmation()
                ->modalHeading('Anular liquidación')
                ->modalDescription('La liquidación queda anulada y no se pierde el registro — puede consultarse siempre, solo deja de estar pendiente de girar.')
                ->schema([
                    Textarea::make('motivo_anulacion')
                        ->label('Motivo de anulación')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    $this->record->update([
                        'estado' => 'anulada',
                        'notas' => trim(($this->record->notas ?? '') . ' Anulada: ' . $data['motivo_anulacion']),
                    ]);
                    Notification::make()->title('Liquidación anulada')->warning()->send();
                    $this->redirect($this->getResource()::getUrl('index'));
                }),
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
