<?php

namespace App\Http\Controllers;

use App\Models\Chip;
use App\Models\Market;
use App\Models\Profile;
use App\Models\Tarjeta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NfcController extends Controller
{
    public function vincularTarjeta($hash)
    {
        // 1. Buscamos si el chip físico ya le pertenece a alguien
        $tarjetaEnUso = Profile::where('nfc_uid', $hash)->first();

        if ($tarjetaEnUso) {
            // ¡LA MAGIA REAL! Si la tarjeta ya está activa, lo enviamos a su link maestro
            // (dauntoque.com/nickname del primer perfil), que muestra el perfil activo
            $urlPublica = Profile::urlPublica(Profile::maestroDe($tarjetaEnUso->user_id)->nickname);

            return redirect()->away($urlPublica);
        }

        // 2. Solo se activan chips vendidos (registrados en /admin/chips); los demás ven cómo pedir ayuda
        if (! Chip::habilitado($hash)) {
            return response()->view('nfc.no-habilitada', ['codigo' => $hash, 'whatsapp' => Market::where('code', 'CO')->value('whatsapp')], 404);
        }

        // 3. Si la tarjeta es virgen (nueva), guardamos su código temporalmente
        session(['nfc_pendiente' => $hash, 'nfc_medio' => 'tarjeta']);

        // Si la persona que escanea no tiene cuenta, lo mandamos al registro
        if (!Auth::check()) {
            return redirect('/register')->with('status', '¡Paso 1! Crea tu cuenta gratuita para vincular tu nueva tarjeta NFC.');
        }

        $usuario = Auth::user();
        // El chip se vincula al perfil que se muestra en público (misma regla que /{nickname})
        $perfil = Profile::activoDe($usuario->id);

        // Si ya tiene cuenta pero su perfil está vacío (Estado Cero)
        if (!$perfil) {
            return redirect('/dashboard')->with('status', '¡Casi listo! Crea tu primer perfil para terminar de enlazar tu tarjeta física.');
        }

        // 4. VINCULACIÓN EXITOSA (El primer escaneo que detona el Confeti)
        Tarjeta::vincular($perfil, $hash);
        session()->forget('nfc_pendiente');

        return redirect('/dashboard')->with('nfc_success', true);
    }
}
