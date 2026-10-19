<?php

namespace App\Support;

use Illuminate\Http\Request;

/** Lugar y dispositivo de quien hace la petición, en palabras (para el administrador). */
class Visitante
{
    private const PAISES = ['CO' => 'Colombia', 'US' => 'Estados Unidos', 'MX' => 'México', 'EC' => 'Ecuador', 'PE' => 'Perú', 'VE' => 'Venezuela', 'PA' => 'Panamá', 'ES' => 'España', 'CL' => 'Chile', 'AR' => 'Argentina'];

    /**
     * "Medellín, Antioquia, Colombia". Sale de los encabezados que pone Cloudflare:
     * el país siempre; la ciudad y la región solo con «Add visitor location headers» encendido.
     */
    public static function lugar(Request $request): ?string
    {
        $pais = strtoupper((string) $request->header('CF-IPCountry'));
        $partes = array_filter([
            $request->header('CF-IPCity'),
            $request->header('CF-Region'),
            in_array($pais, ['', 'XX', 'T1'], true) ? null : (self::PAISES[$pais] ?? $pais),
        ]);

        return $partes ? mb_substr(implode(', ', array_unique($partes)), 0, 120) : null;
    }

    /** "Android 14 · Samsung SM-S918B · Chrome", "iPhone · iOS 17.5 · Safari", "Windows · Edge". */
    public static function dispositivo(?string $agente): ?string
    {
        if (blank($agente)) {
            return null;
        }
        $partes = [];
        if (preg_match('~Android ([\d.]+)(?:;[^;)]*)*?; ([^;)]+?)(?: Build[^;)]*)?\)~', $agente, $m)) {
            $partes[] = 'Android '.$m[1];
            $modelo = trim($m[2]);
            if ($modelo !== '' && ! in_array($modelo, ['K', 'wv', 'Mobile'], true)) {
                $partes[] = (str_starts_with($modelo, 'SM-') ? 'Samsung ' : '').$modelo;
            }
        } elseif (preg_match('~(iPhone|iPad).*? OS ([\d_]+)~', $agente, $m)) {
            $partes[] = $m[1];
            $partes[] = 'iOS '.str_replace('_', '.', $m[2]);
        } elseif (str_contains($agente, 'Windows')) {
            $partes[] = 'Windows';
        } elseif (str_contains($agente, 'Macintosh')) {
            $partes[] = 'Mac';
        } elseif (str_contains($agente, 'Linux')) {
            $partes[] = 'Linux';
        }

        $navegador = match (true) {
            str_contains($agente, 'SamsungBrowser') => 'Samsung Internet',
            str_contains($agente, 'Edg/') => 'Edge',
            str_contains($agente, 'OPR/') => 'Opera',
            str_contains($agente, 'Firefox/') || str_contains($agente, 'FxiOS') => 'Firefox',
            str_contains($agente, 'CriOS') || str_contains($agente, 'Chrome/') => 'Chrome',
            str_contains($agente, 'Safari/') => 'Safari',
            default => null,
        };
        if ($navegador) {
            $partes[] = $navegador;
        }

        return $partes ? mb_substr(implode(' · ', $partes), 0, 120) : 'Otro';
    }
}
