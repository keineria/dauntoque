<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Enlace con UTM (o su QR): a dónde lleva, por qué canal se comparte y, si se paga publicidad, cuánto se invierte. */
class Campana extends Model
{
    protected $table = 'campanas';

    protected $fillable = [
        'nombre', 'canal', 'utm_source', 'utm_medium', 'utm_campaign', 'destino',
        'presupuesto', 'moneda', 'inicia_en', 'termina_en', 'notas',
    ];

    protected $casts = [
        'presupuesto' => 'decimal:2',
        'inicia_en' => 'date',
        'termina_en' => 'date',
    ];

    /**
     * Canales con sus UTM recomendados (fuente y medio), primero los digitales. Los anuncios pagos usan
     * "paid_social" o "cpc" para que Google Analytics y Meta los agrupen como tráfico pago.
     */
    public const CANALES = [
        'instagram' => ['nombre' => 'Instagram (bio, historias, publicaciones)', 'source' => 'instagram', 'medium' => 'social'],
        'facebook' => ['nombre' => 'Facebook (página, grupos, publicaciones)', 'source' => 'facebook', 'medium' => 'social'],
        'tiktok' => ['nombre' => 'TikTok (perfil, videos)', 'source' => 'tiktok', 'medium' => 'social'],
        'whatsapp' => ['nombre' => 'WhatsApp (estados, mensajes, grupos)', 'source' => 'whatsapp', 'medium' => 'social'],
        'linkedin' => ['nombre' => 'LinkedIn', 'source' => 'linkedin', 'medium' => 'social'],
        'correo' => ['nombre' => 'Correo', 'source' => 'newsletter', 'medium' => 'email'],
        'meta_ads' => ['nombre' => 'Anuncio pagado en Meta (Facebook e Instagram)', 'source' => 'facebook', 'medium' => 'paid_social'],
        'google_ads' => ['nombre' => 'Anuncio pagado en Google', 'source' => 'google', 'medium' => 'cpc'],
        'tiktok_ads' => ['nombre' => 'Anuncio pagado en TikTok', 'source' => 'tiktok', 'medium' => 'paid_social'],
        'qr' => ['nombre' => 'QR (impreso, tarjeta, evento)', 'source' => 'qr', 'medium' => 'offline'],
        'aliado' => ['nombre' => 'Aliado o influenciador', 'source' => 'aliado', 'medium' => 'referral'],
        'otro' => ['nombre' => 'Otro', 'source' => '', 'medium' => ''],
    ];

    public function canalNombre(): string
    {
        return self::CANALES[$this->canal]['nombre'] ?? $this->canal;
    }

    /** Enlace con sus UTM, listo para pegar en el anuncio. */
    public function enlace(): string
    {
        return self::conUtm($this->destino, [
            'utm_source' => $this->utm_source,
            'utm_medium' => $this->utm_medium,
            'utm_campaign' => $this->utm_campaign,
        ]);
    }

    /** @param  array<string, ?string>  $utm */
    public static function conUtm(string $url, array $utm): string
    {
        $utm = array_filter($utm, fn ($v) => filled($v));
        [$sinAncla, $ancla] = array_pad(explode('#', $url, 2), 2, null);

        return $sinAncla . (str_contains($sinAncla, '?') ? '&' : '?') . http_build_query($utm, '', '&', PHP_QUERY_RFC3986)
            . ($ancla !== null ? '#' . $ancla : '');
    }
}
