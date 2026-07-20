<?php

namespace App\Filament\Resources\Accounting\Pages;

use App\Filament\Pages\Accounting\BalancePrueba;
use App\Filament\Pages\Accounting\ComprobanteEgreso;
use App\Filament\Pages\Accounting\ComprobanteIngreso;
use App\Filament\Pages\Accounting\LibroDiario;
use App\Filament\Pages\Accounting\LibroMayor;
use App\Filament\Resources\Accounting\AccountingEntryResource;
use App\Filament\Widgets\ComprobantesStatsWidget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEntries extends ListRecords
{
    protected static string $resource = AccountingEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('libro_diario')
                ->label('Libro Diario')
                ->icon('heroicon-o-book-open')
                ->outlined()
                ->color('gray')
                ->url(fn() => LibroDiario::getUrl()),

            \Filament\Actions\Action::make('libro_mayor')
                ->label('Libro Auxiliar')
                ->icon('heroicon-o-rectangle-stack')
                ->outlined()
                ->color('gray')
                ->url(fn() => LibroMayor::getUrl()),

            \Filament\Actions\Action::make('balance_prueba')
                ->label('Balance de Comprobación')
                ->icon('heroicon-o-scale')
                ->outlined()
                ->color('gray')
                ->url(fn() => BalancePrueba::getUrl()),

            \Filament\Actions\Action::make('comprobante_ingreso')
                ->label('Comprobante de Ingreso')
                ->icon('heroicon-o-arrow-down-circle')
                ->color('success')
                ->url(fn() => ComprobanteIngreso::getUrl()),

            \Filament\Actions\Action::make('comprobante_egreso')
                ->label('Comprobante de Egreso')
                ->icon('heroicon-o-arrow-up-circle')
                ->color('danger')
                ->url(fn() => ComprobanteEgreso::getUrl()),

            CreateAction::make()
                ->label('Otros Comprobantes')
                ->icon('heroicon-o-plus')
                ->extraAttributes([
                    'style' => 'background:linear-gradient(135deg,#1e3a8a,#E11D48)!important;color:#fff!important;border:none!important;box-shadow:0 4px 14px rgba(30,58,138,.35)!important;font-weight:700!important;',
                ]),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [ComprobantesStatsWidget::class];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 1;
    }
}
