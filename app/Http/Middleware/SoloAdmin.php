<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Solo los correos de ADMIN_CORREOS (config/chips.php) entran a /admin. */
class SoloAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->esAdmin(), 404);

        return $next($request);
    }
}
