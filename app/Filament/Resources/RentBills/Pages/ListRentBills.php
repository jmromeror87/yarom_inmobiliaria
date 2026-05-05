<?php
namespace App\Filament\Resources\RentBills\Pages;
use App\Filament\Resources\RentBills\RentBillResource;
use App\Jobs\GenerarFacturasMensuales;
use Filament\Actions\CreateAction;
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
                ->color('warning')
                ->icon('heroicon-o-bolt')
                ->requiresConfirmation()
                ->modalHeading('¿Generar facturas del mes?')
                ->modalDescription('Se generarán facturas para todos los contratos activos que no tengan factura este mes.')
                ->modalSubmitActionLabel('Sí, generar')
                ->action(function () {
                    (new GenerarFacturasMensuales())->handle();
                    Notification::make()->title('✅ Facturas generadas y enviadas por WhatsApp')->success()->send();
                }),
        ];
    }
}
