<?php

namespace App\Services\Agenda;

use App\Mail\AgendaCorreo;
use App\Models\AgendaAjuste;
use App\Models\AgendaServicio;
use App\Models\Cita;
use App\Models\Profile;
use App\Services\GoogleCalendar;
use App\Support\MediosPago;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Todo lo que le pasa a una cita: reservar, aprobar, rechazar, pedir y responder un cambio de hora y cancelar.
 * Cada paso deja historial, avisa por correo a la otra parte y mantiene al día Google Calendar.
 * "por" es quién hace el paso: 'cliente' o 'duenio'.
 */
class Citas
{
    /** Reserva una hora. El cliente solo puede tomar horas libres del horario; el dueño puede agendar a cualquier hora sin cruces. */
    public static function reservar(AgendaServicio $servicio, CarbonInterface $inicio, array $datos, string $idioma, string $por = 'cliente'): Cita
    {
        $cita = DB::transaction(function () use ($servicio, $inicio, $datos, $idioma, $por) {
            // Un turno a la vez por agenda: dos personas no pueden tomar la misma hora
            $ajustes = AgendaAjuste::where('user_id', $servicio->user_id)->lockForUpdate()->firstOrFail();
            $inicio = Carbon::instance($inicio)->utc();
            $fin = $inicio->copy()->addMinutes($servicio->duracion_min);
            $disponible = new Disponibilidad($ajustes);
            $libre = $por === 'duenio'
                ? $disponible->sinCruce($inicio, $fin->copy()->addMinutes($servicio->colchon_min))
                : $disponible->estaLibre($servicio, $inicio);
            if (!$libre) {
                throw ValidationException::withMessages(['hora' => __('agenda.hora_ocupada')]);
            }

            $cita = Cita::create([
                'user_id' => $servicio->user_id,
                'servicio_id' => $servicio->id,
                'servicio_nombre' => $servicio->nombre,
                'colchon_min' => $servicio->colchon_min,
                'cliente_nombre' => $datos['nombre'],
                'cliente_correo' => $datos['correo'],
                'cliente_whatsapp' => $datos['whatsapp'] ?? null,
                'nota' => $datos['nota'] ?? null,
                'inicia_en' => $inicio,
                'termina_en' => $fin,
                'estado' => $por === 'duenio' || !$servicio->requiere_aprobacion ? 'confirmada' : 'pendiente',
                'idioma' => $idioma === 'en' ? 'en' : 'es',
            ]);
            $cita->registrar('creada', $por);

            return $cita;
        });

        if ($cita->estado === 'confirmada') {
            GoogleCalendar::guardarEvento($cita);
            self::alCliente($cita, 'confirmada');
        } else {
            self::alCliente($cita, 'recibida');
        }
        if ($por === 'cliente') {
            self::alDuenio($cita, 'nueva');
        }

        return $cita;
    }

    public static function aprobar(Cita $cita): void
    {
        abort_unless($cita->estado === 'pendiente', 422);
        $cita->update(['estado' => 'confirmada']);
        $cita->registrar('aprobada', 'duenio');
        GoogleCalendar::guardarEvento($cita);
        self::alCliente($cita, 'confirmada');
    }

    public static function rechazar(Cita $cita, ?string $motivo): void
    {
        abort_unless($cita->estado === 'pendiente', 422);
        $cita->update(['estado' => 'cancelada', 'cancelada_por' => 'duenio', 'motivo' => $motivo]);
        $cita->registrar('rechazada', 'duenio', array_filter(['motivo' => $motivo]));
        self::alCliente($cita, 'rechazada');
    }

    /**
     * Propone una nueva hora para una cita confirmada. Queda en "cambio" hasta que la otra parte acepte;
     * mientras tanto se mantiene la hora original. El cliente solo puede elegir horas libres del horario.
     */
    public static function proponerCambio(Cita $cita, CarbonInterface $nueva, string $por): void
    {
        abort_unless($cita->estado === 'confirmada', 422);

        DB::transaction(function () use ($cita, $nueva, $por) {
            $ajustes = AgendaAjuste::where('user_id', $cita->user_id)->lockForUpdate()->firstOrFail();
            $nueva = Carbon::instance($nueva)->utc();
            $disponible = new Disponibilidad($ajustes, $cita->id);
            $servicio = $cita->servicio ?? new AgendaServicio(['duracion_min' => $cita->duracionMin(), 'colchon_min' => $cita->colchon_min]);
            $libre = $por === 'duenio' || !$cita->servicio
                ? $disponible->sinCruce($nueva, $nueva->copy()->addMinutes($cita->duracionMin() + $cita->colchon_min))
                : $disponible->estaLibre($servicio, $nueva);
            if (!$libre || $nueva->isPast() || $nueva->eq($cita->inicia_en)) {
                throw ValidationException::withMessages(['hora' => __('agenda.hora_ocupada')]);
            }

            $cita->update(['estado' => 'cambio', 'propuesta_inicia_en' => $nueva, 'propuesta_por' => $por]);
            $cita->registrar('cambio_propuesto', $por, ['de' => $cita->inicia_en->toIso8601String(), 'a' => $nueva->toIso8601String()]);
        });

        $por === 'duenio' ? self::alCliente($cita, 'cambio_propuesto') : self::alDuenio($cita, 'cambio_pedido');
    }

