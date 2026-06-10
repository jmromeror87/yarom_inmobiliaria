<?php

namespace App\Filament\Resources\Properties\Pages;

use App\Filament\Resources\Properties\PropertyResource;
use App\Filament\Widgets\PropertyHeroWidget;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditProperty extends EditRecord
{
    protected static string $resource = PropertyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Eliminar Inmueble')
                ->icon('heroicon-o-trash')
                ->outlined()
                ->requiresConfirmation()
                ->modalHeading('¿Eliminar este inmueble?')
                ->modalDescription('Esta acción eliminará el inmueble y todos sus documentos asociados. Solo administradores pueden realizar esta acción.')
                ->modalSubmitActionLabel('Sí, eliminar')
                ->visible(fn () => auth()->user()?->hasAnyRole(['super_admin', 'admin'])),
            ForceDeleteAction::make()->outlined()
                ->visible(fn () => auth()->user()?->hasAnyRole(['super_admin', 'admin'])),
            RestoreAction::make()->outlined()
                ->visible(fn () => auth()->user()?->hasAnyRole(['super_admin', 'admin'])),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [PropertyHeroWidget::class];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 1;
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Inmueble actualizado')
            ->body('Los cambios fueron guardados correctamente.')
            ->duration(4000);
    }

    protected function getFailedSaveNotification(): ?Notification
    {
        return Notification::make()
            ->danger()
            ->title('Error al guardar')
            ->body('Revisa los campos obligatorios e intenta de nuevo.')
            ->persistent();
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->label('Guardar cambios')
            ->icon('heroicon-o-check-circle')
            ->extraAttributes([
                'style' => 'background:linear-gradient(135deg,#1e3a8a,#E11D48)!important;border:none!important;color:#fff!important;font-weight:700!important;border-radius:10px!important;padding:9px 22px!important;box-shadow:0 3px 10px rgba(225,29,72,.28)!important;',
            ]);
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->label('Cancelar')
            ->outlined()
            ->extraAttributes([
                'style' => 'border-radius:10px!important;font-weight:600!important;border-color:#cbd5e1!important;color:#475569!important;',
            ]);
    }
}
