<?php

namespace App\Support;

use App\Models\User;

/**
 * Apariencia del panel (config/apariencia.php): la paleta y el modo que eligió el usuario,
 * y las variables CSS de cada paleta para partials/panel-estilos.
 */
class Apariencia
{
    public static function paletas(): array
    {
        return config('apariencia.paletas');
    }

    /** ['paleta' => 'papel', 'modo' => 'auto'], con los valores por defecto si no eligió o ya no existen. */
    public static function de(?User $usuario): array
    {
        $paleta = $usuario?->panel_paleta;
        $modo = $usuario?->panel_modo;

        return [
            'paleta' => isset(self::paletas()[$paleta]) ? $paleta : config('apariencia.paleta_defecto'),
            'modo' => isset(config('apariencia.modos')[$modo]) ? $modo : config('apariencia.modo_defecto'),
        ];
    }

    /** Reglas CSS: una por paleta y modo, sobre html[data-paleta][data-esquema]. */
    public static function css(): string
    {
        $css = '';
        foreach (self::paletas() as $clave => $paleta) {
            foreach (['claro', 'oscuro'] as $esquema) {
                // La oscura de DaUnToque es la base de :root (vidrio y brillos): no se repite aquí
                if ($clave === 'dauntoque' && $esquema === 'oscuro') {
                    continue;
                }
                $c = $paleta[$esquema];
                $css .= "html[data-paleta=\"$clave\"][data-esquema=\"$esquema\"]{"
                    . "--bg:{$c['fondo']};--card:{$c['tarjeta']};--card-solid:{$c['tarjeta']};--surface:{$c['superficie']};"
                    . "--border:{$c['borde']};--text:{$c['texto']};--muted:{$c['suave']};--sidebar-muted:{$c['suave']};--sidebar-text:{$c['texto']};"
                    . "--primary:{$c['tinta']};--primary-2:{$c['acento']};--on-primary:{$c['sobre']};--link:{$c['enlace']};"
                    . "--sidebar-bg:{$c['fondo']};--glow:none;--orb1:transparent;--orb2:transparent;"
                    . ($clave === 'dauntoque' ? '--orb1:rgba(37,99,235,.07);--orb2:rgba(124,58,237,.06);' : '')
                    . "}\n";
            }
        }

        return $css;
    }

    /** Variables de una miniatura (Mi cuenta): --m-fondo-claro, --m-tinta-oscuro, ... */
    public static function variablesMiniatura(array $paleta): string
    {
        $vars = [];
        foreach (['claro', 'oscuro'] as $esquema) {
            foreach (['fondo', 'tarjeta', 'superficie', 'tinta', 'acento', 'texto'] as $color) {
                $vars[] = "--m-$color-$esquema:{$paleta[$esquema][$color]}";
            }
        }

        return implode(';', $vars);
    }
}
