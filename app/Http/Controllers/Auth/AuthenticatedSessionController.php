<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        // Con verificación en dos pasos: la sesión no se abre hasta escribir el código de la app
        $usuario = Auth::user();
        if ($usuario->tieneDosPasos()) {
            Auth::guard('web')->logout();
            $request->session()->regenerate();
            $request->session()->put('dos_pasos', ['id' => $usuario->id, 'recordar' => $request->boolean('remember'), 'hasta' => now()->addMinutes(10)->timestamp]);

            return redirect('/dos-pasos');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
