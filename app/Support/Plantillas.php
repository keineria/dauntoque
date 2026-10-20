<?php

namespace App\Support;

use App\Mail\PedidoConfirmado;
use App\Mail\PedidoNuevo;
use App\Mail\PromoCarrito;
use App\Mail\RecordatorioUso;
use App\Mail\ServicioPorVencer;
use App\Models\Ajuste;
use App\Models\Chip;
use App\Models\Pedido;
use App\Models\Profile;
use App\Models\Tarjeta;
use App\Models\User;
use Illuminate\Mail\Mailable;

/**
 * Plantillas de correo que se editan en HTML desde el administrador.
 * Los {{campos}} se cambian por los datos de cada persona al enviar; {{campo|texto}} usa "texto" si viene vacío.
 */
class Plantillas
{
    private const PEDIDO = [
        'nombre' => 'Primer nombre del cliente',
        'nombre_completo' => 'Nombre completo',
        'correo' => 'Correo del cliente',
        'celular' => 'Celular',
        'pedido' => 'Número de pedido (DT-00012)',
        'fecha' => 'Fecha del pago',
        'tarjeta' => 'Acabado de la tarjeta',
        'nombre_tarjeta' => 'Nombre impreso en la tarjeta',
        'total' => 'Total pagado con moneda',
        'direccion' => 'Dirección de envío',
        'ciudad' => 'Ciudad',
        'departamento' => 'Departamento',
        'codigo_activacion' => 'Código de activación de la tarjeta',
        'enlace_registro' => 'Enlace para crear la cuenta',
        'enlace_panel' => 'Enlace al panel del cliente',
    ];

    /** @var array<string, array{nombre: string, cuando: string, campos: array<string, string>}> */
    public const EDITABLES = [
        'pedido_confirmado' => ['nombre' => 'Compra confirmada', 'cuando' => 'Al cliente cuando Bold confirma el pago.', 'campos' => self::PEDIDO],
        'pedido_nuevo' => ['nombre' => 'Aviso de pedido nuevo', 'cuando' => 'A tu correo de avisos, para grabar y despachar la tarjeta.', 'campos' => self::PEDIDO + [
            'documento' => 'Documento del cliente',
            'enlace_grabar' => 'Enlace para grabar en el chip con NFC Tools',
        ]],
        'servicio' => ['nombre' => 'Renovación del servicio', 'cuando' => 'Un mes antes, la última semana, al vencer y al pausar. {{titulo}} y {{mensaje}} cambian en cada aviso.', 'campos' => [
            'nombre' => 'Primer nombre del cliente',
            'nombre_completo' => 'Nombre completo',
            'correo' => 'Correo del cliente',
            'titulo' => 'Título según el aviso (te quedan 30 días…)',
            'mensaje' => 'Texto según el aviso',
            'dias_restantes' => 'Días que le quedan',
            'dias_gracia' => 'Días de gracia que le quedan',
            'vence' => 'Fecha de vencimiento',
            'chip' => 'Final del código de la tarjeta',
            'precio' => 'Precio de la renovación',
            'enlace_panel' => 'Enlace al panel (para renovar)',
        ]],
        'promo_carrito' => ['nombre' => 'Última promoción (carrito abandonado)', 'cuando' => 'Cuando la mandas desde Carritos.', 'campos' => [
            'nombre' => 'Primer nombre',
            'nombre_completo' => 'Nombre completo',
            'correo' => 'Correo',
            'tarjeta' => 'Acabado que eligió',
            'descuento' => 'Porcentaje de descuento',
            'dias' => 'Días que vale la promoción',
            'mensaje' => 'Mensaje de la promoción (se edita en Carritos)',
            'enlace_compra' => 'Enlace para terminar la compra con descuento',
        ]],
        'recordatorio_uso' => ['nombre' => 'Recordatorio de uso', 'cuando' => 'Cuando lo mandas desde «Sin usar tarjeta o enlace».', 'campos' => [
            'nombre' => 'Primer nombre',
            'nombre_completo' => 'Nombre completo',
            'correo' => 'Correo',
            'usuario' => 'Usuario del perfil',
            'enlace_perfil' => 'Enlace público del perfil',
            'enlace_panel' => 'Enlace al panel',
            'meses' => 'Meses sin uso que cuentan',
        ]],
    ];

