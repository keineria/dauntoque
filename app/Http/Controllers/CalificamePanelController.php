<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Calificacion;
use App\Models\CalificameAjuste;
use App\Models\Profile;
use App\Services\Agenda\Agenda;
use App\Services\Calificame\Calificame;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/** Califícame en el panel del dueño: promedio, calificaciones, qué comentarios salen en el perfil y los ajustes. */
class CalificamePanelController extends Controller
{
    public function index(Request $request): View
    {
        $userId = Auth::id();
        $app = Calificame::app($userId);
        if (!$app) {
            return view('dashboard.calificame', ['app' => null]);
        }

        $filtro = in_array($request->query('ver'), ['buenas', 'bajas'], true) ? $request->query('ver') : 'todas';
        $buenas = config('calificame.estrellas_buenas');
        $maestro = Profile::maestroDe($userId)?->nickname;

        return view('dashboard.calificame', [
            'app' => $app,
            'ajustes' => CalificameAjuste::de($userId),
            'resumen' => Calificame::resumen($userId),
            'filtro' => $filtro,
            'calificaciones' => Calificacion::where('user_id', $userId)
                ->when($filtro === 'buenas', fn ($q) => $q->where('estrellas', '>=', $buenas))
                ->when($filtro === 'bajas', fn ($q) => $q->where('estrellas', '<', $buenas))
                ->with('cita:id,servicio_nombre')->latest()->paginate(20)->withQueryString(),
            'visibles' => Calificacion::where('user_id', $userId)->where('visible', true)->count(),
            'enlace' => $maestro ? Profile::urlPublica($maestro) . '/califica' : null,
            'tieneAgenda' => Agenda::app($userId) !== null,
        ]);
    }

    public function activar(): RedirectResponse
    {
        Calificame::activar(Auth::id());

        return redirect('/dashboard/calificame')->with('status', 'Califícame quedó activa. Comparte tu enlace y empieza a recibir calificaciones.');
    }

    public function ajustes(Request $request): RedirectResponse
    {
        abort_unless(Calificame::app(Auth::id()), 404);
        $datos = $request->validate([
            'google_url' => ['nullable', 'url:https', 'max:300'],
        ], ['google_url.*' => 'Pega el enlace completo de tu perfil de Google (empieza por https://).']);

        CalificameAjuste::de(Auth::id())->update([
            'mostrar_sello' => $request->boolean('mostrar_sello'),
            'mostrar_boton' => $request->boolean('mostrar_boton'),
            'pedir_tras_cita' => $request->boolean('pedir_tras_cita'),
            'google_url' => $datos['google_url'] ?? null,
        ]);

        return redirect('/dashboard/calificame')->with('status', 'Ajustes guardados.');
    }

    /** Muestra u oculta un comentario en el perfil. Solo los de 4 y 5 estrellas con comentario, hasta el máximo. */
    public function visible(Request $request, int $id): JsonResponse
    {
        $calificacion = Calificacion::where('user_id', Auth::id())->findOrFail($id);
        $mostrar = $request->boolean('visible');

        if ($mostrar && !$calificacion->publicable()) {
            return response()->json(['message' => 'Solo puedes mostrar comentarios de 4 o 5 estrellas.'], 422);
        }
        $maximo = config('calificame.maximo_comentarios');
        if ($mostrar && !$calificacion->visible && Calificacion::where('user_id', Auth::id())->where('visible', true)->count() >= $maximo) {
            return response()->json(['message' => "Puedes mostrar hasta $maximo comentarios. Oculta uno para mostrar este."], 422);
        }
        $calificacion->update(['visible' => $mostrar]);

        return response()->json(['visible' => $calificacion->visible]);
    }
}
