<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Models\Profile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * SEO de los perfiles públicos: datos estructurados (schema.org) de cada perfil
 * y el mapa del sitio con el enlace permanente de cada cliente.
 */
class SeoPerfil
{
    /** Minutos que se guarda el mapa de perfiles antes de volver a consultarlo. */
    private const MINUTOS_MAPA = 60;

    /** Máximo de direcciones por mapa según el protocolo de sitemaps. */
    private const LIMITE_MAPA = 50000;

    /** Descripción corta para buscadores y vistas previas: profesión + bio, en una línea. */
    public static function descripcion(array $datos): string
    {
        $texto = trim(($datos['profesion'] ? $datos['profesion'] . '. ' : '') . ($datos['bio'] ?? ''));
        $texto = trim((string) preg_replace('/\s+/u', ' ', $texto));

        return Str::limit($texto, 155) ?: 'Tarjeta digital de ' . $datos['nombre'] . ': guarda su contacto y escríbele con un toque.';
    }

    /**
     * ProfilePage + Person (o LocalBusiness si es un perfil de empresa) con los datos que ya son públicos en el perfil.
     *
     * @param  array<string, mixed>  $datos  Los mismos datos que pinta partials/perfil-publico
     */
    public static function schema(array $datos): array
    {
        $esEmpresa = ($datos['tipo'] ?? null) === 'empresa';
        $redes = self::enlacesRedes($datos['redes'] ?? []);

        $entidad = array_filter([
            '@type' => $esEmpresa ? 'LocalBusiness' : 'Person',
            '@id' => $datos['enlace'] . '#perfil',
            'name' => $datos['nombre'],
            $esEmpresa ? 'slogan' : 'jobTitle' => $datos['profesion'] ?: null,
            'description' => $datos['bio'] ? Str::limit(trim((string) preg_replace('/\s+/u', ' ', $datos['bio'])), 300) : null,
            'image' => $datos['foto'] ? url($datos['foto']) : null,
            'url' => $datos['enlace'],
            'telephone' => $datos['telefono'] ?: null,
            'email' => $datos['correo'] ?: null,
            'address' => $datos['direccion'] ?: null,
            'sameAs' => $redes ?: null,
        ]);

        // Estrellas de Califícame: solo cuando el dueño muestra el sello (ya son públicas en el perfil)
        $cal = $datos['calificame'] ?? null;
        if ($esEmpresa && $cal && $cal['sello'] && $cal['total'] > 0) {
            $entidad['aggregateRating'] = ['@type' => 'AggregateRating', 'ratingValue' => $cal['promedio'], 'reviewCount' => $cal['total'], 'bestRating' => 5];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'ProfilePage',
            'url' => $datos['enlace'],
            'name' => $datos['nombre'],
            'isPartOf' => ['@type' => 'WebSite', 'name' => 'DaUnToque', 'url' => config('perfiles.url_publica') . '/'],
            'mainEntity' => $entidad,
        ];
    }

    /**
     * Enlace de cada red social del perfil (misma regla que dtUrlRed en partials/perfil-publico).
     *
     * @param  array<int, array<string, mixed>>  $redes  Redes ya saneadas por RedesSociales::sanear()
     * @return array<int, array{nombre: string, url: string}>
     */
    public static function redes(array $redes): array
    {
        $bases = ['ig' => 'https://instagram.com/', 'fb' => 'https://facebook.com/', 'tk' => 'https://tiktok.com/@',
            'in' => 'https://linkedin.com/in/', 'x' => 'https://x.com/', 'yt' => 'https://youtube.com/@'];
        $enlaces = [];

        foreach ($redes as $red) {
            $usuario = trim((string) ($red['usuario'] ?? ''));
            if ($usuario === '') {
                continue;
            }
            $url = preg_match('#^https?://#i', $usuario) ? $usuario : ($bases[$red['id']] ?? 'https://') . rawurlencode(ltrim($usuario, '@'));
            $enlaces[] = ['nombre' => (string) $red['nombre'], 'url' => $url];
        }

        return $enlaces;
    }

    /** @return array<int, string> */
    private static function enlacesRedes(array $redes): array
    {
        return array_column(self::redes($redes), 'url');
    }

    /**
     * Enlace permanente (primer perfil) de cada cliente con la fecha de su último cambio.
     *
     * @return array<int, array{loc: string, lastmod: string}>
     */
    public static function mapa(): array
    {
        return Cache::remember('seo.mapa-perfiles', now()->addMinutes(self::MINUTOS_MAPA), function (): array {
            $cambios = DB::table('profiles')->selectRaw('user_id, MIN(id) as maestro_id, MAX(updated_at) as cambio')->groupBy('user_id');

            return DB::table('profiles')
                ->joinSub($cambios, 'c', 'c.maestro_id', '=', 'profiles.id')
                ->whereNotNull('profiles.nombre')->where('profiles.nombre', '!=', '')
                // Solo perfiles con contenido: los de prueba o vacíos (sin foto y sin profesión ni descripción) le restan calidad al sitio
                ->where(fn ($q) => $q->where(fn ($f) => $f->whereNotNull('profiles.foto_perfil')->where('profiles.foto_perfil', '!=', ''))
                    ->orWhere(fn ($f) => $f->whereNotNull('profiles.profesion')->where('profiles.profesion', '!=', ''))
                    ->orWhere(fn ($f) => $f->whereNotNull('profiles.bio')->where('profiles.bio', '!=', '')))
                ->orderBy('profiles.id')
                ->limit(self::LIMITE_MAPA)
                ->get(['profiles.nickname', 'c.cambio'])
                ->map(fn ($fila) => [
                    'loc' => Profile::urlPublica($fila->nickname),
                    'lastmod' => $fila->cambio ? substr((string) $fila->cambio, 0, 10) : now()->toDateString(),
                ])->all();
        });
    }
}
