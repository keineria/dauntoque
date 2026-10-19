<?php

namespace App\Http\Controllers;

use App\Support\DosPasos;
use App\Support\Totp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/** Mi cuenta: cambiar la contraseña y la verificación en dos pasos (opcional para cualquier usuario). */
class CuentaController extends Controller
{
    public function index(Request $request): View
    {
        $usuario = $request->user();
        // Secreto nuevo mientras se configura la app (aún no se guarda en la cuenta)
        $secreto = $usuario->tieneDosPasos() ? null : $request->session()->get('dos_pasos_nuevo');

        return view('dashboard.cuenta', [
            'usuario' => $usuario,
            'secreto' => $secreto,
            'enlaceApp' => $secreto ? Totp::enlace($secreto, $usuario->email) : null,
            'respaldoRestante' => DosPasos::respaldoRestante($usuario),
            'codigos' => $request->session()->get('codigos_respaldo'),
        ]);
    }

    public function cambiarClave(Request $request): RedirectResponse
    {
        $request->validateWithBag('clave', [
            'actual' => ['required', 'current_password'],
            'nueva' => ['required', 'confirmed', 'different:actual', Password::min(10)->letters()->numbers()],
        ], [
            'actual.current_password' => 'La contraseña actual no es correcta.',
            'nueva.different' => 'La nueva contraseña debe ser distinta de la actual.',
        ], ['actual' => 'contraseña actual', 'nueva' => 'nueva contraseña']);

        $request->user()->forceFill(['password' => Hash::make($request->input('nueva')), 'debe_cambiar_clave' => false])->save();

        return redirect('/dashboard/cuenta')->with('status', 'Contraseña actualizada.');
    }

    /** Empieza a configurar la app autenticadora: muestra el QR con un secreto nuevo. */
    public function iniciarDosPasos(Request $request): RedirectResponse
    {
        abort_if($request->user()->tieneDosPasos(), 422, 'La verificación en dos pasos ya está activa.');
        $request->session()->put('dos_pasos_nuevo', Totp::secreto());

        return redirect('/dashboard/cuenta#dos-pasos');
    }

    /** Activa la verificación cuando el primer código de la app coincide. */
    public function confirmarDosPasos(Request $request): RedirectResponse
    {
        $request->validateWithBag('dosPasos', ['codigo' => ['required', 'string', 'max:10']]);
        $secreto = $request->session()->get('dos_pasos_nuevo');
        if (! $secreto || ! Totp::verificar($secreto, $request->input('codigo'))) {
            return back()->withErrors(['codigo' => 'El código no coincide. Revisa que la hora de tu celular esté bien y escribe el que aparece ahora.'], 'dosPasos');
        }

        $codigos = DosPasos::activar($request->user(), $secreto);
        $request->session()->forget('dos_pasos_nuevo');

        return redirect('/dashboard/cuenta#dos-pasos')->with('codigos_respaldo', $codigos)->with('status', 'Verificación en dos pasos activada.');
    }

    public function desactivarDosPasos(Request $request): RedirectResponse
    {
        $request->validateWithBag('dosPasos', ['clave' => ['required', 'current_password']], ['clave.current_password' => 'La contraseña no es correcta.']);
        DosPasos::desactivar($request->user());

        return redirect('/dashboard/cuenta#dos-pasos')->with('status', 'Verificación en dos pasos desactivada.');
    }

    public function nuevosCodigos(Request $request): RedirectResponse
    {
        $request->validateWithBag('dosPasos', ['clave' => ['required', 'current_password']], ['clave.current_password' => 'La contraseña no es correcta.']);
        abort_unless($request->user()->tieneDosPasos(), 422);

        return redirect('/dashboard/cuenta#dos-pasos')->with('codigos_respaldo', DosPasos::renovarRespaldo($request->user()));
    }
}