    /** La otra parte acepta o rechaza la nueva hora. Si rechaza, la cita sigue confirmada a la hora original. */
    public static function responderCambio(Cita $cita, bool $acepta, string $por): void
    {
        abort_unless($cita->estado === 'cambio' && $cita->propuesta_por !== $por, 422);

        if ($acepta) {
            $duracion = $cita->duracionMin();
            $cita->update([
                'estado' => 'confirmada',
                'inicia_en' => $cita->propuesta_inicia_en,
                'termina_en' => $cita->propuesta_inicia_en->copy()->addMinutes($duracion),
                'propuesta_inicia_en' => null, 'propuesta_por' => null,
                'recordado_24h_en' => null, 'recordado_2h_en' => null,
            ]);
            $cita->registrar('cambio_aceptado', $por);
            GoogleCalendar::guardarEvento($cita);
        } else {
            $cita->update(['estado' => 'confirmada', 'propuesta_inicia_en' => null, 'propuesta_por' => null]);
            $cita->registrar('cambio_rechazado', $por);
        }

        $tipo = $acepta ? 'cambio_aceptado' : 'cambio_rechazado';
        $por === 'duenio' ? self::alCliente($cita, $tipo) : self::alDuenio($cita, $tipo);
        // Quien aceptó también recibe la cita al día (con el calendario nuevo)
        if ($acepta && $por === 'cliente') {
            self::alCliente($cita, 'confirmada');
        }
    }

    public static function cancelar(Cita $cita, string $por, ?string $motivo = null): void
    {
        abort_unless($cita->activa(), 422);
        $cita->update(['estado' => 'cancelada', 'cancelada_por' => $por, 'motivo' => $motivo, 'propuesta_inicia_en' => null, 'propuesta_por' => null]);
        $cita->registrar('cancelada', $por, array_filter(['motivo' => $motivo]));
        GoogleCalendar::borrarEvento($cita);

        $por === 'duenio' ? self::alCliente($cita, 'cancelada') : self::alDuenio($cita, 'cancelada');
    }

    /** Enlace de WhatsApp al cliente con el mensaje que el dueño configuró ({nombre} {servicio} {fecha} {hora} {enlace}). */
    public static function enlaceWhatsapp(Cita $cita, AgendaAjuste $ajustes): ?string
    {
        $numero = preg_replace('/\D/', '', (string) $cita->cliente_whatsapp);
        if (strlen($numero) < 7) {
            return null;
        }
        // Número local de 10 dígitos: se le pone el indicativo del país de la agenda
        if (strlen($numero) === 10) {
            $numero = (MediosPago::pais(Profile::activoDe($cita->user_id)?->pais) === 'US' ? '1' : '57') . $numero;
        }

        $texto = strtr($ajustes->mensaje_whatsapp ?: config('agenda.mensaje_whatsapp'), [
            '{nombre}' => strtok($cita->cliente_nombre, ' '),
            '{servicio}' => $cita->servicio_nombre,
            '{fecha}' => $cita->fechaLocal($ajustes->zona_horaria),
            '{hora}' => $cita->horaLocal($ajustes->zona_horaria),
            '{enlace}' => $cita->enlace(),
        ]);

        return 'https://wa.me/' . $numero . '?text=' . rawurlencode($texto);
    }

    public static function alCliente(Cita $cita, string $tipo): void
    {
        self::enviar($cita->cliente_correo, (new AgendaCorreo($tipo, $cita))->locale($cita->idioma));
    }

    public static function alDuenio(Cita $cita, string $tipo): void
    {
        self::enviar($cita->user?->email, (new AgendaCorreo($tipo, $cita, paraDuenio: true))->locale('es'));
    }

    /** Un correo que no sale no debe tumbar la reserva: se registra y se sigue. */
    private static function enviar(?string $correo, AgendaCorreo $correoAgenda): void
    {
        if (!$correo) {
            return;
        }
        try {
            Mail::to($correo)->send($correoAgenda);
        } catch (Throwable $e) {
            Log::error('Agenda: no se pudo enviar el correo', ['tipo' => $correoAgenda->tipo, 'correo' => $correo, 'error' => $e->getMessage()]);
        }
    }
}
