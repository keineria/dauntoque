<?php

declare(strict_types=1);

namespace App\Services\Calificame;

use App\Models\AppAdicional;
use App\Models\CalificameAjuste;
use App\Models\Profile;
use App\Support\MediosPago;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/**
 * Negocio de Google del dueño (API de Places, nueva): lo busca por nombre, lo conecta y guarda su calificación
 * y hasta 5 reseñas, que se actualizan una vez al día. Se muestran aparte de Califícame, tal como las entrega Google.
 * Docs: developers.google.com/maps/documentation/places/web-service/text-search y /place-details
 */
class GoogleNegocio
{
    private const CAMPOS_BUSQUEDA = 'places.id,places.displayName,places.formattedAddress,places.rating,places.userRatingCount';

    private const CAMPOS_DETALLE = 'id,displayName,formattedAddress,rating,userRatingCount,reviews,googleMapsUri';

    public static function configurado(): bool
    {
        return filled(config('services.google_places.key'));
    }

    private static function cliente(string $campos)
    {
        return Http::withHeaders(['X-Goog-Api-Key' => config('services.google_places.key'), 'X-Goog-FieldMask' => $campos])
            ->timeout(config('services.google_places.timeout'))
            ->baseUrl(rtrim(config('services.google_places.url'), '/'));
    }

    /** Idioma y región de las búsquedas según el país del dueño. */
    private static function region(int $userId): array
    {
        $pais = MediosPago::pais(Profile::activoDe($userId)?->pais);

        return $pais === 'US' ? ['en', 'US'] : ['es', 'CO'];
    }

    /** Negocios que coinciden con lo que escribió el dueño (nombre y ciudad): hasta 5. */
    public static function buscar(int $userId, string $texto): array
    {
        [$idioma, $region] = self::region($userId);
        $respuesta = self::cliente(self::CAMPOS_BUSQUEDA)->post('/places:searchText', [
            'textQuery' => $texto, 'languageCode' => $idioma, 'regionCode' => $region, 'pageSize' => 5,
        ]);
        if ($respuesta->failed()) {
            throw new RuntimeException('Google no respondió la búsqueda: HTTP ' . $respuesta->status());
        }

        return collect($respuesta->json('places', []))->map(fn (array $p) => [
            'id' => $p['id'],
            'nombre' => $p['displayName']['text'] ?? '',
            'direccion' => $p['formattedAddress'] ?? '',
            'rating' => isset($p['rating']) ? (float) $p['rating'] : null,
            'total' => (int) ($p['userRatingCount'] ?? 0),
        ])->take(5)->values()->all();
    }

    /** Conecta el negocio elegido y trae su calificación de una vez. */
    public static function conectar(int $userId, string $placeId): CalificameAjuste
    {
        $ajustes = CalificameAjuste::de($userId);
        $ajustes->google_place_id = $placeId;
        self::actualizar($ajustes);

        return $ajustes->fresh();
    }

    public static function desconectar(int $userId): void
    {
        $ajustes = CalificameAjuste::de($userId);
        $ajustes->update([
            // El enlace de reseñas que pusimos solos se va con el negocio; uno que haya escrito el dueño se queda
            'google_url' => str_starts_with((string) $ajustes->google_url, 'https://search.google.com/local/writereview') ? null : $ajustes->google_url,
            'google_place_id' => null, 'google_nombre' => null, 'google_direccion' => null, 'google_maps_url' => null,
            'google_rating' => null, 'google_total' => null, 'google_resenas' => null, 'google_actualizado_en' => null,
        ]);
    }

    /** Trae de Google la calificación, el total y hasta 5 reseñas del negocio conectado. */
    public static function actualizar(CalificameAjuste $ajustes): void
    {
        [$idioma, $region] = self::region($ajustes->user_id);
        $respuesta = self::cliente(self::CAMPOS_DETALLE)
            ->get('/places/' . rawurlencode($ajustes->google_place_id), ['languageCode' => $idioma, 'regionCode' => $region]);
        if ($respuesta->failed()) {
            throw new RuntimeException('Google no respondió el negocio: HTTP ' . $respuesta->status());
        }
        $p = $respuesta->json();

        $ajustes->fill([
            'google_nombre' => mb_substr($p['displayName']['text'] ?? '', 0, 200),
            'google_direccion' => mb_substr($p['formattedAddress'] ?? '', 0, 300),
            'google_maps_url' => $p['googleMapsUri'] ?? null,
            'google_rating' => isset($p['rating']) ? round((float) $p['rating'], 1) : null,
            'google_total' => (int) ($p['userRatingCount'] ?? 0),
            'google_resenas' => collect($p['reviews'] ?? [])->map(fn (array $r) => [
                'autor' => $r['authorAttribution']['displayName'] ?? '',
                'autor_url' => $r['authorAttribution']['uri'] ?? null,
                'estrellas' => (int) ($r['rating'] ?? 0),
                'texto' => mb_substr($r['text']['text'] ?? $r['originalText']['text'] ?? '', 0, 600),
                'cuando' => $r['relativePublishTimeDescription'] ?? '',
            ])->take(5)->values()->all(),
            'google_actualizado_en' => now(),
        ]);
        // El botón "Reseña en Google" de la página de gracias: si el dueño no puso otro, el de su negocio
        $ajustes->google_url ??= 'https://search.google.com/local/writereview?placeid=' . rawurlencode($ajustes->google_place_id);
        $ajustes->save();
    }

    /** Una vez al día (comando calificame:google): actualiza los negocios conectados de las apps vigentes. */
    public static function actualizarTodos(): int
    {
        if (!self::configurado()) {
            return 0;
        }
        $actualizados = 0;
        CalificameAjuste::whereNotNull('google_place_id')
            ->where(fn ($q) => $q->whereNull('google_actualizado_en')->orWhere('google_actualizado_en', '<', now()->subHours(20)))
            ->whereIn('user_id', AppAdicional::where('app', 'calificame')->where('vence_en', '>', now())->select('user_id'))
            ->each(function (CalificameAjuste $ajustes) use (&$actualizados) {
                try {
                    self::actualizar($ajustes);
                    $actualizados++;
                } catch (Throwable $e) {
                    Log::warning('Califícame: no se pudo actualizar el negocio de Google', ['user_id' => $ajustes->user_id, 'error' => $e->getMessage()]);
                }
            });

        return $actualizados;
    }

    /** Lo que muestra el perfil, o null si no está conectado, no tiene calificación o el dueño lo ocultó. */
    public static function publico(CalificameAjuste $ajustes): ?array
    {
        if (!$ajustes->mostrar_google || !$ajustes->googleConectado() || !$ajustes->google_rating) {
            return null;
        }

        return [
            'promedio' => $ajustes->google_rating,
            'total' => $ajustes->google_total,
            'resenas' => array_slice(array_values(array_filter($ajustes->google_resenas ?? [], fn ($r) => filled($r['texto'] ?? null))), 0, 3),
            'enlace' => $ajustes->google_maps_url,
            'escribir' => 'https://search.google.com/local/writereview?placeid=' . rawurlencode($ajustes->google_place_id),
        ];
    }
}
