<?php

namespace App\Http\Controllers;

use App\Models\Market;
use App\Models\Profile;
use App\Services\GeminiBio;
use App\Support\Galeria;
use App\Support\ImageStore;
use App\Support\MediosPago;
use App\Support\RedesSociales;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
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
        $mercados = Market::opciones();
        // Estado de la agenda para el bloque "Botón Agendar cita" del editor
        $agendaEstado = \App\Services\Agenda\Agenda::estado(Auth::id());

        // País de pagos para un perfil nuevo: el de sus otros perfiles o el de su conexión
        $paisSugerido = MediosPago::sugerido($request, Auth::id());

        return view('editor', compact('perfil', 'mercados', 'agendaEstado', 'paisSugerido'));
    }

    public function validarNickname(Request $request)
    {
        $error = self::errorNickname(strtolower(trim((string) $request->nickname)));

        return response()->json(['disponible' => $error === null, 'mensaje' => $error]);
    }

    /**
     * Revisa un nickname (ya en minúsculas): formato, palabras reservadas y que nadie más lo use.
     * Devuelve el error para mostrar o null si se puede usar.
     */
    private static function errorNickname(string $nickname, ?int $perfilId = null): ?string
    {
        if ($nickname === '') {
            return 'Escribe un nombre de usuario.';
        }
        if (!preg_match('/^[a-z0-9._-]{3,30}$/', $nickname)) {
            return 'El usuario debe tener de 3 a 30 caracteres: solo letras sin tilde, números, guion (-), guion bajo (_) o punto (.).';
        }
        if (in_array($nickname, config('perfiles.reservados'), true)) {
            return 'Ese nombre de usuario está reservado. Elige otro.';
        }
        if (Profile::where('nickname', $nickname)->when($perfilId, fn ($q) => $q->where('id', '!=', $perfilId))->exists()) {
            return 'Ese nombre de usuario ya está en uso. Elige otro.';
        }

        return null;
    }

    /** Reglas de los datos del perfil; los mensajes salen en español sin depender del idioma de la app. */
    private static function validarCampos(Request $request, bool $nuevo): void
    {
        $texto = fn (int $max) => ['nullable', 'string', 'max:' . $max];

        $request->validate([
            'nombre' => [$nuevo ? 'required' : 'sometimes', 'required', 'string', 'max:100'],
            'nombre_perfil' => $texto(60),
            'profesion' => $texto(120),
            'bio' => $texto(1500),
            'direccion' => $texto(200),
            'telefono' => ['nullable', 'string', 'max:30', 'regex:/^[0-9 +()\-]+$/'],
            'correo_contacto' => ['nullable', 'string', 'max:150', 'email'],
            'tipo_perfil' => ['sometimes', 'required', Rule::in(config('perfiles.tipos'))],
            'design_config' => ['nullable', 'string', 'max:5000', 'json'],
        ], [
            'nombre.required' => 'Escribe tu nombre público.',
            'telefono.regex' => 'El teléfono solo puede tener números, espacios y los signos + ( ) -.',
            'correo_contacto.email' => 'Escribe un correo de contacto válido (ej. nombre@correo.com).',
            'tipo_perfil.*' => 'Elige un uso válido para tu tarjeta: corporativo, empresa o hobbies.',
            'design_config.*' => 'El diseño de la tarjeta no es válido. Vuelve a elegirlo.',
            'max' => ':attribute no puede tener más de :max caracteres.',
            'string' => ':attribute no es válido.',
        ], [
            'nombre' => 'El nombre', 'nombre_perfil' => 'El nombre del perfil', 'profesion' => 'El título o profesión',
            'bio' => 'La descripción', 'direccion' => 'La dirección', 'telefono' => 'El teléfono', 'correo_contacto' => 'El correo',
        ]);
    }

    public function store(Request $request)
    {
        $usuario = Auth::user();

        // CLAVE: Saber si estamos editando o creando uno nuevo (un perfil de otra cuenta da 404)
        $perfil = null;
        if ($request->filled('perfil_id') && $request->perfil_id != 'null') {
            $perfil = Profile::where('id', $request->perfil_id)->where('user_id', $usuario->id)->firstOrFail();
        }

        try {
            if (!$perfil) {
                // Validar límite si es nuevo
                $limite = config('perfiles.limite');
                $totalPerfiles = Profile::where('user_id', $usuario->id)->count();
                if ($totalPerfiles >= $limite) {
                    return response()->json(['status' => 'error', 'message' => "Tu cuenta incluye $limite perfiles y ya los tienes. Puedes editar o borrar uno."], 422);
                }
                $perfil = new Profile();
                $perfil->user_id = $usuario->id;
                // El primer perfil de la cuenta es el activo; los siguientes solo cuando el usuario los activa
                $perfil->activo_en = $totalPerfiles === 0 ? now() : null;
            }

            self::validarCampos($request, !$perfil->exists);

            // El nickname solo se revisa si es nuevo o cambia (los perfiles antiguos conservan el suyo)
            $nickname = strtolower(trim((string) $request->nickname));
            if (!$perfil->exists || ($nickname !== '' && $nickname !== $perfil->nickname)) {
                if ($error = self::errorNickname($nickname, $perfil->id)) {
                    return response()->json(['status' => 'error', 'message' => $error], 422);
                }
                $perfil->nickname = $nickname;
            }

            // Solo se actualizan los campos que llegan en la petición: el editor los envía todos,
            // la pantalla de Configuración envía una parte y el resto se conserva.
            $campos = ['nombre_perfil', 'design_config', 'nombre', 'correo_contacto', 'profesion', 'bio',
                       'telefono', 'direccion', 'tipo_perfil'];
            foreach ($campos as $campo) {
                // nombre_perfil es obligatorio en la BD: vacío conserva el actual (o el valor por defecto)
                if ($request->has($campo) && !($campo === 'nombre_perfil' && blank($request->input($campo)))) {
                    $perfil->{$campo} = $request->input($campo);
                }
            }

            // País y medios de pago: solo los del país elegido (Nequi y Llave Bre-B en CO; Zelle, Cash App y Venmo en US)
            if ($request->has('pais')) {
                $perfil->pais = MediosPago::pais($request->input('pais'));
            } elseif (!$perfil->exists) {
                $perfil->pais = MediosPago::pais(MediosPago::sugerido($request, $usuario->id));
            }
            if ($request->has('payment_methods')) {
                $perfil->payment_methods = MediosPago::sanear($request->input('payment_methods'), $perfil->pais) ?: null;
            }

            // Solo redes del catálogo; el SVG se reconstruye en el servidor (anti-XSS)
            if ($request->has('redes_sociales')) {
                $perfil->redes_sociales = RedesSociales::sanear($request->redes_sociales);
            }

            // Foto: dataURL validado como imagen, o una ruta que apunte a una imagen propia
            $perfil->foto_perfil = ImageStore::resolver($request->foto_perfil, 'avatar', $perfil->foto_perfil);

            // Galería: [{url, titulo}]; las imágenes nuevas llegan como dataURL
            if ($request->has('galeria')) {
                $perfil->galeria = Galeria::guardar($request->galeria, (array) $perfil->galeria);
            }
            $perfil->save();

            // Chip escaneado antes de tener perfil: va al perfil activo, igual que en NfcController
            ActivarTarjetaController::enlazarPendiente($usuario->id);

            // nickname = link maestro de la cuenta (el botón "Ver mi perfil" del editor)
            return response()->json(['status' => 'success', 'message' => 'Guardado exitoso', 'nickname' => Profile::maestroDe($usuario->id)?->nickname]);
        } catch (ValidationException $e) {
            return response()->json(['status' => 'error', 'message' => collect($e->errors())->flatten()->first()], 422);
        } catch (QueryException $e) {
            Log::error('Error guardando perfil', ['user_id' => Auth::id(), 'error' => $e->getMessage()]);
            $mensaje = in_array($e->errorInfo[1] ?? null, [1062, 19])
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
        $usuario = Auth::user();
        $perfilOriginal = Profile::where('id', $id)->where('user_id', $usuario->id)->firstOrFail();

        try {
            $limite = config('perfiles.limite');
            if (Profile::where('user_id', $usuario->id)->count() >= $limite) {
                return response()->json(['status' => 'error', 'message' => "Tu cuenta incluye $limite perfiles y ya los tienes. Puedes editar o borrar uno."], 422);
            }

            $nuevoPerfil = $perfilOriginal->replicate();
            $nuevoPerfil->nombre_perfil = $perfilOriginal->nombre_perfil . ' (Copia)';

            // Generamos el sufijo para evitar el colapso de la base de datos
            $nuevoPerfil->nickname = $perfilOriginal->nickname . '-' . substr(uniqid(), -4);

            $nuevoPerfil->nfc_uid = null;
            $nuevoPerfil->activo_en = null;
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
        $usuario = Auth::user();
        $nuevoActivo = Profile::where('id', $id)->where('user_id', $usuario->id)->firstOrFail();

        try {
            if ($nuevoActivo->activo_en && Profile::activoDe($usuario->id)?->id === $nuevoActivo->id) {
                return response()->json(['status' => 'success', 'message' => 'Este perfil ya es el activo.']);
            }

            // Si el usuario tiene chip físico, lo mudamos de tarjeta a tarjeta
            $perfilConNfc = Profile::where('user_id', $usuario->id)->whereNotNull('nfc_uid')->first();
            if ($perfilConNfc && $perfilConNfc->id !== $nuevoActivo->id) {
                $chipIdTemporal = $perfilConNfc->nfc_uid;
                $perfilConNfc->nfc_uid = null;
                $perfilConNfc->save();

                $nuevoActivo->nfc_uid = $chipIdTemporal;
            }

            // Marca explícita del perfil activo: es el que muestra su link maestro (y su tarjeta)
            Profile::where('user_id', $usuario->id)->where('id', '!=', $nuevoActivo->id)->update(['activo_en' => null]);
            $nuevoActivo->activo_en = now();
            $nuevoActivo->save();

            return response()->json(['status' => 'success', 'message' => 'Perfil fijado como principal exitosamente.']);
        } catch (\Exception $e) {
            Log::error('Error activando perfil', ['user_id' => Auth::id(), 'perfil' => $id, 'error' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => 'Error al activar.'], 500);
        }
    }

    public function eliminar($id)
    {
        $usuario = Auth::user();
        // 0. Un perfil de otra cuenta da 404
        $perfil = Profile::where('id', $id)->where('user_id', $usuario->id)->firstOrFail();

        try {

            // 1. Verificamos que no sea su único perfil
            $totalPerfiles = Profile::where('user_id', $usuario->id)->count();
            if ($totalPerfiles <= 1) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No puedes eliminar tu único perfil. Si deseas modificarlo, usa la opción Editar.'
                ]);
            }

            // 2. Obtenemos el perfil activo (la misma regla que el perfil público)
            $perfilActivo = Profile::activoDe($usuario->id);

            // 3. Bloqueo estricto: solo se impide si coincide exactamente con el perfil activo
            if ($perfilActivo && $perfilActivo->id === $perfil->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No puedes eliminar el perfil activo en tu tarjeta física. Fija otro perfil como activo primero.'
                ]);
            }

            // 4. Eliminación del perfil secundario
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
