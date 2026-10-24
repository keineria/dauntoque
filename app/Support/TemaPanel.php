<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\User;

/**
 * Temas de color del panel del dueño (no cambian su perfil público): una paleta y una apariencia
 * (automático, claro u oscuro), guardadas en la cuenta. Los colores de cada paleta están en partials/panel-estilos.
 */
final class TemaPanel
{
    public const PALETA_DEFECTO = 'dauntoque';

    public const APARIENCIA_DEFECTO = 'oscuro';

    /** @var array<string, string> */
    public const APARIENCIAS = ['auto' => 'Automático', 'claro' => 'Claro', 'oscuro' => 'Oscuro'];

    /** Nombre, descripción y puntos de color de la miniatura de cada paleta. */
    public const PALETAS = [
        'dauntoque' => ['nombre' => 'Da un Toque', 'desc' => 'Azul y violeta de la marca', 'puntos' => ['#2563eb', '#8b5cf6', '#22c55e']],
        'grafito' => ['nombre' => 'Grafito', 'desc' => 'Gris neutro', 'puntos' => ['#1f2023', '#f3cfb3', '#d9d3f6']],
        'papel' => ['nombre' => 'Papel', 'desc' => 'Crema cálido', 'puntos' => ['#2e241d', '#f3cfb3', '#d9d3f6']],
        'salvia' => ['nombre' => 'Salvia', 'desc' => 'Verde gris', 'puntos' => ['#232c25', '#f3cfb3', '#d9d3f6']],
        'marea' => ['nombre' => 'Marea', 'desc' => 'Azul de agua', 'puntos' => ['#152233', '#f3cfb3', '#d9d3f6']],
        'ciruela' => ['nombre' => 'Ciruela', 'desc' => 'Malva suave', 'puntos' => ['#2a1e28', '#f3cfb3', '#d9d3f6']],
        'arcilla' => ['nombre' => 'Arcilla', 'desc' => 'Terracota tostada', 'puntos' => ['#2e2016', '#f3cfb3', '#d9d3f6']],
        'glicina' => ['nombre' => 'Glicina', 'desc' => 'Violeta azulado', 'puntos' => ['#1d1d2e', '#f3cfb3', '#d9d3f6']],
    ];

    public static function paleta(?User $usuario): string
    {
        $paleta = (string) ($usuario?->tema_panel ?? '');

        return array_key_exists($paleta, self::PALETAS) ? $paleta : self::PALETA_DEFECTO;
    }

    public static function apariencia(?User $usuario): string
    {
        $apariencia = (string) ($usuario?->apariencia_panel ?? '');

        return array_key_exists($apariencia, self::APARIENCIAS) ? $apariencia : self::APARIENCIA_DEFECTO;
    }

    /** Atributos de <html>: en "auto" el script de panel-estilos corrige data-modo antes de pintar. */
    public static function atributos(?User $usuario): string
    {
        $apariencia = self::apariencia($usuario);

        return sprintf(
            'data-paleta="%s" data-apariencia="%s" data-modo="%s"',
            e(self::paleta($usuario)),
            e($apariencia),
            $apariencia === 'claro' ? 'claro' : 'oscuro',
        );
    }
}
