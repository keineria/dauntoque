<?php

namespace App\Services\Agenda;

use App\Models\AgendaAjuste;
use App\Models\AgendaHorario;
use App\Models\AgendaServicio;
use App\Models\AppAdicional;
use App\Models\Market;
use App\Models\Profile;
use App\Support\MediosPago;

/** Estado de la app Agenda de un usuario: activación, año gratis y si ya recibe reservas en línea. */
class Agenda
{
    public static function app(int $userId): ?AppAdicional
    {
        return AppAdicional::de($userId, 'agenda');
    }

    /** Activa la agenda con su primer año gratis y crea los ajustes con la zona horaria del país. */
    public static function activar(int $userId): AppAdicional
    {
        $app = AppAdicional::firstOrCreate(['user_id' => $userId, 'app' => 'agenda'], [
            'activada_en' => now(),
            'vence_en' => now()->addDays(config('agenda.dias_gratis')),
        ]);
        AgendaAjuste::de($userId);

        return $app;
    }

    /**
     * Cómo se ve la agenda en el perfil público:
     * 'activa' (recibe reservas), 'basica' (venció: solo botón de WhatsApp) o null (sin agenda o sin configurar).
     */
    public static function modoPublico(int $userId): ?string
    {
        $app = self::app($userId);
        if (!$app) {
            return null;
        }
        if (!$app->vigente()) {
            return 'basica';
        }

        $lista = AgendaServicio::where('user_id', $userId)->where('activo', true)->exists()
            && AgendaHorario::where('user_id', $userId)->exists();

        return $lista ? 'activa' : null;
    }

    /** Precio de renovación de la agenda en la moneda del país: "$99.000 COP" o "$39 USD". */
    public static function precioRenovacion(?string $pais): string
    {
        $pais = MediosPago::pais($pais);
        $monto = config("agenda.precio_renovacion.$pais", 0);
        $moneda = Market::where('code', $pais)->value('currency') ?? ($pais === 'US' ? 'USD' : 'COP');

        return '$' . number_format($monto, 0, ',', $pais === 'US' ? ',' : '.') . ' ' . $moneda;
    }

    /** "$25.000" o "$40" en la moneda del país del dueño; null si el servicio no tiene precio. */
    public static function precio(?string $monto, int $userId): ?string
    {
        if ($monto === null || (float) $monto <= 0) {
            return null;
        }
        $us = MediosPago::pais(Profile::activoDe($userId)?->pais) === 'US';
        $decimales = $us && floor((float) $monto) != (float) $monto ? 2 : 0;

        return '$' . number_format((float) $monto, $decimales, $us ? '.' : ',', $us ? ',' : '.');
    }
}
