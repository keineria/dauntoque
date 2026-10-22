<?php

namespace App\Support;

use App\Models\Market;
use App\Models\User;
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

    /**
     * Monto de la renovación anual en la moneda del país (config/servicio.php),
     * o el precio especial que el administrador le puso a ese cliente.
     */
    public static function montoRenovacion(?string $pais, ?User $usuario = null): ?int
    {
        $especial = $usuario?->precio_renovacion;

        return $especial !== null ? (int) $especial : config('servicio.precio_renovacion.' . MediosPago::pais($pais));
    }

    public static function moneda(?string $pais): string
    {
        $pais = MediosPago::pais($pais);

        return Market::where('code', $pais)->value('currency') ?? ($pais === 'US' ? 'USD' : 'COP');
    }

    /** Precio de la renovación anual con su moneda: "$120.000 COP" o "$55 USD". */
    public static function renovacion(?string $pais, ?User $usuario = null): ?string
    {
        $monto = static::montoRenovacion($pais, $usuario);

        return $monto === null ? null : static::texto($monto, $pais);
    }

    public static function texto(int $monto, ?string $pais): string
    {
        $pais = MediosPago::pais($pais);

        return '$' . number_format($monto, 0, ',', $pais === 'US' ? ',' : '.') . ' ' . static::moneda($pais);
    }
}
