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

    // Asunto fijo, se manda 5 días antes de la fecha del examen aprobado
    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Recordatorio: examen de certificación próximo - MeCert');
    }

    // Vista con la fecha y el lugar del examen ya aprobado
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
