<?php

declare(strict_types=1);

namespace App\Services;

use App\Mail\PedidoConfirmado;
use App\Mail\PedidoNuevo;
use App\Mail\RenovacionConfirmada;
use App\Models\Pago;
use App\Models\Pedido;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Correos que salen cuando entra un pago: al cliente y el aviso interno.
 * Un correo que no sale nunca tumba el pago: se registra y, en compras, se reintenta con pedidos:reintentar-correos.
 */
class AvisosPago
{
    /** Compra pagada: correo al cliente (enlace y código de activación) y aviso interno para despachar. */
    public static function compra(Pedido $pedido, bool $conAvisoInterno = true): bool
    {
        $enviado = self::enviar($pedido->correo, new PedidoConfirmado($pedido), ['pedido' => $pedido->id]);
        if ($enviado) {
            $pedido->update(['correo_enviado_en' => now()]);
        }
        if ($conAvisoInterno && ($aviso = config('pedidos.aviso_correo'))) {
            self::enviar($aviso, new PedidoNuevo($pedido), ['pedido' => $pedido->id, 'interno' => true]);
        }

        return $enviado;
    }

    /** Renovación pagada: comprobante al cliente con la nueva fecha y aviso interno. */
    public static function renovacion(Pago $pago): void
    {
        $pago->loadMissing(['tarjeta.user:id,name,email']);
        $correo = $pago->tarjeta?->user?->email;
        if (! $correo) {
            return;
        }
        self::enviar($correo, new RenovacionConfirmada($pago), ['pago' => $pago->id]);
        if ($aviso = config('pedidos.aviso_correo')) {
            self::enviar($aviso, new RenovacionConfirmada($pago, interno: true), ['pago' => $pago->id, 'interno' => true]);
        }
    }

    private static function enviar(string $para, object $correo, array $contexto): bool
    {
        try {
            Mail::to($para)->send($correo);

            return true;
        } catch (Throwable $e) {
            Log::error('No se pudo enviar un correo de pago', $contexto + ['clase' => class_basename($correo), 'error' => $e->getMessage()]);

            return false;
        }
    }
}
