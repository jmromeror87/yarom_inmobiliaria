<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvitacionUsuario extends Mailable
{
    use Queueable, SerializesModels;

    public string $urlActivacion;
    public string $rolNombre;

    public function __construct(public User $usuario, string $token)
    {
        $this->urlActivacion = url("/invitacion/{$token}");
        $this->rolNombre     = $usuario->roles->first()?->name ?? 'usuario';
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Invitación al sistema YarOM — Activa tu cuenta',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invitacion-usuario',
        );
    }
}
