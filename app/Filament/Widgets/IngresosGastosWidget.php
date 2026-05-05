<?php

namespace App\Filament\Widgets;

use App\Models\AdministrationContract;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class IngresosGastosWidget extends ChartWidget
{
    protected ?string $heading = 'Ingresos vs Recaudos — Canon Administrado por Mes';
    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = 8;
    protected ?string $maxHeight = '300px';

    

    public ?array $filters = null;

    protected function getData(): array
    {
        // Hasta que exista el módulo de pagos, mostramos el canon
        // pactado por mes como referencia del ingreso esperado.
        $meses  = [];
        $esperados = [];
        $comisiones = [];

        for ($i = 5; $i >= 0; $i--) {
            $mes = now()->subMonths($i);
            $meses[] = $mes->isoFormat('MMM YY');

            // Canon total de contratos activos en ese mes
            $canon = AdministrationContract::whereNull('deleted_at')
                ->where('estado', 'activo')
                ->where('fecha_inicio', '<=', $mes->endOfMonth())
                ->where('fecha_fin', '>=', $mes->startOfMonth())
                ->sum('canon_pactado');

            $esperados[]   = round($canon, 0);
            $comisiones[]  = round($canon * 0.10, 0); // 10% comisión
        }

        return [
            'datasets' => [
                [
                    'label'           => 'Canon Esperado ($)',
                    'data'            => $esperados,
                    'borderColor'     => '#2563EB',
                    'backgroundColor' => 'rgba(37,99,235,0.08)',
                    'fill'            => true,
                    'tension'         => 0.4,
                    'pointBackgroundColor' => '#2563EB',
                    'pointRadius'     => 5,
                ],
                [
                    'label'           => 'Comisión Proyectada ($)',
                    'data'            => $comisiones,
                    'borderColor'     => '#22c55e',
                    'backgroundColor' => 'rgba(34,197,94,0.08)',
                    'fill'            => true,
                    'tension'         => 0.4,
                    'pointBackgroundColor' => '#22c55e',
                    'pointRadius'     => 5,
                    'borderDash'      => [5, 5],
                ],
            ],
            'labels' => $meses,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'top',
                    'labels'   => ['padding' => 16, 'font' => ['size' => 11]],
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks'       => [
                        'callback' => "function(v){ return '$ ' + v.toLocaleString('es-CO'); }",
                    ],
                ],
            ],
        ];
    }
}