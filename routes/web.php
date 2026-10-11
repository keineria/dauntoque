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
Route::post('/webhooks/bold', App\Http\Controllers\WebhookBoldController::class);

// Compra de la tarjeta en línea (Colombia, Bold) y página privada del pedido
Route::get('/comprar', [App\Http\Controllers\CompraController::class, 'formulario']);
Route::post('/comprar', [App\Http\Controllers\CompraController::class, 'crear'])->middleware('throttle:10,1');
Route::get('/pedido/{pedido}', [App\Http\Controllers\CompraController::class, 'ver'])->name('pedido.ver');

// Analíticas del perfil público (visitas y clics); público, con CSRF y límite por IP
Route::post('/api/analiticas/registrar', [AnaliticasController::class, 'registrar'])->middleware('throttle:60,1');

// Agenda de citas: la cita del cliente (enlace de cada correo) y "Mis citas" con código al correo
Route::controller(App\Http\Controllers\AgendaPublicaController::class)->prefix('cita/{token}')->group(function () {
    Route::get('/', 'cita');
    Route::get('/horas', 'horasCita')->middleware('throttle:60,1');
    Route::get('/ics', 'ics');
    Route::post('/cambiar', 'cambiar')->middleware('throttle:10,1');
    Route::post('/responder', 'responder')->middleware('throttle:10,1');
    Route::post('/cancelar', 'cancelar')->middleware('throttle:10,1');
});
Route::get('/mis-citas', [App\Http\Controllers\MisCitasController::class, 'mostrar']);
Route::post('/mis-citas/codigo', [App\Http\Controllers\MisCitasController::class, 'enviarCodigo'])->middleware('throttle:5,1');
Route::post('/mis-citas/entrar', [App\Http\Controllers\MisCitasController::class, 'verificar'])->middleware('throttle:10,1');
Route::post('/mis-citas/salir', [App\Http\Controllers\MisCitasController::class, 'salir']);

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
    // Agenda de citas (app adicional)
    Route::controller(App\Http\Controllers\AgendaPanelController::class)->prefix('dashboard/agenda')->group(function () {
        Route::get('/', 'index');
        Route::post('/activar', 'activar');
        Route::post('/citas', 'crear');
        Route::post('/citas/{id}/aprobar', 'aprobar');
        Route::post('/citas/{id}/rechazar', 'rechazar');
        Route::post('/citas/{id}/cancelar', 'cancelar');
        Route::post('/citas/{id}/mover', 'mover');
        Route::post('/citas/{id}/responder', 'responder');
        Route::get('/ajustes', 'ajustes');
        Route::post('/reglas', 'guardarReglas');
        Route::post('/servicios', 'guardarServicio');
        Route::delete('/servicios/{id}', 'borrarServicio');
        Route::post('/horario', 'guardarHorario');
        Route::post('/bloqueos', 'guardarBloqueo');
        Route::delete('/bloqueos/{id}', 'borrarBloqueo');
        Route::get('/google/conectar', 'googleConectar');
        Route::get('/google/volver', 'googleVolver');
        Route::post('/google/desconectar', 'googleDesconectar');
    });
    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::get('/chips', [App\Http\Controllers\AdminChipsController::class, 'index']);
        Route::post('/chips', [App\Http\Controllers\AdminChipsController::class, 'guardar']);
        Route::post('/chips/generar', [App\Http\Controllers\AdminChipsController::class, 'generar']);
        Route::delete('/chips/{chip}', [App\Http\Controllers\AdminChipsController::class, 'eliminar']);
    });
    Route::post('/renovar/{tarjeta}', [App\Http\Controllers\RenovacionController::class, 'crear']);
    Route::post('/api/ia/generar-bio', [App\Http\Controllers\ProfileController::class, 'generarBioIA']);

});

require __DIR__.'/auth.php';

// ==========================================
// RUTAS PÚBLICAS (Catch-All) - DEBEN IR AL FINAL
// ==========================================
Route::get('/{nickname}', [App\Http\Controllers\PublicProfileController::class, 'show']);
Route::get('/{nickname}/vcf', [App\Http\Controllers\PublicProfileController::class, 'descargarVCF']);
Route::get('/{nickname}/agenda', [App\Http\Controllers\AgendaPublicaController::class, 'mostrar']);
Route::get('/{nickname}/agenda/horas', [App\Http\Controllers\AgendaPublicaController::class, 'horas'])->middleware('throttle:60,1');
Route::post('/{nickname}/agenda', [App\Http\Controllers\AgendaPublicaController::class, 'reservar'])->middleware('throttle:10,1');
