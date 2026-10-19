<?php

namespace App\Http\Controllers;

use App\Models\AgendaAjuste;
use App\Models\AgendaBloqueo;
use App\Models\AgendaHorario;
use App\Models\AgendaServicio;
use App\Models\Cita;
use App\Models\Market;
use App\Models\Profile;
use App\Services\Agenda\Agenda;
use App\Services\Agenda\Citas;
use App\Services\GoogleCalendar;
use App\Support\MediosPago;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/** La agenda en el panel del dueño: citas del día y la semana, aprobar, mover, cancelar y los ajustes. */
class AgendaPanelController extends Controller
{
    private function cita(int $id): Cita
    {
        return Cita::where('user_id', Auth::id())->findOrFail($id);
    }

    private function zona(): string
    {
        return AgendaAjuste::de(Auth::id())->zona_horaria;
    }

    public function index(Request $request)
    {
        $userId = Auth::id();
        $app = Agenda::app($userId);
        $perfil = Profile::activoDe($userId);
        if (!$app) {
            return view('dashboard.agenda', [
                'app' => null,
                'precio' => Agenda::precioRenovacion($perfil?->pais),
            ]);
        }

        $ajustes = AgendaAjuste::de($userId);
        $zona = $ajustes->zona_horaria;
        $vista = $request->query('vista') === 'semana' ? 'semana' : 'dia';
        $fecha = rescue(fn () => Carbon::createFromFormat('Y-m-d', (string) $request->query('fecha'), $zona)->startOfDay(), now($zona)->startOfDay(), false);
        $desde = $vista === 'semana' ? $fecha->copy()->startOfWeek(Carbon::MONDAY) : $fecha->copy();
        $hasta = $vista === 'semana' ? $desde->copy()->addDays(7) : $desde->copy()->addDay();

        $citas = Cita::where('user_id', $userId)->where('estado', '!=', 'cancelada')
            ->where('inicia_en', '>=', $desde->copy()->utc())->where('inicia_en', '<', $hasta->copy()->utc())
            ->orderBy('inicia_en')->get();

        $porResolver = Cita::where('user_id', $userId)->where('termina_en', '>', now())
            ->where(fn ($q) => $q->where('estado', 'pendiente')->orWhere(fn ($q) => $q->where('estado', 'cambio')->where('propuesta_por', 'cliente')))
            ->orderBy('inicia_en')->get();

        $maestro = Profile::maestroDe($userId)?->nickname;

        return view('dashboard.agenda', [
            'app' => $app,
            'ajustes' => $ajustes,
            'zona' => $zona,
            'vista' => $vista,
            'fecha' => $fecha,
            'desde' => $desde,
            'citas' => $citas,
            'porResolver' => $porResolver,
            'servicios' => AgendaServicio::where('user_id', $userId)->where('activo', true)->orderBy('orden')->orderBy('id')->get(),
            'tieneHorario' => AgendaHorario::where('user_id', $userId)->exists(),
            'enlace' => $maestro ? Profile::urlPublica($maestro) . '/agenda' : null,
            'precio' => Agenda::precioRenovacion($perfil?->pais),
            'whatsappVentas' => Market::where('code', MediosPago::pais($perfil?->pais))->value('whatsapp'),
            'proximas' => Cita::where('user_id', $userId)->where('estado', 'confirmada')->where('inicia_en', '>', now())->count(),
            'lista' => Agenda::modoPublico($userId) === 'activa',
            'perfiles' => Profile::where('user_id', $userId)->orderBy('id')->get()->map(fn (Profile $p) => [
                'id' => $p->id, 'nombre' => $p->nombre_perfil ?: ($p->nombre ?: $p->nickname),
                'activo' => $p->id === $perfil?->id,
            ] + Agenda::botonDe($p)),
        ]);
    }

    public function activar()
    {
        Agenda::activar(Auth::id());

        return redirect('/dashboard/agenda/ajustes')->with('status', 'Tu agenda está activa y gratis por un año. Ahora crea tus servicios y tu horario.');
    }

