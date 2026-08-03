<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class GiroPropietarioBloqueadoNotification extends Notification
{
    public function __construct(
        private string $propietario,
        private string $inmueble,
        private int    $mesesEnMora,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'format'    => 'filament',
            'title'     => "⚠️ Giro NO generado — {$this->propietario}",
            'body'      => "El inquilino de {$this->inmueble} debe {$this->mesesEnMora} meses. El sistema no giró automáticamente por mora considerable — requiere revisión manual.",
            'icon'      => 'heroicon-o-exclamation-triangle',
            'iconColor' => 'danger',
            'actions'   => [[
                'name'                  => 'ver_liquidaciones',
                'label'                 => 'Revisar',
                'url'                   => '/admin/owner-liquidations',
                'shouldOpenUrlInNewTab' => false,
            ]],
            'tipo'      => 'giro_bloqueado_mora',
        ];
    }
}