    /** @return array{asunto: ?string, html: string}|null */
    public static function personalizada(string $id): ?array
    {
        $html = Ajuste::valor("plantilla_{$id}_html");

        return $html ? ['asunto' => Ajuste::valor("plantilla_{$id}_asunto"), 'html' => $html] : null;
    }

    public static function guardar(string $id, ?string $asunto, string $html): void
    {
        Ajuste::guardar(["plantilla_{$id}_asunto" => $asunto, "plantilla_{$id}_html" => $html]);
    }

    public static function restaurar(string $id): void
    {
        Ajuste::guardar(["plantilla_{$id}_asunto" => null, "plantilla_{$id}_html" => null]);
    }

    /** Cambia los {{campos}} por sus valores (escapados para HTML si $html). */
    public static function llenar(string $texto, array $valores, bool $html = true): string
    {
        return preg_replace_callback('/\{\{\s*([a-z_]+)\s*(?:\|([^}]*))?\}\}/', function ($m) use ($valores, $html) {
            $valor = (string) ($valores[$m[1]] ?? '');
            if ($valor === '') {
                $valor = trim($m[2] ?? '');
            }

            return $html ? e($valor) : $valor;
        }, $texto);
    }

    /** El correo con datos de ejemplo (María Pérez, Medellín), para la vista previa y la prueba. */
    public static function muestra(string $id): Mailable
    {
        $pedido = (new Pedido)->forceFill([
            'id' => 12, 'codigo' => 'DT-00012', 'token' => 'ejemplo', 'promo_token' => 'ejemplo', 'nombre' => 'María Pérez', 'correo' => 'maria@ejemplo.com',
            'celular' => '3001234567', 'documento' => '1020304050', 'departamento' => 'Antioquia', 'ciudad' => 'Medellín', 'direccion' => 'Calle 10 # 43-12',
            'acabado' => 'crystal', 'nombre_tarjeta' => 'Constructora Andina', 'pais' => 'CO', 'moneda' => 'COP', 'monto' => 299900,
            'estado' => 'pagado', 'pagado_en' => now(), 'pago_proveedor_id' => 'PAGO-EJEMPLO',
        ]);
        $pedido->setRelation('chip', new Chip(['codigo' => 'k7mq-2xpa']));
        $usuario = (new User)->forceFill(['id' => 0, 'name' => 'María Pérez', 'email' => 'maria@ejemplo.com']);

        return match ($id) {
            'pedido_confirmado' => new PedidoConfirmado($pedido),
            'pedido_nuevo' => new PedidoNuevo($pedido),
            'promo_carrito' => new PromoCarrito($pedido, 15),
            'servicio' => new ServicioPorVencer(
                (new Tarjeta)->forceFill(['nfc_uid' => 'k7mq-2xpa', 'activada_en' => now()->subDays(335), 'vence_en' => now()->addDays(30)])->setRelation('user', $usuario),
                'mes', '$120.000 COP'),
            'recordatorio_uso' => new RecordatorioUso($usuario, (new Profile)->forceFill(['nickname' => 'mariaperez'])),
        };
    }

    /** Datos de ejemplo de cada campo. */
    public static function ejemplo(string $id): array
    {
        return array_map(fn ($v) => (string) $v, static::muestra($id)->valores());
    }

    /**
     * Punto de partida del editor: el correo de siempre con los datos de ejemplo cambiados por sus {{campos}}.
     *
     * @return array{asunto: string, html: string}
     */
    public static function original(string $id): array
    {
        $correo = static::muestra($id);
        $valores = array_filter(static::ejemplo($id), fn ($v) => $v !== '');
        uasort($valores, fn ($a, $b) => mb_strlen($b) <=> mb_strlen($a));
        $html = $correo->render();
        $asunto = $correo->envelope()->subject;
        foreach ($valores as $campo => $valor) {
            if (ctype_digit($valor) && strlen($valor) <= 3) {
                // Los números solo junto a su unidad, para no tocar medidas del diseño (padding:15px)
                $patron = '/(?<![\w#.:%-])'.$valor.'(?= ?(?:%|d[ií]as?|mes))/u';
                $html = preg_replace($patron, '{{'.$campo.'}}', $html);
                $asunto = preg_replace($patron, '{{'.$campo.'}}', $asunto);

                continue;
            }
            $html = str_replace([e($valor), $valor], '{{'.$campo.'}}', $html);
            $asunto = str_replace($valor, '{{'.$campo.'}}', $asunto);
        }

        return ['asunto' => $asunto, 'html' => $html];
    }
}
