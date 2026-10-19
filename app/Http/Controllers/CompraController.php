<?php

namespace App\Http\Controllers;

use App\Models\Market;
use App\Models\Pedido;
use App\Services\Bold;
use App\Services\Carritos;
use App\Support\Precios;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

/** Compra de la tarjeta en línea (Colombia, con Bold) desde el sitio de ventas. */
class CompraController extends Controller
{
    private const PAIS = 'CO';

    public function formulario(Request $request)
    {
        $precio = Precios::tarjeta(self::PAIS);
        abort_unless($precio, 404);
        $moneda = Market::where('code', self::PAIS)->value('currency') ?? 'COP';
        // Enlace de la última promoción (carrito abandonado): precio con descuento y datos ya escritos
        $promo = Carritos::promoVigente($request->query('promo'));

        return view('compra.formulario', [
            'precio' => $promo ? Carritos::conDescuento($precio, $moneda) : $precio,
            'promo' => $promo,
            'descuento' => $promo ? Carritos::descuento() : 0,
            'moneda' => $moneda,
            'acabado' => array_key_exists($request->query('acabado'), Pedido::ACABADOS) ? $request->query('acabado') : 'matte-black',
            'departamentos' => config('pedidos.departamentos'),
            'enLinea' => Bold::configurado(),
            'whatsapp' => Market::where('code', self::PAIS)->value('whatsapp'),
        ]);
    }

    public function crear(Request $request)
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'correo' => ['required', 'email', 'max:180'],
            'celular' => ['required', 'string', 'regex:/^[0-9 +()-]{7,20}$/'],
            'documento' => ['nullable', 'string', 'max:30'],
            'departamento' => ['required', Rule::in(config('pedidos.departamentos'))],
            'ciudad' => ['required', 'string', 'max:80'],
            'direccion' => ['required', 'string', 'max:200'],
            'acabado' => ['required', Rule::in(array_keys(Pedido::ACABADOS))],
            'nombre_tarjeta' => ['nullable', 'string', 'max:40'],
            'promo' => ['nullable', 'string', 'size:40'],
        ], [
            'celular.regex' => 'Escribe un número de celular válido.',
        ]);

        if (!Bold::configurado()) {
            return back()->withInput()->with('status', 'El pago en línea aún no está activo. Escríbenos por WhatsApp para comprar.');
        }

        $precio = Precios::tarjeta(self::PAIS);
        $moneda = Market::where('code', self::PAIS)->value('currency') ?? 'COP';
        $promo = Carritos::promoVigente($datos['promo'] ?? null);
        if ($promo) {
            $precio = Carritos::conDescuento($precio, $moneda);
        }
        unset($datos['promo']);
        $pedido = Pedido::create(array_merge($datos, [
            'correo' => mb_strtolower($datos['correo']),
            'pais' => self::PAIS,
            'moneda' => $moneda,
            'monto' => $precio->amount,
            'promo_de_pedido_id' => $promo?->id,
        ]));

        try {
            $link = Bold::crearLink(
                (int) round($precio->amount),
                $pedido->moneda,
                'Tarjeta DaUnToque ' . $pedido->acabadoNombre() . ' · pedido ' . $pedido->codigo,
                $pedido->correo,
                route('pedido.ver', $pedido),
            );
        } catch (\Throwable $e) {
            Log::error('Error creando link de compra', ['pedido' => $pedido->id, 'error' => $e->getMessage()]);

            return back()->withInput()->with('status', 'No pudimos abrir el pago. Inténtalo de nuevo en unos minutos.');
        }

        $pedido->update(['link_id' => $link['id']]);

        return redirect()->away($link['url']);
    }

    /** Página privada del pedido (Bold devuelve aquí al cliente). */
    public function ver(Pedido $pedido)
    {
        return view('compra.pedido', ['pedido' => $pedido]);
    }
}
