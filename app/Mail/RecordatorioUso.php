<?php

namespace App\Mail;

use App\Mail\Concerns\PlantillaEditable;
use App\Models\Profile;
use App\Models\User;
use App\Services\MetricasAdmin;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Str;

/** Al cliente que lleva meses sin visitas en su perfil: ideas para volver a usar su tarjeta. */
class RecordatorioUso extends Mailable
{
    use PlantillaEditable;

    public function __construct(public User $usuario, public ?Profile $perfil = null) {}

    public function plantilla(): string
    {
        return 'recordatorio_uso';
    }

    public function valores(): array
    {
        $perfil = $this->perfil ?? Profile::maestroDe($this->usuario->id);

        return [
            'nombre' => Str::before($this->usuario->name, ' '),
            'nombre_completo' => $this->usuario->name,
            'correo' => $this->usuario->email,
            'usuario' => $perfil?->nickname,
            'enlace_perfil' => $perfil ? Profile::urlPublica($perfil->nickname) : url('/dashboard'),
            'enlace_panel' => url('/dashboard'),
            'meses' => MetricasAdmin::MESES_SIN_USO,
        ];
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->asuntoEditable(Str::before($this->usuario->name, ' ').', tu tarjeta DaUnToque tiene más para darte'));
    }

    public function content(): Content
    {
        return $this->contenidoEditable(new Content(view: 'mail.recordatorio-uso', with: $this->valores()));
    }
}
