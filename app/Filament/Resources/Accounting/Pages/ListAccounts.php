<?php

namespace App\Filament\Resources\Accounting\Pages;

use App\Filament\Resources\Accounting\AccountingAccountResource;
use App\Filament\Widgets\PlanCuentasStatsWidget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Livewire\Attributes\On;

class ListAccounts extends ListRecords
{
    protected static string $resource = AccountingAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nueva cuenta')
                ->icon('heroicon-o-plus')
                ->extraAttributes([
                    'style' => 'background:linear-gradient(135deg,#1e3a8a,#E11D48)!important;color:#fff!important;border:none!important;box-shadow:0 4px 14px rgba(30,58,138,.35)!important;font-weight:700!important;',
                ]),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [PlanCuentasStatsWidget::class];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 1;
    }

    #[On('puc-filter')]
    public function filterByClase(string $clase): void
    {
        $this->tableFilters['clase']['value'] = $clase;
    }

    #[On('puc-filter-clear')]
    public function clearClaseFilter(): void
    {
        $this->tableFilters['clase']['value'] = '';
    }
}
