<?php

namespace App\Support;

class RedesSociales
{
    public const MAXIMO = 4;

    /**
     * Conserva solo redes del catálogo (config/redes.php) y reconstruye nombre, clase y SVG
     * desde el servidor. Del navegador solo se acepta el id y el nombre de usuario.
     */
    public static function sanear($redes): array
    {
        if (is_string($redes)) {
            $redes = json_decode($redes, true);
        }
        if (! is_array($redes)) {
            return [];
        }

        $catalogo = config('redes', []);
        $limpias = [];

        foreach ($redes as $red) {
            $id = is_array($red) ? ($red['id'] ?? null) : null;
            if (! is_string($id) || ! isset($catalogo[$id])) {
                continue;
            }

            $usuario = $red['usuario'] ?? '';
            $limpias[] = [
                'id' => $id,
                'nombre' => $catalogo[$id]['nombre'],
                'claseCSS' => $catalogo[$id]['claseCSS'],
                'svg' => $catalogo[$id]['svg'],
                'usuario' => is_scalar($usuario) ? mb_substr(trim((string) $usuario), 0, 255) : '',
            ];

            if (count($limpias) >= self::MAXIMO) {
                break;
            }
        }

        return $limpias;
    }
}
