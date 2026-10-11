<?php

namespace App\Mail;

use App\Models\AgendaAjuste;
use App\Models\Cita;
use App\Models\Profile;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Correos de la agenda. Al cliente (en el idioma de su cita): recibida, confirmada, rechazada, cambio_propuesto,
 * cambio_aceptado, cambio_rechazado, cancelada, recordatorio y codigo. Al dueño (en español): nueva, cambio_pedido,
 * cambio_aceptado, cambio_rechazado y cancelada.
 */
class AgendaCorreo extends Mailable
{
    /** Correos al cliente que llevan el .ics para su calendario */
    private const CON_ICS = ['confirmada', 'cambio_aceptado', 'recordatorio', 'cancelada'];

    public function __construct(public string $tipo, public ?Cita $cita = null, public bool $paraDuenio = false, public array $extra = []) {}

    private function negocio(): string
    {
        $perfil = $this->cita ? Profile::activoDe($this->cita->user_id) : null;

        return $perfil?->nombre ?: ($this->cita?->user?->name ?? 'DaUnToque');
    }

    private function zona(): string
    {
        return AgendaAjuste::where('user_id', $this->cita?->user_id)->value('zona_horaria') ?? 'America/Bogota';
    }

    public function envelope(): Envelope
    {
        if ($this->tipo === 'codigo') {
            return new Envelope(subject: __('agenda.asunto_codigo', ['codigo' => $this->extra['codigo']]));
        }

        $c = $this->cita;
        $zona = $this->zona();
        $cuando = $c->fechaLocal($zona) . ', ' . $c->horaLocal($zona);

        if ($this->paraDuenio) {
            return new Envelope(subject: match ($this->tipo) {
                'nueva' => ($c->estado === 'pendiente' ? 'Cita por aprobar: ' : 'Nueva cita: ') . "{$c->cliente_nombre} · $cuando",
                'cambio_pedido' => "{$c->cliente_nombre} pide cambiar su cita del $cuando",
                'cambio_aceptado' => "{$c->cliente_nombre} aceptó la nueva hora: $cuando",
                'cambio_rechazado' => "{$c->cliente_nombre} prefiere mantener su cita del $cuando",
                default => "{$c->cliente_nombre} canceló su cita del $cuando",
            });
        }

        $hora = $c->horaLocal($zona);
        $dia = $c->inicia_en->copy()->setTimezone($zona)->startOfDay();
        $cuandoRecordatorio = $dia->isSameDay(now($zona)) ? __('agenda.hoy_a', ['hora' => $hora])
            : ($dia->isSameDay(now($zona)->addDay()) ? __('agenda.manana') . ', ' . $hora : $cuando);

        return new Envelope(subject: __('agenda.asunto_' . $this->tipo, ['nombre' => $this->negocio(), 'cuando' => $cuandoRecordatorio]));
    }

    public function content(): Content
    {
        return new Content(view: 'mail.agenda', with: [
            'negocio' => $this->negocio(),
            'zona' => $this->zona(),
            'perfil' => $this->cita ? Profile::activoDe($this->cita->user_id) : null,
            'asunto' => $this->envelope()->subject,
        ]);
    }

    public function attachments(): array
    {
        if ($this->paraDuenio || !$this->cita || !in_array($this->tipo, self::CON_ICS, true)) {
            return [];
        }
        $perfil = Profile::activoDe($this->cita->user_id);
        $ics = $this->cita->ics($this->cita->servicio_nombre . ' · ' . $this->negocio(), $perfil?->direccion);

        return [Attachment::fromData(fn () => $ics, 'cita.ics')->withMime('text/calendar; charset=utf-8')];
    }
}
