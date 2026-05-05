<?php
namespace App\Filament\Resources\OwnerLiquidations\Pages;

use App\Filament\Resources\OwnerLiquidations\OwnerLiquidationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListOwnerLiquidations extends ListRecords
{
    protected static string $resource = OwnerLiquidationResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()->label('Nueva Liquidación Manual')];
    }

    public function getTabs(): array
    {
        return [
            'todas'     => Tab::make('Todas'),
            'pendiente' => Tab::make('Pendientes')
                ->modifyQueryUsing(fn(Builder $q) => $q->where('estado','pendiente'))
                ->badge(fn() => \App\Models\OwnerLiquidation::where('estado','pendiente')->count())
                ->badgeColor('warning'),
            'aprobada'  => Tab::make('Aprobadas')
                ->modifyQueryUsing(fn(Builder $q) => $q->where('estado','aprobada'))
                ->badge(fn() => \App\Models\OwnerLiquidation::where('estado','aprobada')->count())
                ->badgeColor('info'),
            'pagada'    => Tab::make('Pagadas')
                ->modifyQueryUsing(fn(Builder $q) => $q->where('estado','pagada'))
                ->badgeColor('success'),
            'anulada'   => Tab::make('Anuladas')
                ->modifyQueryUsing(fn(Builder $q) => $q->where('estado','anulada'))
                ->badgeColor('danger'),
        ];
    }
}