    /** Publica u oculta el botón "Agendar cita" en un perfil y decide si va justo después de la descripción. */
    public function boton(Request $request)
    {
        $datos = $request->validate(['perfil_id' => 'required|integer', 'visible' => 'required|boolean', 'prioridad' => 'nullable|boolean']);
        $perfil = Profile::where('user_id', Auth::id())->findOrFail($datos['perfil_id']);
        $actual = Agenda::botonDe($perfil);
        Agenda::guardarBoton($perfil, (bool) $datos['visible'], (bool) ($datos['prioridad'] ?? $actual['prioridad']));

        return back()->with('status', $datos['visible'] ? 'Listo: el botón "Agendar cita" ya se ve en tu perfil.' : 'Ocultaste el botón "Agendar cita" de ese perfil.');
    }

    public function aprobar(int $id)
    {
        Citas::aprobar($this->cita($id));

        return back()->with('status', 'Cita aprobada. Le avisamos al cliente por correo.');
    }

    public function rechazar(Request $request, int $id)
    {
        Citas::rechazar($this->cita($id), $request->validate(['motivo' => 'nullable|string|max:200'])['motivo'] ?? null);

        return back()->with('status', 'Cita rechazada. Le avisamos al cliente por correo.');
    }

    public function cancelar(Request $request, int $id)
    {
        Citas::cancelar($this->cita($id), 'duenio', $request->validate(['motivo' => 'nullable|string|max:200'])['motivo'] ?? null);

        return back()->with('status', 'Cita cancelada. Le avisamos al cliente por correo.');
    }

    /** El dueño propone otra hora: el cliente la acepta o se mantiene la original. */
    public function mover(Request $request, int $id)
    {
        $datos = $request->validate(['fecha' => 'required|date_format:Y-m-d', 'hora' => 'required|date_format:H:i']);
        try {
            Citas::proponerCambio($this->cita($id), Carbon::createFromFormat('Y-m-d H:i', $datos['fecha'] . ' ' . $datos['hora'], $this->zona()), 'duenio');
        } catch (ValidationException) {
            // Va como aviso y no como error del formulario, para no abrir la ventana de "Nueva cita"
            return back()->with('error', 'Esa hora se cruza con otra cita o con un cierre. Elige otra.');
        }

        return back()->with('status', 'Le enviamos la nueva hora al cliente. Mientras responde, la cita sigue a la hora original.');
    }

    /** Respuesta del dueño a un cambio que pidió el cliente. */
    public function responder(Request $request, int $id)
    {
        $acepta = $request->boolean('acepta');
        Citas::responderCambio($this->cita($id), $acepta, 'duenio');

        return back()->with('status', $acepta ? 'Cita movida a la nueva hora. Le avisamos al cliente.' : 'La cita sigue a la hora original. Le avisamos al cliente.');
    }

    /** Cita agendada a mano (alguien que llamó o escribió). */
    public function crear(Request $request)
    {
        $datos = $request->validate([
            'servicio_id' => 'required|integer',
            'fecha' => 'required|date_format:Y-m-d',
            'hora' => 'required|date_format:H:i',
            'nombre' => 'required|string|max:80',
            'correo' => 'required|email|max:120',
            'whatsapp' => 'nullable|string|max:30',
            'nota' => 'nullable|string|max:500',
        ]);
        $servicio = AgendaServicio::where('user_id', Auth::id())->findOrFail($datos['servicio_id']);
        $inicio = Carbon::createFromFormat('Y-m-d H:i', $datos['fecha'] . ' ' . $datos['hora'], $this->zona());
        $perfil = Profile::activoDe(Auth::id());

        Citas::reservar($servicio, $inicio, $datos, MediosPago::pais($perfil?->pais) === 'US' ? 'en' : 'es', 'duenio');

        return redirect('/dashboard/agenda?fecha=' . $datos['fecha'])->with('status', 'Cita agendada. El cliente recibió su confirmación por correo.');
    }

    // ---------------- Ajustes ----------------

