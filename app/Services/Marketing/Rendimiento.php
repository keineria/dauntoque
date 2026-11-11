<?php

declare(strict_types=1);

namespace App\Services\Marketing;

use App\Models\Campana;
use App\Models\Pedido;
use App\Models\VisitaMarketing;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Rendimiento con la medición propia: personas que visitan el sitio (por fuente y campaña),
 * pedidos que llegan al pago, compras pagadas y ventas. No depende de Meta, Google ni TikTok.
 */
class Rendimiento
{
    public const PERIODOS = [7 => 'Últimos 7 días', 30 => 'Últimos 30 días', 90 => 'Últimos 90 días', 365 => 'Último año'];

    private Carbon $desde;

    public function __construct(int $dias = 30)
    {
        $this->desde = now()->subDays(array_key_exists($dias, self::PERIODOS) ? $dias : 30)->startOfDay();
    }

    public function desde(): Carbon
    {
        return $this->desde;
    }

    /** Embudo del periodo: personas → páginas → pedidos (llegaron al pago) → pagados, y ventas por moneda. */
    public function totales(): array
    {
        $visitas = VisitaMarketing::where('created_at', '>=', $this->desde);
        $pedidos = Pedido::where('created_at', '>=', $this->desde);
        $personas = (clone $visitas)->distinct()->count('visitante');
        $pagados = (clone $pedidos)->where('estado', 'pagado')->count();

        return [
            'personas' => $personas,
            'paginas' => (clone $visitas)->count(),
            'pedidos' => (clone $pedidos)->count(),
            'pagados' => $pagados,
            'conversion' => $personas > 0 ? round($pagados * 100 / $personas, 2) : null,
            'ventas' => (clone $pedidos)->where('estado', 'pagado')->groupBy('moneda')->selectRaw('moneda, SUM(monto) as total')->pluck('total', 'moneda')->map(fn ($t) => (float) $t)->all(),
        ];
    }

    /** Por fuente y medio (facebook / paid_social, google / organic…). Sin UTM ni referente = directo. */
    public function porFuente(): Collection
    {
        $visitas = VisitaMarketing::where('created_at', '>=', $this->desde)
            ->groupBy('utm_source', 'utm_medium')
            ->selectRaw('utm_source, utm_medium, COUNT(DISTINCT visitante) as personas, COUNT(*) as paginas')
            ->get()->keyBy(fn ($f) => $f->utm_source . '|' . $f->utm_medium);
        $pedidos = $this->pedidosAgrupados(['utm_source', 'utm_medium']);

        return $visitas->keys()->merge($pedidos->keys())->unique()->map(function (string $clave) use ($visitas, $pedidos) {
            [$source, $medium] = explode('|', $clave, 2);

            return $this->fila($visitas->get($clave), $pedidos->get($clave)) + [
                'fuente' => $source !== '' ? $source : 'Directo',
                'medio' => $medium !== '' ? $medium : ($source !== '' ? '—' : 'escribió la dirección o sin datos'),
            ];
        })->sortByDesc(fn ($f) => [$f['pagados'], $f['personas']])->values();
    }

    /** Por campaña (utm_campaign), con presupuesto, costo por compra y retorno (ROAS) si la campaña está creada. */
    public function porCampana(): Collection
    {
        $visitas = VisitaMarketing::where('created_at', '>=', $this->desde)->whereNotNull('utm_campaign')
            ->groupBy('utm_campaign')
            ->selectRaw('utm_campaign, COUNT(DISTINCT visitante) as personas, COUNT(*) as paginas')
            ->get()->keyBy('utm_campaign');
        $pedidos = $this->pedidosAgrupados(['utm_campaign'], fn ($q) => $q->whereNotNull('utm_campaign'));
        // Todos los enlaces creados (aunque aún no tengan visitas) y cualquier otro UTM que llegue
        $campanas = Campana::query()->latest('id')
            ->get(['id', 'nombre', 'canal', 'utm_source', 'utm_medium', 'utm_campaign', 'destino', 'presupuesto', 'moneda', 'inicia_en', 'termina_en', 'notas'])
            ->keyBy('utm_campaign');

        return $campanas->keys()->merge($visitas->keys())->merge($pedidos->keys())->unique()->map(function (string $utm) use ($visitas, $pedidos, $campanas) {
            $fila = $this->fila($visitas->get($utm), $pedidos->get($utm));
            $campana = $campanas->get($utm);
            $presupuesto = $campana?->presupuesto !== null ? (float) $campana->presupuesto : null;
            $ventasMoneda = $campana ? ($fila['ventas'][$campana->moneda] ?? 0.0) : 0.0;

            return $fila + [
                'utm' => $utm,
                'campana' => $campana,
                'costoCompra' => $presupuesto && $fila['pagados'] > 0 ? $presupuesto / $fila['pagados'] : null,
                'roas' => $presupuesto ? round($ventasMoneda / $presupuesto, 2) : null,
            ];
        })->sortByDesc(fn ($f) => [$f['pagados'], $f['personas'], $f['campana']?->id ?? 0])->values();
    }

