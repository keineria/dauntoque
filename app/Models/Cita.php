<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Una reserva de la agenda. Estados: pendiente (espera que el dueño la apruebe), confirmada,
 * cambio (hay una nueva hora propuesta en espera de la otra parte) y cancelada.
 * "Realizada" no se guarda: es una cita confirmada cuya hora ya pasó.
 */
class Cita extends Model
{
    public const ACTIVAS = ['pendiente', 'confirmada', 'cambio'];

    protected $fillable = [
        'user_id', 'servicio_id', 'servicio_nombre', 'colchon_min', 'cliente_nombre', 'cliente_correo', 'cliente_whatsapp',
        'nota', 'inicia_en', 'termina_en', 'estado', 'propuesta_inicia_en', 'propuesta_por', 'cancelada_por', 'motivo',
        'token', 'idioma', 'recordado_24h_en', 'recordado_2h_en', 'google_evento_id',
    ];

    protected $casts = [
        'inicia_en' => 'datetime', 'termina_en' => 'datetime', 'propuesta_inicia_en' => 'datetime',
        'recordado_24h_en' => 'datetime', 'recordado_2h_en' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Cita $cita) {
            $cita->token ??= Str::random(48);
            $cita->cliente_correo = Str::lower(trim($cita->cliente_correo));
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function servicio()
    {
        return $this->belongsTo(AgendaServicio::class, 'servicio_id');
    }

    public function historial()
    {
        return $this->hasMany(CitaHistorial::class)->orderBy('id');
    }

    public function scopeActivas(Builder $query): Builder
    {
        return $query->whereIn('estado', self::ACTIVAS);
    }

    public function activa(): bool
    {
        return in_array($this->estado, self::ACTIVAS, true);
    }

    public function realizada(): bool
    {
        return $this->estado === 'confirmada' && $this->termina_en->isPast();
    }

    /** Estado para mostrar: pendiente, confirmada, cambio, cancelada o realizada. */
    public function estadoVisible(): string
    {
        return $this->realizada() ? 'realizada' : $this->estado;
    }

    public function duracionMin(): int
    {
        return (int) $this->inicia_en->diffInMinutes($this->termina_en);
    }

    /** Fin de la nueva hora propuesta (misma duración). */
    public function propuestaTermina(): ?CarbonInterface
    {
        return $this->propuesta_inicia_en?->copy()->addMinutes($this->duracionMin());
    }

    /** El cliente puede cancelar o pedir cambio hasta X horas antes (agenda_ajustes.limite_cancelar_horas). */
    public function clientePuedeCambiar(int $limiteHoras): bool
    {
        return $this->activa() && now()->addHours($limiteHoras)->lt($this->inicia_en);
    }

    public function enlace(): string
    {
        return url('/cita/' . $this->token);
    }

    public function registrar(string $accion, string $por, array $detalle = []): void
    {
        $this->historial()->create(['accion' => $accion, 'por' => $por, 'detalle' => $detalle ?: null, 'created_at' => now()]);
    }

    /** Fecha y hora en la zona de la agenda, en el idioma de la cita: "martes 14 de octubre" / "Tuesday, October 14". */
    public function fechaLocal(string $zona, ?CarbonInterface $momento = null): string
    {
        $m = Carbon::instance($momento ?? $this->inicia_en)->setTimezone($zona)->locale($this->idioma);

        return $this->idioma === 'en' ? $m->translatedFormat('l, F j') : $m->translatedFormat('l j \d\e F');
    }

    public function horaLocal(string $zona, ?CarbonInterface $momento = null): string
    {
        $m = Carbon::instance($momento ?? $this->inicia_en)->setTimezone($zona);

        return $this->idioma === 'en' ? $m->format('g:i A') : $m->format('g:i a');
    }

    /** Evento .ics para añadir la cita al calendario del celular (también sirve para cancelarla). */
    public function ics(string $titulo, ?string $lugar = null): string
    {
        $f = fn (CarbonInterface $d) => $d->copy()->utc()->format('Ymd\THis\Z');
        $txt = fn ($v) => str_replace(['\\', ';', ',', "\r\n", "\n"], ['\\\\', '\\;', '\\,', '\\n', '\\n'], (string) $v);
        $cancelada = $this->estado === 'cancelada';

        $lineas = [
            'BEGIN:VCALENDAR', 'VERSION:2.0', 'PRODID:-//DaUnToque//Agenda//ES', 'CALSCALE:GREGORIAN',
            'METHOD:' . ($cancelada ? 'CANCEL' : 'PUBLISH'),
            'BEGIN:VEVENT',
            'UID:cita-' . $this->id . '@dauntoque.com',
            'SEQUENCE:' . $this->historial()->count(),
            'DTSTAMP:' . $f(now()),
            'DTSTART:' . $f($this->inicia_en),
            'DTEND:' . $f($this->termina_en),
            'SUMMARY:' . $txt($titulo),
            'DESCRIPTION:' . $txt($this->enlace()),
            'URL:' . $this->enlace(),
        ];
        if ($lugar) {
            $lineas[] = 'LOCATION:' . $txt($lugar);
        }
        $lineas[] = 'STATUS:' . ($cancelada ? 'CANCELLED' : 'CONFIRMED');
        $lineas[] = 'END:VEVENT';
        $lineas[] = 'END:VCALENDAR';

        return implode("\r\n", $lineas) . "\r\n";
    }
}
