<?php

namespace App\Filament\Widgets;

use App\Models\CuentaPorCobrar;
use Filament\Widgets\ChartWidget;

class CarteraPorEdadWidget extends ChartWidget
{
    protected ?string $heading     = 'Cartera por Antigüedad de Mora';
    protected ?string $description = 'Distribución del saldo pendiente según los días de retraso — verde = saludable, rojo = crítico';
    protected static ?int $sort    = 3;
    protected int|string|array $columnSpan = ['default' => 12, 'lg' => 12];
    public static function canView(): bool { return true; }
    protected ?string $maxHeight   = '300px';

    protected function getData(): array
    {
        $cuentas = CuentaPorCobrar::whereIn('estado', ['pendiente', 'parcial'])
            ->where('saldo', '>', 0)
            ->get(['saldo', 'fecha_vencimiento']);

        $rangos = [0, 0, 0, 0, 0, 0];

        foreach ($cuentas as $c) {
            if (!$c->fecha_vencimiento) continue;
            $dias  = (int) now()->startOfDay()->diffInDays($c->fecha_vencimiento->startOfDay(), false);
            $saldo = (float) $c->saldo;

            if ($dias >= 0)        $rangos[0] += $saldo;
            elseif ($dias >= -30)  $rangos[1] += $saldo;
            elseif ($dias >= -60)  $rangos[2] += $saldo;
            elseif ($dias >= -90)  $rangos[3] += $saldo;
            elseif ($dias >= -180) $rangos[4] += $saldo;
            else                   $rangos[5] += $saldo;
        }

        return [
            'labels' => [
                ['Al día', '(0 días)'],
                ['1 – 30', 'días'],
                ['31 – 60', 'días'],
                ['61 – 90', 'días'],
                ['91 – 180', 'días'],
                ['Más de', '180 días'],
            ],
            'datasets' => [[
                'label'               => 'Saldo pendiente',
                'data'                => $rangos,
                'backgroundColor'     => [
                    'rgba(34,197,94,0.80)',
                    'rgba(234,179,8,0.80)',
                    'rgba(249,115,22,0.80)',
                    'rgba(239,68,68,0.80)',
                    'rgba(185,28,28,0.85)',
                    'rgba(127,29,29,0.90)',
                ],
                'borderColor'         => [
                    'rgb(22,163,74)',
                    'rgb(202,138,4)',
                    'rgb(234,88,12)',
                    'rgb(220,38,38)',
                    'rgb(153,27,27)',
                    'rgb(100,20,20)',
                ],
                'borderWidth'         => 2,
                'borderRadius'        => 12,
                'borderSkipped'       => false,
                'hoverBorderWidth'    => 0,
            ]],
        ];
    }

    protected function getType(): string { return 'bar'; }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['display' => false],
                'tooltip' => [
                    'callbacks' => [
                        'label' => "function(c){
                            var v = c.raw;
                            var fmt = new Intl.NumberFormat('es-CO',{style:'currency',currency:'COP',minimumFractionDigits:0,maximumFractionDigits:0});
                            return '  Saldo: ' + fmt.format(v);
                        }",
                    ],
                    'backgroundColor' => 'rgba(15,23,42,0.92)',
                    'titleColor'      => '#f8fafc',
                    'bodyColor'       => '#94a3b8',
                    'padding'         => 14,
                    'cornerRadius'    => 10,
                    'titleFont'       => ['size' => 13, 'weight' => '700'],
                    'bodyFont'        => ['size' => 12],
                ],
            ],
            'scales' => [
                'x' => [
                    'grid'   => ['display' => false],
                    'border' => ['display' => false],
                    'ticks'  => [
                        'font'  => ['size' => 11, 'weight' => '600'],
                        'color' => '#64748b',
                    ],
                ],
                'y' => [
                    'beginAtZero' => true,
                    'grid'   => ['color' => 'rgba(148,163,184,0.1)', 'drawBorder' => false],
                    'border' => ['display' => false],
                    'ticks'  => [
                        'callback'     => "function(v){ if(v>=1000000) return '$'+(v/1000000).toFixed(1)+'M'; if(v>=1000) return '$'+(v/1000).toFixed(0)+'K'; return v===0?'$0':('$'+v); }",
                        'font'         => ['size' => 10],
                        'color'        => '#94a3b8',
                        'maxTicksLimit'=> 5,
                    ],
                ],
            ],
            'responsive'          => true,
            'maintainAspectRatio' => false,
            'animation'           => ['duration' => 700, 'easing' => 'easeOutQuart'],
            'layout'              => ['padding' => ['top' => 8, 'bottom' => 0, 'left' => 4, 'right' => 4]],
        ];
    }
}
