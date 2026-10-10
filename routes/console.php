<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Avisos de renovación del servicio anual. En el servidor necesita el cron de Laravel: * * * * * php artisan schedule:run
Schedule::command('servicio:avisar')->dailyAt('09:00')->timezone('America/Bogota')->withoutOverlapping();
