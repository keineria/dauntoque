<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\MusicaAjustesRequest;
use App\Models\MusicaAjuste;
use App\Models\Profile;
use App\Services\Agenda\Agenda;
use App\Services\Musica\Musica;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/** App Música en el panel del dueño: canción destacada y lista de Spotify para su perfil. */
class MusicaPanelController extends Controller
{
    public function index(): View
    {
        $userId = Auth::id();
        $app = Musica::app($userId);
        if (!$app) {
            return view('dashboard.musica', ['app' => null, 'precio' => Agenda::precioRenovacion(Auth::user()->paisFacturacion(), 'musica')]);
        }
        $maestro = Profile::maestroDe($userId)?->nickname;

        return view('dashboard.musica', [
            'app' => $app,
            'ajustes' => MusicaAjuste::de($userId),
            'perfil' => $maestro ? Profile::urlPublica($maestro) : null,
        ]);
    }

    public function activar(): RedirectResponse
    {
        Musica::activar(Auth::id());

        return redirect('/dashboard/musica')->with('status', 'Música quedó activa. Pega el enlace de tu canción y de tu lista de Spotify.');
    }

    public function ajustes(MusicaAjustesRequest $request): RedirectResponse
    {
        MusicaAjuste::de(Auth::id())->update($request->ajustes());

        return redirect('/dashboard/musica')->with('status', 'Música guardada.');
    }
}
