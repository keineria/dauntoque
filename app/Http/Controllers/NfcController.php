<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NfcController extends Controller
{
    public function vincularTarjeta($hash)
    {
        // 1. Buscamos si el chip físico ya le pertenece a alguien
        $tarjetaEnUso = Profile::where('nfc_uid', $hash)->first();

        if ($tarjetaEnUso) {
            // ¡LA MAGIA REAL! Si la tarjeta ya está activa, lo enviamos 
            // directamente a portal.dauntoque.com/nickname
            $urlPublica = url('/' . $tarjetaEnUso->nickname);
            
            return redirect()->away($urlPublica);
        }

        // 2. Si la tarjeta es virgen (nueva), guardamos su código temporalmente
        session(['nfc_pendiente' => $hash]);

        // Si la persona que escanea no tiene cuenta, lo mandamos al registro
        if (!Auth::check()) {
            return redirect('/register')->with('status', '¡Paso 1! Crea tu cuenta gratuita para vincular tu nueva tarjeta NFC.');
        }

        $usuario = Auth::user();
        $perfil = Profile::where('user_id', $usuario->id)->latest()->first();

        // Si ya tiene cuenta pero su perfil está vacío (Estado Cero)
        if (!$perfil) {
            return redirect('/dashboard')->with('status', '¡Casi listo! Crea tu primer perfil para terminar de enlazar tu tarjeta física.');
        }

        // 3. VINCULACIÓN EXITOSA (El primer escaneo que detona el Confeti)
        $perfil->nfc_uid = $hash;
        $perfil->save();
        session()->forget('nfc_pendiente'); 

        return redirect('/dashboard')->with('nfc_success', true);
    }
}