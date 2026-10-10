<?php

namespace App\Mail;

use App\Models\Tarjeta;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Al dueño de la tarjeta: su año de servicio está por vencer, venció o quedó en pausa. */
class ServicioPorVencer extends Mailable
{
    public function __construct(public Tarjeta $tarjeta, public string $etapa, public ?string $precio) {}

    public function envelope(): Envelope
    {
        $dias = $this->tarjeta->servicio()['diasRestantes'];

        return new Envelope(subject: match ($this->etapa) {
            'mes', 'semana' => "Tu tarjeta DaUnToque vence en $dias " . ($dias === 1 ? 'día' : 'días'),
            'vencido' => 'Tu año de servicio terminó: renueva y tu perfil sigue igual',
            default => 'Tu tarjeta DaUnToque está en pausa',
        });
    }

    public function content(): Content
    {
        return new Content(view: 'mail.servicio-por-vencer', with: ['servicio' => $this->tarjeta->servicio()]);
    }
}
