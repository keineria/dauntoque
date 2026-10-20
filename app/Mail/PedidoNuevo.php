<?php

namespace App\Mail;

use App\Models\Pedido;
use App\Mail\Concerns\PlantillaEditable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Aviso interno: pedido pagado, listo para despachar. */
class PedidoNuevo extends Mailable
{
    use PlantillaEditable;

    public function __construct(public Pedido $pedido) {}

    public function plantilla(): string
    {
        return 'pedido_nuevo';
    }

    public function valores(): array
    {
        return $this->pedido->valoresCorreo();
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->asuntoEditable('Pedido pagado ' . $this->pedido->codigo . ' · ' . $this->pedido->acabadoNombre() . ' · ' . $this->pedido->ciudad));
    }

    public function content(): Content
    {
        return $this->contenidoEditable(new Content(view: 'mail.pedido-nuevo'));
    }
}
