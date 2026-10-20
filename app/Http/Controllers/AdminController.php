<?php

namespace App\Http\Controllers;

use App\Mail\CorreoLibre;
use App\Mail\CorreoPrueba;
use App\Mail\PedidoConfirmado;
use App\Models\Ajuste;
use App\Models\Pedido;
use App\Models\User;
use App\Services\ClientesAdmin;
use App\Services\Carritos;
use App\Services\MetricasAdmin;
use App\Support\Correos;
use App\Support\Plantillas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

/** Administrador del negocio: resumen, carritos abandonados y correos de notificación. */
class AdminController extends Controller
{
    public function inicio(Request $request, MetricasAdmin $metricas): View
    {
        $lista = array_key_exists($request->query('lista'), ClientesAdmin::LISTAS) ? $request->query('lista') : 'todos';
        $buscar = trim((string) $request->query('buscar'));
        $por = in_array((int) $request->query('por'), ClientesAdmin::POR_PAGINA, true) ? (int) $request->query('por') : 20;
        $clientes = ClientesAdmin::consulta($lista, $buscar)->latest('id')->paginate($por)->withQueryString()->fragment('clientes');

        return view('admin.inicio', [
            'm' => $metricas->resumen(),
            'mesesSinUso' => MetricasAdmin::MESES_SIN_USO,
            'ultimasCompras' => Pedido::with('chip')->where('estado', 'pagado')->latest('pagado_en')->limit(8)->get(),
            'lista' => $lista,
            'buscar' => $buscar,
            'por' => $por,
            'clientes' => $clientes,
            'filas' => ClientesAdmin::filas($clientes),
        ]);
    }

