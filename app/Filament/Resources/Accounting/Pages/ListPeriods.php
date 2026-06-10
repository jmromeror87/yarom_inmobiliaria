<?php

namespace App\Filament\Resources\Accounting\Pages;

use App\Filament\Resources\Accounting\AccountingPeriodResource;
use App\Filament\Widgets\PeriodosContablesStatsWidget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPeriods extends ListRecords
{
    protected static string $resource = AccountingPeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nuevo período')
                ->icon('heroicon-o-plus')
                ->extraAttributes([
                    'style' => 'background:linear-gradient(135deg,#1e3a8a,#E11D48)!important;color:#fff!important;border:none!important;box-shadow:0 4px 14px rgba(30,58,138,.35)!important;font-weight:700!important;',
                ])
                ->modalHeading('Crear Período Contable')
                ->modalSubmitAction(
                    fn(\Filament\Actions\Action $action) => $action
                        ->label('Crear período')
                        ->icon('heroicon-o-check-circle')
                        ->extraAttributes([
                            'style' => 'background:linear-gradient(135deg,#1e3a8a,#E11D48)!important;color:#fff!important;border:none!important;box-shadow:0 4px 14px rgba(30,58,138,.3)!important;font-weight:700!important;',
                        ])
                )
                ->modalCancelAction(
                    fn(\Filament\Actions\Action $action) => $action
                        ->label('Cancelar')
                        ->icon('heroicon-o-x-mark')
                        ->color('gray')
                ),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [PeriodosContablesStatsWidget::class];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 1;
    }
}
