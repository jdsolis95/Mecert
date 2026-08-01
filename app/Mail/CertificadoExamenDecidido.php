<?php

namespace App\Mail;

use App\Models\CertificadoExamen;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CertificadoExamenDecidido extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public CertificadoExamen $examen,
    ) {}

    public function envelope(): Envelope
    {
        $asunto = $this->examen->estado === 'aprobado'
            ? 'Examen de renovación aprobado - MeCert'
            : 'Examen de renovación rechazado - MeCert';

        return new Envelope(subject: $asunto);
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.certificado-examen-decidido',
            with: [
                'examen' => $this->examen,
            ],
        );
    }
}
