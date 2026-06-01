<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class LiquidacionesPendientesNotification extends Notification
{
    public function __construct(
        private int   $totalPendientes,
        private float $totalValor,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'format'    => 'filament',
            'title'     => "💰 {$this->totalPendientes} " . ($this->totalPendientes === 1 ? 'liquidación pendiente de aprobar' : 'liquidaciones pendientes de aprobar'),
            'body'      => "Total a girar: \$" . number_format($this->totalValor, 0, ',', '.') . " COP",
            'icon'      => 'heroicon-o-banknotes',
            'iconColor' => 'info',
            'actions'   => [[
                'name'                  => 'ver_liquidaciones',
                'label'                 => 'Ver liquidaciones',
                'url'                   => '/admin/owner-liquidations',
                'shouldOpenUrlInNewTab' => false,
            ]],
            'tipo'      => 'liquidaciones_pendientes',
        ];
    }
}
