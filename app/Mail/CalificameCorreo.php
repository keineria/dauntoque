<?php

namespace App\Mail;

use App\Models\Calificacion;
use App\Models\Cita;
use App\Models\Profile;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Correos de Califícame. "pedir": al cliente después de su cita (en el idioma de la cita), con el enlace para calificar.
 * "baja": al dueño (en español) cuando alguien lo califica con 3 estrellas o menos, para que lo arregle en privado.
 */
class CalificameCorreo extends Mailable
{
    public function __construct(public string $tipo, public ?Calificacion $calificacion = null, public ?Cita $cita = null) {}

    private function negocio(): string
    {
        $userId = $this->cita?->user_id ?? $this->calificacion?->user_id;
        $perfil = $userId ? Profile::activoDe($userId) : null;

        return $perfil?->nombre ?: 'DaUnToque';
    }

    /** Enlace para calificar esta cita: dauntoque.com/usuario/califica?cita=… */
    public function enlace(): ?string
    {
        $maestro = $this->cita ? Profile::maestroDe($this->cita->user_id)?->nickname : null;

        return $maestro ? Profile::urlPublica($maestro) . '/califica?' . http_build_query(['cita' => $this->cita->token, 'lang' => $this->cita->idioma ?: 'es']) : null;
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->tipo === 'baja'
            ? 'Te calificaron con ' . $this->calificacion->estrellas . ' ' . ($this->calificacion->estrellas === 1 ? 'estrella' : 'estrellas') . ': revísalo en privado'
            : __('calificame.asunto_pedir', ['nombre' => $this->negocio()]));
    }

    public function content(): Content
    {
        return new Content(view: 'mail.calificame', with: [
            'negocio' => $this->negocio(),
            'asunto' => $this->envelope()->subject,
            'enlace' => $this->enlace(),
        ]);
    }
}