    public function ajustes()
    {
        $userId = Auth::id();
        $app = Agenda::app($userId) ?? abort(404);
        $ajustes = AgendaAjuste::de($userId);
        $zona = $ajustes->zona_horaria;

        $horario = [];
        foreach (AgendaHorario::where('user_id', $userId)->orderBy('desde')->get() as $f) {
            $horario[$f->dia_semana][] = ['desde' => substr($f->desde, 0, 5), 'hasta' => substr($f->hasta, 0, 5)];
        }

        return view('dashboard.agenda-ajustes', [
            'app' => $app,
            'ajustes' => $ajustes,
            'servicios' => AgendaServicio::where('user_id', $userId)->where('activo', true)->orderBy('orden')->orderBy('id')->get(),
            'horario' => $horario,
            'bloqueos' => AgendaBloqueo::where('user_id', $userId)->where('hasta', '>', now())->orderBy('desde')->get(),
            'zona' => $zona,
            'moneda' => MediosPago::pais(Profile::activoDe($userId)?->pais) === 'US' ? 'USD' : 'COP',
            'google' => GoogleCalendar::configurado(),
        ]);
    }

    public function guardarReglas(Request $request)
    {
        $datos = $request->validate([
            'zona_horaria' => ['required', 'in:' . implode(',', array_keys(config('agenda.zonas')))],
            'intervalo_min' => ['required', 'in:' . implode(',', config('agenda.intervalos'))],
            'antelacion_min' => 'required|integer|min:0|max:10080',
            'dias_adelante' => 'required|integer|min:1|max:180',
            'limite_cancelar_horas' => 'required|integer|min:0|max:168',
            'mensaje_whatsapp' => 'nullable|string|max:500',
            'politica_cancelacion' => 'nullable|string|max:600',
        ]);
        AgendaAjuste::de(Auth::id())->update($datos);

        return redirect('/dashboard/agenda/ajustes#reglas')->with('status', 'Reglas guardadas.');
    }

    public function guardarServicio(Request $request)
    {
        $datos = $request->validate([
            'id' => 'nullable|integer',
            'nombre' => 'required|string|max:80',
            'descripcion' => 'nullable|string|max:200',
            'duracion_min' => 'required|integer|min:5|max:720',
            'colchon_min' => 'nullable|integer|min:0|max:240',
            'precio' => 'nullable|numeric|min:0|max:999999999',
        ]);
        $datos['colchon_min'] = (int) ($datos['colchon_min'] ?? 0);
        $datos['requiere_aprobacion'] = $request->boolean('requiere_aprobacion');
        $userId = Auth::id();

        if (!empty($datos['id'])) {
            AgendaServicio::where('user_id', $userId)->findOrFail($datos['id'])->update($datos);
        } else {
            AgendaServicio::create($datos + ['user_id' => $userId, 'orden' => AgendaServicio::where('user_id', $userId)->max('orden') + 1]);
        }

        return redirect('/dashboard/agenda/ajustes#servicios')->with('status', 'Servicio guardado.');
    }

    /** Se apaga (no se borra) para que las citas viejas sigan mostrando su servicio. */
    public function borrarServicio(int $id)
    {
        AgendaServicio::where('user_id', Auth::id())->findOrFail($id)->update(['activo' => false]);

        return redirect('/dashboard/agenda/ajustes#servicios')->with('status', 'Servicio quitado de la agenda.');
    }

    /** Horario semanal completo: hasta dos franjas por día ("dias[1][0][desde]" = lunes, primera franja). */
    public function guardarHorario(Request $request)
    {
        $request->validate(['dias' => 'nullable|array', 'dias.*' => 'array|max:2', 'dias.*.*.desde' => 'nullable|date_format:H:i', 'dias.*.*.hasta' => 'nullable|date_format:H:i']);
        $franjas = [];
        foreach ((array) $request->input('dias', []) as $dia => $lista) {
            if (!in_array((int) $dia, range(0, 6), true) || !$request->boolean("abierto.$dia")) {
                continue;
            }
            foreach ($lista as $f) {
                if (empty($f['desde']) || empty($f['hasta'])) {
                    continue;
                }
                if ($f['hasta'] <= $f['desde']) {
                    return back()->withErrors(['horario' => 'En cada franja la hora final debe ser después de la inicial.'])->withInput();
                }
                $franjas[] = ['user_id' => Auth::id(), 'dia_semana' => (int) $dia, 'desde' => $f['desde'], 'hasta' => $f['hasta']];
            }
        }

        AgendaHorario::where('user_id', Auth::id())->delete();
        if ($franjas) {
            AgendaHorario::insert($franjas);
        }

        return redirect('/dashboard/agenda/ajustes#horario')->with('status', 'Horario guardado.');
    }

