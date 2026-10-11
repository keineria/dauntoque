<?php

namespace App\Services;

use App\Models\AgendaAjuste;
use App\Models\Cita;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Google Calendar de la agenda, por la API REST (sin SDK): OAuth web con refresh token,
 * horas ocupadas (freeBusy) y un evento por cada cita confirmada.
 * Nada de esto bloquea una reserva: si Google falla, se registra en el log y la agenda sigue.
 */
class GoogleCalendar
{
    private const OAUTH = 'https://accounts.google.com/o/oauth2/v2/auth';
    private const TOKEN = 'https://oauth2.googleapis.com/token';
    private const API = 'https://www.googleapis.com/calendar/v3';
    private const ALCANCES = 'https://www.googleapis.com/auth/calendar.events https://www.googleapis.com/auth/calendar.freebusy';

    public static function configurado(): bool
    {
        return filled(config('services.google.client_id')) && filled(config('services.google.client_secret'));
    }

    public static function urlAutorizacion(string $estado): string
    {
        return self::OAUTH . '?' . http_build_query([
            'client_id' => config('services.google.client_id'),
            'redirect_uri' => config('services.google.redirect'),
            'response_type' => 'code',
            'scope' => self::ALCANCES,
            'access_type' => 'offline',
            'prompt' => 'consent',
            'include_granted_scopes' => 'true',
            'state' => $estado,
        ]);
    }

    /** Cambia el código de Google por el refresh token y guarda el correo del calendario. */
    public static function conectar(AgendaAjuste $ajustes, string $codigo): bool
    {
        $r = Http::asForm()->timeout(config('services.google.timeout'))->post(self::TOKEN, [
            'code' => $codigo,
            'client_id' => config('services.google.client_id'),
            'client_secret' => config('services.google.client_secret'),
            'redirect_uri' => config('services.google.redirect'),
            'grant_type' => 'authorization_code',
        ]);
        if (!$r->successful() || !$r->json('refresh_token')) {
            Log::warning('Google Calendar: no se pudo conectar', ['estado' => $r->status(), 'error' => $r->json('error')]);

            return false;
        }

        Cache::put(self::claveToken($ajustes), $r->json('access_token'), max(60, (int) $r->json('expires_in', 3600) - 60));
        $correo = Http::withToken($r->json('access_token'))->timeout(config('services.google.timeout'))
            ->get(self::API . '/calendars/primary')->json('id');
        $ajustes->update(['google_token' => $r->json('refresh_token'), 'google_correo' => $correo]);

        return true;
    }

    public static function desconectar(AgendaAjuste $ajustes): void
    {
        try {
            Http::asForm()->timeout(config('services.google.timeout'))->post('https://oauth2.googleapis.com/revoke', ['token' => $ajustes->google_token]);
        } catch (Throwable) {
            // Se borra igual de este lado
        }
        Cache::forget(self::claveToken($ajustes));
        Cache::forget(self::claveOcupado($ajustes));
        $ajustes->update(['google_token' => null, 'google_correo' => null]);
    }

    /**
     * Intervalos ocupados del calendario principal entre dos momentos, en UTC: [[inicio, fin], ...].
     * Se guarda 2 minutos en caché para no llamar a Google en cada consulta de horas.
     */
    public static function ocupado(AgendaAjuste $ajustes, CarbonInterface $desde, CarbonInterface $hasta): array
    {
        if (!self::configurado() || !$ajustes->conGoogle()) {
            return [];
        }

        $lista = Cache::remember(self::claveOcupado($ajustes), 120, function () use ($ajustes, $desde, $hasta) {
            $r = self::llamar($ajustes, 'post', '/freeBusy', [
                'timeMin' => Carbon::instance($desde)->utc()->toIso8601ZuluString(),
                'timeMax' => Carbon::instance($hasta)->utc()->toIso8601ZuluString(),
                'items' => [['id' => 'primary']],
            ]);

            return $r ? (array) ($r['calendars']['primary']['busy'] ?? []) : [];
        });

        return array_map(fn ($b) => [Carbon::parse($b['start'])->utc(), Carbon::parse($b['end'])->utc()], $lista);
    }

