<?php

namespace App\Filament\Widgets;

use App\Models\RentBill;
use App\Models\RentPayment;
use Filament\Widgets\ChartWidget;

class IngresosGastosWidget extends ChartWidget
{
    protected ?string $heading   = 'Facturado vs Recaudado — Últimos 12 meses';
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = ['default' => 12, 'lg' => 6];
    protected ?string $maxHeight = '320px';

    protected function getData(): array
    {
        $meses      = [];
        $facturado  = [];
        $recaudado  = [];
        $mora       = [];

        for ($i = 11; $i >= 0; $i--) {
            $d = now()->subMonths($i);
            $meses[]     = $d->isoFormat('MMM YY');
            $facturado[] = (float) RentBill::where('mes', $d->month)
                                ->where('anio', $d->year)->sum('total_factura');
            $recaudado[] = (float) RentPayment::whereYear('fecha_pago', $d->year)
                                ->whereMonth('fecha_pago', $d->month)->sum('total_pagado');
            $mora[]      = (float) RentBill::where('estado', 'en_mora')
                                ->where('mes', $d->month)->where('anio', $d->year)->sum('mora_acumulada');
        }

        return [
            'datasets' => [
                [
                    'label'           => 'Facturado',
                    'data'            => $facturado,
                    'borderColor'     => '#2563EB',
                    'backgroundColor' => 'rgba(37,99,235,0.08)',
                    'fill'            => true,
                    'tension'         => 0.4,
                    'pointBackgroundColor' => '#2563EB',
                    'pointRadius'     => 4,
                    'borderWidth'     => 2,
                ],
                [
                    'label'           => 'Recaudado',
                    'data'            => $recaudado,
                    'borderColor'     => '#22c55e',
                    'backgroundColor' => 'rgba(34,197,94,0.08)',
                    'fill'            => true,
                    'tension'         => 0.4,
                    'pointBackgroundColor' => '#22c55e',
                    'pointRadius'     => 4,
                    'borderWidth'     => 2,
                ],
                [
                    'label'           => 'Mora acumulada',
                    'data'            => $mora,
                    'borderColor'     => '#ef4444',
                    'backgroundColor' => 'rgba(239,68,68,0)',
                    'fill'            => false,
                    'tension'         => 0.4,
                    'pointBackgroundColor' => '#ef4444',
                    'pointRadius'     => 4,
                    'borderWidth'     => 2,
                    'borderDash'      => [4, 4],
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
                'tooltip' => [
                    'callbacks' => [
                        'label' => "function(c){ return ' $' + c.raw.toLocaleString('es-CO',{minimumFractionDigits:0}); }",
                    ],
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks'       => [
                        'callback' => "function(v){ return '$' + (v/1000000).toFixed(1) + 'M'; }",
                        'font'     => ['size' => 10],
                    ],
                ],
                'x' => ['ticks' => ['font' => ['size' => 10]]],
            ],
        ];
    }
}
