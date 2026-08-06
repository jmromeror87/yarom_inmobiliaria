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
                    Select::make('mes_desde')
                        ->label('Mes desde')
                        ->options([
                            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
                        ])
                        ->default(now()->month)
                        ->required()
                        ->native(false),

                    Select::make('anio_desde')
                        ->label('Año desde')
                        ->options(array_combine(
                            range(now()->year - 1, now()->year + 1),
                            range(now()->year - 1, now()->year + 1)
                        ))
                        ->default(now()->year)
                        ->required()
                        ->native(false),

                    Select::make('mes_hasta')
                        ->label('Mes hasta')
                        ->options([
                            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
                        ])
                        ->default(now()->month)
                        ->required()
                        ->native(false)
                        ->helperText('Igual al "desde" si solo quieres generar un mes.'),

                    Select::make('anio_hasta')
                        ->label('Año hasta')
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
                        ->columnSpanFull()
                        ->helperText('Deja en blanco para generar facturas de todos los orígenes a la vez.'),
                ])
                ->action(function (array $data) {
                    $desde = ((int) $data['anio_desde']) * 100 + (int) $data['mes_desde'];
                    $hasta = ((int) $data['anio_hasta']) * 100 + (int) $data['mes_hasta'];

                    if ($desde > $hasta) {
                        Notification::make()
                            ->title('El período "desde" es posterior al "hasta"')
                            ->danger()
                            ->send();
                        return;
                    }

                    $periodos = [];
                    $anio = (int) $data['anio_desde'];
                    $mes = (int) $data['mes_desde'];
                    while (($anio * 100 + $mes) <= $hasta) {
                        $periodos[] = [$mes, $anio];
                        $mes++;
                        if ($mes > 12) {
                            $mes = 1;
                            $anio++;
                        }
                    }

                    foreach ($periodos as [$mesPeriodo, $anioPeriodo]) {
                        (new GenerarFacturasMensuales(
                            mesParam: $mesPeriodo,
                            anioParam: $anioPeriodo,
                            businessOriginId: $data['business_origin_id'] ? (int) $data['business_origin_id'] : null,
                        ))->handle();
                    }

                    Notification::make()
                        ->title('Facturas generadas y enviadas por WhatsApp')
                        ->body(count($periodos) > 1 ? ('Períodos procesados: ' . count($periodos)) : null)
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
