<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Correo con asunto y HTML ya armados (la prueba de una plantilla desde el editor). */
class CorreoLibre extends Mailable
{
    public function __construct(public string $titulo, public string $cuerpo) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '[Prueba] '.$this->titulo);
    }

    public function content(): Content
    {
        return new Content(htmlString: $this->cuerpo);
    }
}
