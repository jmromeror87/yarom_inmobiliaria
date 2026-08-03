<?php

namespace App\Filament\Pages;

use App\Models\AdministrationContract;
use App\Models\OwnerLiquidation;
use App\Models\RentalContract;
use Carbon\Carbon;
use Filament\Pages\Page;

class CalendarioLiquidaciones extends Page
{
    protected string $view = 'filament.pages.calendario-liquidaciones';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';
    protected static ?int $navigationSort = 5;

    public static function getNavigationLabel(): string { return 'Calendario de Liquidaciones'; }
    public static function getNavigationGroup(): ?string { return 'Cobros'; }
    public function getTitle(): string { return 'Calendario de Liquidaciones'; }

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

    public function cargar(): void
    {
        $periodoBase = Carbon::create($this->anio, $this->mes, 1);
        $fin = $periodoBase->copy()->endOfMonth();

        $contratos = AdministrationContract::whereNotNull('dia_giro')
            ->where('estado', 'activo')
            ->with(['propietario', 'property'])
            ->get()
            ->groupBy(fn (AdministrationContract $c) => min((int) $c->dia_giro, $fin->day));

        $rentalPorAdminId = RentalContract::whereIn('administration_contract_id', $contratos->flatten()->pluck('id'))
            ->where('estado', 'activo')
            ->get()
            ->keyBy('administration_contract_id');

        $dias = [];
        $primerDiaSemana = $periodoBase->copy()->dayOfWeek;
        for ($i = 0; $i < $primerDiaSemana; $i++) {
            $dias[] = null;
        }

        for ($d = 1; $d <= $fin->day; $d++) {
            $delDia = $contratos->get($d, collect());
            $total = $delDia->count();
            $girados = 0;

            $items = $delDia->map(function (AdministrationContract $c) use (&$girados, $rentalPorAdminId) {
                $rc = $rentalPorAdminId->get($c->id);
                $liq = $rc ? OwnerLiquidation::where('rental_contract_id', $rc->id)
                    ->where('mes', $this->mes)->where('anio', $this->anio)->first() : null;

                $estado = $liq?->estado === 'pagada' ? 'girada' : 'pendiente';
                if ($estado === 'girada') $girados++;

                $canonBruto = (float) ($rc?->canon_mensual ?? $c->canon_pactado ?? 0);
                $netoEstimado = $liq
                    ? (float) $liq->total_giro
                    : round($canonBruto * (1 - ((float) ($c->comision_porcentaje ?? 10) / 100)), 2);

                return [
                    'propietario' => $c->propietario?->nombre_completo ?? '—',
                    'inmueble' => $c->property?->codigo ?? '—',
                    'direccion' => $c->property?->direccion ?? '—',
                    'canon' => $netoEstimado,
                    'estado' => $estado,
                    'fecha_giro' => $liq?->fecha_giro?->toDateString(),
                    'liquidation_id' => $liq?->id,
                ];
            })->values()->toArray();

            $dias[] = [
                'dia' => $d,
                'esHoy' => $periodoBase->copy()->day($d)->isSameDay(now()),
                'total' => $total,
                'girados' => $girados,
                'items' => $items,
            ];
        }

        $this->dias = $dias;
    }

    public function getMesLabelProperty(): string
    {
        return ucfirst(Carbon::create($this->anio, $this->mes, 1)->locale('es')->isoFormat('MMMM YYYY'));
    }
}
