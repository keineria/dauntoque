<?php

namespace App\Support;

use App\Models\Market;
use Illuminate\Support\Facades\DB;

/** Precio vigente de la tarjeta por país (tabla prices). Lo usan el sitio de ventas y el checkout. */
class Precios
{
    /** Fila de prices vigente hoy para la tarjeta NFC en ese país (amount, compare_at) o null. */
    public static function tarjeta(string $pais): ?object
    {
        $producto = DB::table('products')->where('slug', 'tarjeta-nfc')->value('id');
        $ahora = now();

        return DB::table('prices')->where('product_id', $producto)->where('market_code', $pais)
            ->where(fn ($q) => $q->whereNull('valid_from')->orWhere('valid_from', '<=', $ahora))
            ->where(fn ($q) => $q->whereNull('valid_to')->orWhere('valid_to', '>=', $ahora))
            ->orderByDesc('id')->first();
    }

    /** Precio de la renovación anual en la moneda del país: "$120.000 COP" o "$55 USD" (config/servicio.php). */
    public static function renovacion(?string $pais): ?string
    {
        $pais = MediosPago::pais($pais);
        $monto = config("servicio.precio_renovacion.$pais");
        if ($monto === null) {
            return null;
        }
        $moneda = Market::where('code', $pais)->value('currency') ?? ($pais === 'US' ? 'USD' : 'COP');

        return '$' . number_format($monto, 0, ',', $pais === 'US' ? ',' : '.') . ' ' . $moneda;
    }
}
