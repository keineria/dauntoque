<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;

// Página de inicio del portal (Redirige al login)
Route::get('/', function () {
    return redirect('/login');
});

// Ruta libre para leer la tarjeta física
Route::get('/activar/{hash}', [App\Http\Controllers\NfcController::class, 'vincularTarjeta']);

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
    Route::post('/api/ia/generar-bio', [App\Http\Controllers\ProfileController::class, 'generarBioIA']);
    
});

require __DIR__.'/auth.php';

// ==========================================
// RUTAS PÚBLICAS (Catch-All) - DEBEN IR AL FINAL
// ==========================================
Route::get('/{nickname}', [App\Http\Controllers\PublicProfileController::class, 'show']);
Route::get('/{nickname}/vcf', [App\Http\Controllers\PublicProfileController::class, 'descargarVCF']);