<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Services\Musica\Musica;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

/** Ajustes de la app Música: los enlaces tienen que ser de Spotify y del tipo correcto. */
class MusicaAjustesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Musica::app((int) $this->user()->id) !== null;
    }

    public function rules(): array
    {
        $deSpotify = fn (array $tipos, string $mensaje): Closure => function (string $campo, mixed $valor, Closure $falla) use ($tipos, $mensaje): void {
            if (!Musica::extraer((string) $valor, $tipos)) {
                $falla($mensaje);
            }
        };

        return [
            'titulo' => ['nullable', 'string', 'max:60'],
            'cancion' => ['nullable', 'string', 'max:300', $deSpotify(['track'], 'Pega el enlace de una canción de Spotify (Compartir › Copiar enlace de la canción). Empieza por https://open.spotify.com/track/')],
            'lista' => ['nullable', 'string', 'max:300', $deSpotify(config('musica.tipos_lista'), 'Pega el enlace de una lista, álbum o artista de Spotify (Compartir › Copiar enlace). Empieza por https://open.spotify.com/')],
        ];
    }

    /** Lo que se guarda: los enlaces ya limpios como "tipo/id". */
    public function ajustes(): array
    {
        return [
            'titulo' => filled($this->input('titulo')) ? trim((string) $this->input('titulo')) : null,
            'cancion' => Musica::extraer($this->input('cancion'), ['track']),
            'lista' => Musica::extraer($this->input('lista'), config('musica.tipos_lista')),
            'mostrar' => $this->boolean('mostrar'),
        ];
    }
}
