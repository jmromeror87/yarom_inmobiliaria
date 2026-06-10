<?php

namespace App\Filament\Resources\PropertyServices\Pages;

use App\Filament\Resources\PropertyServices\PropertyServiceResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPropertyService extends EditRecord
{
    protected static string $resource = PropertyServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Eliminar')
                ->icon('heroicon-o-trash')
                ->outlined()
                ->color('danger'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->label('Guardar cambios')
            ->icon('heroicon-o-check-circle')
            ->extraAttributes([
                'style' => 'background:linear-gradient(135deg,#1e3a8a,#E11D48)!important;color:#fff!important;border:none!important;box-shadow:0 4px 14px rgba(30,58,138,.3)!important;font-weight:700!important;',
            ]);
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->label('Cancelar')
            ->icon('heroicon-o-x-mark')
            ->outlined()
            ->color('gray');
    }
}
