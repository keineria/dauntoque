<?php

namespace App\Providers;

use App\Support\Correos;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Carbon;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Fechas de Carbon (diffForHumans, translatedFormat) en el idioma de la app (APP_LOCALE=es)
        Carbon::setLocale(config('app.locale'));

        // dauntoque.com/nombre llega por portal.php desde este servidor: así las analíticas ven la IP del visitante
        TrustProxies::at(config('perfiles.proxies'));

        // Correos de notificación: remitente y avisos del administrador; las plantillas apagadas no se envían
        Correos::aplicarAjustes();
        Event::listen(MessageSending::class, [Correos::class, 'filtrar']);
    }
}
