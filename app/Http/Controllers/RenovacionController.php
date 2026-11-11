<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\Tarjeta;
use App\Services\Bold;
use App\Support\Precios;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/** Renovación anual del servicio de la tarjeta con un link de pago de Bold (el aviso llega a WebhookBoldController). */
class RenovacionController extends Controller
{
    /** Crea el link de pago de la renovación y lleva al cliente al checkout de Bold. */
    public function crear(Tarjeta $tarjeta)
    {
        abort_unless($tarjeta->user_id === Auth::id(), 404);

        if (!Bold::configurado()) {
            return redirect('/dashboard')->with('status', 'Los pagos en línea aún no están activos. Escríbenos por WhatsApp para renovar.');
        }

        $pais = Auth::user()->paisFacturacion();
        $monto = Precios::montoRenovacion($pais, Auth::user());
        $moneda = Precios::moneda($pais);

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
}
