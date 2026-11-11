<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Pago;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Comprobante de la renovación anual: al cliente, o al correo de avisos (interno). */
class RenovacionConfirmada extends Mailable
{
    public function __construct(public Pago $pago, public bool $interno = false) {}

    public function envelope(): Envelope
    {
        $vence = $this->pago->tarjeta->vence_en->format('d/m/Y');

        return new Envelope(subject: $this->interno
            ? 'Renovación pagada: ' . ($this->pago->tarjeta->user->email ?? '') . ' · ' . $this->total()
            : "Renovación confirmada: tu tarjeta DaUnToque sigue activa hasta el $vence");
    }

    public function content(): Content
    {
        return new Content(view: 'mail.renovacion-confirmada', with: ['total' => $this->total()]);
    }

    private function total(): string
    {
        $monto = (float) $this->pago->monto;

        return $this->pago->moneda === 'USD'
            ? '$' . rtrim(rtrim(number_format($monto, 2), '0'), '.') . ' USD'
            : '$' . number_format($monto, 0, ',', '.') . ' COP';
    }
}
