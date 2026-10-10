<?php

namespace App\Http\Controllers;

use App\Models\Market;
use App\Models\Pago;
use App\Models\Profile;
use App\Models\Tarjeta;
use App\Services\Bold;
use App\Support\MediosPago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/** Renovación anual del servicio de la tarjeta: link de pago de Bold y su webhook. */
class RenovacionController extends Controller
{
    /** Crea el link de pago de la renovación y lleva al cliente al checkout de Bold. */
    public function crear(Tarjeta $tarjeta)
    {
        abort_unless($tarjeta->user_id === Auth::id(), 404);

        if (!Bold::configurado()) {
            return redirect('/dashboard')->with('status', 'Los pagos en línea aún no están activos. Escríbenos por WhatsApp para renovar.');
        }

        $pais = MediosPago::pais(Profile::activoDe(Auth::id())?->pais);
        $monto = config("servicio.precio_renovacion.$pais");
        $moneda = Market::where('code', $pais)->value('currency') ?? ($pais === 'US' ? 'USD' : 'COP');

        try {
            $link = Bold::crearLink(
                $monto,
                $moneda,
                'Renovación anual DaUnToque · chip ···' . mb_substr($tarjeta->nfc_uid, -6),
                Auth::user()->email,
                url('/dashboard?renovacion=1'),
            );
        } catch (\Throwable $e) {
            Log::error('Error creando link de renovación', ['user_id' => Auth::id(), 'tarjeta' => $tarjeta->id, 'error' => $e->getMessage()]);

            return redirect('/dashboard')->with('status', 'No pudimos abrir el pago. Inténtalo de nuevo en unos minutos.');
        }

        Pago::create([
            'user_id' => Auth::id(),
            'tarjeta_id' => $tarjeta->id,
            'concepto' => 'renovacion',
            'proveedor' => 'bold',
            'pais' => $pais,
            'moneda' => $moneda,
            'monto' => $monto,
            'link_id' => $link['id'],
            'url' => $link['url'],
        ]);

        return redirect()->away($link['url']);
    }

    /**
     * Webhook de Bold. Debe responder 200 en menos de 2 s; Bold reintenta y puede repetir avisos,
     * así que un pago ya procesado no vuelve a sumar días.
     */
    public function webhookBold(Request $request)
    {
        if (!Bold::firmaValida($request->getContent(), $request->header('x-bold-signature'))) {
            return response()->json(['ok' => false], 401);
        }

        $tipo = $request->input('type');
        // En links de pago, metadata.reference es el id del link (LNK_...)
        $pago = Pago::where('proveedor', 'bold')->where('link_id', $request->input('data.metadata.reference'))->first();
        if (!$pago) {
            return response()->json(['ok' => true]);
        }

        DB::transaction(function () use ($pago, $tipo, $request) {
            $pago = Pago::whereKey($pago->id)->lockForUpdate()->first();
            if ($pago->estado === 'pagado') {
                return;
            }
            if ($tipo === 'SALE_APPROVED') {
                $pago->update(['estado' => 'pagado', 'pagado_en' => now(), 'pago_proveedor_id' => $request->input('data.payment_id')]);
                if ($pago->concepto === 'renovacion' && $pago->tarjeta) {
                    $pago->tarjeta->renovar();
                }
            } elseif ($tipo === 'SALE_REJECTED') {
                $pago->update(['estado' => 'rechazado']);
            }
        });

        return response()->json(['ok' => true]);
    }
}
