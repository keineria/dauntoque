<?php

namespace App\Mail;

use App\Models\Pedido;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Aviso interno: pedido pagado, listo para despachar. */
class PedidoNuevo extends Mailable
{
    public function __construct(public Pedido $pedido) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Pedido pagado ' . $this->pedido->codigo . ' · ' . $this->pedido->acabadoNombre() . ' · ' . $this->pedido->ciudad);
    }

    public function content(): Content
    {
        return new Content(view: 'mail.pedido-nuevo');
    }
}
