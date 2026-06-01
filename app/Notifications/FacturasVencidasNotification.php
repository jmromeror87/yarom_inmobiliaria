<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class FacturasVencidasNotification extends Notification
{
    public function __construct(
        private int $totalFacturas,
        private float $totalCartera,
        private int $diasMaxMora,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'format'    => 'filament',
            'title'     => "⚠️ {$this->totalFacturas} " . ($this->totalFacturas === 1 ? 'factura en mora' : 'facturas en mora'),
            'body'      => "Cartera vencida: \$" . number_format($this->totalCartera, 0, ',', '.') . " COP · Mora máxima: {$this->diasMaxMora} días",
            'icon'      => 'heroicon-o-exclamation-triangle',
            'iconColor' => 'warning',
            'actions'   => [[
                'name'    => 'ver_facturas',
                'label'   => 'Ver facturas',
                'url'     => '/admin/facturacion',
                'shouldOpenUrlInNewTab' => false,
            ]],
            'tipo'      => 'facturas_mora',
        ];
    }
}
