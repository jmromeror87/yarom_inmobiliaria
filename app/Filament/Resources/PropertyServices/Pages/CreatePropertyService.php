<?php

namespace App\Filament\Resources\PropertyServices\Pages;

use App\Filament\Resources\PropertyServices\PropertyServiceResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreatePropertyService extends CreateRecord
{
    protected static string $resource = PropertyServiceResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->label('Registrar servicio')
            ->icon('heroicon-o-check-circle')
            ->extraAttributes([
                'style' => 'background:linear-gradient(135deg,#1e3a8a,#E11D48)!important;color:#fff!important;border:none!important;box-shadow:0 4px 14px rgba(30,58,138,.3)!important;font-weight:700!important;',
            ]);
    }

    protected function getCreateAnotherFormAction(): Action
    {
        return parent::getCreateAnotherFormAction()
            ->label('Registrar y agregar otro')
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
        return view('filament.pages.property-services.create-header');
    }
}
