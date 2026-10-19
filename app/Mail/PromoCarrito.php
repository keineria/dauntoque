<?php

namespace App\Mail;

use App\Models\Ajuste;
use App\Models\Pedido;
use App\Services\Carritos;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Carrito abandonado: la última promoción para terminar la compra (se envía una sola vez por correo). */
class PromoCarrito extends Mailable
{
    public function __construct(public Pedido $pedido, public int $descuento) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: Ajuste::valor('promo_asunto', 'Tu tarjeta DaUnToque te espera con un '.$this->descuento.' % de descuento'));
    }

    public function content(): Content
    {
        return new Content(view: 'mail.promo-carrito', with: [
            'mensaje' => Ajuste::valor('promo_mensaje', 'Vimos que no terminaste tu compra. Te guardamos tu tarjeta con un descuento especial, solo por estos días.'),
            'enlace' => url('/comprar?'.http_build_query(['promo' => $this->pedido->promo_token, 'acabado' => $this->pedido->acabado])),
            'dias' => Carritos::DIAS_PROMO,
        ]);
    }
}
