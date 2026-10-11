<?php

namespace App\Console\Commands;

use App\Models\Cita;
use App\Services\Agenda\Citas;
use Illuminate\Console\Command;

/**
 * Recordatorios de la agenda (corre cada 5 minutos): correo 24 horas y 2 horas antes de cada cita confirmada.
 * Si la cita se reservó con menos tiempo, ese recordatorio no se manda (ya tiene su confirmación reciente).
 * También cierra lo que quedó sin respuesta: solicitudes pendientes y cambios cuya hora ya pasó.
 */
class RecordarCitas extends Command
{
    protected $signature = 'citas:recordar';

    protected $description = 'Manda los recordatorios de las citas y cierra solicitudes vencidas';

    public function handle(): int
    {
        $enviados = 0;

        foreach (['24h' => 24, '2h' => 2] as $clave => $horas) {
            $campo = "recordado_{$clave}_en";
            $citas = Cita::whereIn('estado', ['confirmada', 'cambio'])->whereNull($campo)
                ->where('inicia_en', '>', now())->where('inicia_en', '<=', now()->addHours($horas))->get();

            foreach ($citas as $cita) {
                // Reservada hace poco (menos del tiempo del recordatorio + 1 hora): no hace falta recordarla
                $sirve = $cita->created_at->lt($cita->inicia_en->copy()->subHours($horas + 1));
                // El de 24 horas no se manda si ya toca el de 2 horas
                $sirve = $sirve && ($horas === 2 || $cita->inicia_en->gt(now()->addHours(2)));
                $cita->forceFill([$campo => now()])->save();
                if ($sirve) {
                    Citas::alCliente($cita, 'recordatorio');
                    $enviados++;
                }
            }
        }

        // Solicitudes que el dueño no alcanzó a aprobar antes de la hora
        $vencidas = Cita::where('estado', 'pendiente')->where('inicia_en', '<=', now())->get();
        foreach ($vencidas as $cita) {
            $cita->update(['estado' => 'cancelada', 'cancelada_por' => 'sistema', 'motivo' => null]);
            $cita->registrar('vencida', 'sistema');
        }

        // Cambios sin respuesta cuya nueva hora ya pasó: la cita sigue a su hora original
        $cambios = Cita::where('estado', 'cambio')->where('propuesta_inicia_en', '<=', now())->get();
        foreach ($cambios as $cita) {
            $cita->update(['estado' => 'confirmada', 'propuesta_inicia_en' => null, 'propuesta_por' => null]);
            $cita->registrar('cambio_vencido', 'sistema');
        }

        $this->info("Recordatorios enviados: $enviados. Solicitudes vencidas: {$vencidas->count()}. Cambios vencidos: {$cambios->count()}.");

        return self::SUCCESS;
    }
}
