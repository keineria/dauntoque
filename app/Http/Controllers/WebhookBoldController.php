<?php

namespace App\Http\Controllers;

use App\Models\AvisoPago;
use App\Models\Chip;
use App\Models\Pago;
use App\Models\Pedido;
use App\Models\User;
use App\Services\AvisosPago;
use App\Services\Bold;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
            AvisoPago::anotar('bold', (string) $request->input('type') ?: null, 'firma inválida');

            return response()->json(['ok' => false], 401);
        }

        $tipo = $request->input('type');
        $pagoId = $request->input('data.payment_id') ?? $request->input('subject');
        // En links de pago, metadata.reference es el id del link (LNK_...); por si Bold lo manda en otro campo, se prueban varios
        $candidatos = array_values(array_unique(array_filter(array_map(
            fn ($campo) => (string) $request->input($campo),
            ['data.metadata.reference', 'data.reference', 'data.payment_link', 'data.payment_link_id', 'data.link_id']
        ))));

        $pedido = $candidatos ? Pedido::whereIn('link_id', $candidatos)->first() : null;
        $pago = !$pedido && $candidatos ? Pago::where('proveedor', 'bold')->whereIn('link_id', $candidatos)->first() : null;

        // Huella de cada aviso (sin datos personales) para saber qué manda Bold y por qué un aviso no encuentra su pedido
        Log::info('Webhook de Bold', [
            'tipo' => $tipo,
            'pago_id' => $pagoId,
            'referencias' => $candidatos,
            'campos_data' => array_keys((array) $request->input('data', [])),
            'campos_metadata' => array_keys((array) $request->input('data.metadata', [])),
            'resultado' => $pedido ? 'pedido ' . $pedido->id : ($pago ? 'pago ' . $pago->id : 'sin coincidencia'),
        ]);

        AvisoPago::anotar('bold', $tipo, $pedido ? 'pedido ' . $pedido->codigo : ($pago ? 'renovación' : 'sin coincidencia'), $candidatos[0] ?? null);

        if ($pedido) {
            $this->pedido($pedido, $tipo, $pagoId);
        } elseif ($pago) {
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
            AvisosPago::compra($pedido->fresh());
        }
    }

    private function renovacion(Pago $pago, ?string $tipo, ?string $pagoId): void
    {
        $recienRenovado = DB::transaction(function () use ($pago, $tipo, $pagoId) {
            $pago = Pago::whereKey($pago->id)->lockForUpdate()->first();
            if ($pago->estado === 'pagado') {
                return false;
            }
            if ($tipo === 'SALE_APPROVED') {
                $pago->update(['estado' => 'pagado', 'pagado_en' => now(), 'pago_proveedor_id' => $pagoId]);
                if ($pago->concepto === 'renovacion' && $pago->tarjeta) {
                    $pago->tarjeta->renovar();

                    return true;
                }
            } elseif ($tipo === 'SALE_REJECTED') {
                $pago->update(['estado' => 'rechazado']);
            }

            return false;
        });

        if ($recienRenovado) {
            AvisosPago::renovacion($pago->fresh());
        }
    }
}
