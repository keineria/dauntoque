<?php

namespace App\Mail;

use App\Models\Pedido;
use App\Mail\Concerns\PlantillaEditable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Al cliente: compra confirmada y enlace para crear su perfil en el portal. */
class PedidoConfirmado extends Mailable
{
    use PlantillaEditable;

    public function __construct(public Pedido $pedido) {}

    public function plantilla(): string
    {
        return 'pedido_confirmado';
    }

    public function valores(): array
    {
        return $this->pedido->valoresCorreo();
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->asuntoEditable('Tu tarjeta DaUnToque está confirmada · pedido ' . $this->pedido->codigo));
    }

    public function content(): Content
    {
        return $this->contenidoEditable(new Content(view: 'mail.pedido-confirmado'));
    }
}