    /** Cierra días u horas. Sin horas = el día completo. */
    public function guardarBloqueo(Request $request)
    {
        $datos = $request->validate([
            'desde_fecha' => 'required|date_format:Y-m-d',
            'hasta_fecha' => 'required|date_format:Y-m-d|after_or_equal:desde_fecha',
            'desde_hora' => 'nullable|date_format:H:i',
            'hasta_hora' => 'nullable|date_format:H:i',
            'motivo' => 'nullable|string|max:120',
        ]);
        $zona = $this->zona();
        $desde = Carbon::parse($datos['desde_fecha'] . ' ' . ($datos['desde_hora'] ?? '00:00'), $zona);
        $hasta = $datos['hasta_hora'] ?? null
            ? Carbon::parse($datos['hasta_fecha'] . ' ' . $datos['hasta_hora'], $zona)
            : Carbon::parse($datos['hasta_fecha'], $zona)->addDay()->startOfDay();
        if ($hasta->lte($desde)) {
            return back()->withErrors(['bloqueo' => 'El cierre debe terminar después de empezar.'])->withInput();
        }

        AgendaBloqueo::create(['user_id' => Auth::id(), 'desde' => $desde->utc(), 'hasta' => $hasta->utc(), 'motivo' => $datos['motivo'] ?? null]);

        // Citas que ya estaban en ese tiempo: el dueño decide si moverlas o cancelarlas
        $choques = Cita::where('user_id', Auth::id())->activas()->where('inicia_en', '<', $hasta)->where('termina_en', '>', $desde)->count();

        return redirect('/dashboard/agenda/ajustes#bloqueos')->with('status', $choques
            ? "Cerrado. Ojo: ya tienes $choques " . Str::plural('cita', $choques) . ' en ese tiempo; muévelas o cancélalas desde tu agenda.'
            : 'Cerrado. Esas horas ya no se pueden reservar.');
    }

    public function borrarBloqueo(int $id)
    {
        AgendaBloqueo::where('user_id', Auth::id())->findOrFail($id)->delete();

        return redirect('/dashboard/agenda/ajustes#bloqueos')->with('status', 'Esas horas quedaron abiertas otra vez.');
    }

    // ---------------- Google Calendar ----------------

    public function googleConectar(Request $request)
    {
        abort_unless(GoogleCalendar::configurado(), 404);
        $estado = Str::random(40);
        $request->session()->put('google_estado', $estado);

        return redirect()->away(GoogleCalendar::urlAutorizacion($estado));
    }

    public function googleVolver(Request $request)
    {
        abort_unless(GoogleCalendar::configurado(), 404);
        $estado = $request->session()->pull('google_estado');
        if (!$estado || !hash_equals($estado, (string) $request->query('state')) || !$request->query('code')) {
            return redirect('/dashboard/agenda/ajustes#google')->with('error', 'No se conectó Google Calendar. Intenta de nuevo.');
        }

        $ok = GoogleCalendar::conectar(AgendaAjuste::de(Auth::id()), (string) $request->query('code'));
        if ($ok) {
            // Las citas que ya estaban confirmadas también pasan al calendario
            Cita::where('user_id', Auth::id())->where('estado', 'confirmada')->where('inicia_en', '>', now())->whereNull('google_evento_id')
                ->get()->each(fn (Cita $c) => GoogleCalendar::guardarEvento($c));
        }

        return redirect('/dashboard/agenda/ajustes#google')->with($ok ? 'status' : 'error', $ok
            ? 'Google Calendar conectado. Tus citas aparecerán ahí y tus eventos ocupados bloquearán la agenda.'
            : 'Google no devolvió el permiso. Intenta de nuevo.');
    }

    public function googleDesconectar()
    {
        GoogleCalendar::desconectar(AgendaAjuste::de(Auth::id()));

        return redirect('/dashboard/agenda/ajustes#google')->with('status', 'Google Calendar desconectado.');
    }
}
