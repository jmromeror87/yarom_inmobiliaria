<?php
namespace App\Filament\Resources\RentBills\Pages;

use App\Filament\Resources\RentBills\RentBillResource;
use App\Filament\Widgets\FacturacionStatsWidget;
use App\Jobs\GenerarFacturasMensuales;
use App\Models\BusinessOrigin;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListRentBills extends ListRecords
{
    protected static string $resource = RentBillResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generar_facturas')
                ->label('Generar facturas')
                ->icon('heroicon-o-bolt')
                ->color('warning')
                ->extraAttributes([
                    'style' => 'background:linear-gradient(135deg,#d97706,#f59e0b)!important;color:#fff!important;border:none!important;box-shadow:0 4px 14px rgba(217,119,6,.35)!important;font-weight:700!important;',
                ])
                ->modalHeading('Generar facturas de arrendamiento')
                ->modalDescription('Se generará una factura para cada contrato activo del periodo y origen seleccionados que aún no tenga factura creada.')
                ->modalSubmitActionLabel('Generar facturas')
                ->modalIcon('heroicon-o-bolt')
                ->schema([
                    Select::make('mes')
                        ->label('Mes')
                        ->options([
                            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
                        ])
                        ->default(now()->month)
                        ->required()
                        ->native(false),

                    Select::make('anio')
                        ->label('Año')
                        ->options(array_combine(
                            range(now()->year - 1, now()->year + 1),
                            range(now()->year - 1, now()->year + 1)
                        ))
                        ->default(now()->year)
                        ->required()
                        ->native(false),

                    Select::make('business_origin_id')
                        ->label('Origen del negocio')
                        ->placeholder('Todos los orígenes')
                        ->options(BusinessOrigin::where('is_active', true)->pluck('nombre', 'id'))
                        ->native(false)
                        ->helperText('Deja en blanco para generar facturas de todos los orígenes a la vez.'),
                ])
                ->action(function (array $data) {
                    (new GenerarFacturasMensuales(
                        mesParam: (int) $data['mes'],
                        anioParam: (int) $data['anio'],
                        businessOriginId: $data['business_origin_id'] ? (int) $data['business_origin_id'] : null,
                    ))->handle();

                    Notification::make()
                        ->title('Facturas generadas y enviadas por WhatsApp')
                        ->success()
                        ->send();
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
