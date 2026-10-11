<?php

namespace App\Services\Agenda;

use App\Models\AgendaAjuste;
use App\Models\AgendaBloqueo;
use App\Models\AgendaHorario;
use App\Models\AgendaServicio;
use App\Models\Cita;
use App\Services\GoogleCalendar;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/**
 * Horas libres de una agenda: el horario semanal menos las citas activas (con su colchón), los bloqueos,
 * lo ocupado en Google Calendar, la antelación mínima y el límite de días hacia adelante.
 * Todo se calcula en la zona horaria de la agenda y se compara en UTC.
 */
class Disponibilidad
{
    /** @var array<int, array{0: CarbonInterface, 1: CarbonInterface}>|null */
    private ?array $ocupado = null;

    public function __construct(private AgendaAjuste $ajustes, private ?int $ignorarCitaId = null) {}

    /**
     * Horas libres por día local para un servicio: ['2026-10-14' => ['09:00', '09:30', ...], ...].
     * Solo incluye días con al menos una hora libre.
     */
    public function porDias(AgendaServicio $servicio): array
    {
        $zona = $this->ajustes->zona_horaria;
        $hoy = now($zona)->startOfDay();
        $dias = [];

        for ($i = 0; $i <= $this->ajustes->dias_adelante; $i++) {
            $fecha = $hoy->copy()->addDays($i)->format('Y-m-d');
            $horas = $this->horasDelDia($servicio, $fecha);
            if ($horas) {
                $dias[$fecha] = $horas;
            }
        }

        return $dias;
    }

    /** Horas libres ('H:i', hora local) de un día local para un servicio. */
    public function horasDelDia(AgendaServicio $servicio, string $fecha): array
    {
        $zona = $this->ajustes->zona_horaria;
        $dia = Carbon::createFromFormat('Y-m-d', $fecha, $zona)->startOfDay();
        $minimo = now()->addMinutes($this->ajustes->antelacion_min);
        $maximo = now($zona)->startOfDay()->addDays($this->ajustes->dias_adelante + 1);
        if ($dia->gte($maximo)) {
            return [];
        }

        $paso = max(5, (int) $this->ajustes->intervalo_min);
        $largo = $servicio->duracion_min + $servicio->colchon_min;
        $horas = [];

        $franjas = AgendaHorario::where('user_id', $this->ajustes->user_id)->where('dia_semana', $dia->dayOfWeek)->orderBy('desde')->get();
        foreach ($franjas as $franja) {
            $inicio = Carbon::parse($fecha . ' ' . substr($franja->desde, 0, 5), $zona);
            $fin = Carbon::parse($fecha . ' ' . substr($franja->hasta, 0, 5), $zona);

            for ($s = $inicio->copy(); $s->copy()->addMinutes($servicio->duracion_min)->lte($fin); $s->addMinutes($paso)) {
                if ($s->lt($minimo)) {
                    continue;
                }
                if (!$this->choca($s, $s->copy()->addMinutes($largo))) {
                    $horas[] = $s->format('H:i');
                }
            }
        }

        return array_values(array_unique($horas));
    }

    /** ¿Esa hora (cualquier zona) es una de las horas libres del servicio? Lo usan la reserva y el cambio pedido por el cliente. */
    public function estaLibre(AgendaServicio $servicio, CarbonInterface $inicio): bool
    {
        $local = Carbon::instance($inicio)->setTimezone($this->ajustes->zona_horaria);

        return in_array($local->format('H:i'), $this->horasDelDia($servicio, $local->format('Y-m-d')), true);
    }

    /** Sin cruce con otras citas, bloqueos ni Google (sin mirar el horario): para cuando el dueño agenda o mueve a mano. */
    public function sinCruce(CarbonInterface $inicio, CarbonInterface $fin): bool
    {
        return !$this->choca($inicio, $fin);
    }

    private function choca(CarbonInterface $desde, CarbonInterface $hasta): bool
    {
        foreach ($this->ocupado() as [$a, $b]) {
            if ($desde->lt($b) && $hasta->gt($a)) {
                return true;
            }
        }

        return false;
    }

    /** Intervalos ocupados en UTC (se cargan una vez por cálculo). */
    private function ocupado(): array
    {
        if ($this->ocupado !== null) {
            return $this->ocupado;
        }

        $userId = $this->ajustes->user_id;
        $desde = now()->subDay();
        $hasta = now()->addDays($this->ajustes->dias_adelante + 2);
        $ocupado = [];

        $citas = Cita::where('user_id', $userId)->activas()
            ->when($this->ignorarCitaId, fn ($q) => $q->where('id', '!=', $this->ignorarCitaId))
            ->where('termina_en', '>', $desde)->where('inicia_en', '<', $hasta)->get();
        foreach ($citas as $cita) {
            $ocupado[] = [$cita->inicia_en, $cita->termina_en->copy()->addMinutes($cita->colchon_min)];
            // Mientras se decide un cambio, la hora propuesta también queda apartada
            if ($cita->estado === 'cambio' && $cita->propuesta_inicia_en) {
                $ocupado[] = [$cita->propuesta_inicia_en, $cita->propuestaTermina()->addMinutes($cita->colchon_min)];
            }
        }

        foreach (AgendaBloqueo::where('user_id', $userId)->where('hasta', '>', $desde)->where('desde', '<', $hasta)->get() as $b) {
            $ocupado[] = [$b->desde, $b->hasta];
        }

        // Lo ocupado en Google ya incluye los eventos de las citas, así que al mover una cita se ignora su propio evento
        $propio = $this->ignorarCitaId ? Cita::find($this->ignorarCitaId) : null;
        foreach (GoogleCalendar::ocupado($this->ajustes, $desde, $hasta) as [$a, $b]) {
            if ($propio && $a->eq($propio->inicia_en) && $b->eq($propio->termina_en)) {
                continue;
            }
            $ocupado[] = [$a, $b];
        }

        return $this->ocupado = $ocupado;
    }
}
