<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Quien entra con una contraseña temporal la cambia antes de usar el panel. */
class CambiarClaveTemporal
{
    public function handle(Request $request, Closure $next): Response
    {
        $privada = $request->is('dashboard*', 'admin*', 'editor*', 'perfil/*', 'api/ia/*', 'renovar/*');
        if ($privada && $request->user()?->debe_cambiar_clave && ! $request->is('dashboard/cuenta*')) {
            return $request->expectsJson()
                ? response()->json(['status' => 'error', 'message' => 'Cambia tu contraseña temporal para continuar.'], 403)
                : redirect('/dashboard/cuenta')->with('error', 'Por seguridad, cambia tu contraseña temporal antes de seguir.');
        }

        return $next($request);
    }
}
