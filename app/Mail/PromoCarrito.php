<?php

namespace App\Mail;

use App\Models\Ajuste;
use App\Models\Pedido;
use App\Services\Carritos;
use App\Mail\Concerns\PlantillaEditable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Carrito abandonado: la última promoción para terminar la compra (se envía una sola vez por correo). */
class PromoCarrito extends Mailable
{
    use PlantillaEditable;

    public function __construct(public Pedido $pedido, public int $descuento) {}

    public function plantilla(): string
    {
        return 'promo_carrito';
    }

    public function valores(): array
    {
        return $this->pedido->valoresCorreo() + [
            'descuento' => $this->descuento,
            'dias' => Carritos::DIAS_PROMO,
            'mensaje' => $this->mensaje(),
            'enlace_compra' => $this->enlace(),
        ];
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->asuntoEditable(Ajuste::valor('promo_asunto', 'Tu tarjeta DaUnToque te espera con un '.$this->descuento.' % de descuento')));
    }

    public function content(): Content
    {
        return $this->contenidoEditable(new Content(view: 'mail.promo-carrito', with: [
            'mensaje' => $this->mensaje(),
            'enlace' => $this->enlace(),
            'dias' => Carritos::DIAS_PROMO,
        ]));
    }

    private function mensaje(): string
    {
        return Ajuste::valor('promo_mensaje', 'Vimos que no terminaste tu compra. Te guardamos tu tarjeta con un descuento especial, solo por estos días.');
    }

    private function enlace(): string
    {
        return url('/comprar?'.http_build_query(['promo' => $this->pedido->promo_token, 'acabado' => $this->pedido->acabado]));
    }
}