    /** Reenvía al cliente el correo de su compra, con el código de activación que aún no usa. */
    public function reenviarCodigo(User $usuario): RedirectResponse
    {
        $chip = ClientesAdmin::chipSinActivar($usuario);
        if (! $chip) {
            return back()->with('error', 'Ese cliente no tiene un código de compra pendiente por activar.');
        }

        try {
            Mail::to($usuario->email)->send(new PedidoConfirmado($chip->pedido));
        } catch (\Throwable $e) {
            Log::error('No se pudo reenviar el código de activación', ['user' => $usuario->id, 'error' => $e->getMessage()]);

            return back()->with('error', 'No se pudo enviar el correo. Revisa la configuración en Correos.');
        }

        return back()->with('status', 'Código '.$chip->codigo.' reenviado a '.$usuario->email.'.');
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
            'plantillas' => collect(Correos::PLANTILLAS)->map(fn ($p, $id) => $p + [
                'activa' => Correos::activa($id),
                'editable' => isset(Plantillas::EDITABLES[$id]),
                'propia' => isset(Plantillas::EDITABLES[$id]) && Plantillas::personalizada($id) !== null,
            ]),
            'remitente' => Ajuste::valor('correo_remitente', ''),
            'remitenteNombre' => Ajuste::valor('correo_remitente_nombre', ''),
            'avisos' => Ajuste::valor('correo_avisos_pedidos', ''),
            'actual' => ['direccion' => config('mail.from.address'), 'nombre' => config('mail.from.name'), 'avisos' => config('pedidos.aviso_correo')],
            // La contraseña nunca vuelve a la pantalla: solo se dice si hay una guardada
            'smtp' => [
                'host' => Ajuste::valor('smtp_host', ''),
                'puerto' => Ajuste::valor('smtp_puerto', ''),
                'cifrado' => Ajuste::valor('smtp_cifrado', 'ssl'),
                'usuario' => Ajuste::valor('smtp_usuario', ''),
                'clave' => filled(Ajuste::valor('smtp_clave')),
                'desdeAdmin' => Correos::smtpDesdeAdmin(),
                'envio' => config('mail.default'),
                'hostEnv' => config('mail.mailers.smtp.host'),
                'claveEnv' => filled(config('mail.mailers.smtp.password')),
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

    /** Servidor de correo (SMTP). La contraseña se guarda cifrada y, si se deja vacía, se conserva la anterior. */
    public function guardarSmtp(Request $request): RedirectResponse
    {
        if ($request->boolean('quitar')) {
            Ajuste::guardar(['smtp_host' => null, 'smtp_puerto' => null, 'smtp_cifrado' => null, 'smtp_usuario' => null, 'smtp_clave' => null]);

            return back()->with('status', 'Se borró el servidor de correo del administrador. Ahora se usa el del archivo .env.');
        }
        $datos = $request->validate([
            'smtp_host' => ['required', 'string', 'max:180', 'regex:/^[A-Za-z0-9.-]+$/'],
            'smtp_puerto' => ['required', 'integer', 'between:1,65535'],
            'smtp_cifrado' => ['required', 'in:ssl,tls'],
            'smtp_usuario' => ['required', 'string', 'max:180'],
            'smtp_clave' => [filled(Ajuste::valor('smtp_clave')) ? 'nullable' : 'required', 'string', 'max:255'],
        ], [], ['smtp_host' => 'servidor', 'smtp_puerto' => 'puerto', 'smtp_cifrado' => 'seguridad', 'smtp_usuario' => 'usuario', 'smtp_clave' => 'contraseña']);

        if (filled($datos['smtp_clave'] ?? null)) {
            $datos['smtp_clave'] = Crypt::encryptString($datos['smtp_clave']);
        } else {
            unset($datos['smtp_clave']);
        }
        Ajuste::guardar($datos);

        return back()->with('status', 'Servidor de correo guardado. Envía un correo de prueba para confirmar que funciona.');
    }

    /** Editor de una plantilla: HTML a la izquierda, vista previa con datos de ejemplo a la derecha. */
    public function plantilla(string $id): View
    {
        abort_unless(isset(Plantillas::EDITABLES[$id]), 404);
        $propia = Plantillas::personalizada($id);
        $original = Plantillas::original($id);

        return view('admin.plantilla', [
            'id' => $id,
            'def' => Plantillas::EDITABLES[$id],
            'propia' => $propia !== null,
            'asunto' => old('asunto', $propia['asunto'] ?? $original['asunto']),
            'html' => old('html', $propia['html'] ?? $original['html']),
            'ejemplo' => Plantillas::ejemplo($id),
            'activa' => Correos::activa($id),
        ]);
    }

    public function guardarPlantilla(Request $request, string $id): RedirectResponse
    {
        abort_unless(isset(Plantillas::EDITABLES[$id]), 404);
        $datos = $request->validate([
            'asunto' => ['required', 'string', 'max:200'],
            'html' => ['required', 'string', 'max:60000'],
        ]);

        if ($request->input('accion') === 'probar') {
            return $this->probarPlantilla($request, $id, $datos);
        }
        Plantillas::guardar($id, $datos['asunto'], $datos['html']);

        return back()->with('status', 'Plantilla guardada. Los próximos correos salen con este diseño.');
    }

    /** Envía al administrador lo que hay en el editor (aún sin guardar), con los datos de ejemplo. */
    private function probarPlantilla(Request $request, string $id, array $datos): RedirectResponse
    {
        $ejemplo = Plantillas::ejemplo($id);
        try {
            Mail::to($request->user()->email)->send(new CorreoLibre(
                Plantillas::llenar($datos['asunto'], $ejemplo, false),
                Plantillas::llenar($datos['html'], $ejemplo),
            ));
        } catch (\Throwable $e) {
            Log::error('Falló la prueba de plantilla', ['plantilla' => $id, 'error' => $e->getMessage()]);

            return back()->withInput()->with('error', 'No salió el correo: '.mb_substr($e->getMessage(), 0, 160));
        }

        return back()->withInput()->with('status', 'Prueba enviada a '.$request->user()->email.' con los datos de ejemplo. Aún no se ha guardado.');
    }

    public function restaurarPlantilla(string $id): RedirectResponse
    {
        abort_unless(isset(Plantillas::EDITABLES[$id]), 404);
        Plantillas::restaurar($id);

        return redirect('/admin/correos/plantilla/'.$id)->with('status', 'Se restauró el diseño original.');
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
