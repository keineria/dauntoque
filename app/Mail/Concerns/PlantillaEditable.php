<?php

namespace App\Mail\Concerns;

use App\Support\Plantillas;
use Illuminate\Mail\Mailables\Content;

/**
 * Correo cuya plantilla se puede reemplazar desde Administrador → Correos.
 * Si hay una versión guardada, se usa con los datos de este correo en sus {{campos}};
 * si no, la vista Blade de siempre.
 */
trait PlantillaEditable
{
    /** Clave en Plantillas::EDITABLES. */
    abstract public function plantilla(): string;

    /** @return array<string, string|int|null> valores de los {{campos}} */
    abstract public function valores(): array;

    protected function asuntoEditable(string $porDefecto): string
    {
        $propia = Plantillas::personalizada($this->plantilla());

        return $propia && filled($propia['asunto']) ? Plantillas::llenar($propia['asunto'], $this->valores(), false) : $porDefecto;
    }

    protected function contenidoEditable(Content $porDefecto): Content
    {
        $propia = Plantillas::personalizada($this->plantilla());

        return $propia ? new Content(htmlString: Plantillas::llenar($propia['html'], $this->valores())) : $porDefecto;
    }
}
