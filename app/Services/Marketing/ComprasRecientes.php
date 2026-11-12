<?php

declare(strict_types=1);

namespace App\Services\Marketing;

use App\Models\Ajuste;
use App\Models\Pedido;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Avisos «X compró la tarjeta» del sitio de ventas. Solo pedidos PAGADOS reales y recientes (config/pedidos.php),
 * sin datos que identifiquen a la persona: nombre e inicial del apellido, ciudad, acabado y hora del pago.
 * Nunca se inventan compras: sin pedidos recientes, la lista sale vacía y el sitio no muestra nada.
 */
class ComprasRecientes
{
    private const CACHE = 'compras-recientes.';

    private const MINUTOS_CACHE = 5;

    /** Se encienden desde /admin/pagos (apagados mientras Bold esté en pruebas). */
    public static function activos(): bool
    {
        return Ajuste::valor('avisos_compras') === '1';
    }

    public static function olvidar(): void
    {
        foreach (['CO', 'US'] as $pais) {
            Cache::forget(self::CACHE . $pais);
        }
    }

    /**
     * @param  bool  $soloSiActivos  false = vista previa del administrador (aunque estén apagados)
     * @return list<array{nombre: string, ciudad: string, producto: string, t: int}>
     */
    public function lista(string $pais, bool $soloSiActivos = true): array
    {
        if ($soloSiActivos && ! self::activos()) {
            return [];
        }

        return Cache::remember(self::CACHE . $pais, now()->addMinutes(self::MINUTOS_CACHE), fn (): array => Pedido::query()
            ->where('estado', 'pagado')
            ->where('pais', $pais)
            ->where('pagado_en', '>=', now()->subDays((int) config('pedidos.avisos.dias', 15)))
            ->latest('pagado_en')
            ->limit((int) config('pedidos.avisos.maximo', 8))
            ->get(['nombre', 'ciudad', 'acabado', 'pagado_en'])
            ->map(fn (Pedido $p): array => [
                'nombre' => self::anonimo((string) $p->nombre),
                'ciudad' => Str::limit(self::titulo((string) $p->ciudad), 30, ''),
                'producto' => $p->acabadoNombre(),
                't' => $p->pagado_en->getTimestamp(),
            ])
            ->filter(fn (array $c): bool => $c['nombre'] !== '')
            ->values()
            ->all());
    }

    /** "luis fernando PATIÑO gómez" -> "Luis P." (primer nombre e inicial del primer apellido; nunca el apellido completo). */
    public static function anonimo(string $nombre): string
    {
        $partes = preg_split('/\s+/u', trim(preg_replace('/[^\p{L}\s\'-]/u', '', $nombre) ?? ''), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        if ($partes === []) {
            return '';
        }
        // Con 4 o más palabras, lo usual es dos nombres y dos apellidos: el primer apellido es la tercera
        $apellido = $partes[count($partes) >= 4 ? 2 : 1] ?? null;

        return self::titulo($partes[0]) . ($apellido ? ' ' . mb_strtoupper(mb_substr($apellido, 0, 1)) . '.' : '');
    }

    private static function titulo(string $texto): string
    {
        return mb_convert_case(mb_strtolower(trim($texto)), MB_CASE_TITLE, 'UTF-8');
    }
}
