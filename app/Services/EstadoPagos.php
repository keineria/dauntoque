<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Ajuste;
use App\Models\AvisoPago;
use App\Models\Campana;
use App\Models\Pago;
use App\Models\Pedido;
use App\Services\Marketing\Etiquetas;
use App\Support\Precios;

/** Estado de las pasarelas (Bold hoy, Stripe después), cifras de cobros y la lista «Listo para vender». */
class EstadoPagos
{
    private const DIAS = 30;

    public function bold(): array
    {
        $ultimo = AvisoPago::where('proveedor', 'bold')->latest('id')->first();
        $ultimoReal = AvisoPago::where('proveedor', 'bold')->where('tipo', 'SALE_APPROVED')->where('resultado', 'like', 'pedido %')->latest('id')->first();

        return [
            'llave' => Bold::configurado(),
            'secreta' => filled(config('services.bold.secret')),
            'webhook' => url('/webhooks/bold'),
            'ultimoAviso' => $ultimo,
            'ultimoPagoReal' => $ultimoReal,
            'avisos' => AvisoPago::where('proveedor', 'bold')->latest('id')->limit(12)->get(),
        ];
    }

    /** Compras de tarjetas y renovaciones de los últimos 30 días. */
    public function cobros(): array
    {
        $desde = now()->subDays(self::DIAS);
        $pedidos = Pedido::where('created_at', '>=', $desde)->groupBy('estado')->selectRaw('estado, COUNT(*) as n')->pluck('n', 'estado');
        $pagos = Pago::where('created_at', '>=', $desde)->groupBy('estado')->selectRaw('estado, COUNT(*) as n')->pluck('n', 'estado');
        $cerrados = (int) ($pedidos['pagado'] ?? 0) + (int) ($pedidos['rechazado'] ?? 0);

        return [
            'dias' => self::DIAS,
            'pagados' => (int) ($pedidos['pagado'] ?? 0),
            'pendientes' => (int) ($pedidos['pendiente'] ?? 0),
            'rechazados' => (int) ($pedidos['rechazado'] ?? 0),
            'aprobacion' => $cerrados > 0 ? round(($pedidos['pagado'] ?? 0) * 100 / $cerrados) : null,
            'ventas' => Pedido::where('estado', 'pagado')->where('pagado_en', '>=', $desde)->groupBy('moneda')->selectRaw('moneda, SUM(monto) as total')->pluck('total', 'moneda')->all(),
            'renovacionesPagadas' => (int) ($pagos['pagado'] ?? 0),
            'renovacionesPendientes' => (int) ($pagos['pendiente'] ?? 0),
            'ultimosPedidos' => Pedido::latest('id')->limit(15)->get(['id', 'codigo', 'nombre', 'correo', 'estado', 'moneda', 'monto', 'utm_source', 'utm_campaign', 'created_at', 'pagado_en']),
            'ultimasRenovaciones' => Pago::latest('id')->limit(8)->get(['id', 'user_id', 'concepto', 'estado', 'moneda', 'monto', 'created_at', 'pagado_en']),
        ];
    }

    /** Lo que falta para salir a vender. @return list<array{hecho: bool, texto: string, ayuda: string, enlace: ?string}> */
    public function listoParaVender(): array
    {
        $bold = $this->bold();
        $correo = filled(Ajuste::valor('smtp_host')) || ! in_array(config('mail.default'), ['log', 'array'], true);

        return [
            ['hecho' => (bool) Precios::tarjeta('CO'), 'texto' => 'Precio de la tarjeta en Colombia', 'ayuda' => 'Sale de la tabla de precios del portal.', 'enlace' => null],
            ['hecho' => $bold['llave'], 'texto' => 'Llave de identidad de Bold en el servidor', 'ayuda' => 'BOLD_API_KEY en el archivo .env del servidor (nunca en el chat).', 'enlace' => '/admin/pagos'],
            ['hecho' => $bold['secreta'], 'texto' => 'Llave secreta de Bold (firma de los avisos)', 'ayuda' => 'BOLD_SECRET_KEY en el .env. Sin ella, en producción todo aviso se rechaza.', 'enlace' => '/admin/pagos'],
            ['hecho' => (bool) $bold['ultimoPagoReal'], 'texto' => 'Primer pago real confirmado por Bold', 'ayuda' => 'Una compra pequeña en producción que Bold avise al webhook (y luego se reembolsa).', 'enlace' => '/admin/pagos'],
            ['hecho' => $correo, 'texto' => 'Correo de salida configurado', 'ayuda' => 'Para enviar el código de activación y los avisos.', 'enlace' => '/admin/correos'],
            ['hecho' => Etiquetas::ids() !== [], 'texto' => 'Al menos un píxel conectado', 'ayuda' => 'Meta, Google o TikTok, para medir y optimizar los anuncios.', 'enlace' => '/admin/pixeles'],
            ['hecho' => Campana::exists(), 'texto' => 'Primer enlace UTM o QR', 'ayuda' => 'Así se sabe qué publicación, anuncio o QR trae cada venta.', 'enlace' => '/admin/campanas'],
        ];
    }
}
