<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Str;

/** Verificación en dos pasos de una cuenta: activar, comprobar códigos y códigos de respaldo de un solo uso. */
class DosPasos
{
    /**
     * Activa la verificación con el secreto ya comprobado y devuelve los códigos de respaldo (se muestran una vez).
     *
     * @return array<int, string>
     */
    public static function activar(User $usuario, string $secreto): array
    {
        $codigos = self::codigosNuevos();
        $usuario->forceFill([
            'dos_pasos_secreto' => $secreto,
            'dos_pasos_respaldo' => array_map([self::class, 'huella'], $codigos),
            'dos_pasos_activo_en' => now(),
        ])->save();

        return $codigos;
    }

    public static function desactivar(User $usuario): void
    {
        $usuario->forceFill(['dos_pasos_secreto' => null, 'dos_pasos_respaldo' => null, 'dos_pasos_activo_en' => null])->save();
    }

    /** @return array<int, string> */
    public static function renovarRespaldo(User $usuario): array
    {
        $codigos = self::codigosNuevos();
        $usuario->forceFill(['dos_pasos_respaldo' => array_map([self::class, 'huella'], $codigos)])->save();

        return $codigos;
    }

    /** Código de la app (6 dígitos) o de respaldo (xxxx-xxxx); el de respaldo se gasta al usarlo. */
    public static function verificar(User $usuario, string $codigo): bool
    {
        $codigo = trim($codigo);
        if (preg_match('/^\d[\d ]{4,6}\d$/', $codigo)) {
            return Totp::verificar((string) $usuario->dos_pasos_secreto, $codigo);
        }

        $huella = self::huella($codigo);
        $guardados = $usuario->dos_pasos_respaldo ?? [];
        if (! in_array($huella, $guardados, true)) {
            return false;
        }
        $usuario->forceFill(['dos_pasos_respaldo' => array_values(array_diff($guardados, [$huella]))])->save();

        return true;
    }

    public static function respaldoRestante(User $usuario): int
    {
        return count($usuario->dos_pasos_respaldo ?? []);
    }

    /** @return array<int, string> */
    private static function codigosNuevos(): array
    {
        return array_map(fn () => Str::lower(Str::random(4)).'-'.Str::lower(Str::random(4)), range(1, 8));
    }

    private static function huella(string $codigo): string
    {
        return hash_hmac('sha256', strtolower(str_replace([' ', '-'], '', $codigo)), (string) config('app.key'));
    }
}