    /** Crea o actualiza el evento de una cita confirmada. */
    public static function guardarEvento(Cita $cita): void
    {
        $ajustes = AgendaAjuste::where('user_id', $cita->user_id)->first();
        if (!$ajustes || !self::configurado() || !$ajustes->conGoogle()) {
            return;
        }

        $zona = $ajustes->zona_horaria;
        $evento = [
            'summary' => $cita->servicio_nombre . ' · ' . $cita->cliente_nombre,
            'description' => trim(implode("\n", array_filter([
                'Cliente: ' . $cita->cliente_nombre,
                'Correo: ' . $cita->cliente_correo,
                $cita->cliente_whatsapp ? 'WhatsApp: ' . $cita->cliente_whatsapp : null,
                $cita->nota ? 'Nota: ' . $cita->nota : null,
                'Ver en DaUnToque: ' . url('/dashboard/agenda?fecha=' . $cita->inicia_en->copy()->setTimezone($zona)->format('Y-m-d')),
            ]))),
            'start' => ['dateTime' => $cita->inicia_en->copy()->setTimezone($zona)->toIso8601String(), 'timeZone' => $zona],
            'end' => ['dateTime' => $cita->termina_en->copy()->setTimezone($zona)->toIso8601String(), 'timeZone' => $zona],
            'reminders' => ['useDefault' => true],
        ];

        $r = $cita->google_evento_id
            ? self::llamar($ajustes, 'patch', '/calendars/primary/events/' . rawurlencode($cita->google_evento_id), $evento)
            : self::llamar($ajustes, 'post', '/calendars/primary/events', $evento);

        if ($r && !$cita->google_evento_id && !empty($r['id'])) {
            $cita->forceFill(['google_evento_id' => $r['id']])->saveQuietly();
        }
        Cache::forget(self::claveOcupado($ajustes));
    }

    public static function borrarEvento(Cita $cita): void
    {
        $ajustes = AgendaAjuste::where('user_id', $cita->user_id)->first();
        if (!$cita->google_evento_id || !$ajustes || !self::configurado() || !$ajustes->conGoogle()) {
            return;
        }

        self::llamar($ajustes, 'delete', '/calendars/primary/events/' . rawurlencode($cita->google_evento_id));
        $cita->forceFill(['google_evento_id' => null])->saveQuietly();
        Cache::forget(self::claveOcupado($ajustes));
    }

    /** Llamada a la API con un token de acceso vigente. Devuelve el JSON (o [] si no trae cuerpo) o null si falló. */
    private static function llamar(AgendaAjuste $ajustes, string $metodo, string $ruta, array $datos = []): ?array
    {
        try {
            $token = self::tokenAcceso($ajustes);
            if (!$token) {
                return null;
            }
            $peticion = Http::withToken($token)->acceptJson()->timeout(config('services.google.timeout'));
            $r = $metodo === 'delete' ? $peticion->delete(self::API . $ruta) : $peticion->{$metodo}(self::API . $ruta, $datos);
            if ($r->successful() || ($metodo === 'delete' && in_array($r->status(), [404, 410], true))) {
                return $r->json() ?? [];
            }
            Log::warning('Google Calendar: la llamada falló', ['ruta' => $ruta, 'estado' => $r->status(), 'usuario' => $ajustes->user_id]);
        } catch (Throwable $e) {
            Log::warning('Google Calendar: error de conexión', ['ruta' => $ruta, 'error' => $e->getMessage()]);
        }

        return null;
    }

    private static function tokenAcceso(AgendaAjuste $ajustes): ?string
    {
        $clave = self::claveToken($ajustes);
        if ($token = Cache::get($clave)) {
            return $token;
        }

        $r = Http::asForm()->timeout(config('services.google.timeout'))->post(self::TOKEN, [
            'client_id' => config('services.google.client_id'),
            'client_secret' => config('services.google.client_secret'),
            'refresh_token' => $ajustes->google_token,
            'grant_type' => 'refresh_token',
        ]);
        if (!$r->successful()) {
            // El dueño quitó el permiso desde su cuenta de Google: se desconecta para no insistir
            if ($r->json('error') === 'invalid_grant') {
                $ajustes->update(['google_token' => null]);
            }
            Log::warning('Google Calendar: no se pudo renovar el token', ['usuario' => $ajustes->user_id, 'error' => $r->json('error')]);

            return null;
        }

        Cache::put($clave, $r->json('access_token'), max(60, (int) $r->json('expires_in', 3600) - 60));

        return $r->json('access_token');
    }

    private static function claveToken(AgendaAjuste $ajustes): string
    {
        return 'google-token-' . $ajustes->user_id;
    }

    private static function claveOcupado(AgendaAjuste $ajustes): string
    {
        return 'google-ocupado-' . $ajustes->user_id;
    }
}
