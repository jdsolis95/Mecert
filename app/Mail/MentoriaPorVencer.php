<?php

namespace App\Mail;

use App\Models\Mentoria;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MentoriaPorVencer extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Mentoria $mentoria,
        public string $estado,
    ) {}

    public function envelope(): Envelope
    {
        $asunto = $this->estado === 'rojo'
            ? 'Mentoría vencida - MeCert'
            : 'Mentoría próxima a vencer - MeCert';

        return new Envelope(subject: $asunto);
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.mentoria-por-vencer',
            with: [
                'mentoria' => $this->mentoria,
                'estado' => $this->estado,
            ],
        );
    }
}
