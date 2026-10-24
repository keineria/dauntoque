<?php

namespace App\Http\Controllers;

use App\Mail\PedidoConfirmado;
use App\Mail\PedidoNuevo;
use App\Models\Chip;
use App\Models\Pago;
use App\Models\Pedido;
use App\Models\User;
use App\Services\Bold;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Avisos de pago de Bold: compras de tarjetas (pedidos) y renovaciones (pagos).
 * Debe responder 200 en menos de 2 s; Bold reintenta y puede repetir avisos, así que
 * lo ya procesado no se vuelve a procesar.
 */
class WebhookBoldController extends Controller
{
    public function __invoke(Request $request)
    {
        if (!Bold::firmaValida($request->getContent(), $request->header('x-bold-signature'))) {
            return response()->json(['ok' => false], 401);
        }

        $tipo = $request->input('type');
        // En links de pago, metadata.reference es el id del link (LNK_...)
        $link = (string) $request->input('data.metadata.reference');
        $pagoId = $request->input('data.payment_id');

        if ($pedido = Pedido::where('link_id', $link)->first()) {
            $this->pedido($pedido, $tipo, $pagoId);
        } elseif ($pago = Pago::where('proveedor', 'bold')->where('link_id', $link)->first()) {
            $this->renovacion($pago, $tipo, $pagoId);
        }

        return response()->json(['ok' => true]);
    }

    private function pedido(Pedido $pedido, ?string $tipo, ?string $pagoId): void
    {
        $recienPagado = DB::transaction(function () use ($pedido, $tipo, $pagoId) {
            $pedido = Pedido::whereKey($pedido->id)->lockForUpdate()->first();
            if ($pedido->estado === 'pagado') {
                return false;
            }
            if ($tipo === 'SALE_APPROVED') {
                // Si el correo ya tiene cuenta, la compra queda a su nombre de una vez (si no, al registrarse)
                $pedido->update(['estado' => 'pagado', 'pagado_en' => now(), 'pago_proveedor_id' => $pagoId,
                    'user_id' => $pedido->user_id ?? User::where('email', $pedido->correo)->value('id')]);
                Chip::deCompra($pedido);

                return true;
            }
            if ($tipo === 'SALE_REJECTED') {
                $pedido->update(['estado' => 'rechazado']);
            }

            return false;
        });

        if ($recienPagado) {
            $this->avisarCompra($pedido->fresh());
        }
    }

    /** Correo al cliente (compra, enlace para crear su perfil y código de activación) y aviso interno para despachar. */
    private function avisarCompra(Pedido $pedido): void
    {
        try {
            Mail::to($pedido->correo)->send(new PedidoConfirmado($pedido));
            $pedido->update(['correo_enviado_en' => now()]);
        } catch (\Throwable $e) {
            Log::error('No se pudo enviar el correo del pedido', ['pedido' => $pedido->id, 'error' => $e->getMessage()]);
        }

        if ($aviso = config('pedidos.aviso_correo')) {
            try {
                Mail::to($aviso)->send(new PedidoNuevo($pedido));
            } catch (\Throwable $e) {
                Log::error('No se pudo enviar el aviso interno del pedido', ['pedido' => $pedido->id, 'error' => $e->getMessage()]);
            }
        }
    }

    private function renovacion(Pago $pago, ?string $tipo, ?string $pagoId): void
    {
        DB::transaction(function () use ($pago, $tipo, $pagoId) {
            $pago = Pago::whereKey($pago->id)->lockForUpdate()->first();
            if ($pago->estado === 'pagado') {
                return;
            }
            if ($tipo === 'SALE_APPROVED') {
                $pago->update(['estado' => 'pagado', 'pagado_en' => now(), 'pago_proveedor_id' => $pagoId]);
                if ($pago->concepto === 'renovacion' && $pago->tarjeta) {
                    $pago->tarjeta->renovar();
                }
            } elseif ($tipo === 'SALE_REJECTED') {
                $pago->update(['estado' => 'rechazado']);
            }
        });
    }
}
