<?php

namespace App\Filament\Widgets;

use App\Models\RentBill;
use Filament\Widgets\ChartWidget;

class CarteraPorEdadWidget extends ChartWidget
{
    protected ?string $heading    = 'Cartera por Edades de Mora';
    protected static ?int $sort   = 3;
    protected int|string|array $columnSpan = ['default' => 12, 'lg' => 7];
    public static function canView(): bool { return false; }
    protected ?string $maxHeight  = '280px';

    protected function getData(): array
    {
        $hoy = now()->toDateString();

        // Facturas con saldo pendiente > 0
        $bills = RentBill::whereIn('estado', ['pendiente', 'parcial', 'en_mora', 'vencida'])
            ->where('saldo_pendiente', '>', 0)
            ->get(['saldo_pendiente', 'fecha_limite_pago']);

        $rangos = [
            'Al día (0)'     => 0,
            '1–30 días'      => 0,
            '31–60 días'     => 0,
            '61–90 días'     => 0,
            '91–180 días'    => 0,
            'Más de 180 días'=> 0,
        ];

        foreach ($bills as $bill) {
            $dias  = (int) now()->startOfDay()->diffInDays($bill->fecha_limite_pago->startOfDay(), false);
            $saldo = (float) $bill->saldo_pendiente;

            if ($dias >= 0)         $rangos['Al día (0)']      += $saldo;
            elseif ($dias >= -30)   $rangos['1–30 días']        += $saldo;
            elseif ($dias >= -60)   $rangos['31–60 días']       += $saldo;
            elseif ($dias >= -90)   $rangos['61–90 días']       += $saldo;
            elseif ($dias >= -180)  $rangos['91–180 días']      += $saldo;
            else                    $rangos['Más de 180 días']  += $saldo;
        }

        return [
            'datasets' => [[
                'label'           => 'Saldo pendiente ($)',
                'data'            => array_values($rangos),
                'backgroundColor' => [
                    'rgba(34,197,94,0.8)',   // verde - al día
                    'rgba(234,179,8,0.8)',   // amarillo - 1-30
                    'rgba(249,115,22,0.8)',  // naranja - 31-60
                    'rgba(239,68,68,0.8)',   // rojo - 61-90
                    'rgba(185,28,28,0.85)',  // rojo oscuro - 91-180
                    'rgba(127,29,29,0.9)',   // rojo muy oscuro - >180
                ],
                'borderColor'     => 'transparent',
                'borderRadius'    => 6,
            ]],
            'labels' => array_keys($rangos),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'plugins'   => [
                'legend' => ['display' => false],
                'tooltip' => [
                    'callbacks' => [
                        'label' => "function(c){ return ' $' + c.raw.toLocaleString('es-CO',{minimumFractionDigits:0}); }",
                    ],
                ],
            ],
            'scales' => [
                'x' => [
                    'beginAtZero' => true,
                    'ticks'       => [
                        'callback' => "function(v){ return '$' + (v/1000000).toFixed(1) + 'M'; }",
                        'font'     => ['size' => 10],
                    ],
                ],
                'y' => ['ticks' => ['font' => ['size' => 11]]],
            ],
        ];
    }
}
