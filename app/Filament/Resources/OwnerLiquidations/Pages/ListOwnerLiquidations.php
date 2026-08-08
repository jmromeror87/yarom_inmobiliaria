<?php
namespace App\Filament\Resources\OwnerLiquidations\Pages;

use App\Filament\Resources\OwnerLiquidations\OwnerLiquidationResource;
use App\Filament\Widgets\LiquidacionesStatsWidget;
use App\Models\Company;
use App\Models\OwnerLiquidation;
use App\Models\Property;
use App\Models\RentBill;
use App\Models\Third;
use App\Services\WhatsAppService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;

class ListOwnerLiquidations extends ListRecords
{
    protected static string $resource = OwnerLiquidationResource::class;

    protected string $view = 'filament.resources.owner-liquidations.pages.list-owner-liquidations';

    public string $search = '';

    #[Url]
    public ?string $filtro = null;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generar_mes')
                ->label('Generar liquidaciones')
                ->icon('heroicon-o-bolt')
                ->color('warning')
                ->extraAttributes([
                    'style' => 'background:linear-gradient(135deg,#d97706,#f59e0b)!important;color:#fff!important;border:none!important;box-shadow:0 4px 14px rgba(217,119,6,.35)!important;font-weight:700!important;',
                ])
                ->modalHeading('Generar liquidaciones por período')
                ->modalDescription('Elige el mes o un rango de meses. Por política de la inmobiliaria, se le gira al propietario haya pagado o no el inquilino ese mes — la única excepción es si el inquilino ya acumula más de 3 meses en mora, ahí no se genera y se notifica para revisión manual.')
                ->schema([
                    Select::make('mes_desde')->label('Mes desde')
                        ->options(array_combine(range(1, 12), ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre']))
                        ->required()->default(now()->month)->native(false),
                    TextInput::make('anio_desde')->label('Año desde')->numeric()->required()->default(now()->year),
                    Select::make('mes_hasta')->label('Mes hasta')
                        ->options(array_combine(range(1, 12), ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre']))
                        ->required()->default(now()->month)->native(false),
                    TextInput::make('anio_hasta')->label('Año hasta')->numeric()->required()->default(now()->year),
                ])
                ->action(function (array $data) {
                    $desde = ((int) $data['anio_desde']) * 100 + (int) $data['mes_desde'];
                    $hasta = ((int) $data['anio_hasta']) * 100 + (int) $data['mes_hasta'];

                    if ($desde > $hasta) {
                        Notification::make()->title('El período "desde" es posterior al "hasta"')->danger()->send();
                        return;
                    }

                    $bills = RentBill::whereRaw('(anio * 100 + mes) >= ?', [$desde])
                        ->whereRaw('(anio * 100 + mes) <= ?', [$hasta])
                        ->whereNotIn('estado', ['anulada'])
                        ->whereNull('owner_liquidation_id')
                        ->get();

                    $n = 0;
                    $bloqueadas = 0;
                    foreach ($bills as $b) {
                        $mesesEnMora = RentBill::where('rental_contract_id', $b->rental_contract_id)
                            ->whereNotIn('estado', ['pagada', 'anulada'])
                            ->count();
                        if ($mesesEnMora > 3) {
                            $bloqueadas++;
                            continue;
                        }
                        if (OwnerLiquidation::generarDesdeFact($b)) $n++;
                    }

                    Notification::make()
                        ->title("{$n} liquidaciones generadas")
                        ->body($bloqueadas > 0 ? "{$bloqueadas} no se generaron por mora mayor a 3 meses — revisar manualmente." : null)
                        ->success()->send();
                }),

            Action::make('reporte_pdf')
                ->label('Reporte PDF del mes')
                ->icon('heroicon-o-document-arrow-down')
                ->color('info')
                ->extraAttributes([
                    'style' => 'background:linear-gradient(135deg,#1e3a8a,#2563eb)!important;color:#fff!important;border:none!important;box-shadow:0 4px 14px rgba(30,58,138,.3)!important;font-weight:700!important;',
                ])
                ->schema([
                    Select::make('mes')->label('Mes')
                        ->options(array_combine(range(1, 12), ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre']))
                        ->required()->default(now()->month)->native(false),
                    TextInput::make('anio')->label('Año')->numeric()->required()->default(now()->year),
                ])
                ->action(function (array $data) {
                    $url = route('liquidacion.reporte.pdf', ['mes' => $data['mes'], 'anio' => $data['anio']]);
                    $this->js("window.open('{$url}', '_blank')");
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [LiquidacionesStatsWidget::class];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 1;
    }

    /**
     * Resuelve propietarios que coinciden con el término de búsqueda por
     * cédula/nombre propio, o indirectamente porque uno de sus inmuebles
     * coincide (dirección o código).
     */
    public function getResultados(): Collection
    {
        $term = trim($this->search);

        if ($term === '') {
            return collect();
        }

        $thirdIdsDirectos = Third::query()
            ->where('es_propietario', true)
            ->where(function ($q) use ($term) {
                $q->where('nombre_completo', 'like', "%{$term}%")
                    ->orWhere('numero_documento', 'like', "%{$term}%");
            })
            ->pluck('id');

        $thirdIdsPorInmueble = Property::query()
            ->where(function ($q) use ($term) {
                $q->where('direccion', 'like', "%{$term}%")
                    ->orWhere('codigo', 'like', "%{$term}%");
            })
            ->pluck('propietario_id');

        $thirdIds = $thirdIdsDirectos->merge($thirdIdsPorInmueble)->filter()->unique();

        if ($thirdIds->isEmpty()) {
            return collect();
        }

        return Third::query()
            ->whereIn('id', $thirdIds)
            ->with(['properties' => function ($q) {
                $q->with(['businessOrigin', 'ownerLiquidations' => function ($q2) {
                    $q2->orderByDesc('anio')->orderByDesc('mes');
                }]);
            }])
            ->orderBy('nombre_completo')
            ->limit(25)
            ->get();
    }

    /**
     * Liquidaciones detrás de cada KPI del banner (mismo criterio exacto
     * que LiquidacionesStatsWidget), para ver en vivo qué compone ese número.
     */
    public function getResultadosPorFiltro(): Collection
    {
        $mes = now()->month;
        $anio = now()->year;

        $query = match ($this->filtro) {
            'pendiente' => OwnerLiquidation::where('estado', 'pendiente'),
            'aprobada'  => OwnerLiquidation::where('estado', 'aprobada'),
            'pagada'    => OwnerLiquidation::where('estado', 'pagada')->where('mes', $mes)->where('anio', $anio),
            'anulada'   => OwnerLiquidation::where('estado', 'anulada'),
            default     => null,
        };

        if (! $query) {
            return collect();
        }

        return $query
            ->with(['propietario', 'property'])
            ->orderByDesc('total_giro')
            ->limit(100)
            ->get();
    }

    public function getFiltroLabel(): ?string
    {
        return match ($this->filtro) {
            'pendiente' => 'Pendientes',
            'aprobada'  => 'Aprobadas',
            'pagada'    => 'Pagadas — ' . ucfirst(now()->translatedFormat('F Y')),
            'anulada'   => 'Anuladas',
            default     => null,
        };
    }

    public function aprobarAction(): Action
    {
        return Action::make('aprobar')
            ->label('Aprobar')
            ->icon('heroicon-o-check-badge')
            ->color('info')
            ->requiresConfirmation()
            ->modalHeading('¿Aprobar esta liquidación?')
            ->action(function (array $arguments) {
                $liq = self::liquidacionDeArgumentos($arguments);
                if (! $liq) return;
                $liq->update(['estado' => 'aprobada']);
                Notification::make()->title('Liquidación aprobada')->success()->send();
            });
    }

    public function enviarWapLiquidacionAction(): Action
    {
        return Action::make('enviarWapLiquidacion')
            ->label('Enviar WhatsApp')
            ->icon('heroicon-o-chat-bubble-left-ellipsis')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Enviar liquidación por WhatsApp')
            ->modalDescription(function (array $arguments) {
                $liq = self::liquidacionDeArgumentos($arguments);
                return $liq ? "Se enviará el detalle de {$liq->numero} a {$liq->propietario?->nombre_completo} al número {$liq->propietario?->celular}." : '';
            })
            ->action(function (array $arguments) {
                $r = self::liquidacionDeArgumentos($arguments);
                if (! $r) return;

                $celular = $r->propietario?->celular;
                if (! $celular) {
                    Notification::make()->title('El propietario no tiene celular registrado')->danger()->send();
                    return;
                }

                $company = Company::first();
                $empresa = $company?->razon_social ?? 'Serviarrendar S.A.S';
                $meses = [1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'];
                $periodo = ($meses[$r->mes] ?? $r->mes) . ' ' . $r->anio;

                $msg = "🏠 *Liquidación de Arrendamiento*\n\n"
                    . "Estimado(a) {$r->propietario->nombre_completo},\n\n"
                    . "📋 *{$r->numero}* — Período: {$periodo}\n"
                    . "🏠 Inmueble: {$r->property?->codigo} — {$r->property?->direccion}\n\n"
                    . "💰 Canon cobrado: \$" . number_format($r->canon_cobrado, 0, ',', '.') . " COP\n"
                    . "📉 Comisión adm. ({$r->comision_porcentaje}%): -\$" . number_format($r->comision_valor, 0, ',', '.') . " COP\n"
                    . "📉 IVA comisión: -\$" . number_format($r->iva_comision, 0, ',', '.') . " COP\n"
                    . ($r->retefuente_valor > 0 ? "📉 Retefuente: -\$" . number_format($r->retefuente_valor, 0, ',', '.') . " COP\n" : '')
                    . "💵 *Total a girar: \$" . number_format($r->total_giro, 0, ',', '.') . " COP*\n\n"
                    . ($r->estado === 'pagada' && $r->fecha_giro
                        ? "✅ Giro realizado el {$r->fecha_giro->format('d/m/Y')} — {$r->forma_giro}\n\n"
                        : "⏳ Pendiente de giro.\n\n")
                    . "— {$empresa}\n☎️ " . ($company?->celular ?? '318 693 4710');

                $resultado = app(WhatsAppService::class)->enviar($celular, $msg);

                if ($resultado['ok'] ?? false) {
                    $r->update(['wap_enviado' => true, 'wap_enviado_at' => now()]);
                    Notification::make()->title('✅ Enviado por WhatsApp')->body("Enviado a {$celular}")->success()->send();
                } else {
                    Notification::make()->title('❌ No se pudo enviar')->body($resultado['error'] ?? 'El servicio de WhatsApp no respondió correctamente.')->danger()->send();
                }
            });
    }

    private static function liquidacionDeArgumentos(array $arguments): ?OwnerLiquidation
    {
        return isset($arguments['record'])
            ? OwnerLiquidation::with('propietario', 'property')->find($arguments['record'])
            : null;
    }
}
