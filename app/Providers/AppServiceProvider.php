<?php

namespace App\Providers;

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
    }
}
