<?php

namespace App\Filament\Widgets;

use App\Models\CuentaPorCobrar;
use App\Models\RentBill;
use Filament\Widgets\ChartWidget;

class CarteraPorEdadWidget extends ChartWidget
{
    protected ?string $heading     = 'Cartera por Antigüedad de Mora';
    protected ?string $description = 'Saldo pendiente por días de retraso — heredado de Siinmob vs. facturación del sistema nuevo';
    protected static ?int $sort    = 3;
    protected int|string|array $columnSpan = ['default' => 12, 'lg' => 12];
    public static function canView(): bool { return true; }
    protected ?string $maxHeight   = '320px';

    protected function getData(): array
    {
        $rangosHeredado = [0, 0, 0, 0, 0, 0];
        $rangosActivo   = [0, 0, 0, 0, 0, 0];

        $bucket = function (int $dias) {
            if ($dias >= 0)        return 0;
            if ($dias >= -30)      return 1;
            if ($dias >= -60)      return 2;
            if ($dias >= -90)      return 3;
            if ($dias >= -180)     return 4;
            return 5;
        };

        $heredadas = CuentaPorCobrar::whereIn('estado', ['pendiente', 'parcial'])
            ->where('saldo', '>', 0)->get(['saldo', 'fecha_vencimiento']);
        foreach ($heredadas as $c) {
            if (!$c->fecha_vencimiento) continue;
            $dias = (int) now()->startOfDay()->diffInDays($c->fecha_vencimiento->startOfDay(), false);
            $rangosHeredado[$bucket($dias)] += (float) $c->saldo;
        }

        $activas = RentBill::whereIn('estado', ['pendiente', 'parcial', 'en_mora', 'vencida'])
            ->where('saldo_pendiente', '>', 0)->get(['saldo_pendiente', 'fecha_limite_pago']);
        foreach ($activas as $b) {
            if (!$b->fecha_limite_pago) continue;
            $dias = (int) now()->startOfDay()->diffInDays($b->fecha_limite_pago->startOfDay(), false);
            $rangosActivo[$bucket($dias)] += (float) $b->saldo_pendiente;
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
            'datasets' => [
                [
                    'label'           => 'Cartera activa (sistema nuevo)',
                    'data'            => $rangosActivo,
                    'backgroundColor' => 'rgba(37,99,235,0.85)',
                    'borderColor'     => 'rgb(29,78,216)',
                    'borderWidth'     => 0,
                    'borderRadius'    => 8,
                    'stack'           => 'cartera',
                ],
                [
                    'label'           => 'Heredado Siinmob',
                    'data'            => $rangosHeredado,
                    'backgroundColor' => 'rgba(124,58,237,0.85)',
                    'borderColor'     => 'rgb(109,40,217)',
                    'borderWidth'     => 0,
                    'borderRadius'    => 8,
                    'stack'           => 'cartera',
                ],
            ],
        ];
    }

    protected function getType(): string { return 'bar'; }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display'  => true,
                    'position' => 'bottom',
                    'labels'   => [
                        'usePointStyle' => true,
                        'pointStyle'    => 'circle',
                        'padding'       => 18,
                        'font'          => ['size' => 11.5, 'weight' => '600'],
                        'color'         => '#475569',
                    ],
                ],
                'tooltip' => [
                    'mode'      => 'index',
                    'intersect' => false,
                    'callbacks' => [
                        'label' => "function(c){
                            var v = c.raw;
                            var fmt = new Intl.NumberFormat('es-CO',{style:'currency',currency:'COP',minimumFractionDigits:0,maximumFractionDigits:0});
                            return '  ' + c.dataset.label + ': ' + fmt.format(v);
                        }",
                        'footer' => "function(items){
                            var fmt = new Intl.NumberFormat('es-CO',{style:'currency',currency:'COP',minimumFractionDigits:0,maximumFractionDigits:0});
                            var total = items.reduce(function(s,i){ return s + i.raw; }, 0);
                            return 'Total: ' + fmt.format(total);
                        }",
                    ],
                    'backgroundColor' => 'rgba(15,23,42,0.94)',
                    'titleColor'      => '#f8fafc',
                    'bodyColor'       => '#cbd5e1',
                    'footerColor'     => '#f8fafc',
                    'footerFont'      => ['weight' => '700'],
                    'padding'         => 14,
                    'cornerRadius'    => 10,
                    'titleFont'       => ['size' => 13, 'weight' => '700'],
                    'bodyFont'        => ['size' => 12],
                ],
            ],
            'scales' => [
                'x' => [
                    'stacked' => true,
                    'grid'    => ['display' => false],
                    'border'  => ['display' => false],
                    'ticks'   => [
                        'font'  => ['size' => 11, 'weight' => '600'],
                        'color' => '#64748b',
                    ],
                ],
                'y' => [
                    'stacked'     => true,
                    'beginAtZero' => true,
                    'grid'        => ['color' => 'rgba(148,163,184,0.12)', 'drawBorder' => false],
                    'border'      => ['display' => false],
                    'ticks'       => [
                        'callback'      => "function(v){ if(v>=1000000) return '$'+(v/1000000).toFixed(1)+'M'; if(v>=1000) return '$'+(v/1000).toFixed(0)+'K'; return v===0?'$0':('$'+v); }",
                        'font'          => ['size' => 10],
                        'color'         => '#94a3b8',
                        'maxTicksLimit' => 5,
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
