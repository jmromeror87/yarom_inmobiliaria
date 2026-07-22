<?php

namespace App\Filament\Pages;

use App\Models\RentBill;
use Carbon\Carbon;
use Filament\Pages\Page;

class CalendarioPagos extends Page
{
    protected string $view = 'filament.pages.calendario-pagos';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string { return 'Calendario de Pagos'; }
    public static function getNavigationGroup(): ?string { return 'Cobros'; }
    public function getTitle(): string { return 'Calendario de Pagos'; }

    public int $mes;
    public int $anio;
    public array $dias = [];

    public function mount(): void
    {
        $this->mes  = (int) (request()->query('mes') ?: now()->month);
        $this->anio = (int) (request()->query('anio') ?: now()->year);
        $this->cargar();
    }

    public function mesAnterior(): void
    {
        $fecha = Carbon::create($this->anio, $this->mes, 1)->subMonth();
        $this->mes = $fecha->month;
        $this->anio = $fecha->year;
        $this->cargar();
    }

    public function mesSiguiente(): void
    {
        $fecha = Carbon::create($this->anio, $this->mes, 1)->addMonth();
        $this->mes = $fecha->month;
        $this->anio = $fecha->year;
        $this->cargar();
    }

    public function irHoy(): void
    {
        $this->mes = now()->month;
        $this->anio = now()->year;
        $this->cargar();
    }

    private function cargar(): void
    {
        $periodoBase = Carbon::create($this->anio, $this->mes, 1);
        $inicio = $periodoBase->copy()->startOfMonth();
        $fin    = $periodoBase->copy()->endOfMonth();

        $bills = RentBill::whereBetween('fecha_limite_pago', [$inicio->toDateString(), $fin->toDateString()])
            ->with(['rentalContract.arrendatario', 'property'])
            ->get()
            ->groupBy(fn (RentBill $b) => $b->fecha_limite_pago->day);

        $dias = [];
        $primerDiaSemana = $inicio->copy()->dayOfWeek; // 0=domingo
        for ($i = 0; $i < $primerDiaSemana; $i++) {
            $dias[] = null;
        }

        $hoy = now()->startOfDay();

        for ($d = 1; $d <= $fin->day; $d++) {
            $delDia = $bills->get($d, collect());
            $pagadas = $delDia->where('estado', 'pagada')->count();
            $total   = $delDia->count();

            // Las que faltan por pagar: distinguir si siguen dentro de los
            // días de gracia del contrato (aún no es urgente) o si ya se
            // pasó la gracia (esas sí son las realmente atrasadas).
            $pendientes  = $delDia->where('estado', '!=', 'pagada');
            $finDeGracia = $pendientes->every(function (RentBill $b) use ($hoy) {
                $finGracia = $b->fecha_limite_pago->copy()->addDays($b->dias_gracia)->startOfDay();
                return $hoy->lte($finGracia);
            });
            $enGracia = $total > 0 && $pagadas < $total && $finDeGracia;

            $dias[] = [
                'dia'      => $d,
                'esHoy'    => $periodoBase->copy()->day($d)->isSameDay(now()),
                'total'    => $total,
                'pagadas'  => $pagadas,
                'enGracia' => $enGracia,
                'facturas' => $delDia->map(function (RentBill $b) use ($hoy) {
                    $finGracia = $b->fecha_limite_pago->copy()->addDays($b->dias_gracia)->startOfDay();
                    return [
                        'id'           => $b->id,
                        'numero'       => $b->numero,
                        'arrendatario' => $b->rentalContract?->arrendatario?->nombre_completo ?? $b->arrendatario?->nombre_completo ?? '—',
                        'inmueble'     => $b->property?->codigo ?? '—',
                        'total_factura'=> (float) $b->total_factura,
                        'estado'       => $b->estado,
                        'en_gracia'    => $b->estado !== 'pagada' && $hoy->lte($finGracia),
                        'fin_gracia'   => $finGracia->toDateString(),
                    ];
                })->values()->toArray(),
            ];
        }

        $this->dias = $dias;
    }

    public function getMesLabelProperty(): string
    {
        return ucfirst(Carbon::create($this->anio, $this->mes, 1)->locale('es')->isoFormat('MMMM YYYY'));
    }
}
