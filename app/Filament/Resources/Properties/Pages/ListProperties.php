<?php

namespace App\Filament\Resources\Properties\Pages;

use App\Filament\Resources\Properties\PropertyResource;
use App\Filament\Widgets\PropertiesStatsWidget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Livewire\Attributes\On;

class ListProperties extends ListRecords
{
    protected static string $resource = PropertyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Crear Inmueble')
                ->icon('heroicon-o-plus-circle')
                ->extraAttributes([
                    'style' => 'background:linear-gradient(135deg,#1e3a8a,#E11D48)!important;border:none!important;color:#fff!important;font-weight:800!important;letter-spacing:.02em!important;padding:10px 22px!important;border-radius:12px!important;box-shadow:0 4px 14px rgba(225,29,72,.35)!important;transition:transform .12s,box-shadow .12s!important;',
                    'onmouseover' => "this.style.transform='translateY(-1px)';this.style.boxShadow='0 6px 20px rgba(225,29,72,.45)'",
                    'onmouseout'  => "this.style.transform='';this.style.boxShadow='0 4px 14px rgba(225,29,72,.35)'",
                ]),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [PropertiesStatsWidget::class];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 1;
    }

    #[On('properties-filter')]
    public function applyPropertiesFilter(string $filter, string $value): void
    {
        $this->tableFilters[$filter] = ['value' => $value];
    }

    #[On('properties-filter-clear')]
    public function clearPropertiesFilter(): void
    {
        $this->resetTableFiltersForm();
    }
}
