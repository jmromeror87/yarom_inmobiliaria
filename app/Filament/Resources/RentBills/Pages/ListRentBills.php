<?php
namespace App\Filament\Resources\RentBills\Pages;

use App\Filament\Resources\RentBills\RentBillResource;
use App\Filament\Widgets\FacturacionStatsWidget;
use App\Jobs\GenerarFacturasMensuales;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListRentBills extends ListRecords
{
    protected static string $resource = RentBillResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generar_facturas')
                ->label('⚡ Generar facturas del mes')
                ->icon('heroicon-o-bolt')
                ->extraAttributes([
                    'style' => 'background:linear-gradient(135deg,#d97706,#f59e0b)!important;color:#fff!important;border:none!important;box-shadow:0 4px 14px rgba(217,119,6,.35)!important;font-weight:700!important;',
                ])
                ->requiresConfirmation()
                ->modalHeading('¿Generar facturas del mes?')
                ->modalDescription('Se generarán facturas para todos los contratos activos que no tengan factura este mes.')
                ->modalSubmitActionLabel('Sí, generar')
                ->action(function () {
                    (new GenerarFacturasMensuales())->handle();
                    Notification::make()->title('Facturas generadas y enviadas por WhatsApp')->success()->send();
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [FacturacionStatsWidget::class];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 1;
    }
}
