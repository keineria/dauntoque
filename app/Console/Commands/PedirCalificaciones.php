<?php

namespace App\Console\Commands;

use App\Services\Calificame\Calificame;
use Illuminate\Console\Command;

/** Califícame (corre cada 15 minutos): después de cada cita realizada, le pide al cliente por correo que califique la atención. */
class PedirCalificaciones extends Command
{
    protected $signature = 'calificame:pedir';

    protected $description = 'Pide la calificación por correo a los clientes de las citas que ya terminaron';

    public function handle(): int
    {
        $this->info('Calificaciones pedidas: ' . Calificame::pedirTrasCitas() . '.');

        return self::SUCCESS;
    }
}
