<?php

namespace App\Support;

use App\Mail\AgendaCorreo;
use App\Mail\PedidoConfirmado;
use App\Mail\PedidoNuevo;
use App\Mail\PromoCarrito;
use App\Mail\RecordatorioUso;
use App\Mail\ServicioPorVencer;
use App\Models\Ajuste;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Crypt;

/**
 * Correos de notificación que envía la plataforma, configurables desde el administrador:
 * remitente, correo de avisos de pedidos y cuáles plantillas se envían.
 */
class Correos
{
    /** @var array<string, array{clase: class-string, nombre: string, desc: string}> */
    public const PLANTILLAS = [
        'pedido_confirmado' => ['clase' => PedidoConfirmado::class, 'nombre' => 'Compra confirmada', 'desc' => 'Al cliente cuando paga: gracias y enlace para crear su perfil.'],
        'pedido_nuevo' => ['clase' => PedidoNuevo::class, 'nombre' => 'Aviso de pedido nuevo', 'desc' => 'A tu correo de avisos, para despachar la tarjeta.'],
        'servicio' => ['clase' => ServicioPorVencer::class, 'nombre' => 'Renovación del servicio', 'desc' => 'Al cliente: un mes antes, la última semana, al vencer y al pausar.'],
        'agenda' => ['clase' => AgendaCorreo::class, 'nombre' => 'Agenda de citas', 'desc' => 'Reservas, cambios, cancelaciones y recordatorios. El código para entrar a «Mis citas» se envía siempre.'],
        'promo_carrito' => ['clase' => PromoCarrito::class, 'nombre' => 'Última promoción (carrito abandonado)', 'desc' => 'Se envía solo cuando tú la mandas desde Carritos.'],
        'recordatorio_uso' => ['clase' => RecordatorioUso::class, 'nombre' => 'Recordatorio de uso', 'desc' => 'Se envía solo cuando tú lo mandas desde «Sin usar tarjeta o enlace».'],
    ];

    public static function activa(string $plantilla): bool
    {
        return Ajuste::valor("correo_activo_$plantilla", '1') === '1';
    }

    /** ¿El servidor de correo (SMTP) se configuró en el administrador? Si no, se usa el del .env. */
    public static function smtpDesdeAdmin(): bool
    {
        return filled(Ajuste::valor('smtp_host'));
    }

    /** Pone en la configuración el servidor de correo, el remitente y el correo de avisos guardados en el administrador. */
    public static function aplicarAjustes(): void
    {
        if (self::smtpDesdeAdmin()) {
            try {
                $clave = Crypt::decryptString((string) Ajuste::valor('smtp_clave'));
            } catch (\Throwable) {
                $clave = null;
            }
            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.url' => null,
                'mail.mailers.smtp.host' => Ajuste::valor('smtp_host'),
                'mail.mailers.smtp.port' => (int) Ajuste::valor('smtp_puerto', 465),
                'mail.mailers.smtp.scheme' => Ajuste::valor('smtp_cifrado') === 'tls' ? 'smtp' : 'smtps',
                'mail.mailers.smtp.username' => Ajuste::valor('smtp_usuario'),
                'mail.mailers.smtp.password' => $clave,
            ]);
        }
        if ($direccion = Ajuste::valor('correo_remitente')) {
            config(['mail.from.address' => $direccion]);
        }
        if ($nombre = Ajuste::valor('correo_remitente_nombre')) {
            config(['mail.from.name' => $nombre]);
        }
        if ($aviso = Ajuste::valor('correo_avisos_pedidos')) {
            config(['pedidos.aviso_correo' => $aviso]);
        }
    }

    /** Escucha MessageSending: devuelve false (no se envía) si la plantilla está apagada. */
    public static function filtrar(MessageSending $evento): bool
    {
        $clase = $evento->data['__laravel_mailable'] ?? null;
        foreach (self::PLANTILLAS as $id => $plantilla) {
            if ($plantilla['clase'] !== $clase || self::activa($id)) {
                continue;
            }
            // Sin el código no se puede entrar a «Mis citas»: nunca se apaga
            if ($id === 'agenda' && ($evento->data['tipo'] ?? null) === 'codigo') {
                return true;
            }

            return false;
        }

        return true;
    }

    /** Muestra solo el comienzo de un dato sensible: "pedi••••••". */
    public static function ocultar(?string $valor, int $visibles = 4): string
    {
        if (blank($valor)) {
            return '';
        }

        return mb_substr($valor, 0, min($visibles, intdiv(mb_strlen($valor), 2))).str_repeat('•', 6);
    }
}
