<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Services\GeminiBio;
use App\Support\ImageStore;
use App\Support\RedesSociales;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function index(Request $request)
    {
        $perfil = null;
        if ($request->has('id')) {
            // Busca el perfil exacto si se pasó un ID en la URL
            $perfil = Profile::where('id', $request->id)->where('user_id', Auth::id())->first();
        }
        return view('editor', compact('perfil'));
    }

    public function validarNickname(Request $request)
    {
        $nickname = strtolower(trim($request->nickname));
        if (in_array($nickname, config('perfiles.reservados'))) {
            return response()->json(['disponible' => false]);
        }
        $existe = Profile::where('nickname', $nickname)->where('user_id', '!=', Auth::id())->exists();
        return response()->json(['disponible' => !$existe]);
    }

    public function store(Request $request)
    {
        try {
            $usuario = Auth::user();

            // CLAVE: Saber si estamos editando o creando uno nuevo
            if ($request->has('perfil_id') && $request->perfil_id != 'null') {
                $perfil = Profile::where('id', $request->perfil_id)->where('user_id', $usuario->id)->firstOrFail();
            } else {
                // Validar límite si es nuevo
                $limite = config('perfiles.limite');
                $totalPerfiles = Profile::where('user_id', $usuario->id)->count();
                if ($totalPerfiles >= $limite) {
                    return response()->json(['status' => 'error', 'message' => "Límite de $limite perfiles alcanzado. Mejora a PRO."]);
                }
                $perfil = new Profile();
                $perfil->user_id = $usuario->id;
            }

            $perfil->nickname = $request->nickname ?: ($perfil->nickname ?: 'user_' . uniqid());
            if (in_array(strtolower($perfil->nickname), config('perfiles.reservados'))) {
                return response()->json(['status' => 'error', 'message' => 'Ese nombre de usuario está reservado. Elige otro.'], 422);
            }

            // Solo se actualizan los campos que llegan en la petición: el editor los envía todos,
            // la pantalla de Configuración envía una parte y el resto se conserva.
            $campos = ['nombre_perfil', 'design_config', 'nombre', 'correo_contacto', 'profesion', 'bio',
                       'telefono', 'direccion', 'zelle', 'cashapp', 'venmo'];
            foreach ($campos as $campo) {
                // nombre_perfil es obligatorio en la BD: vacío conserva el actual (o el valor por defecto)
                if ($request->has($campo) && !($campo === 'nombre_perfil' && blank($request->input($campo)))) {
                    $perfil->{$campo} = $request->input($campo);
                }
            }

            // Solo redes del catálogo; el SVG se reconstruye en el servidor (anti-XSS)
            if ($request->has('redes_sociales')) {
                $perfil->redes_sociales = RedesSociales::sanear($request->redes_sociales);
            }

            // Foto: dataURL validado como imagen, o una ruta que apunte a una imagen propia
            $perfil->foto_perfil = ImageStore::resolver($request->foto_perfil, 'avatar', $perfil->foto_perfil);

            if ($request->has('galeria')) {
                $galeriaActual = is_array($perfil->galeria) ? $perfil->galeria : [];
                $galeriaUrls = [];
                $galeriaData = json_decode($request->galeria, true);
                if (is_array($galeriaData)) {
                    foreach ($galeriaData as $img) {
                        if (!is_string($img)) {
                            continue;
                        }
                        if (str_starts_with($img, 'data:image')) {
                            $galeriaUrls[] = ImageStore::guardarDataUrl($img, 'galeria');
                        } elseif (in_array($img, $galeriaActual, true) || ImageStore::esPropia($img)) {
                            $galeriaUrls[] = $img;
                        }
                    }
                }
                $perfil->galeria = $galeriaUrls;
            }
            $perfil->save();

            if (session()->has('nfc_pendiente')) {
                $perfil->nfc_uid = session('nfc_pendiente');
                $perfil->save();
                session()->forget('nfc_pendiente');
            }

            return response()->json(['status' => 'success', 'message' => 'Guardado exitoso']);
        } catch (ValidationException $e) {
            return response()->json(['status' => 'error', 'message' => collect($e->errors())->flatten()->first()], 422);
        } catch (QueryException $e) {
            Log::error('Error guardando perfil', ['user_id' => Auth::id(), 'error' => $e->getMessage()]);
            $mensaje = ($e->errorInfo[1] ?? null) == 1062
                ? 'Ese nombre de usuario ya está en uso. Elige otro.'
                : 'No se pudo guardar el perfil. Inténtalo de nuevo.';
            return response()->json(['status' => 'error', 'message' => $mensaje], 500);
        } catch (\Exception $e) {
            Log::error('Error guardando perfil', ['user_id' => Auth::id(), 'error' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => 'No se pudo guardar el perfil. Inténtalo de nuevo.'], 500);
        }
    }

    public function duplicar($id)
    {
        try {
            $usuario = \Illuminate\Support\Facades\Auth::user();
            $limite = config('perfiles.limite');
            if (Profile::where('user_id', $usuario->id)->count() >= $limite) {
                return response()->json(['status' => 'error', 'message' => "Límite de $limite perfiles alcanzado."]);
            }

            $perfilOriginal = Profile::where('id', $id)->where('user_id', $usuario->id)->firstOrFail();

            $nuevoPerfil = $perfilOriginal->replicate();
            $nuevoPerfil->nombre_perfil = $perfilOriginal->nombre_perfil . ' (Copia)';

            // Generamos el sufijo para evitar el colapso de la base de datos
            $nuevoPerfil->nickname = $perfilOriginal->nickname . '-' . substr(uniqid(), -4);

            $nuevoPerfil->nfc_uid = null;
            $nuevoPerfil->created_at = now();
            $nuevoPerfil->updated_at = now()->subSecond();

            $nuevoPerfil->save();

            return response()->json(['status' => 'success', 'message' => 'Perfil duplicado con éxito.']);
        } catch (\Exception $e) {
            Log::error('Error duplicando perfil', ['user_id' => Auth::id(), 'perfil' => $id, 'error' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => 'No se pudo duplicar el perfil. Inténtalo de nuevo.'], 500);
        }
    }

    public function activar($id)
    {
        try {
            $usuario = \Illuminate\Support\Facades\Auth::user();
            $nuevoActivo = Profile::where('id', $id)->where('user_id', $usuario->id)->firstOrFail();

            // Buscamos si el usuario ya tiene un chip físico
            $perfilConNfc = Profile::where('user_id', $usuario->id)->whereNotNull('nfc_uid')->first();

            if ($perfilConNfc) {
                if ($perfilConNfc->id == $id) {
                    return response()->json(['status' => 'success', 'message' => 'Este perfil ya es el activo.']);
                }
                // Si tiene chip, lo mudamos de tarjeta a tarjeta
                $chipIdTemporal = $perfilConNfc->nfc_uid;
                $perfilConNfc->nfc_uid = null;
                $perfilConNfc->save();

                $nuevoActivo->nfc_uid = $chipIdTemporal;
            }

            // Forzamos la actualización de la fecha. Así, incluso si no tiene chip físico,
            // el sistema sabrá que esta es la tarjeta que debe mostrar en su Link de Bio.
            $nuevoActivo->updated_at = now();
            $nuevoActivo->save();

            return response()->json(['status' => 'success', 'message' => 'Perfil fijado como principal exitosamente.']);
        } catch (\Exception $e) {
            Log::error('Error activando perfil', ['user_id' => Auth::id(), 'perfil' => $id, 'error' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => 'Error al activar.'], 500);
        }
    }

    public function eliminar($id)
    {
        try {
            $usuario = \Illuminate\Support\Facades\Auth::user();

            // 1. Verificamos que no sea su único perfil
            $totalPerfiles = Profile::where('user_id', $usuario->id)->count();
            if ($totalPerfiles <= 1) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No puedes eliminar tu único perfil. Si deseas modificarlo, usa la opción Editar.'
                ]);
            }

            // 2. Buscamos el perfil que se desea eliminar
            $perfil = Profile::where('id', $id)->where('user_id', $usuario->id)->firstOrFail();

            // 3. Obtenemos el perfil activo (la misma regla que el perfil público)
            $perfilActivo = Profile::activoDe($usuario->id);

            // 4. Bloqueo estricto: solo se impide si coincide exactamente con el perfil activo
            if ($perfilActivo && $perfilActivo->id === $perfil->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No puedes eliminar el perfil activo en tu tarjeta física. Fija otro perfil como activo primero.'
                ]);
            }

            // 5. Eliminación del perfil secundario
            $perfil->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Perfil eliminado correctamente.'
            ]);

        } catch (\Exception $e) {
            Log::error('Error eliminando perfil', ['user_id' => Auth::id(), 'perfil' => $id, 'error' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => 'No se pudo eliminar el perfil. Inténtalo de nuevo.'
            ], 500);
        }
    }

    public function generarBioIA(Request $request, GeminiBio $gemini)
    {
        $profesion = mb_substr((string) ($request->profesion ?? 'profesional'), 0, 120);
        $bioActual = mb_substr((string) ($request->bio_actual ?? ''), 0, 600);

        $bioRespaldo = "Soy un $profesion altamente capacitado con un sólido enfoque en resultados. " .
                       ($bioActual ? "Me especializo en: $bioActual. " : "") .
                       "Mi principal objetivo es aportar valor inmediato a mis clientes a través de soluciones innovadoras.";

        // Si Gemini no responde, se devuelve el texto de respaldo (mismo comportamiento que antes)
        $bio = $gemini->generar($profesion, $bioActual) ?? $bioRespaldo;

        return response()->json(['status' => 'success', 'bio' => $bio]);
    }
}
