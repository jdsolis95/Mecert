<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class PasswordReset extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $usuario,
        public string $temporaryPassword
    ) {}

    // Asunto fijo, se manda cuando un admin resetea la contraseña de un usuario
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reset de Contraseña - MeCert',
        );
    }

    // Vista con la contraseña temporal generada
    public function content(): Content
    {
        return new Content(
            view: 'mail.password-reset',
            with: [
                'usuario' => $this->usuario,
                'temporaryPassword' => $this->temporaryPassword,
            ],
        );
    }
}
