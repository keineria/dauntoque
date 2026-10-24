<?php

declare(strict_types=1);

namespace App\Services\Calificame;

use App\Mail\CalificameCorreo;
use App\Models\AppAdicional;
use App\Models\Calificacion;
use App\Models\CalificameAjuste;
use App\Models\Cita;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * App Califícame: el cliente califica la atención de 1 a 5 estrellas. Las buenas suman al sello del perfil
 * y el dueño elige qué comentarios mostrar (uno, varios o ninguno); las bajas le llegan en privado.
 */
class Calificame
{
    public static function app(int $userId): ?AppAdicional
    {
        return AppAdicional::de($userId, 'calificame');
    }

    /** Activada y dentro de su año (recibe calificaciones y muestra el sello). */
    public static function vigente(int $userId): bool
    {
        return (bool) self::app($userId)?->vigente();
    }

    /** Activa la app con su primer año gratis. */
    public static function activar(int $userId): AppAdicional
    {
        $app = AppAdicional::firstOrCreate(['user_id' => $userId, 'app' => 'calificame'], [
            'activada_en' => now(),
            'vence_en' => now()->addDays(config('calificame.dias_gratis')),
        ]);
        CalificameAjuste::de($userId);

        return $app;
    }

    /** Promedio, total y cuántas hay de cada número de estrellas: ['promedio' => 4.8, 'total' => 12, 'por' => [5 => 9, ...]]. */
    public static function resumen(int $userId): array
    {
        $por = Calificacion::where('user_id', $userId)->selectRaw('estrellas, count(*) as n')->groupBy('estrellas')->pluck('n', 'estrellas');
        $total = (int) $por->sum();
        $suma = $por->reduce(fn (int $s, $n, $estrellas) => $s + (int) $n * (int) $estrellas, 0);

        return [
            'promedio' => $total ? round($suma / $total, 1) : 0.0,
            'total' => $total,
            'por' => collect([5, 4, 3, 2, 1])->mapWithKeys(fn (int $e) => [$e => (int) ($por[$e] ?? 0)])->all(),
        ];
    }

    /** "4,8" en español; "4.8" en inglés lo arma el perfil. */
    public static function promedioTexto(float $promedio): string
    {
        return number_format($promedio, 1, ',', '.');
    }

    /**
     * Lo que muestra el perfil público, o null si la app no está activa.
     * El sello sale solo si el dueño lo dejó encendido y ya hay el mínimo de calificaciones;
     * los comentarios son solo los que el dueño eligió.
     */
    public static function publico(int $userId, string $maestro): ?array
    {
        if (!self::vigente($userId)) {
            return null;
        }
        $ajustes = CalificameAjuste::de($userId);
        $resumen = self::resumen($userId);
        $sello = $ajustes->mostrar_sello && $resumen['total'] >= config('calificame.minimo_sello');

        $comentarios = Calificacion::where('user_id', $userId)->where('visible', true)
            ->where('estrellas', '>=', config('calificame.estrellas_buenas'))->whereNotNull('comentario')
            ->latest()->limit(config('calificame.maximo_comentarios'))->get(['nombre', 'estrellas', 'comentario'])
            ->map(fn (Calificacion $c) => ['nombre' => $c->nombre, 'estrellas' => $c->estrellas, 'texto' => $c->comentario])->all();

        if (!$sello && !$comentarios && !$ajustes->mostrar_boton) {
            return null;
        }

        return [
            'sello' => $sello,
            'promedio' => $resumen['promedio'],
            'total' => $resumen['total'],
            'comentarios' => $comentarios,
            'enlace' => $ajustes->mostrar_boton ? Profile::urlPublica($maestro) . '/califica' : null,
        ];
    }

    /** Cita de la Agenda que todavía se puede calificar desde el enlace de su correo (del mismo dueño, ya realizada y sin calificar). */
    public static function citaPorCalificar(int $userId, ?string $token): ?Cita
    {
        if (!$token) {
            return null;
        }
        $cita = Cita::where('user_id', $userId)->where('token', $token)->first();

        return $cita && $cita->realizada() && Calificacion::where('cita_id', $cita->id)->doesntExist() ? $cita : null;
    }

    /** Guarda la calificación y, si es baja, se la avisa al dueño en privado para que pueda arreglarlo. */
    public static function registrar(int $userId, array $datos, ?Cita $cita = null): Calificacion
    {
        $calificacion = Calificacion::create([
            'user_id' => $userId,
            'cita_id' => $cita?->id,
            'estrellas' => (int) $datos['estrellas'],
            'comentario' => filled($datos['comentario'] ?? null) ? trim($datos['comentario']) : null,
            'nombre' => filled($datos['nombre'] ?? null) ? trim($datos['nombre']) : $cita?->cliente_nombre,
            'contacto' => filled($datos['contacto'] ?? null) ? trim($datos['contacto']) : $cita?->cliente_correo,
        ]);

        if (!$calificacion->buena()) {
            self::enviar(User::whereKey($userId)->value('email'), new CalificameCorreo('baja', $calificacion));
        }

        return $calificacion;
    }

    /**
     * Después de cada cita realizada, le pide al cliente que califique (una sola vez por cita).
     * Solo si el dueño tiene Califícame activa y dejó encendido "Pedir calificación después de cada cita".
     */
    public static function pedirTrasCitas(): int
    {
        $enviados = 0;
        $citas = Cita::where('estado', 'confirmada')->whereNull('calificacion_pedida_en')
            ->where('termina_en', '<=', now()->subHours(config('calificame.pedir_tras_horas')))
            ->where('termina_en', '>', now()->subDays(config('calificame.pedir_hasta_dias')))
            ->whereIn('user_id', AppAdicional::where('app', 'calificame')->where('vence_en', '>', now())->select('user_id'))
            ->with('user:id,email')->limit(200)->get();

        foreach ($citas as $cita) {
            $cita->forceFill(['calificacion_pedida_en' => now()])->save();
            $maestro = Profile::maestroDe($cita->user_id)?->nickname;
            if (!$maestro || !CalificameAjuste::de($cita->user_id)->pedir_tras_cita) {
                continue;
            }
            self::enviar($cita->cliente_correo, (new CalificameCorreo('pedir', cita: $cita))->locale($cita->idioma ?: 'es'));
            $enviados++;
        }

        return $enviados;
    }

    /** Un correo que no sale no debe tumbar la calificación: se registra y se sigue. */
    private static function enviar(?string $correo, CalificameCorreo $mensaje): void
    {
        if (!$correo) {
            return;
        }
        try {
            Mail::to($correo)->send($mensaje);
        } catch (Throwable $e) {
            Log::error('Califícame: no se pudo enviar el correo', ['tipo' => $mensaje->tipo, 'correo' => $correo, 'error' => $e->getMessage()]);
        }
    }
}
