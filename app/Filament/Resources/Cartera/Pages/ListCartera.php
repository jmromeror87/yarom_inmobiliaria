<?php

namespace App\Filament\Resources\Cartera\Pages;

use App\Filament\Resources\Cartera\CarteraResource;
use App\Filament\Resources\Cartera\Tables\CarteraTable;
use App\Models\CuentaPorCobrar;
use Filament\Actions\Action;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ListCartera extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = CarteraResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('crear')
                ->label('Nueva cuenta por cobrar')
                ->icon('heroicon-o-plus')
                ->url(CarteraResource::getUrl('create'))
                ->extraAttributes([
                    'style' => 'background:linear-gradient(135deg,#1e3a8a,#E11D48)!important;color:#fff!important;border:none!important;box-shadow:0 4px 14px rgba(30,58,138,.35)!important;font-weight:700!important;',
                ]),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [CarteraResource::getWidgets()[0] ?? []];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 4;
    }

    protected function getStats(): array
    {
        $total    = CuentaPorCobrar::whereIn('estado', ['pendiente','parcial'])->sum('saldo');
        $vencidas = CuentaPorCobrar::whereIn('estado', ['pendiente','parcial'])
            ->where('fecha_vencimiento', '<', now())->sum('saldo');
        $pagadas  = CuentaPorCobrar::where('estado', 'pagado')
            ->whereMonth('fecha_pago_total', now()->month)->sum('valor_original');
        $count    = CuentaPorCobrar::whereIn('estado', ['pendiente','parcial'])->count();

        return [
            Stat::make('Cartera total', '$' . number_format($total, 0, ',', '.'))->color('warning'),
            Stat::make('Cartera vencida', '$' . number_format($vencidas, 0, ',', '.'))->color('danger'),
            Stat::make('Recaudo este mes', '$' . number_format($pagadas, 0, ',', '.'))->color('success'),
            Stat::make('Cuentas activas', $count)->color('info'),
        ];
    }

    public function table(Table $table): Table
    {
        return CarteraTable::configure($table);
    }
}
