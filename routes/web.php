<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AnaliticasController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;

// Página de inicio del portal (Redirige al login)
Route::get('/', function () {
    return redirect('/login');
});

// Ruta libre para leer la tarjeta física (con límite de intentos para evitar barridos de códigos)
Route::get('/activar/{hash}', [App\Http\Controllers\NfcController::class, 'vincularTarjeta'])->middleware('throttle:20,1');

// Avisos de pago de Bold (firmados con x-bold-signature; sin CSRF, ver bootstrap/app.php)
Route::post('/webhooks/bold', [App\Http\Controllers\RenovacionController::class, 'webhookBold']);

// Analíticas del perfil público (visitas y clics); público, con CSRF y límite por IP
Route::post('/api/analiticas/registrar', [AnaliticasController::class, 'registrar'])->middleware('throttle:60,1');

// Todas estas rutas ahora están protegidas por el candado de "auth"
Route::middleware(['auth'])->group(function () {

   Route::get('/perfil/validar-nickname', [App\Http\Controllers\ProfileController::class, 'validarNickname']);
    Route::post('/perfil/{id}/duplicar', [ProfileController::class, 'duplicar']);
    Route::post('/perfil/{id}/activar', [App\Http\Controllers\ProfileController::class, 'activar']);
    Route::delete('/perfil/{id}/eliminar', [App\Http\Controllers\ProfileController::class, 'eliminar']);
    // Editor
    Route::get('/editor', [ProfileController::class, 'index']);
    Route::post('/perfil/guardar', [ProfileController::class, 'store']);

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/perfiles', [DashboardController::class, 'perfiles']);
    Route::get('/dashboard/analiticas', [DashboardController::class, 'analiticas']);
    Route::get('/dashboard/configuracion', [DashboardController::class, 'configuracion']);
    Route::post('/renovar/{tarjeta}', [App\Http\Controllers\RenovacionController::class, 'crear']);
    Route::post('/api/ia/generar-bio', [App\Http\Controllers\ProfileController::class, 'generarBioIA']);

});

require __DIR__.'/auth.php';

// ==========================================
// RUTAS PÚBLICAS (Catch-All) - DEBEN IR AL FINAL
// ==========================================
Route::get('/{nickname}', [App\Http\Controllers\PublicProfileController::class, 'show']);
Route::get('/{nickname}/vcf', [App\Http\Controllers\PublicProfileController::class, 'descargarVCF']);
