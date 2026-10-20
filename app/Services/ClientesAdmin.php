<?php

namespace App\Services;

use App\Models\Chip;
use App\Models\Pedido;
use App\Models\Profile;
use App\Models\ProfileEvent;
use App\Models\Tarjeta;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/** Listas de clientes del administrador: filtros, buscador y los datos de cada fila. */
class ClientesAdmin
{
    public const LISTAS = [
        'todos' => 'Todos',
        'activos' => 'Activos',
        'inactivos' => 'Inactivos',
        'sin_activar' => 'Sin activar',
        'sin_uso' => 'Sin usar la tarjeta',
    ];

    public const POR_PAGINA = [20, 50, 100];

    public static function consulta(string $lista, string $buscar = ''): Builder
    {
        $vigente = fn ($q) => $q->vinculadas()->where('vence_en', '>=', self::limiteGracia());

        return User::query()
            ->when($buscar !== '', fn ($q) => $q->where(fn ($q) => $q->where('name', 'like', "%$buscar%")->orWhere('email', 'like', "%$buscar%")
                ->orWhereIn('id', Profile::select('user_id')->where('nombre', 'like', "%$buscar%")->orWhere('nickname', 'like', "%$buscar%"))))
            ->when($lista === 'activos', fn ($q) => $q->whereHas('tarjetas', $vigente))
            ->when($lista === 'inactivos', fn ($q) => $q->whereHas('tarjetas', fn ($t) => $t->vinculadas())->whereDoesntHave('tarjetas', $vigente))
            ->when($lista === 'sin_activar', fn ($q) => $q->whereDoesntHave('tarjetas', fn ($t) => $t->vinculadas()))
            ->when($lista === 'sin_uso', fn ($q) => $q->whereIn('id', self::sinUso()->select('id')));
    }

    /** Cuentas de más de MESES_SIN_USO meses, con perfil y sin ninguna visita ni clic en ese tiempo. */
    public static function sinUso(): Builder
    {
        $desde = now()->subMonths(MetricasAdmin::MESES_SIN_USO);

        return User::where('created_at', '<', $desde)
            ->whereIn('id', Profile::select('user_id'))
            ->whereNotIn('id', ProfileEvent::query()
                ->join('profiles', 'profiles.id', '=', 'profile_events.profile_id')
                ->where('profile_events.created_at', '>=', $desde)
                ->select('profiles.user_id'));
    }

    /** Fecha desde la que una tarjeta vencida sigue en gracia (antes de eso, en pausa). */
    public static function limiteGracia(): Carbon
    {
        return now()->startOfDay()->subDays(max(0, (int) config('servicio.gracia_dias') - 1));
    }

    /**
     * Datos de cada cliente de la página: perfil, tarjeta (código, activación, lugar y dispositivo),
     * servicio, compra y uso.
     */
    public static function filas(LengthAwarePaginator $usuarios): array
    {
        $ids = $usuarios->getCollection()->pluck('id');
        $perfiles = Profile::whereIn('user_id', $ids)->get(['id', 'user_id', 'nickname', 'nombre', 'telefono', 'nfc_uid', 'activo_en', 'created_at'])->groupBy('user_id');
        $tarjetas = Tarjeta::vinculadas()->whereIn('user_id', $ids)->latest('activada_en')->get()->groupBy('user_id');
        $pedidos = Pedido::with('chip')->where('estado', 'pagado')
            ->where(fn ($q) => $q->whereIn('user_id', $ids)->orWhereIn('correo', $usuarios->getCollection()->pluck('email')))
            ->latest('pagado_en')->get();
        $eventos = ProfileEvent::query()
            ->join('profiles', 'profiles.id', '=', 'profile_events.profile_id')
            ->whereIn('profiles.user_id', $ids)
            ->groupBy('profiles.user_id')
            ->selectRaw('profiles.user_id as id, COUNT(*) as total, SUM(CASE WHEN accion = ? THEN 1 ELSE 0 END) as vistas, MAX(profile_events.created_at) as ultimo', ['vista_perfil'])
            ->get()->keyBy('id');

        return $usuarios->getCollection()->map(function (User $u) use ($perfiles, $tarjetas, $pedidos, $eventos) {
            $suyos = $perfiles->get($u->id, collect());
            $tarjeta = $tarjetas->get($u->id)?->first();
            $pedido = $pedidos->first(fn (Pedido $p) => $p->user_id === $u->id || strcasecmp($p->correo, $u->email) === 0);
            $uso = $eventos->get($u->id);
            $servicio = $tarjeta?->servicio();

            return [
                'id' => $u->id,
                'nombre' => $u->name,
                'correo' => $u->email,
                'celular' => $pedido?->celular ?: $suyos->pluck('telefono')->filter()->first(),
                'registro' => $u->created_at,
                'perfiles' => $suyos->map(fn (Profile $p) => ['nickname' => $p->nickname, 'nombre' => $p->nombre, 'url' => Profile::urlPublica($p->nickname)])->values()->all(),
                'codigo' => $tarjeta?->nfc_uid,
                'codigoCompra' => $pedido?->chip?->codigo,
                'activadaEn' => $tarjeta?->activada_en,
                'medio' => $tarjeta?->activacion_medio,
                'lugar' => $tarjeta?->activacion_lugar,
                'dispositivo' => $tarjeta?->activacion_dispositivo,
                'estado' => $servicio['estado'] ?? null,
                'venceEn' => $tarjeta?->vence_en,
                'pedido' => $pedido ? ['codigo' => $pedido->codigo, 'total' => $pedido->totalTexto(), 'fecha' => $pedido->pagado_en, 'ciudad' => $pedido->ciudad, 'acabado' => $pedido->acabadoNombre()] : null,
                'vistas' => (int) ($uso->vistas ?? 0),
                'interacciones' => (int) ($uso->total ?? 0),
                'ultimoUso' => isset($uso->ultimo) ? Carbon::parse($uso->ultimo) : null,
                'recordatorioEn' => $u->recordatorio_uso_en,
                'admin' => $u->esAdmin(),
            ];
        })->all();
    }

    /** Código de compra que el cliente aún no ha activado (para reenviárselo). */
    public static function chipSinActivar(User $usuario): ?Chip
    {
        return Chip::whereIn('pedido_id', Pedido::where('estado', 'pagado')->where(fn ($q) => $q->where('user_id', $usuario->id)->orWhere('correo', $usuario->email))->select('id'))
            ->whereDoesntHave('tarjeta')->latest('id')->first();
    }
}
