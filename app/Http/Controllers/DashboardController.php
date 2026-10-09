<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    private function cargarDatos()
    {
        $usuario = Auth::user(); 
        $perfil = Profile::where('user_id', $usuario->id)->latest()->first(); 

        if (!$perfil) {
            $perfil = new Profile();
        }

        // Se garantiza la conversión nativa a Arrays para evitar errores en JavaScript
        $datos = [
            'nickname' => $perfil->nickname ?? '',
            'nombre' => $perfil->nombre ?? '',
            'correo_contacto' => $perfil->correo_contacto ?? '',
            'profesion' => $perfil->profesion ?? '',
            'bio' => $perfil->bio ?? '',
            'telefono' => $perfil->telefono ?? '',
            'direccion' => $perfil->direccion ?? '',
            'zelle' => $perfil->zelle ?? '',
            'cashapp' => $perfil->cashapp ?? '',
            'titulo_galeria' => $perfil->titulo_galeria ?? 'Servicios',
            'foto_portada' => $perfil->foto_portada ?? null,
            'foto_perfil' => $perfil->foto_perfil ?? null,
            'galeria' => is_string($perfil->galeria ?? '') ? json_decode($perfil->galeria, true) : ($perfil->galeria ?? []),
            'redes_sociales' => is_string($perfil->redes_sociales ?? '') ? json_decode($perfil->redes_sociales, true) : ($perfil->redes_sociales ?? [])
        ];

        return [
            'usuario' => $usuario,
            'perfil' => $perfil->exists ? $perfil : null, 
            // HEX_* evita que un texto con </script> rompa el bloque <script> donde se imprime
            'datosJsonSeguros' => json_encode($datos, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)
        ];
    }

    public function index() { return view('dashboard.index', $this->cargarDatos()); }
    public function perfiles() { return view('dashboard.perfiles', $this->cargarDatos()); }
    public function analiticas() { return view('dashboard.analiticas', $this->cargarDatos()); }
    public function configuracion() { return view('dashboard.configuracion', $this->cargarDatos()); }
}