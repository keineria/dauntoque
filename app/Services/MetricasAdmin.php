<?php

namespace App\Services;

use App\Models\Pedido;
use App\Models\SoporteConversacion;
use App\Models\Tarjeta;
use App\Models\User;
use Illuminate\Support\Collection;

/** Números del administrador: compras, clientes, uso de las tarjetas, carritos y soporte. */
class MetricasAdmin
{
    /** Meses sin visitas ni clics en el perfil para contar a un cliente como "sin usar su tarjeta o enlace". */
    public const MESES_SIN_USO = 2;

    public function resumen(): array
    {
        $pagados = Pedido::where('estado', 'pagado');

        return [
            'compras' => (clone $pagados)->count(),
            'comprasMes' => (clone $pagados)->where('pagado_en', '>=', now()->startOfMonth())->count(),
            'ventas' => (clone $pagados)->selectRaw('moneda, SUM(monto) as total')->groupBy('moneda')->pluck('total', 'moneda')->all(),
            'enProceso' => Carritos::enProceso()->distinct()->count('correo'),
            'abandonados' => Carritos::abandonados()->count(),
            'declinados' => Carritos::declinados()->count(),
            'promosEnviadas' => Pedido::whereNotNull('promo_enviada_en')->count(),
            'comprasConPromo' => Pedido::where('estado', 'pagado')->whereNotNull('promo_de_pedido_id')->count(),
        ] + $this->clientes() + $this->soporte();
    }

    /**
     * Clientes: activos (tarjeta con servicio vigente o en gracia), inactivos (servicio en pausa)
     * y cuentas que aún no activan su tarjeta.
     */
    private function clientes(): array
    {
        $estados = Tarjeta::vinculadas()->get()
            ->groupBy('user_id')
            // Si tiene varias tarjetas, cuenta la que mejor está
            ->map(fn (Collection $tarjetas) => $tarjetas->map(fn (Tarjeta $t) => $t->servicio()['estado'])->contains(fn ($e) => $e !== 'pausa'));
        $total = User::count();

        return [
            'clientes' => $total,
            'activos' => $estados->filter()->count(),
            'inactivos' => $estados->reject(fn ($activo) => $activo)->count(),
            'sinTarjeta' => $total - $estados->count(),
            'sinUso' => ClientesAdmin::sinUso()->count(),
        ];
    }

    private function soporte(): array
    {
        return [
            'chatsEnCola' => SoporteConversacion::enCola()->count(),
            'chatsEnAtencion' => SoporteConversacion::where('tipo', 'chat')->where('estado', 'abierta')->count(),
            'ticketsAbiertos' => SoporteConversacion::where('tipo', 'ticket')->where('estado', '!=', 'cerrada')->count(),
        ];
    }
}
