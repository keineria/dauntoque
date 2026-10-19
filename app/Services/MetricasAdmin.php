<?php

namespace App\Services;

use App\Models\Pedido;
use App\Models\Profile;
use App\Models\ProfileEvent;
use App\Models\SoporteConversacion;
use App\Models\Tarjeta;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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
            'sinUso' => $this->sinUsoConsulta()->count(),
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

    /** Usuarios con perfil y cuenta de más de MESES_SIN_USO meses, sin ningún evento en sus perfiles en ese tiempo. */
    private function sinUsoConsulta()
    {
        $desde = now()->subMonths(self::MESES_SIN_USO);

        return User::where('created_at', '<', $desde)
            ->whereIn('id', Profile::select('user_id'))
            ->whereNotIn('id', ProfileEvent::query()
                ->join('profiles', 'profiles.id', '=', 'profile_events.profile_id')
                ->where('profile_events.created_at', '>=', $desde)
                ->select('profiles.user_id'));
    }

    /**
     * Lista de clientes sin uso, con la fecha de la última visita o clic (null si nunca tuvo).
     *
     * @return Collection<int, array{nombre: string, correo: string, ultimoUso: ?string, desde: string}>
     */
    public function sinUso(int $limite = 50): Collection
    {
        $usuarios = $this->sinUsoConsulta()->orderBy('created_at')->limit($limite)->get(['id', 'name', 'email', 'created_at']);
        $ultimos = ProfileEvent::query()
            ->join('profiles', 'profiles.id', '=', 'profile_events.profile_id')
            ->whereIn('profiles.user_id', $usuarios->pluck('id'))
            ->groupBy('profiles.user_id')
            ->pluck(DB::raw('MAX(profile_events.created_at)'), 'profiles.user_id');

        return $usuarios->map(fn (User $u) => [
            'nombre' => $u->name,
            'correo' => $u->email,
            'ultimoUso' => isset($ultimos[$u->id]) ? Carbon::parse($ultimos[$u->id])->format('d/m/Y') : null,
            'desde' => $u->created_at->format('d/m/Y'),
        ]);
    }
}
