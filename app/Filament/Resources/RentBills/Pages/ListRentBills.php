<?php
namespace App\Filament\Resources\RentBills\Pages;

use App\Filament\Resources\RentBills\RentBillResource;
use App\Filament\Widgets\FacturacionStatsWidget;
use App\Jobs\GenerarFacturasMensuales;
use App\Models\BusinessOrigin;
use App\Models\RentBill;
use App\Models\RentalContract;
use App\Models\Third;
use App\Services\WhatsAppService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class ListRentBills extends ListRecords
{
    protected static string $resource = RentBillResource::class;

    protected string $view = 'filament.resources.rent-bills.pages.list-rent-bills';

    public string $search = '';

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

            Action::make('send_all_payment_links')
                ->label('Enviar todos los links pendientes')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->modalHeading('Enviar links de pago masivamente')
                ->modalDescription(function () {
                    $count = self::elegiblesParaEnvioMasivo()->count();
                    return "Se enviará el link de pago por WhatsApp a {$count} arrendatario(s) con factura pendiente. Se excluyen automáticamente los inmuebles de origen Victoria (sin celular registrado) y las facturas que ya tienen el link enviado.";
                })
                ->requiresConfirmation()
                ->action(function () {
                    $bills = self::elegiblesParaEnvioMasivo()->get();

                    $enviados = 0;
                    $fallidos = 0;

                    foreach ($bills as $record) {
                        try {
                            $token = $record->generatePaymentToken();
                            $url   = route('payment.show', ['token' => $token]);

                            $msg = "💳 *Link de pago — {$record->numero}*\n\n"
                                . "Hola {$record->arrendatario->nombre_completo},\n\n"
                                . "Su factura de arrendamiento está lista para pago en línea.\n\n"
                                . "📋 *Factura:* {$record->numero}\n"
                                . "💰 *Valor:* \$" . number_format($record->saldo_pendiente, 0, ',', '.') . " COP\n"
                                . "📅 *Vence:* {$record->fecha_limite_pago->format('d/m/Y')}\n\n"
                                . "🔗 *Pagar aquí:*\n{$url}\n\n"
                                . "Puede pagar con PSE, Nequi, tarjeta débito/crédito o en nuestra oficina.\n"
                                . "— Serviarrendar S.A.S";

                            $resultado = app(WhatsAppService::class)->enviar($record->arrendatario->celular, $msg);

                            if ($resultado['ok'] ?? false) {
                                $record->update(['wap_enviado' => true, 'wap_enviado_at' => now()]);
                                $enviados++;
                            } else {
                                $fallidos++;
                            }
                        } catch (\Throwable $e) {
                            $fallidos++;
                        }
                    }

                    Notification::make()
                        ->title('Envío masivo completado')
                        ->body("{$enviados} link(s) enviados correctamente" . ($fallidos > 0 ? " · {$fallidos} fallaron" : ''))
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

    /**
     * Resuelve inquilinos que coinciden con el término de búsqueda por
     * cédula/nombre propio, o indirectamente porque uno de sus inmuebles
     * arrendados coincide (dirección o código).
     */
    public function getResultados(): Collection
    {
        $term = trim($this->search);

        if ($term === '') {
            return collect();
        }

        $thirdIdsDirectos = Third::query()
            ->where(function ($q) use ($term) {
                $q->where('nombre_completo', 'like', "%{$term}%")
                    ->orWhere('numero_documento', 'like', "%{$term}%");
            })
            ->pluck('id');

        $thirdIdsPorInmueble = RentalContract::query()
            ->whereHas('property', function ($q) use ($term) {
                $q->where('direccion', 'like', "%{$term}%")
                    ->orWhere('codigo', 'like', "%{$term}%");
            })
            ->pluck('arrendatario_id');

        $thirdIds = $thirdIdsDirectos->merge($thirdIdsPorInmueble)->unique();

        if ($thirdIds->isEmpty()) {
            return collect();
        }

        return Third::query()
            ->whereIn('id', $thirdIds)
            ->with(['rentalContracts' => function ($q) {
                $q->with(['property', 'rentBills' => function ($q2) {
                    $q2->orderByDesc('periodo_inicio');
                }])
                    ->orderByRaw("estado = 'activo' desc")
                    ->orderByDesc('fecha_inicio');
            }])
            ->orderBy('nombre_completo')
            ->limit(25)
            ->get();
    }

    public function enviarLink(int $billId): void
    {
        $record = RentBill::with('arrendatario')->findOrFail($billId);

        if (! $record->arrendatario?->celular) {
            Notification::make()->title('Sin número de celular')->body('El arrendatario no tiene celular registrado.')->danger()->send();
            return;
        }

        $token = $record->generatePaymentToken();
        $url   = route('payment.show', ['token' => $token]);

        $msg = "💳 *Link de pago — {$record->numero}*\n\n"
            . "Hola {$record->arrendatario->nombre_completo},\n\n"
            . "Su factura de arrendamiento está lista para pago en línea.\n\n"
            . "📋 *Factura:* {$record->numero}\n"
            . "💰 *Valor:* \$" . number_format($record->saldo_pendiente, 0, ',', '.') . " COP\n"
            . "📅 *Vence:* {$record->fecha_limite_pago->format('d/m/Y')}\n\n"
            . "🔗 *Pagar aquí:*\n{$url}\n\n"
            . "Puede pagar con PSE, Nequi, tarjeta débito/crédito o en nuestra oficina.\n"
            . "— Serviarrendar S.A.S";

        $resultado = app(WhatsAppService::class)->enviar($record->arrendatario->celular, $msg);

        if ($resultado['ok'] ?? false) {
            $record->update(['wap_enviado' => true, 'wap_enviado_at' => now()]);
            Notification::make()->title('✅ Link enviado')->body("Enviado a {$record->arrendatario->celular}")->success()->send();
        } else {
            Notification::make()->title('❌ No se pudo enviar')->body($resultado['error'] ?? 'El servicio de WhatsApp no respondió correctamente.')->danger()->send();
        }
    }

    private static function elegiblesParaEnvioMasivo()
    {
        return RentBill::query()
            ->whereIn('estado', ['pendiente', 'en_mora', 'parcial', 'vencida'])
            ->where('wap_enviado', false)
            ->whereHas('arrendatario', fn ($q) => $q->whereNotNull('celular')->where('celular', '!=', ''))
            ->whereDoesntHave('rentalContract.property.businessOrigin', fn ($q) => $q->where('nombre', 'Victoria'))
            ->with('arrendatario');
    }
}
