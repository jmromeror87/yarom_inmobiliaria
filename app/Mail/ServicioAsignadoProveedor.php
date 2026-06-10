<?php

namespace App\Mail;

use App\Models\PropertyService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ServicioAsignadoProveedor extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public PropertyService $servicio) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Solicitud de servicio — ' . $this->servicio->numero . ' — YarOM Inmobiliaria',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.servicio-asignado-proveedor',
            with: ['servicio' => $this->servicio],
        );
    }
}
