<?php

namespace App\Http\Controllers;

use App\Models\Chip;
use App\Models\Profile;
use App\Models\Tarjeta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Activar la tarjeta escribiendo su código desde el panel (por ejemplo, desde un computador,
 * o si el cliente creó su perfil antes de acercar la tarjeta al celular).
 */
class ActivarTarjetaController extends Controller
{
    public function __invoke(Request $request)
    {
        $datos = $request->validate(['codigo' => ['required', 'string', 'max:200']], [], ['codigo' => 'código']);

        $chip = Chip::escrito($datos['codigo']);
        if (! $chip) {
            return back()->withErrors(['codigo' => 'No encontramos ese código. Revísalo en el correo de tu compra.'])->withInput();
        }

        $perfil = Profile::activoDe(Auth::id());
        if (! $perfil) {
            // Se enlaza solo al crear el perfil (ProfileController) o al volver al panel
            session(['nfc_pendiente' => $chip->codigo]);

            return redirect('/dashboard')->with('status', 'Código correcto. Crea tu perfil y tu tarjeta quedará enlazada.');
        }
        if (Profile::where('user_id', Auth::id())->where('nfc_uid', $chip->codigo)->exists()) {
            return back()->with('status', 'Esa tarjeta ya está enlazada a tu cuenta.');
        }
        if (! Tarjeta::vincular($perfil, $chip->codigo)) {
            return back()->withErrors(['codigo' => 'Esa tarjeta ya está activada en otra cuenta. Escríbenos desde Ayuda y lo revisamos.'])->withInput();
        }

        return redirect('/dashboard')->with('nfc_success', true);
    }

    /**
     * Tarjeta acercada al celular antes de iniciar sesión (o antes de tener perfil):
     * al volver al panel con perfil, queda enlazada sin repetir el paso.
     */
    public static function enlazarPendiente(int $userId): bool
    {
        $codigo = session('nfc_pendiente');
        $perfil = $codigo ? Profile::activoDe($userId) : null;
        if (! $perfil) {
            return false;
        }
        session()->forget('nfc_pendiente');

        return Profile::where('nfc_uid', $codigo)->where('user_id', $userId)->doesntExist()
            && Tarjeta::vincular($perfil, $codigo);
    }
}
