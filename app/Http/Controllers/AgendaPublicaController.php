<?php

namespace App\Http\Controllers;

use App\Models\AgendaAjuste;
use App\Models\AgendaServicio;
use App\Models\Cita;
use App\Models\Profile;
use App\Services\Agenda\Agenda;
use App\Services\Agenda\Citas;
use App\Services\Agenda\Disponibilidad;
use App\Support\ImageStore;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Lo que ve el cliente de la agenda, sin cuenta: reservar en dauntoque.com/usuario/agenda
 * y su cita en /cita/{token} (el enlace de cada correo), donde la mueve o la cancela.
 */
class AgendaPublicaController extends Controller
{
    /** Idioma del cliente: ?lang=, si no el del navegador (español o inglés). */
    public static function idioma(Request $request): string
    {
        $idioma = in_array($request->query('lang'), ['es', 'en'], true) ? $request->query('lang') : ($request->getPreferredLanguage(['es', 'en']) ?? 'es');
        app()->setLocale($idioma);

        return $idioma;
    }

    /** Dueño de la agenda a partir de cualquiera de sus nicknames: [perfil activo, nickname maestro]. */
    private function dueno(string $nickname): array
    {
        $referencia = Profile::where('nickname', $nickname)->firstOrFail();
        $perfil = Profile::activoDe($referencia->user_id) ?? abort(404);

        return [$perfil, Profile::maestroDe($referencia->user_id)?->nickname ?? $nickname];
    }

    /** Datos del negocio para el encabezado de las páginas públicas. */
    public static function encabezado(Profile $perfil): array
    {
        $diseno = is_array($perfil->design_config) ? $perfil->design_config : (json_decode($perfil->design_config ?? '', true) ?: []);
        $color = $diseno['color_primario'] ?? '#3b82f6';

        return [
            'nombre' => $perfil->nombre ?: $perfil->nickname,
            'profesion' => $perfil->profesion,
            'foto' => ImageStore::esPropia($perfil->foto_perfil) ? $perfil->foto_perfil : null,
            'direccion' => $perfil->direccion,
            'color' => preg_match('/^#[0-9a-fA-F]{6}$/', $color) ? $color : '#3b82f6',
            'whatsapp' => preg_replace('/\D/', '', (string) $perfil->telefono),
        ];
    }

    public function mostrar(Request $request, string $nickname)
    {
        $idioma = self::idioma($request);
        [$perfil, $maestro] = $this->dueno($nickname);
        $modo = Agenda::modoPublico($perfil->user_id) ?? abort(404);
        $ajustes = AgendaAjuste::de($perfil->user_id);

        $servicios = AgendaServicio::where('user_id', $perfil->user_id)->where('activo', true)->orderBy('orden')->orderBy('id')->get()
            ->map(fn (AgendaServicio $s) => [
                'id' => $s->id, 'nombre' => $s->nombre, 'descripcion' => $s->descripcion, 'duracion' => $s->duracion_min,
                'precio' => Agenda::precio($s->precio, $perfil->user_id), 'aprobacion' => $s->requiere_aprobacion,
            ])->values();

        return view('agenda.publica', [
            'negocio' => self::encabezado($perfil),
            'modo' => $modo,
            'servicios' => $servicios,
            'idioma' => $idioma,
            'zona' => config('agenda.zonas.' . $ajustes->zona_horaria, $ajustes->zona_horaria),
            'limiteHoras' => $ajustes->limite_cancelar_horas,
            'politica' => $ajustes->politica_cancelacion,
            'urlHoras' => url('/' . $maestro . '/agenda/horas'),
            'urlReservar' => url('/' . $maestro . '/agenda'),
            'urlPerfil' => Profile::urlPublica($maestro),
        ]);
    }

    /** Horas libres por día de un servicio: {"2026-10-14": ["09:00", ...]}. */
    public function horas(Request $request, string $nickname)
    {
        [$perfil] = $this->dueno($nickname);
        abort_unless(Agenda::modoPublico($perfil->user_id) === 'activa', 404);
        $servicio = AgendaServicio::where('user_id', $perfil->user_id)->where('activo', true)->findOrFail((int) $request->query('servicio'));

        return response()->json((new Disponibilidad(AgendaAjuste::de($perfil->user_id)))->porDias($servicio))
            ->header('Cache-Control', 'no-store');
    }

