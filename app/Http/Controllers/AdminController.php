<?php

namespace App\Http\Controllers;

use App\Mail\CorreoPrueba;
use App\Models\Ajuste;
use App\Models\Pedido;
use App\Services\Carritos;
use App\Services\MetricasAdmin;
use App\Support\Correos;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

/** Administrador del negocio: resumen, carritos abandonados y correos de notificación. */
class AdminController extends Controller
{
    public function inicio(MetricasAdmin $metricas): View
    {
        return view('admin.inicio', [
            'm' => $metricas->resumen(),
            'sinUso' => $metricas->sinUso(20),
            'mesesSinUso' => MetricasAdmin::MESES_SIN_USO,
            'ultimasCompras' => Pedido::with('chip')->where('estado', 'pagado')->latest('pagado_en')->limit(8)->get(),
        ]);
    }

    public function carritos(): View
    {
        return view('admin.carritos', [
            'abandonados' => Carritos::abandonados()->latest('id')->paginate(50, pageName: 'pag')->withQueryString(),
            'declinados' => Carritos::declinados()->latest('id')->limit(30)->get(),
            'enProceso' => Carritos::enProceso()->latest('id')->limit(30)->get(),
            'conPromo' => Pedido::whereNotNull('promo_enviada_en')->pluck('correo')->flip(),
            'descuento' => Carritos::descuento(),
            'promoAsunto' => Ajuste::valor('promo_asunto', ''),
            'promoMensaje' => Ajuste::valor('promo_mensaje', ''),
            'horas' => Carritos::HORAS_EN_PROCESO,
            'diasPromo' => Carritos::DIAS_PROMO,
            'promoActiva' => Correos::activa('promo_carrito'),
        ]);
    }

    public function guardarPromo(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'promo_descuento' => ['required', 'integer', 'min:0', 'max:50'],
            'promo_asunto' => ['nullable', 'string', 'max:120'],
            'promo_mensaje' => ['nullable', 'string', 'max:1000'],
        ], [], ['promo_descuento' => 'descuento', 'promo_asunto' => 'asunto', 'promo_mensaje' => 'mensaje']);
        Ajuste::guardar($datos);

        return back()->with('status', 'Promoción guardada.');
    }

    /** Envía la última promoción a un carrito abandonado (una sola vez por correo). */
    public function enviarPromo(int $id): RedirectResponse
    {
        $pedido = Carritos::abandonados()->whereKey($id)->firstOrFail();
        if (! Correos::activa('promo_carrito')) {
            return back()->with('error', 'El correo de promoción está apagado en Correos.');
        }

        try {
            $enviada = Carritos::enviarPromo($pedido);
        } catch (\Throwable $e) {
            Log::error('No se pudo enviar la promoción del carrito', ['pedido' => $id, 'error' => $e->getMessage()]);

            return back()->with('error', 'No se pudo enviar el correo. Revisa la configuración en Correos.');
        }

        return back()->with($enviada ? 'status' : 'error', $enviada
            ? 'Promoción enviada a '.$pedido->correo.'.'
            : $pedido->correo.' ya había recibido la promoción o ya compró.');
    }

    /** Envía la promoción a todos los carritos abandonados que aún no la han recibido. */
    public function enviarPromoTodos(): RedirectResponse
    {
        if (! Correos::activa('promo_carrito')) {
            return back()->with('error', 'El correo de promoción está apagado en Correos.');
        }
        $enviadas = $fallidas = 0;
        Carritos::abandonados()->whereNotIn('correo', Pedido::select('correo')->whereNotNull('promo_enviada_en'))
            ->limit(200)->get()
            ->each(function (Pedido $pedido) use (&$enviadas, &$fallidas) {
                try {
                    Carritos::enviarPromo($pedido) && $enviadas++;
                } catch (\Throwable $e) {
                    $fallidas++;
                    Log::error('No se pudo enviar la promoción del carrito', ['pedido' => $pedido->id, 'error' => $e->getMessage()]);
                }
            });

        return back()->with($fallidas ? 'error' : 'status', "Promociones enviadas: $enviadas.".($fallidas ? " Fallaron $fallidas; revisa Correos." : ''));
    }

    public function correos(): View
    {
        return view('admin.correos', [
            'plantillas' => collect(Correos::PLANTILLAS)->map(fn ($p, $id) => $p + ['activa' => Correos::activa($id)]),
            'remitente' => Ajuste::valor('correo_remitente', ''),
            'remitenteNombre' => Ajuste::valor('correo_remitente_nombre', ''),
            'avisos' => Ajuste::valor('correo_avisos_pedidos', ''),
            'actual' => ['direccion' => config('mail.from.address'), 'nombre' => config('mail.from.name'), 'avisos' => config('pedidos.aviso_correo')],
            // Datos del servidor de correo (.env): solo se muestran recortados y la contraseña nunca
            'smtp' => [
                'mailer' => config('mail.default'),
                'host' => config('mail.mailers.smtp.host'),
                'puerto' => config('mail.mailers.smtp.port'),
                'usuario' => Correos::ocultar(config('mail.mailers.smtp.username')),
                'clave' => filled(config('mail.mailers.smtp.password')),
            ],
        ]);
    }

    public function guardarCorreos(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'correo_remitente' => ['nullable', 'email', 'max:180'],
            'correo_remitente_nombre' => ['nullable', 'string', 'max:80'],
            'correo_avisos_pedidos' => ['nullable', 'email', 'max:180'],
            'activas' => ['array'],
            'activas.*' => ['string'],
        ], [], ['correo_remitente' => 'correo remitente', 'correo_remitente_nombre' => 'nombre del remitente', 'correo_avisos_pedidos' => 'correo de avisos']);

        $activas = $datos['activas'] ?? [];
        unset($datos['activas']);
        foreach (array_keys(Correos::PLANTILLAS) as $id) {
            $datos["correo_activo_$id"] = in_array($id, $activas, true);
        }
        Ajuste::guardar($datos);

        return back()->with('status', 'Configuración de correos guardada.');
    }

    public function probarCorreo(Request $request): RedirectResponse
    {
        $datos = $request->validate(['para' => ['required', 'email', 'max:180']]);

        try {
            Mail::to($datos['para'])->send(new CorreoPrueba);
        } catch (\Throwable $e) {
            Log::error('Falló el correo de prueba', ['error' => $e->getMessage()]);

            return back()->with('error', 'No salió el correo: '.mb_substr($e->getMessage(), 0, 160));
        }

        return back()->with('status', 'Correo de prueba enviado a '.$datos['para'].'. Revisa también la carpeta de spam.');
    }
}
