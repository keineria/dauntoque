<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Prueba de envío desde Administrador › Correos. */
class CorreoPrueba extends Mailable
{
    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Prueba de correo de DaUnToque');
    }

    public function content(): Content
    {
        return new Content(htmlString: '<p style="font-family:Helvetica,Arial,sans-serif;font-size:15px">Si lees esto, los correos de notificación de DaUnToque salen bien.<br>Remitente: '
            .e(config('mail.from.name')).' &lt;'.e(config('mail.from.address')).'&gt;<br>Enviado: '.now()->format('d/m/Y H:i').'</p>');
    }
}
