<?php

declare(strict_types=1);

namespace App\Services\Musica;

use App\Models\AppAdicional;
use App\Models\MusicaAjuste;

/**
 * App Música: el dueño pega el enlace de una canción y de una lista de Spotify y su perfil muestra
 * el reproductor oficial de Spotify (sin llaves de API). Nunca suena solo: el visitante toca play.
 */
class Musica
{
    public static function app(int $userId): ?AppAdicional
    {
        return AppAdicional::de($userId, 'musica');
    }

    /** Activada y dentro de su año (muestra la música en el perfil). */
    public static function vigente(int $userId): bool
    {
        return (bool) self::app($userId)?->vigente();
    }

    /** Activa la app con su primer año gratis. */
    public static function activar(int $userId): AppAdicional
    {
        $app = AppAdicional::firstOrCreate(['user_id' => $userId, 'app' => 'musica'], [
            'activada_en' => now(),
            'vence_en' => now()->addDays(config('musica.dias_gratis')),
        ]);
        MusicaAjuste::de($userId);

        return $app;
    }

    /**
     * Saca "tipo/id" de un enlace de Spotify, o null si no es uno válido de esos tipos.
     * Acepta open.spotify.com (con o sin /intl-es/, con ?si=...), los de /embed/ y spotify:tipo:id.
     *
     * @param  list<string>  $tipos
     */
    public static function extraer(?string $enlace, array $tipos): ?string
    {
        $enlace = trim((string) $enlace);
        $patrones = [
            '#^(?:https?://)?open\.spotify\.com/(?:intl-[a-z]{2}(?:-[a-z]{2})?/)?(?:embed/)?([a-z]+)/([A-Za-z0-9]{22})(?:[/?\#].*)?$#i',
            '#^spotify:([a-z]+):([A-Za-z0-9]{22})$#i',
        ];
        foreach ($patrones as $patron) {
            if (preg_match($patron, $enlace, $m) && in_array(strtolower($m[1]), $tipos, true)) {
                return strtolower($m[1]) . '/' . $m[2];
            }
        }

        return null;
    }

    /** Enlace normal de Spotify para "tipo/id" (el que se muestra en el panel). */
    public static function enlace(?string $tipoId): ?string
    {
        return $tipoId ? 'https://open.spotify.com/' . $tipoId : null;
    }

    /** Dirección del reproductor oficial de Spotify para "tipo/id". */
    public static function reproductor(string $tipoId): string
    {
        return 'https://open.spotify.com/embed/' . $tipoId . '?utm_source=generator';
    }

    /** Lo que muestra el perfil público, o null si la app no está activa, está oculta o no hay nada puesto. */
    public static function publico(int $userId): ?array
    {
        if (!self::vigente($userId)) {
            return null;
        }
        $ajustes = MusicaAjuste::de($userId);
        if (!$ajustes->mostrar || (!$ajustes->cancion && !$ajustes->lista)) {
            return null;
        }

        return [
            'titulo' => $ajustes->titulo,
            'cancion' => $ajustes->cancion ? ['src' => self::reproductor($ajustes->cancion), 'alto' => config('musica.alto_cancion')] : null,
            'lista' => $ajustes->lista ? ['src' => self::reproductor($ajustes->lista), 'alto' => config('musica.alto_lista')] : null,
        ];
    }
}
