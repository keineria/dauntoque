<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\CalificameAjuste;
use App\Models\Profile;
use App\Services\Calificame\Calificame;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Lo que ve el cliente de Califícame, sin cuenta: dauntoque.com/usuario/califica (desde el perfil, un QR o el correo
 * después de su cita). Rutas relativas, igual que la Agenda: la página también se sirve en dauntoque.com por portal.php.
 */
class CalificamePublicaController extends Controller
{
    /** Dueño a partir de cualquiera de sus nicknames: [perfil activo, nickname maestro]. */
    private function dueno(string $nickname): array
    {
        $referencia = Profile::where('nickname', $nickname)->firstOrFail();
        $perfil = Profile::activoDe($referencia->user_id) ?? abort(404);

        return [$perfil, Profile::maestroDe($referencia->user_id)?->nickname ?? $nickname];
    }

    public function mostrar(Request $request, string $nickname): View
    {
        $idioma = AgendaPublicaController::idioma($request);
        [$perfil, $maestro] = $this->dueno($nickname);
        $cita = Calificame::citaPorCalificar($perfil->user_id, $request->query('cita'));
        $gracias = session('calificame_gracias');

        return view('calificame.publica', [
            'negocio' => AgendaPublicaController::encabezado($perfil),
            'activa' => Calificame::vigente($perfil->user_id),
            'idioma' => $idioma,
            'cita' => $cita,
            'gracias' => $gracias,
            'google' => $gracias === 'buena' ? CalificameAjuste::de($perfil->user_id)->google_url : null,
            'urlEnviar' => '/' . $maestro . '/califica',
            'urlPerfil' => Profile::urlPublica($maestro),
        ]);
    }

    public function guardar(Request $request, string $nickname): RedirectResponse
    {
        $idioma = AgendaPublicaController::idioma($request);
        [$perfil, $maestro] = $this->dueno($nickname);
        abort_unless(Calificame::vigente($perfil->user_id), 404);
        $volver = '/' . $maestro . '/califica?lang=' . $idioma;

        // Campo trampa: las personas no lo ven, los robots lo llenan
        if (filled($request->input('sitio_web'))) {
            abort(422);
        }

        try {
            $datos = $request->validate([
                'estrellas' => ['required', 'integer', 'between:1,5'],
                'comentario' => ['nullable', 'string', 'max:600'],
                'nombre' => ['nullable', 'string', 'max:80'],
                'contacto' => ['nullable', 'string', 'max:120'],
                'cita' => ['nullable', 'string', 'max:64'],
            ], ['estrellas.*' => __('calificame.falta_estrellas')]);
        } catch (ValidationException $e) {
            $conCita = $request->filled('cita') ? '&cita=' . rawurlencode((string) $request->input('cita')) : '';

            return redirect()->back()->setTargetUrl($volver . $conCita)->withErrors($e->errors())->withInput($request->except('sitio_web'));
        }

        // Sin cita, una calificación por navegador cada 12 horas para el mismo dueño (evita que alguien infle o hunda el promedio)
        $cita = Calificame::citaPorCalificar($perfil->user_id, $datos['cita'] ?? null);
        $marca = 'calificame_ultima_' . $perfil->user_id;
        if (!$cita && session($marca) && now()->subHours(12)->timestamp < session($marca)) {
            return redirect()->to($volver)->setTargetUrl($volver)->with('calificame_gracias', 'repetida');
        }

        $calificacion = Calificame::registrar($perfil->user_id, $datos, $cita);
        session([$marca => now()->timestamp]);

        return redirect()->to($volver)->setTargetUrl($volver)->with('calificame_gracias', $calificacion->buena() ? 'buena' : 'baja');
    }
}
