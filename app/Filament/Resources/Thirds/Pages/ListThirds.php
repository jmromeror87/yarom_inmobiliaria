<?php

namespace App\Filament\Resources\Thirds\Pages;

use App\Filament\Resources\Thirds\ThirdResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Livewire\Attributes\On;

class ListThirds extends ListRecords
{
    protected static string $resource = ThirdResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nuevo Tercero')
                ->icon('heroicon-o-user-plus')
                ->color('primary')
                ->extraAttributes([
                    'style' => 'background:linear-gradient(135deg,#1e3a8a,#E11D48)!important;border:none!important;color:#fff!important;font-weight:800!important;letter-spacing:.02em!important;padding:10px 22px!important;border-radius:12px!important;box-shadow:0 4px 14px rgba(225,29,72,.35)!important;transition:transform .12s,box-shadow .12s!important;--c-action-icon-color:#fff!important;',
                    'onmouseover' => "this.style.transform='translateY(-1px)';this.style.boxShadow='0 6px 20px rgba(225,29,72,.45)'",
                    'onmouseout'  => "this.style.transform='';this.style.boxShadow='0 4px 14px rgba(225,29,72,.35)'",
                ]),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\ThirdsStatsWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 1;
    }

    #[On('thirds-filter')]
    public function applyThirdsFilter(string $filter, string $value): void
    {
        $this->tableFilters[$filter] = ['value' => $value];
    }

    #[On('thirds-filter-clear')]
    public function clearThirdsFilter(): void
    {
        $this->resetTableFiltersForm();
    }
}