    /**
     * Pedidos del periodo que llegaron por cada UTM de campaña (para ver quién compró con cada enlace).
     *
     * @return Collection<string, Collection<int, \App\Models\Pedido>>
     */
    public function pedidosPorCampana(int $limite = 300): Collection
    {
        return Pedido::where('created_at', '>=', $this->desde)->whereNotNull('utm_campaign')
            ->latest('id')->limit($limite)
            ->get(['id', 'codigo', 'nombre', 'estado', 'moneda', 'monto', 'utm_campaign', 'utm_content', 'created_at'])
            ->groupBy('utm_campaign');
    }

    /** Personas y compras por día, para la gráfica. @return list<array{dia: string, personas: int, pagados: int}> */
    public function porDia(): array
    {
        $personas = VisitaMarketing::where('created_at', '>=', $this->desde)
            ->groupBy(DB::raw('DATE(created_at)'))
            ->selectRaw('DATE(created_at) as dia, COUNT(DISTINCT visitante) as n')->pluck('n', 'dia');
        $pagados = Pedido::where('estado', 'pagado')->where('pagado_en', '>=', $this->desde)
            ->groupBy(DB::raw('DATE(pagado_en)'))
            ->selectRaw('DATE(pagado_en) as dia, COUNT(*) as n')->pluck('n', 'dia');

        $dias = [];
        for ($dia = $this->desde->copy(); $dia->lte(now()); $dia->addDay()) {
            $clave = $dia->toDateString();
            $dias[] = ['dia' => $clave, 'personas' => (int) ($personas[$clave] ?? 0), 'pagados' => (int) ($pagados[$clave] ?? 0)];
        }

        return $dias;
    }

    /** Páginas más vistas del periodo. */
    public function paginas(int $limite = 8): Collection
    {
        return VisitaMarketing::where('created_at', '>=', $this->desde)
            ->groupBy('pagina')
            ->selectRaw('pagina, COUNT(*) as vistas, COUNT(DISTINCT visitante) as personas')
            ->orderByDesc('vistas')->limit($limite)->get();
    }

    /** Personas por dispositivo y por país. @return array{dispositivos: Collection, paises: Collection} */
    public function audiencia(): array
    {
        $base = fn (string $campo) => VisitaMarketing::where('created_at', '>=', $this->desde)
            ->groupBy($campo)->selectRaw("$campo as nombre, COUNT(DISTINCT visitante) as personas")
            ->orderByDesc('personas')->limit(6)->get();

        return ['dispositivos' => $base('dispositivo'), 'paises' => $base('pais')];
    }

    /**
     * Pedidos del periodo agrupados: pedidos (llegaron al pago), pagados y ventas por moneda.
     *
     * @param  list<string>  $campos
     * @return Collection<string, array{pedidos: int, pagados: int, ventas: array<string, float>}>
     */
    private function pedidosAgrupados(array $campos, ?\Closure $filtro = null): Collection
    {
        $consulta = Pedido::where('created_at', '>=', $this->desde);
        if ($filtro) {
            $filtro($consulta);
        }

        return $consulta->groupBy([...$campos, 'moneda'])
            ->selectRaw(implode(', ', $campos) . ", moneda, COUNT(*) as pedidos, SUM(CASE WHEN estado = 'pagado' THEN 1 ELSE 0 END) as pagados, SUM(CASE WHEN estado = 'pagado' THEN monto ELSE 0 END) as ventas")
            ->get()
            ->groupBy(fn ($f) => implode('|', array_map(fn ($c) => (string) $f->{$c}, $campos)))
            ->map(fn (Collection $filas) => [
                'pedidos' => (int) $filas->sum('pedidos'),
                'pagados' => (int) $filas->sum('pagados'),
                'ventas' => $filas->filter(fn ($f) => (float) $f->ventas > 0)->mapWithKeys(fn ($f) => [$f->moneda => (float) $f->ventas])->all(),
            ]);
    }

    private function fila(?object $visitas, ?array $pedidos): array
    {
        $personas = (int) ($visitas->personas ?? 0);
        $pagados = $pedidos['pagados'] ?? 0;

        return [
            'personas' => $personas,
            'paginas' => (int) ($visitas->paginas ?? 0),
            'pedidos' => $pedidos['pedidos'] ?? 0,
            'pagados' => $pagados,
            'ventas' => $pedidos['ventas'] ?? [],
            'conversion' => $personas > 0 ? round($pagados * 100 / $personas, 2) : null,
        ];
    }
}
