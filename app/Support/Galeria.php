<?php

namespace App\Support;

class Galeria
{
    public const MAX_TITULO = 60;

    /**
     * Lleva la galería guardada a [{url, titulo}]. Las galerías viejas guardaban solo la ruta
     * de cada imagen (sin título); se leen igual.
     */
    public static function normalizar($galeria): array
    {
        if (is_string($galeria)) {
            $galeria = json_decode($galeria, true);
        }
        if (! is_array($galeria)) {
            return [];
        }

        $items = [];
        foreach ($galeria as $item) {
            $url = is_array($item) ? ($item['url'] ?? null) : $item;
            if (! is_string($url) || $url === '') {
                continue;
            }
            $items[] = ['url' => $url, 'titulo' => self::titulo(is_array($item) ? ($item['titulo'] ?? '') : '')];
        }

        return $items;
    }

    /**
     * Lo que envía el editor o Configuración: cada imagen nueva (dataURL) se guarda en disco;
     * una ruta solo se acepta si ya estaba en la galería o es una imagen propia.
     */
    public static function guardar($enviada, array $actual): array
    {
        $rutasActuales = array_column(self::normalizar($actual), 'url');
        $limpia = [];

        foreach (self::normalizar($enviada) as $item) {
            if (str_starts_with($item['url'], 'data:image')) {
                $item['url'] = ImageStore::guardarDataUrl($item['url'], 'galeria');
            } elseif (! in_array($item['url'], $rutasActuales, true) && ! ImageStore::esPropia($item['url'])) {
                continue;
            }
            $limpia[] = $item;
        }

        return $limpia;
    }

    /** Solo las imágenes que existen en el portal, para la página pública. */
    public static function publica($galeria): array
    {
        return array_values(array_filter(self::normalizar($galeria), fn ($item) => ImageStore::esPropia($item['url'])));
    }

    /** Rutas de las imágenes, sin títulos. */
    public static function rutas($galeria): array
    {
        return array_column(self::normalizar($galeria), 'url');
    }

    private static function titulo($titulo): string
    {
        return is_scalar($titulo) ? mb_substr(trim(strip_tags((string) $titulo)), 0, self::MAX_TITULO) : '';
    }
}
