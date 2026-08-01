<?php

namespace App\Mail;

use App\Models\CertificadoExamen;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CertificadoExamenPropuesto extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public CertificadoExamen $examen,
    ) {}

    // Asunto fijo, se manda apenas queda registrada la propuesta
    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Examen de renovación propuesto - MeCert');
    }

    // Vista con los datos de la fecha/lugar propuesto
    public function content(): Content
    {
        return new Content(
            view: 'mail.certificado-examen-propuesto',
            with: [
                'examen' => $this->examen,
            ],
        );
    }
}
