<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConfirmarCadastroMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $nome,
        public string $urlConfirmacao,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirme seu cadastro - MeuBolso',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.confirmar-cadastro',
        );
    }
}
