<?php

namespace App\Filament\Widgets;

use App\Models\Property;
use Filament\Widgets\ChartWidget;

class PropiedadesEstadoWidget extends ChartWidget
{
    protected ?string $heading = 'Propiedades por Estado';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 4;
    protected ?string $maxHeight = '280px';

    protected function getData(): array
    {
        $estados = [
            'disponible'           => ['label' => 'Disponible',       'color' => '#22c55e'],
            'arrendado'            => ['label' => 'Arrendado',        'color' => '#2563EB'],
            'en_captacion'         => ['label' => 'En Captación',     'color' => '#f59e0b'],
            'documentos_pendientes'=> ['label' => 'Docs. Pendientes', 'color' => '#f97316'],
            'en_venta'             => ['label' => 'En Venta',         'color' => '#8b5cf6'],
            'en_mantenimiento'     => ['label' => 'Mantenimiento',    'color' => '#64748b'],
            'inactivo'             => ['label' => 'Inactivo',         'color' => '#e11d48'],
        ];

        $data   = [];
        $labels = [];
        $colors = [];

        foreach ($estados as $key => $info) {
            $count = Property::whereNull('deleted_at')->where('estado', $key)->count();
            if ($count > 0) {
                $data[]   = $count;
                $labels[] = $info['label'];
                $colors[] = $info['color'];
            }
        }

        return [
            'datasets' => [[
                'data'                 => $data,
                'backgroundColor'      => $colors,
                'borderColor'          => '#ffffff',
                'borderWidth'          => 3,
                'hoverOffset'          => 6,
            ]],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                    'labels'   => ['padding' => 12, 'font' => ['size' => 11]],
                ],
            ],
            'cutout' => '65%',
        ];
    }
}