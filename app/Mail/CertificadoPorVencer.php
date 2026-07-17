<?php

namespace App\Mail;

use App\Models\Certificado;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CertificadoPorVencer extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Certificado $certificado,
        public string $estado,
    ) {}

    public function envelope(): Envelope
    {
        $asunto = $this->estado === 'rojo'
            ? 'Certificado vencido - MeCert'
            : 'Certificado próximo a vencer - MeCert';

        return new Envelope(subject: $asunto);
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.certificado-por-vencer',
            with: [
                'certificado' => $this->certificado,
                'estado' => $this->estado,
            ],
        );
    }
}
