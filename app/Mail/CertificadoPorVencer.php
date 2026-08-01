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

    // El asunto cambia según si el certificado ya venció o solo está por vencer
    public function envelope(): Envelope
    {
        $asunto = $this->estado === 'rojo'
            ? 'Certificado vencido - MeCert'
            : 'Certificado próximo a vencer - MeCert';

        return new Envelope(subject: $asunto);
    }

    // Vista con el detalle del certificado y su estado de vigencia
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
