<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Pedido;
use App\Services\AvisosPago;
use Illuminate\Console\Command;

/**
 * Compras pagadas cuyo correo al cliente no salió (servidor de correo caído en ese momento): lo vuelve a intentar.
 * Solo las de los últimos 3 días, para no escribirle a nadie con una compra vieja.
 */
class ReintentarCorreosPedidos extends Command
{
    protected $signature = 'pedidos:reintentar-correos';

    protected $description = 'Reenvía el correo de compra confirmada a los pedidos pagados que no lo recibieron';

    public function handle(): int
    {
        $pedidos = Pedido::with('chip')->where('estado', 'pagado')->whereNull('correo_enviado_en')
            ->where('pagado_en', '>=', now()->subDays(3))->limit(50)->get();

        $enviados = $pedidos->filter(fn (Pedido $pedido) => AvisosPago::compra($pedido, conAvisoInterno: false))->count();
        $this->info("Correos de compra reenviados: $enviados de {$pedidos->count()}.");

        return self::SUCCESS;
    }
}
