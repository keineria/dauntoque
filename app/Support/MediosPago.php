<?php

namespace App\Support;

use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Medios de pago del perfil (config/pagos.php): qué pide cada país y cómo se limpia lo que escribe el usuario.
 * Se guardan en profiles.payment_methods como [{type, value}].
 */
class MediosPago
{
    public static function paises(): array
    {
        return array_keys(config('pagos.por_pais'));
    }

    public static function pais(?string $pais): string
    {
        $pais = strtoupper((string) $pais);

        return in_array($pais, self::paises(), true) ? $pais : config('pagos.pais_defecto');
    }

    /** País de la conexión (encabezado CF-IPCountry que pone Cloudflare) si es uno de los países de pago; si no, null. */
    public static function porConexion(Request $request): ?string
    {
        $pais = strtoupper((string) $request->header('CF-IPCountry'));

        return in_array($pais, self::paises(), true) ? $pais : null;
    }

    /**
     * País de pagos de un perfil nuevo: el de sus otros perfiles y, si es el primero, el de su conexión.
     * null si ninguno sirve: entonces el editor lo adivina por la zona horaria del navegador.
     * El usuario lo cambia luego en Datos del perfil → Métodos de pago por país.
     */
    public static function sugerido(Request $request, ?int $userId): ?string
    {
        $deSusPerfiles = $userId ? Profile::where('user_id', $userId)->whereNotNull('pais')->orderBy('id')->value('pais') : null;

        return in_array($deSusPerfiles, self::paises(), true) ? $deSusPerfiles : self::porConexion($request);
    }

    /** Tipos que se piden en un país, en el orden en que se muestran. */
    public static function tiposDe(?string $pais): array
    {
        return config('pagos.por_pais.' . self::pais($pais), []);
    }

    /**
     * Limpia la lista que llega del editor: solo tipos del país, sin duplicados, en el orden del país.
     * Lanza ValidationException si un dato no tiene el formato del medio.
     */
    public static function sanear($entrada, ?string $pais): array
    {
        $lista = is_string($entrada) ? json_decode($entrada, true) : $entrada;
        if (!is_array($lista)) {
            return [];
        }

        $valores = [];
        foreach ($lista as $medio) {
            $tipo = is_array($medio) ? ($medio['type'] ?? null) : null;
            $valor = is_array($medio) && is_scalar($medio['value'] ?? null) ? self::limpiar($tipo, (string) $medio['value']) : '';
            if (is_string($tipo) && $valor !== '' && !isset($valores[$tipo])) {
                $valores[$tipo] = $valor;
            }
        }

        $resultado = [];
        foreach (self::tiposDe($pais) as $tipo) {
            if (isset($valores[$tipo])) {
                self::validar($tipo, $valores[$tipo]);
                $resultado[] = ['type' => $tipo, 'value' => $valores[$tipo]];
            }
        }

        return $resultado;
    }

    /** Lista guardada lista para el perfil público (solo tipos conocidos y con valor). */
    public static function publicos($guardados): array
    {
        $lista = is_string($guardados) ? json_decode($guardados, true) : $guardados;
        $medios = config('pagos.medios');

        return array_values(array_filter(
            is_array($lista) ? $lista : [],
            fn ($m) => is_array($m) && isset($medios[$m['type'] ?? '']) && filled($m['value'] ?? null)
        ));
    }

    private static function limpiar(?string $tipo, string $valor): string
    {
        $valor = trim(preg_replace('/[\x00-\x1F\x7F]/u', '', $valor));
        $valor = mb_substr($valor, 0, 80);
        $sinPrefijo = fn (string $prefijo) => ($resto = ltrim($valor, $prefijo . ' ')) === '' ? '' : $prefijo . $resto;

        return match ($tipo) {
            // "+57 300 123 4567" se guarda como 3001234567
            'nequi' => preg_replace('/^57(?=3\d{9}$)/', '', preg_replace('/\D/', '', $valor)),
            'cashapp' => $sinPrefijo('$'),
            'venmo' => $sinPrefijo('@'),
            default => $valor,
        };
    }

    private static function validar(string $tipo, string $valor): void
    {
        $error = match ($tipo) {
            'nequi' => preg_match('/^3\d{9}$/', $valor) ? null : 'El número de Nequi debe ser un celular de 10 dígitos que empiece por 3.',
            'cashapp' => preg_match('/^\$[A-Za-z][A-Za-z0-9_-]{0,19}$/', $valor) ? null : 'El $Cashtag de Cash App solo lleva letras y números (ej. $carlos).',
            'venmo' => preg_match('/^@[A-Za-z0-9_-]{2,30}$/', $valor) ? null : 'El usuario de Venmo solo lleva letras, números, guion o guion bajo.',
            default => null,
        };

        if ($error) {
            throw ValidationException::withMessages(['payment_methods' => $error]);
        }
    }
}
