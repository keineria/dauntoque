<?php

namespace App\Console\Commands;

use App\Mail\ServicioPorVencer;
use App\Models\Tarjeta;
use App\Support\Precios;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/** Manda por correo los avisos de renovación del servicio anual (corre una vez al día). */
class AvisarServicio extends Command
{
    protected $signature = 'servicio:avisar {--simular : Muestra a quién se le escribiría, sin mandar correos}';

    protected $description = 'Avisa por correo cuando el año de servicio de una tarjeta está por vencer o venció';

    public function handle(): int
    {
        $enviados = 0;
        // Solo tarjetas dentro de la ventana de avisos; las que terminaron la gracia hace más de 30 días ya no se molestan
        $tarjetas = Tarjeta::with('user')
            ->where('vence_en', '<=', now()->addDays(config('servicio.aviso_dias') + 1))
            ->where('vence_en', '>=', now()->subDays(config('servicio.gracia_dias') + 30))
            ->get();

        foreach ($tarjetas as $tarjeta) {
            $etapa = $tarjeta->avisoPendiente();
            $correo = $tarjeta->user?->email;
            if ($etapa === null || ! $correo) {
                continue;
            }
            $linea = "$etapa · $correo · chip ···" . substr($tarjeta->nfc_uid, -6);
            if ($this->option('simular')) {
                $this->line("[simulado] $linea");
                continue;
            }
            try {
                Mail::to($correo)->send(new ServicioPorVencer($tarjeta, $etapa, Precios::renovacion($tarjeta->user?->paisFacturacion(), $tarjeta->user)));
                $tarjeta->forceFill(['aviso_etapa' => $etapa])->save();
                $enviados++;
                $this->line($linea);
            } catch (\Throwable $e) {
                Log::error('No se pudo mandar el aviso de renovación', ['tarjeta' => $tarjeta->id, 'error' => $e->getMessage()]);
                $this->error("Falló: $linea");
            }
        }
        $this->info("$enviados avisos enviados.");

        return self::SUCCESS;
    }
}
