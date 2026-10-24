<?php

namespace App\Console\Commands;

use App\Services\Calificame\GoogleNegocio;
use Illuminate\Console\Command;

/** Califícame (una vez al día): actualiza la calificación y las reseñas de los negocios de Google conectados. */
class ActualizarGoogle extends Command
{
    protected $signature = 'calificame:google';

    protected $description = 'Actualiza la calificación y las reseñas de Google de los negocios conectados';

    public function handle(): int
    {
        $this->info('Negocios de Google actualizados: ' . GoogleNegocio::actualizarTodos() . '.');

        return self::SUCCESS;
    }
}
