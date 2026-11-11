<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AnaliticasController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;

// Inicio de dauntoque.com: la página del sitio de ventas (sitio:generar --dentro-de-laravel) si existe;
// si no, el portal manda al login. (Si el servidor ya sirve public/index.html primero, esto ni se usa.)
Route::get('/', function () {
    $inicio = public_path('index.html');

    return is_file($inicio) ? response()->file($inicio, ['Content-Type' => 'text/html; charset=UTF-8']) : redirect('/login');
});

// Mapa del sitio de los perfiles públicos (el del sitio de ventas es el archivo sitemap.xml que genera sitio:generar)
Route::get('/sitemap-perfiles.xml', App\Http\Controllers\SitemapPerfilesController::class);

// Ruta libre para leer la tarjeta física (con límite de intentos para evitar barridos de códigos)
Route::get('/activar/{hash}', [App\Http\Controllers\NfcController::class, 'vincularTarjeta'])->middleware('throttle:20,1');

// Avisos de pago de Bold (firmados con x-bold-signature; sin CSRF, ver bootstrap/app.php)
Route::post('/webhooks/bold', App\Http\Controllers\WebhookBoldController::class);

// Centro de mando: píxeles conectados (/etiquetas.js) y medición propia de visitas del sitio de ventas.
// Sin sesión ni galletas de Laravel: así Cloudflare puede guardar el script en caché y las visitas no crean sesiones.
Route::withoutMiddleware([
    \Illuminate\Session\Middleware\StartSession::class,
    \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    \Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class,
    \App\Http\Middleware\CambiarClaveTemporal::class,
])->controller(App\Http\Controllers\EtiquetasController::class)->group(function () {
    Route::get('/etiquetas.js', 'script');
    Route::post('/etiquetas/visita', 'visita')->middleware('throttle:120,1');
});

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
    Route::post('/tarjeta/activar', App\Http\Controllers\ActivarTarjetaController::class)->middleware('throttle:10,1');
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
    // Apps adicionales
    Route::get('/dashboard/apps', [App\Http\Controllers\AppsController::class, 'index']);
    // Califícame (app adicional): calificaciones de los clientes y qué comentarios salen en el perfil
    Route::controller(App\Http\Controllers\CalificamePanelController::class)->prefix('dashboard/calificame')->group(function () {
        Route::get('/', 'index');
        Route::post('/activar', 'activar');
        Route::post('/ajustes', 'ajustes');
        Route::post('/{id}/visible', 'visible')->whereNumber('id');
    });
    // Música (app adicional): canción destacada y lista de Spotify en el perfil
    Route::controller(App\Http\Controllers\MusicaPanelController::class)->prefix('dashboard/musica')->group(function () {
        Route::get('/', 'index');
        Route::post('/activar', 'activar');
        Route::post('/ajustes', 'ajustes');
    });
    // Agenda de citas (app adicional)
    Route::controller(App\Http\Controllers\AgendaPanelController::class)->prefix('dashboard/agenda')->group(function () {
        Route::get('/', 'index');
        Route::post('/activar', 'activar');
        Route::post('/boton', 'boton');
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
    // Mi cuenta: apariencia del panel, contraseña y verificación en dos pasos
    Route::controller(App\Http\Controllers\CuentaController::class)->prefix('dashboard/cuenta')->group(function () {
        Route::get('/', 'index');
        Route::post('/apariencia', 'guardarTema')->middleware('throttle:60,1');
        Route::post('/clave', 'cambiarClave')->middleware('throttle:10,1');
        Route::post('/dos-pasos', 'iniciarDosPasos');
        Route::post('/dos-pasos/confirmar', 'confirmarDosPasos')->middleware('throttle:10,1');
        Route::post('/dos-pasos/desactivar', 'desactivarDosPasos')->middleware('throttle:10,1');
        Route::post('/dos-pasos/codigos', 'nuevosCodigos')->middleware('throttle:10,1');
    });
    // Ayuda: tickets y chat con un agente
    Route::controller(App\Http\Controllers\SoporteController::class)->prefix('dashboard/ayuda')->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'crear')->middleware('throttle:10,1');
        Route::get('/{id}', 'ver')->whereNumber('id');
        Route::get('/{id}/mensajes', 'mensajes')->whereNumber('id')->middleware('throttle:120,1');
        Route::post('/{id}/mensajes', 'escribir')->whereNumber('id')->middleware('throttle:30,1');
        Route::post('/{id}/cerrar', 'cerrar')->whereNumber('id');
    });
    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::get('/', [App\Http\Controllers\AdminController::class, 'inicio']);
        Route::controller(App\Http\Controllers\AdminClientesController::class)->prefix('clientes/{usuario}')->whereNumber('usuario')->group(function () {
            Route::get('/', 'ver');
            Route::put('/', 'guardar');
            Route::put('/tarjetas/{tarjeta}', 'tarjeta')->whereNumber('tarjeta');
        });
        Route::post('/clientes/{usuario}/reenviar-codigo', [App\Http\Controllers\AdminController::class, 'reenviarCodigo'])->whereNumber('usuario')->middleware('throttle:10,1');
        Route::controller(App\Http\Controllers\AdminSinUsoController::class)->prefix('sin-uso')->group(function () {
            Route::get('/', 'index');
            Route::post('/enviar-todos', 'enviarTodos');
            Route::post('/{usuario}/enviar', 'enviar')->whereNumber('usuario');
        });
        Route::get('/carritos', [App\Http\Controllers\AdminController::class, 'carritos']);
        Route::post('/carritos/promo', [App\Http\Controllers\AdminController::class, 'guardarPromo']);
        Route::post('/carritos/enviar-todos', [App\Http\Controllers\AdminController::class, 'enviarPromoTodos']);
        Route::post('/carritos/{id}/enviar', [App\Http\Controllers\AdminController::class, 'enviarPromo'])->whereNumber('id');
        Route::get('/correos', [App\Http\Controllers\AdminController::class, 'correos']);
        Route::post('/correos', [App\Http\Controllers\AdminController::class, 'guardarCorreos']);
        Route::post('/correos/prueba', [App\Http\Controllers\AdminController::class, 'probarCorreo'])->middleware('throttle:5,1');
        Route::post('/correos/smtp', [App\Http\Controllers\AdminController::class, 'guardarSmtp']);
        Route::get('/correos/plantilla/{id}', [App\Http\Controllers\AdminController::class, 'plantilla']);
        Route::post('/correos/plantilla/{id}', [App\Http\Controllers\AdminController::class, 'guardarPlantilla'])->middleware('throttle:20,1');
        Route::post('/correos/plantilla/{id}/restaurar', [App\Http\Controllers\AdminController::class, 'restaurarPlantilla']);
        Route::controller(App\Http\Controllers\AdminSoporteController::class)->prefix('soporte')->group(function () {
            Route::get('/', 'index');
            Route::get('/resumen', 'resumen');
            Route::post('/tomar', 'tomarSiguiente');
            Route::get('/{id}', 'ver')->whereNumber('id');
            Route::get('/{id}/mensajes', 'mensajes')->whereNumber('id');
            Route::post('/{id}/mensajes', 'responder')->whereNumber('id');
            Route::post('/{id}/cerrar', 'cerrar')->whereNumber('id');
        });
        // Centro de mando: pagos y marketing
        Route::get('/pagos', [App\Http\Controllers\AdminPagosController::class, 'index']);
        Route::post('/pagos/probar-bold', [App\Http\Controllers\AdminPagosController::class, 'probarBold'])->middleware('throttle:6,1');
        Route::controller(App\Http\Controllers\AdminMarketingController::class)->group(function () {
            Route::get('/pixeles', 'pixeles');
            Route::post('/pixeles', 'guardarPixeles');
            Route::get('/campanas', 'campanas');
            Route::post('/campanas', 'guardarCampana');
            Route::put('/campanas/{campana}', 'actualizarCampana')->whereNumber('campana');
            Route::delete('/campanas/{campana}', 'eliminarCampana')->whereNumber('campana');
            Route::get('/rendimiento', 'rendimiento');
        });
        Route::get('/chips', [App\Http\Controllers\AdminChipsController::class, 'index']);
        Route::post('/chips', [App\Http\Controllers\AdminChipsController::class, 'guardar']);
        Route::post('/chips/generar', [App\Http\Controllers\AdminChipsController::class, 'generar']);
        Route::get('/chips/exportar', [App\Http\Controllers\AdminChipsController::class, 'exportar']);
        Route::post('/chips/{chip}/reenviar', [App\Http\Controllers\AdminChipsController::class, 'reenviar'])->middleware('throttle:10,1');
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
Route::get('/{nickname}/califica', [App\Http\Controllers\CalificamePublicaController::class, 'mostrar']);
Route::post('/{nickname}/califica', [App\Http\Controllers\CalificamePublicaController::class, 'guardar'])->middleware('throttle:10,1');
Route::get('/{nickname}/agenda', [App\Http\Controllers\AgendaPublicaController::class, 'mostrar']);
Route::get('/{nickname}/agenda/horas', [App\Http\Controllers\AgendaPublicaController::class, 'horas'])->middleware('throttle:60,1');
Route::post('/{nickname}/agenda', [App\Http\Controllers\AgendaPublicaController::class, 'reservar'])->middleware('throttle:10,1');