    public function reservar(Request $request, string $nickname)
    {
        $idioma = self::idioma($request);
        [$perfil] = $this->dueno($nickname);
        abort_unless(Agenda::modoPublico($perfil->user_id) === 'activa', 404);

        // Campo trampa: las personas no lo ven, los robots lo llenan
        if (filled($request->input('sitio_web'))) {
            abort(422);
        }

        $datos = $request->validate([
            'servicio_id' => 'required|integer',
            'fecha' => 'required|date_format:Y-m-d',
            'hora' => 'required|date_format:H:i',
            'nombre' => 'required|string|max:80',
            'correo' => 'required|email|max:120',
            'whatsapp' => ['required', 'string', 'max:30', 'regex:/^[0-9 +()\-]{7,30}$/'],
            'nota' => 'nullable|string|max:500',
        ]);
        $servicio = AgendaServicio::where('user_id', $perfil->user_id)->where('activo', true)->findOrFail($datos['servicio_id']);
        $inicio = Carbon::createFromFormat('Y-m-d H:i', $datos['fecha'] . ' ' . $datos['hora'], AgendaAjuste::de($perfil->user_id)->zona_horaria);

        $cita = Citas::reservar($servicio, $inicio, $datos, $idioma);

        return redirect('/cita/' . $cita->token . '?listo=' . ($cita->estado === 'confirmada' ? 'reservada' : 'pendiente'));
    }

    public function cita(Request $request, string $token)
    {
        $cita = Cita::where('token', $token)->firstOrFail();
        app()->setLocale($cita->idioma);
        $perfil = Profile::activoDe($cita->user_id) ?? abort(404);
        $ajustes = AgendaAjuste::de($cita->user_id);
        $maestro = Profile::maestroDe($cita->user_id)?->nickname;

        return view('agenda.cita', [
            'cita' => $cita,
            'negocio' => self::encabezado($perfil),
            'zona' => $ajustes->zona_horaria,
            'zonaNombre' => config('agenda.zonas.' . $ajustes->zona_horaria, $ajustes->zona_horaria),
            'puedeCambiar' => $cita->clientePuedeCambiar($ajustes->limite_cancelar_horas),
            'limiteHoras' => $ajustes->limite_cancelar_horas,
            'politica' => $ajustes->politica_cancelacion,
            'listo' => $request->query('listo'),
            'urlReservar' => $maestro && Agenda::modoPublico($cita->user_id) === 'activa' ? Profile::urlPublica($maestro) . '/agenda' : null,
        ]);
    }

    /** Horas libres para mover la cita (su propio turno no cuenta como ocupado). */
    public function horasCita(string $token)
    {
        $cita = Cita::where('token', $token)->firstOrFail();
        abort_unless($cita->servicio, 404);

        return response()->json((new Disponibilidad(AgendaAjuste::de($cita->user_id), $cita->id))->porDias($cita->servicio))
            ->header('Cache-Control', 'no-store');
    }

    public function cambiar(Request $request, string $token)
    {
        $cita = Cita::where('token', $token)->firstOrFail();
        app()->setLocale($cita->idioma);
        $ajustes = AgendaAjuste::de($cita->user_id);
        abort_unless($cita->estado === 'confirmada' && $cita->clientePuedeCambiar($ajustes->limite_cancelar_horas), 403);
        $datos = $request->validate(['fecha' => 'required|date_format:Y-m-d', 'hora' => 'required|date_format:H:i']);

        Citas::proponerCambio($cita, Carbon::createFromFormat('Y-m-d H:i', $datos['fecha'] . ' ' . $datos['hora'], $ajustes->zona_horaria), 'cliente');

        return redirect('/cita/' . $cita->token . '?listo=cambio');
    }

    public function responder(Request $request, string $token)
    {
        $cita = Cita::where('token', $token)->firstOrFail();
        abort_unless($cita->estado === 'cambio' && $cita->propuesta_por === 'duenio', 403);
        $acepta = $request->boolean('acepta');

        Citas::responderCambio($cita, $acepta, 'cliente');

        return redirect('/cita/' . $cita->token . '?listo=' . ($acepta ? 'aceptada' : 'rechazada'));
    }

    public function cancelar(string $token)
    {
        $cita = Cita::where('token', $token)->firstOrFail();
        abort_unless($cita->clientePuedeCambiar(AgendaAjuste::de($cita->user_id)->limite_cancelar_horas), 403);

        Citas::cancelar($cita, 'cliente');

        return redirect('/cita/' . $cita->token . '?listo=cancelada');
    }

    public function ics(string $token)
    {
        $cita = Cita::where('token', $token)->firstOrFail();
        $perfil = Profile::activoDe($cita->user_id);

        return response($cita->ics($cita->servicio_nombre . ' · ' . ($perfil?->nombre ?? 'DaUnToque'), $perfil?->direccion))
            ->header('Content-Type', 'text/calendar; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="cita.ics"');
    }
}
