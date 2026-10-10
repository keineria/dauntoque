<?php

namespace App\Http\Controllers;

use App\Models\Market;
use App\Models\Profile;
use App\Models\Tarjeta;
use App\Support\ImageStore;
use App\Support\MediosPago;
use App\Support\RedesSociales;
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
            // Id del perfil cargado: Configuración lo envía al guardar para editarlo (y no crear otro)
            'id' => $perfil->id,
            'nickname' => $perfil->nickname ?? '',
            'nombre' => $perfil->nombre ?? '',
            'correo_contacto' => $perfil->correo_contacto ?? '',
            'profesion' => $perfil->profesion ?? '',
            'bio' => $perfil->bio ?? '',
            'telefono' => $perfil->telefono ?? '',
            'direccion' => $perfil->direccion ?? '',
            'pais' => MediosPago::pais($perfil->pais),
            'payment_methods' => MediosPago::publicos($perfil->payment_methods),
            'titulo_galeria' => $perfil->titulo_galeria ?? 'Servicios',
            'foto_portada' => $perfil->foto_portada ?? null,
            'foto_perfil' => $perfil->foto_perfil ?? null,
            'galeria' => is_string($perfil->galeria ?? '') ? json_decode($perfil->galeria, true) : ($perfil->galeria ?? []),
            // Con "plataforma" para que el selector de Configuración muestre la red correcta
            'redes_sociales' => RedesSociales::conPlataforma($perfil->redes_sociales ?? [])
        ];

        return [
            'usuario' => $usuario,
            'perfil' => $perfil->exists ? $perfil : null, 
            // HEX_* evita que un texto con </script> rompa el bloque <script> donde se imprime
            'datosJsonSeguros' => json_encode($datos, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)
        ];
    }

    /**
     * Perfiles del usuario para las fichas del panel, en el orden de la regla de perfil activo:
     * el primero es el que se ve en público (y el que abre el chip NFC).
     */
    private function fichasPerfiles(int $userId): array
    {
        $maestro = Profile::maestroDe($userId);

        return Profile::where('user_id', $userId)->ordenActivo()->get()
            ->values()
            ->map(function (Profile $p, int $i) use ($maestro) {
                $diseno = is_array($p->design_config) ? $p->design_config : (json_decode($p->design_config ?? '', true) ?: []);
                $color = $diseno['color_primario'] ?? '#3b82f6';

                return [
                    'id' => $p->id,
                    'nombrePerfil' => $p->nombre_perfil ?: 'Mi Tarjeta',
                    'nombre' => $p->nombre,
                    'inicial' => mb_strtoupper(mb_substr($p->nombre ?: '?', 0, 1)),
                    'nickname' => $maestro?->nickname ?? $p->nickname,
                    'foto' => ImageStore::esPropia($p->foto_perfil) ? $p->foto_perfil : null,
                    'publico' => $i === 0,
                    'tieneChip' => filled($p->nfc_uid),
                    'diseno' => $diseno,
                    'tema' => $diseno['tema_base'] ?? 'crystal',
                    'color' => preg_match('/^#[0-9a-fA-F]{3,8}$/', $color) ? $color : '#3b82f6',
                    'forma' => $diseno['forma_botones'] ?? 'rounded-2xl',
                    'estructura' => $diseno['layout_estructura'] ?? 'clasica',
                ];
            })->all();
    }

    /** Tarjetas físicas del usuario con su contador de servicio (365 días desde la activación). */
    private function tarjetas(int $userId): array
    {
        return Tarjeta::where('user_id', $userId)->orderBy('activada_en')->get()
            ->map(fn (Tarjeta $t) => ['id' => $t->id, 'uid' => $t->nfc_uid] + $t->servicio())
            ->all();
    }

    /** WhatsApp de ventas del país del usuario (o el de cualquier país que lo tenga) para pedir la renovación. */
    private function whatsappVentas(?string $pais): ?string
    {
        return Market::whereNotNull('whatsapp')->where('whatsapp', '!=', '')
            ->orderByRaw('CASE WHEN code = ? THEN 0 ELSE 1 END', [MediosPago::pais($pais)])
            ->value('whatsapp');
    }

    /** Precio de la renovación anual en la moneda del país del usuario: "$120.000 COP" o "$55 USD". */
    private function precioRenovacion(?string $pais): ?string
    {
        $pais = MediosPago::pais($pais);
        $monto = config("servicio.precio_renovacion.$pais");
        if ($monto === null) {
            return null;
        }
        $moneda = Market::where('code', $pais)->value('currency') ?? ($pais === 'US' ? 'USD' : 'COP');

        return '$' . number_format($monto, 0, ',', $pais === 'US' ? ',' : '.') . ' ' . $moneda;
    }

    public function index()
    {
        return view('dashboard.index', $this->cargarDatos() + [
            'stats' => AnaliticasController::resumen(Auth::id()),
            'fichas' => $this->fichasPerfiles(Auth::id()),
            'tarjetas' => $this->tarjetas(Auth::id()),
            'whatsappRenovar' => $this->whatsappVentas(Profile::activoDe(Auth::id())?->pais),
            'pagosEnLinea' => \App\Services\Bold::configurado(),
            'precioRenovacion' => $this->precioRenovacion(Profile::activoDe(Auth::id())?->pais),
        ]);
    }

    public function perfiles()
    {
        return view('dashboard.perfiles', $this->cargarDatos() + [
            'fichas' => $this->fichasPerfiles(Auth::id()),
            'limite' => config('perfiles.limite'),
        ]);
    }
    public function analiticas()
    {
        return view('dashboard.analiticas', $this->cargarDatos() + ['stats' => AnaliticasController::resumen(Auth::id())]);
    }
    public function configuracion()
    {
        return view('dashboard.configuracion', $this->cargarDatos() + ['mercados' => Market::opciones()]);
    }
}