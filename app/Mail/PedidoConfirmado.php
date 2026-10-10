<?php

namespace App\Mail;

use App\Models\Pedido;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Al cliente: compra confirmada y enlace para crear su perfil en el portal. */
class PedidoConfirmado extends Mailable
{
    public function __construct(public Pedido $pedido) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Tu tarjeta DaUnToque está confirmada · pedido ' . $this->pedido->codigo);
    }

    public function content(): Content
    {
        return new Content(view: 'mail.pedido-confirmado');
    }
}
