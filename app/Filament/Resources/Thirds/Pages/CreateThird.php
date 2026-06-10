<?php

namespace App\Filament\Resources\Thirds\Pages;

use App\Filament\Resources\Thirds\ThirdResource;
use App\Filament\Widgets\ThirdCreateHeaderWidget;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateThird extends CreateRecord
{
    protected static string $resource = ThirdResource::class;

    protected function getHeaderWidgets(): array
    {
        return [ThirdCreateHeaderWidget::class];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 1;
    }

    private array $gradientStyle = [
        'style' => 'background:linear-gradient(135deg,#1e3a8a,#E11D48)!important;border:none!important;color:#fff!important;font-weight:700!important;border-radius:10px!important;box-shadow:0 3px 10px rgba(225,29,72,.28)!important;',
    ];

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->label('Crear Tercero')
            ->icon('heroicon-o-check-circle')
            ->extraAttributes($this->gradientStyle);
    }

    protected function getCreateAnotherFormAction(): Action
    {
        return parent::getCreateAnotherFormAction()
            ->label('Crear y agregar otro')
            ->icon('heroicon-o-plus-circle')
            ->outlined()
            ->extraAttributes([
                'style' => 'border-radius:10px!important;font-weight:600!important;',
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
