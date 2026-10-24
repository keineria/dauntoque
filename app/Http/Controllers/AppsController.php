<?php

namespace App\Http\Controllers;

use App\Models\AppAdicional;
use App\Models\Profile;
use App\Services\Agenda\Agenda;
use Illuminate\Support\Facades\Auth;

/** Catálogo de apps adicionales, al estilo de las extensiones de Chrome: se activan aquí y quedan en "Apps activas". */
class AppsController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        $activas = AppAdicional::where('user_id', $userId)->get()->keyBy('app');
        $precio = Agenda::precioRenovacion(Profile::activoDe($userId)?->pais);

        $apps = [
            [
                'clave' => 'agenda',
                'icono' => '📅',
                'nombre' => 'Agenda de citas',
                'resumen' => 'Tus clientes reservan desde tu tarjeta. Avisos por correo, cambios con tu aprobación y Google Calendar.',
                'precio' => 'Gratis el primer año, luego ' . $precio . ' al año.',
                'abrir' => '/dashboard/agenda',
                'activar' => '/dashboard/agenda/activar',
                'app' => $activas->get('agenda'),
            ],
            [
                'clave' => 'calificame',
                'icono' => '⭐',
                'nombre' => 'Califícame',
                'resumen' => 'Tus clientes califican tu atención en un toque. Sello de estrellas en tu perfil, tú eliges qué comentarios se ven y las bajas te llegan en privado.',
                'precio' => 'Gratis el primer año.',
                'abrir' => '/dashboard/calificame',
                'activar' => '/dashboard/calificame/activar',
                'app' => $activas->get('calificame'),
            ],
            [
                'clave' => 'asistente',
                'icono' => '🤖',
                'nombre' => 'Asistente con IA',
                'resumen' => 'Responde las preguntas de tus clientes desde tu perfil, a cualquier hora.',
                'proximamente' => true,
            ],
            [
                'clave' => 'catalogo',
                'icono' => '🛍️',
                'nombre' => 'Catálogo de productos',
                'resumen' => 'Muestra tus productos con foto y precio, y recibe pedidos por WhatsApp.',
                'proximamente' => true,
            ],
        ];

        return view('dashboard.apps', ['apps' => $apps]);
    }
}
