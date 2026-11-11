<?php

declare(strict_types=1);

namespace App\Services\Marketing;

use App\Models\VisitaMarketing;
use Illuminate\Http\Request;

/** Origen de las visitas y los pedidos: UTM y clic de anuncio que /etiquetas.js guarda en la galleta dt_utm. */
class Origen
{
    public const CAMPOS = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'clic'];

    private const BOTS = '/bot|crawl|spider|slurp|facebookexternalhit|preview|headless|lighthouse|pingdom|uptime/i';

    /** @param  array<string, mixed>  $datos  validados por VisitaRequest */
    public function registrarVisita(Request $request, array $datos): void
    {
        // Ni robots ni las visitas del propio administrador (galleta dt_yo, la pone el Centro de mando) inflan las cifras
        if (preg_match(self::BOTS, (string) $request->userAgent()) || $request->cookie('dt_yo')) {
            return;
        }
        $pais = strtoupper((string) $request->header('CF-IPCountry'));

        VisitaMarketing::create([
            'visitante' => $datos['v'],
            'pagina' => $datos['p'],
            'referente' => self::texto($datos['r'] ?? null, 120),
            'pais' => preg_match('/^[A-Z]{2}$/', $pais) && ! in_array($pais, ['XX', 'T1'], true) ? $pais : null,
            'dispositivo' => self::dispositivo((string) $request->userAgent()),
        ] + self::limpiar((array) ($datos['u'] ?? [])));
    }

    /** UTM del pedido: la galleta dt_utm (último anuncio o enlace con UTM de los últimos 30 días) y el visitante. */
    public function deLaCompra(Request $request): array
    {
        $guardado = json_decode((string) $request->cookie('dt_utm'), true);
        $visitante = (string) $request->cookie('dt_v');

        return self::limpiar(is_array($guardado) ? $guardado : []) + [
            'visitante' => preg_match('/^[a-f0-9]{32}$/', $visitante) ? $visitante : null,
        ];
    }

    /** @return array<string, ?string> */
    private static function limpiar(array $utm): array
    {
        $limpio = [];
        foreach (self::CAMPOS as $campo) {
            $limpio[$campo] = self::texto(is_string($utm[$campo] ?? null) ? $utm[$campo] : null, $campo === 'clic' ? 10 : 100);
        }
        if (! in_array($limpio['clic'], ['gclid', 'fbclid', 'ttclid'], true)) {
            $limpio['clic'] = null;
        }
        // Mismo nombre aunque llegue con mayúsculas (Facebook / facebook)
        foreach (['utm_source', 'utm_medium', 'utm_campaign'] as $campo) {
            $limpio[$campo] = $limpio[$campo] !== null ? mb_strtolower($limpio[$campo]) : null;
        }

        return $limpio;
    }

    private static function texto(?string $valor, int $largo): ?string
    {
        $valor = trim(strip_tags((string) $valor));

        return $valor === '' ? null : mb_substr($valor, 0, $largo);
    }

    private static function dispositivo(string $agente): string
    {
        return match (true) {
            (bool) preg_match('/iPad|Tablet/i', $agente) => 'tableta',
            (bool) preg_match('/Mobi|Android|iPhone/i', $agente) => 'celular',
            default => 'computador',
        };
    }
}
