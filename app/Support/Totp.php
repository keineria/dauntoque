<?php

namespace App\Support;

/**
 * Códigos de 6 dígitos de las apps autenticadoras (Google Authenticator, Microsoft Authenticator, Authy…),
 * según RFC 6238: HMAC-SHA1, pasos de 30 segundos.
 */
class Totp
{
    private const ALFABETO = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /** Secreto nuevo en base32 (160 bits). */
    public static function secreto(): string
    {
        $bytes = random_bytes(20);
        $bits = '';
        foreach (str_split($bytes) as $byte) {
            $bits .= str_pad(decbin(ord($byte)), 8, '0', STR_PAD_LEFT);
        }

        return implode('', array_map(fn ($grupo) => self::ALFABETO[bindec($grupo)], str_split($bits, 5)));
    }

    /** Código vigente en el paso dado (por defecto, el de ahora). */
    public static function codigo(string $secreto, ?int $paso = null): string
    {
        $paso ??= intdiv(time(), 30);
        $hash = hash_hmac('sha1', pack('J', $paso), self::decodificar($secreto), true);
        $desde = ord($hash[19]) & 0x0F;
        $numero = (unpack('N', substr($hash, $desde, 4))[1] & 0x7FFFFFFF) % 1_000_000;

        return str_pad((string) $numero, 6, '0', STR_PAD_LEFT);
    }

    /** Acepta el código de ahora y el de 30 s antes o después (relojes un poco desfasados). */
    public static function verificar(string $secreto, string $codigo): bool
    {
        $codigo = preg_replace('/\D/', '', $codigo);
        if (strlen($codigo) !== 6) {
            return false;
        }
        $ahora = intdiv(time(), 30);
        foreach ([-1, 0, 1] as $desfase) {
            if (hash_equals(self::codigo($secreto, $ahora + $desfase), $codigo)) {
                return true;
            }
        }

        return false;
    }

    /** Enlace otpauth:// que la app lee desde el código QR. */
    public static function enlace(string $secreto, string $cuenta, string $emisor = 'DaUnToque'): string
    {
        return 'otpauth://totp/'.rawurlencode($emisor.':'.$cuenta).'?'.http_build_query([
            'secret' => $secreto, 'issuer' => $emisor, 'algorithm' => 'SHA1', 'digits' => 6, 'period' => 30,
        ]);
    }

    private static function decodificar(string $secreto): string
    {
        $bits = '';
        foreach (str_split(strtoupper(preg_replace('/[^A-Za-z2-7]/', '', $secreto))) as $letra) {
            $bits .= str_pad(decbin(strpos(self::ALFABETO, $letra)), 5, '0', STR_PAD_LEFT);
        }

        return implode('', array_map(fn ($byte) => chr(bindec($byte)), array_filter(str_split($bits, 8), fn ($b) => strlen($b) === 8)));
    }
}
