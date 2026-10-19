<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\DosPasos;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/** Segundo paso del inicio de sesión: código de la app autenticadora o un código de respaldo. */
class DosPasosController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        return $this->pendiente($request) ? view('auth.dos-pasos') : redirect()->route('login');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate(['codigo' => ['required', 'string', 'max:20']]);
        $usuario = $this->pendiente($request);
        if (! $usuario) {
            return redirect()->route('login')->with('status', 'Pasó mucho tiempo. Vuelve a escribir tu correo y contraseña.');
        }
        if (! DosPasos::verificar($usuario, $request->input('codigo'))) {
            return back()->withErrors(['codigo' => 'El código no es correcto o ya venció. Escribe el que muestra ahora tu app.']);
        }

        $recordar = (bool) $request->session()->pull('dos_pasos.recordar');
        $request->session()->forget('dos_pasos');
        Auth::login($usuario, $recordar);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /** Usuario que pasó la contraseña hace menos de 10 minutos y aún debe el código. */
    private function pendiente(Request $request): ?User
    {
        $datos = $request->session()->get('dos_pasos');
        if (! is_array($datos) || ($datos['hasta'] ?? 0) < now()->timestamp) {
            return null;
        }
        $usuario = User::find($datos['id'] ?? null);

        return $usuario?->tieneDosPasos() ? $usuario : null;
    }
}
