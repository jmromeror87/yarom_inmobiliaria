<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class ContratosVencimientoNotification extends Notification
{
    public function __construct(
        private array $resumen, // ['5' => 2, '15' => 1, '30' => 4, '60' => 3]
        private int   $total,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $detalle  = collect($this->resumen)->sortKeys()->map(fn ($c, $d) => "{$c} vencen en {$d}d")->implode(' · ');
        $urgentes = ($this->resumen['5'] ?? 0) + ($this->resumen['15'] ?? 0);
        $icono    = $urgentes > 0 ? '🔴' : '🟡';

        return [
            'format'    => 'filament',
            'title'     => "{$icono} {$this->total} " . ($this->total === 1 ? 'contrato próximo a vencer' : 'contratos próximos a vencer'),
            'body'      => $detalle,
            'icon'      => 'heroicon-o-clock',
            'iconColor' => $urgentes > 0 ? 'danger' : 'warning',
            'actions'   => [[
                'name'                   => 'ver_contratos',
                'label'                  => 'Ver contratos',
                'url'                    => '/admin/contratos-arriendo',
                'shouldOpenUrlInNewTab'  => false,
            ]],
            'tipo'      => 'contratos_vencimiento',
        ];
    }
}
