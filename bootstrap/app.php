<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Bold no envía token CSRF: el webhook se valida con su firma
        $middleware->validateCsrfTokens(except: ['webhooks/bold']);
        $middleware->alias(['admin' => \App\Http\Middleware\SoloAdmin::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Fotos muy pesadas: mensaje en español en vez de "POST data is too large"
        $exceptions->render(function (PostTooLargeException $e, Request $request) {
            $mensaje = 'Las fotos pesan demasiado para guardarlas juntas. Usa imágenes más livianas o sube menos a la vez.';

            return $request->expectsJson()
                ? response()->json(['status' => 'error', 'message' => $mensaje], 413)
                : back()->with('error', $mensaje);
        });
    })->create();
