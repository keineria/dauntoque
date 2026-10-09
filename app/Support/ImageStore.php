<?php

namespace App\Support;

use Illuminate\Validation\ValidationException;

class ImageStore
{
    public const MAX_BYTES = 10 * 1024 * 1024;

    /** Carpetas públicas donde viven las imágenes de los perfiles. */
    private static function carpetasPermitidas(): array
    {
        return array_filter([
            realpath(public_path('uploads')),
            realpath(storage_path('app/public')),
        ]);
    }

    /**
     * Si $valor es un dataURL de imagen, lo valida, lo guarda en public/uploads y devuelve su ruta.
     * Si es una ruta, solo la acepta cuando apunta a una imagen propia; si no, devuelve $actual.
     */
    public static function resolver(?string $valor, string $prefijo, ?string $actual = null): ?string
    {
        if ($valor === null || $valor === '') {
            return $actual;
        }

        if (str_starts_with($valor, 'data:image')) {
            return self::guardarDataUrl($valor, $prefijo);
        }

        return self::esPropia($valor) ? $valor : $actual;
    }

    public static function guardarDataUrl(string $dataUrl, string $prefijo): string
    {
        $partes = explode(';base64,', $dataUrl, 2);
        $binario = isset($partes[1]) ? base64_decode($partes[1], true) : false;

        if ($binario === false || strlen($binario) > self::MAX_BYTES || @getimagesizefromstring($binario) === false) {
            throw ValidationException::withMessages([
                'imagen' => 'Una de las imágenes no es válida o pesa más de 10 MB.',
            ]);
        }

        $carpeta = public_path('uploads/');
        if (! file_exists($carpeta)) {
            mkdir($carpeta, 0775, true);
        }

        $nombre = $prefijo . '_' . uniqid() . '.jpg';
        file_put_contents($carpeta . $nombre, $binario);

        return '/uploads/' . $nombre;
    }

    /** True solo si la ruta apunta a un archivo dentro de las carpetas de imágenes del portal. */
    public static function esPropia(?string $ruta): bool
    {
        if (! is_string($ruta) || $ruta === '' || str_contains($ruta, "\0")) {
            return false;
        }

        $real = realpath(public_path(ltrim($ruta, '/')));
        if ($real === false || ! is_file($real)) {
            return false;
        }

        foreach (self::carpetasPermitidas() as $base) {
            if (str_starts_with($real, $base . DIRECTORY_SEPARATOR)) {
                return true;
            }
        }

        return false;
    }
}
