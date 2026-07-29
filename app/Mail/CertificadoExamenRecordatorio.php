<?php

namespace App\Mail;

use App\Models\CertificadoExamen;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CertificadoExamenRecordatorio extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public CertificadoExamen $examen,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Recordatorio: examen de certificación próximo - MeCert');
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.certificado-examen-recordatorio',
            with: [
                'examen' => $this->examen,
            ],
        );
    }
}
