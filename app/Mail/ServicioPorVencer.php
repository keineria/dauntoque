<?php

namespace App\Mail;

use App\Models\Tarjeta;
use App\Mail\Concerns\PlantillaEditable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Str;

/** Al dueño de la tarjeta: su año de servicio está por vencer, venció o quedó en pausa. */
class ServicioPorVencer extends Mailable
{
    use PlantillaEditable;

    public function __construct(public Tarjeta $tarjeta, public string $etapa, public ?string $precio) {}

    public function plantilla(): string
    {
        return 'servicio';
    }

    public function valores(): array
    {
        $servicio = $this->tarjeta->servicio();
        $textos = self::textos($this->etapa, $servicio);

        return [
            'nombre' => Str::before($this->tarjeta->user->name ?? '', ' '),
            'nombre_completo' => $this->tarjeta->user->name ?? '',
            'correo' => $this->tarjeta->user->email ?? '',
            'titulo' => $textos['titulo'],
            'mensaje' => $textos['texto'],
            'dias_restantes' => $servicio['diasRestantes'],
            'dias_gracia' => $servicio['diasGracia'],
            'vence' => $servicio['venceEn']->format('d/m/Y'),
            'chip' => '···'.Str::substr($this->tarjeta->nfc_uid, -6),
            'precio' => $this->precio,
            'enlace_panel' => url('/dashboard'),
        ];
    }

    public function envelope(): Envelope
    {
        $dias = $this->tarjeta->servicio()['diasRestantes'];

        return new Envelope(subject: $this->asuntoEditable(match ($this->etapa) {
            'mes', 'semana' => "Tu tarjeta DaUnToque vence en $dias " . ($dias === 1 ? 'día' : 'días'),
            'vencido' => 'Tu año de servicio terminó: renueva y tu perfil sigue igual',
            default => 'Tu tarjeta DaUnToque está en pausa',
        }));
    }

    /** Título y texto del correo según la etapa del aviso. */
    public static function textos(string $etapa, array $servicio): array
    {
        $dias = fn (int $n) => $n.' '.($n === 1 ? 'día' : 'días');

        return [
            'titulo' => match ($etapa) {
                'mes', 'semana' => 'Te quedan '.$dias($servicio['diasRestantes']).' de servicio.',
                'vencido' => 'Tu año de servicio terminó.',
                default => 'Tu tarjeta quedó en pausa.',
            },
            'texto' => match ($etapa) {
                'mes' => 'Renueva cuando quieras: el nuevo año se suma al final del actual, así que no pierdes ningún día.',
                'semana' => 'Renueva esta semana para que tu perfil siga abriendo sin pausas cuando entregues tu tarjeta.',
                'vencido' => 'Tu perfil sigue funcionando '.$dias($servicio['diasGracia']).' más. Renueva y todo sigue igual: tu perfil, tus contactos y tus estadísticas.',
                default => 'Renueva y tu tarjeta vuelve a abrir tu perfil al instante, con todo lo que ya tenías.',
            },
        ];
    }

    public function content(): Content
    {
        return $this->contenidoEditable(new Content(view: 'mail.servicio-por-vencer', with: ['servicio' => $this->tarjeta->servicio()]));
    }
}
