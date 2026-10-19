<?php

namespace App\Support;

/** Tipografías que el dueño elige para su perfil público (config/tipografias.php). */
class Tipografias
{
    /** Id válido de la lista, o '' (la letra que trae el estilo de color). */
    public static function valida(mixed $id): string
    {
        return is_string($id) && array_key_exists($id, config('tipografias')) ? $id : '';
    }

    /**
     * Hoja de Google Fonts con las familias de las tipografías pedidas (todas si no se pide ninguna), o null.
     * El navegador solo descarga los archivos de letra que la página usa de verdad.
     *
     * @param  array<int, string>|null  $ids
     */
    public static function urlGoogle(?array $ids = null): ?string
    {
        $lista = config('tipografias');
        $familias = collect($ids === null ? array_keys($lista) : $ids)
            ->filter(fn ($id) => isset($lista[$id]))
            ->flatMap(fn ($id) => $lista[$id]['google'])
            ->unique()->values();

        if ($familias->isEmpty()) {
            return null;
        }

        return 'https://fonts.googleapis.com/css2?'.$familias->map(fn ($f) => 'family='.$f)->implode('&').'&display=swap';
    }

    /**
     * Lista para el navegador: id, nombre, descripción y las familias CSS de títulos y textos.
     *
     * @return array<int, array{id: string, nombre: string, desc: string, titulo: string, texto: string}>
     */
    public static function paraVista(): array
    {
        return collect(config('tipografias'))
            ->map(fn ($t, $id) => ['id' => $id, 'nombre' => $t['nombre'], 'desc' => $t['desc'], 'titulo' => $t['titulo'], 'texto' => $t['texto']])
            ->values()->all();
    }
}
