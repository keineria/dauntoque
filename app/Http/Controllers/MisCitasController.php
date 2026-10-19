<?php

namespace App\Http\Controllers;

use App\Mail\AgendaCorreo;
use App\Models\Cita;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

/**
 * "Mis citas": el cliente escribe su correo, recibe un código de 6 dígitos y ve todas sus citas.
 * Sin contraseña. El código dura 10 minutos y admite 5 intentos; la sesión queda abierta 2 horas.
 */
class MisCitasController extends Controller
{
    private function correoVerificado(Request $request): ?string
    {
        $hasta = $request->session()->get('agenda_correo_hasta');

        return $hasta && now()->timestamp < $hasta ? $request->session()->get('agenda_correo') : null;
    }

    private function clave(string $correo): string
    {
        return 'mis-citas-' . hash('sha256', $correo);
    }

    public function mostrar(Request $request)
    {
        AgendaPublicaController::idioma($request);
        $correo = $this->correoVerificado($request);
        if (!$correo) {
            return view('agenda.mis-citas', ['correo' => null, 'pendiente' => $request->session()->get('agenda_correo_pendiente')]);
        }

        $citas = Cita::where('cliente_correo', $correo)->orderBy('inicia_en')->get();
        $negocios = Profile::whereIn('user_id', $citas->pluck('user_id')->unique())->ordenActivo()->get()->unique('user_id')->keyBy('user_id');
        $zonas = \App\Models\AgendaAjuste::whereIn('user_id', $citas->pluck('user_id')->unique())->pluck('zona_horaria', 'user_id');

        [$proximas, $pasadas] = $citas->partition(fn (Cita $c) => $c->activa() && $c->termina_en->isFuture());

        return view('agenda.mis-citas', [
            'correo' => $correo,
            'proximas' => $proximas->values(),
            'pasadas' => $pasadas->sortByDesc('inicia_en')->take(20)->values(),
            'negocios' => $negocios,
            'zonas' => $zonas,
        ]);
    }

    public function enviarCodigo(Request $request)
    {
        $idioma = AgendaPublicaController::idioma($request);
        $correo = Str::lower(trim($request->validate(['correo' => 'required|email|max:120'])['correo']));
        $request->session()->put('agenda_correo_pendiente', $correo);

        // Siempre la misma respuesta: así nadie averigua qué correos tienen citas
        if (Cita::where('cliente_correo', $correo)->exists()) {
            $codigo = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            Cache::put($this->clave($correo), ['hash' => Hash::make($codigo), 'intentos' => 0], now()->addMinutes(config('agenda.codigo_minutos')));
            try {
                Mail::to($correo)->send((new AgendaCorreo('codigo', extra: ['codigo' => $codigo]))->locale($idioma));
            } catch (Throwable $e) {
                Log::error('Mis citas: no se pudo enviar el código', ['error' => $e->getMessage()]);
            }
        }

        return redirect('/mis-citas')->setTargetUrl('/mis-citas')->with('aviso', __('agenda.codigo_enviado'));
    }

    public function verificar(Request $request)
    {
        AgendaPublicaController::idioma($request);
        $correo = $request->session()->get('agenda_correo_pendiente');
        $codigo = preg_replace('/\D/', '', (string) $request->input('codigo'));
        $guardado = $correo ? Cache::get($this->clave($correo)) : null;

        if (!$guardado || $guardado['intentos'] >= 5 || !Hash::check($codigo, $guardado['hash'])) {
            if ($guardado) {
                Cache::put($this->clave($correo), ['hash' => $guardado['hash'], 'intentos' => $guardado['intentos'] + 1], now()->addMinutes(config('agenda.codigo_minutos')));
            }

            return redirect('/mis-citas')->setTargetUrl('/mis-citas')->withErrors(['codigo' => __('agenda.codigo_invalido')]);
        }

        Cache::forget($this->clave($correo));
        $request->session()->regenerate();
        $request->session()->put('agenda_correo', $correo);
        $request->session()->put('agenda_correo_hasta', now()->addMinutes(config('agenda.sesion_minutos'))->timestamp);
        $request->session()->forget('agenda_correo_pendiente');

        return redirect('/mis-citas')->setTargetUrl('/mis-citas');
    }

    public function salir(Request $request)
    {
        $request->session()->forget(['agenda_correo', 'agenda_correo_hasta', 'agenda_correo_pendiente']);

        return redirect('/mis-citas')->setTargetUrl('/mis-citas');
    }
}
