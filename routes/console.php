<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Avisos de renovación del servicio anual. En el servidor necesita el cron de Laravel: * * * * * php artisan schedule:run
Schedule::command('servicio:avisar')->dailyAt('09:00')->timezone('America/Bogota')->withoutOverlapping();

// Recordatorios de la agenda de citas (24 horas y 2 horas antes). Usa el mismo cron de Laravel.
Schedule::command('citas:recordar')->everyFiveMinutes()->withoutOverlapping();

// Califícame: pide la calificación por correo después de cada cita realizada. Usa el mismo cron de Laravel.
Schedule::command('calificame:pedir')->everyFifteenMinutes()->withoutOverlapping();

// Califícame: calificación y reseñas de Google de los negocios conectados, una vez al día.
Schedule::command('calificame:google')->dailyAt('05:00')->timezone('America/Bogota')->withoutOverlapping();
