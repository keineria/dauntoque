<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Cliente mínimo de Bold: crea links de pago y verifica la firma de los webhooks.
 * Docs: developers.bold.co (API Link de pagos y Webhook).
 */
class Bold
{
    public static function configurado(): bool
    {
        return filled(config('services.bold.api_key'));
    }

    /**
     * Crea un link de pago de monto cerrado. Devuelve ['id' => 'LNK_...', 'url' => 'https://checkout.bold.co/...'].
     * Bold acepta montos en COP o USD; los USD los cobra en pesos con la TRM del día.
     */
    public static function crearLink(int|float $monto, string $moneda, string $descripcion, ?string $correo, string $volverA): array
    {
        $respuesta = Http::withHeaders(['Authorization' => 'x-api-key ' . config('services.bold.api_key')])
            ->timeout(config('services.bold.timeout'))
            ->post(rtrim(config('services.bold.url'), '/') . '/online/link/v1', array_filter([
                'amount_type' => 'CLOSE',
                'amount' => ['currency' => $moneda, 'total_amount' => $monto, 'tip_amount' => 0, 'taxes' => []],
                'description' => mb_substr($descripcion, 0, 100),
                'payer_email' => $correo,
                'callback_url' => $volverA,
            ]));

        $link = $respuesta->json('payload');
        if ($respuesta->failed() || empty($link['payment_link']) || empty($link['url'])) {
            throw new RuntimeException('Bold no creó el link: HTTP ' . $respuesta->status() . ' ' . json_encode($respuesta->json('errors')));
        }

        return ['id' => $link['payment_link'], 'url' => $link['url']];
    }

    /** x-bold-signature = HMAC-SHA256 (hex) del cuerpo en Base64, con la llave secreta. */
    public static function firmaValida(string $cuerpo, ?string $firma): bool
    {
        if ($firma === null || $firma === '') {
            return false;
        }
        $esperada = hash_hmac('sha256', base64_encode($cuerpo), (string) config('services.bold.secret'));

        return hash_equals($esperada, strtolower($firma));
    }
}
