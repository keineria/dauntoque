<?php

namespace App\Services;

use App\Mail\PromoCarrito;
use App\Models\Ajuste;
use App\Models\Pedido;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Compras sin terminar del sitio de ventas.
 * En proceso: pedido pendiente de las últimas horas (puede estar pagando en Bold).
 * Abandonado: pendiente más viejo, de un correo que nunca pagó. Declinado: Bold rechazó el pago.
 */
class Carritos
{
    /** Horas que un pedido pendiente se considera "en proceso de compra". */
    public const HORAS_EN_PROCESO = 2;

    /** Días hacia atrás que se miran los carritos. */
    public const DIAS_VENTANA = 30;

    /** Días que vale el enlace de la promoción. */
    public const DIAS_PROMO = 7;

    public static function enProceso(): Builder
    {
        return Pedido::where('estado', 'pendiente')->where('created_at', '>=', now()->subHours(self::HORAS_EN_PROCESO));
    }

    /** El último pedido pendiente de cada correo que nunca pagó (sin los que siguen en proceso). */
    public static function abandonados(): Builder
    {
        return self::ultimoPorCorreo('pendiente')
            ->where('created_at', '<', now()->subHours(self::HORAS_EN_PROCESO))
            ->whereNotIn('correo', self::correosEnProceso());
    }

    /** El último pedido rechazado de cada correo que nunca pagó. */
    public static function declinados(): Builder
    {
        return self::ultimoPorCorreo('rechazado');
    }

    private static function ultimoPorCorreo(string $estado): Builder
    {
        return Pedido::whereIn('id', Pedido::selectRaw('MAX(id)')
            ->where('estado', $estado)
            ->where('created_at', '>=', now()->subDays(self::DIAS_VENTANA))
            ->groupBy('correo'))
            ->whereNotIn('correo', Pedido::select('correo')->where('estado', 'pagado'));
    }

    private static function correosEnProceso(): Builder
    {
        return self::enProceso()->select('correo');
    }

    public static function descuento(): int
    {
        return max(0, min(50, (int) Ajuste::valor('promo_descuento', 10)));
    }

    public static function yaRecibioPromo(string $correo): bool
    {
        return Pedido::where('correo', $correo)->whereNotNull('promo_enviada_en')->exists();
    }

    /**
     * Manda la última promoción a la persona del pedido. Una sola vez por correo, aunque se pida dos veces
     * o desde dos pestañas a la vez. Devuelve false si ya la había recibido o si ya compró.
     */
    public static function enviarPromo(Pedido $pedido): bool
    {
        $enviar = DB::transaction(function () use ($pedido) {
            // Bloquea los pedidos de ese correo mientras decide
            $pedidos = Pedido::where('correo', $pedido->correo)->lockForUpdate()->get();
            if ($pedidos->whereNotNull('promo_enviada_en')->isNotEmpty() || $pedidos->where('estado', 'pagado')->isNotEmpty()) {
                return false;
            }
            $pedido->forceFill(['promo_token' => Str::random(40), 'promo_enviada_en' => now()])->save();

            return true;
        });

        if ($enviar) {
            Mail::to($pedido->correo)->send(new PromoCarrito($pedido->fresh(), self::descuento()));
        }

        return $enviar;
    }

    /** Pedido de la promoción si el enlace sigue vigente y esa persona aún no ha comprado. */
    public static function promoVigente(?string $token): ?Pedido
    {
        if (! is_string($token) || strlen($token) !== 40) {
            return null;
        }
        $pedido = Pedido::where('promo_token', $token)->where('promo_enviada_en', '>=', now()->subDays(self::DIAS_PROMO))->first();
        if (! $pedido || Pedido::where('correo', $pedido->correo)->where('estado', 'pagado')->exists()) {
            return null;
        }

        return $pedido;
    }

    /** Precio con el descuento de la promoción (redondeado a centenas en pesos). */
    public static function conDescuento(object $precio, string $moneda): object
    {
        $monto = (float) $precio->amount * (100 - self::descuento()) / 100;
        $monto = $moneda === 'COP' ? round($monto / 100) * 100 : round($monto, 2);

        return (object) array_merge((array) $precio, [
            'amount' => $monto,
            'compare_at' => max((float) ($precio->compare_at ?? 0), (float) $precio->amount),
        ]);
    }
}
