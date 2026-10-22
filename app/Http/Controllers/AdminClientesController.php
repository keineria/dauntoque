<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\Profile;
use App\Models\SoporteConversacion;
use App\Models\Tarjeta;
use App\Models\User;
use App\Services\ClientesAdmin;
use App\Services\MetricasAdmin;
use App\Support\Precios;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Ficha de un cliente en el administrador: ver todo lo suyo y corregir sus datos. */
class AdminClientesController extends Controller
{
    public function ver(User $usuario): View
    {
        $pagina = User::whereKey($usuario->id)->paginate(1);
        $fila = ClientesAdmin::filas($pagina)[0];
        $pais = Profile::activoDe($usuario->id)?->pais;

        return view('admin.cliente', [
            'u' => $usuario,
            'c' => $fila,
            'tarjetas' => Tarjeta::where('user_id', $usuario->id)->latest('activada_en')->get()
                ->map(fn (Tarjeta $t) => ['t' => $t, 'vinculada' => Profile::where('user_id', $usuario->id)->where('nfc_uid', $t->nfc_uid)->exists()] + $t->servicio()),
            'pedidos' => Pedido::with('chip')->where(fn ($q) => $q->where('user_id', $usuario->id)->orWhere('correo', $usuario->email))->latest('id')->get(),
            'perfiles' => Profile::where('user_id', $usuario->id)->orderBy('id')->get(),
            'conversaciones' => SoporteConversacion::where('user_id', $usuario->id)->latest('id')->limit(10)->get(),
            'precioGeneral' => Precios::renovacion($pais),
            'precioActual' => Precios::renovacion($pais, $usuario),
            'moneda' => Precios::moneda($pais),
            'sinUso' => ClientesAdmin::sinUso()->whereKey($usuario->id)->exists(),
            'meses' => MetricasAdmin::MESES_SIN_USO,
            'chipPendiente' => ClientesAdmin::chipSinActivar($usuario),
            'volver' => $this->volver(),
        ]);
    }

    /** Nombre, correo de la cuenta y precio especial de renovación. */
    public function guardar(Request $request, User $usuario): RedirectResponse
    {
        $datos = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($usuario->id)],
            'precio_renovacion' => ['nullable', 'integer', 'min:0', 'max:100000000'],
        ], [
            'email.unique' => 'Ese correo ya es de otra cuenta.',
        ], ['name' => 'nombre', 'email' => 'correo', 'precio_renovacion' => 'precio de renovación']);

        $datos['email'] = strtolower(trim($datos['email']));
        $cambioCorreo = $datos['email'] !== $usuario->email;
        $usuario->forceFill($datos)->save();

        return back()->with('status', 'Datos guardados.'.($cambioCorreo ? ' Desde ahora entra con '.$usuario->email.'.' : ''));
    }

    /** Fecha de vencimiento del servicio de una tarjeta (por ejemplo, si renovó por WhatsApp o para dar días de cortesía). */
    public function tarjeta(Request $request, User $usuario, Tarjeta $tarjeta): RedirectResponse
    {
        abort_unless($tarjeta->user_id === $usuario->id, 404);

        if ($request->input('accion') === 'anio') {
            $tarjeta->renovar();

            return back()->with('status', 'Se sumó un año. Ahora vence el '.$tarjeta->vence_en->format('d/m/Y').'.');
        }

        $datos = $request->validate([
            'vence_en' => ['required', 'date', 'after:'.$tarjeta->activada_en->toDateString()],
        ], ['vence_en.after' => 'El vencimiento debe ser después de la activación.'], ['vence_en' => 'vencimiento']);

        // Fin del día en Colombia; los avisos de renovación vuelven a empezar con la fecha nueva
        $tarjeta->forceFill([
            'vence_en' => Carbon::parse($datos['vence_en'])->setTime(23, 59, 59),
            'aviso_etapa' => null,
        ])->save();

        return back()->with('status', 'Vencimiento cambiado al '.$tarjeta->vence_en->format('d/m/Y').'.');
    }

    /** Vuelve a la lista con los mismos filtros con los que se abrió la ficha (?desde=/admin?lista=…). */
    private function volver(): string
    {
        $desde = (string) request()->query('desde');

        return preg_match('~^/admin(/sin-uso)?(\?[^\s]*)?$~', $desde) ? $desde : '/admin#clientes';
    }
}
