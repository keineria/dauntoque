<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\ProfileEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnaliticasController extends Controller
{
    /** Registra una visita o un clic desde el perfil público. Nunca rompe la página: responde 204. */
    public function registrar(Request $request)
    {
        $accion = (string) $request->input('accion');
        $perfil = Profile::find((int) $request->input('perfil_id'));

        // Se ignoran acciones desconocidas, perfiles inexistentes y las visitas del propio dueño
        if (! $perfil || ! in_array($accion, ProfileEvent::accionesValidas(), true) || Auth::id() === $perfil->user_id) {
            return response()->noContent();
        }

        $visitante = substr(hash('sha256', $request->ip() . '|' . $request->userAgent() . '|' . now()->toDateString() . '|' . config('app.key')), 0, 32);

        // Una misma visita recargada varias veces en el día cuenta una sola vez
        if ($accion === 'vista_perfil' && ProfileEvent::where('profile_id', $perfil->id)
                ->where('accion', $accion)->where('visitante', $visitante)
                ->where('created_at', '>=', now()->startOfDay())->exists()) {
            return response()->noContent();
        }

        ProfileEvent::create(['profile_id' => $perfil->id, 'accion' => $accion, 'visitante' => $visitante]);

        return response()->noContent();
    }

    /** Resumen para la pantalla de Analíticas: totales de todos los perfiles del usuario. */
    public static function resumen(int $userId): array
    {
        $perfiles = Profile::where('user_id', $userId)->pluck('id');
        $base = fn () => ProfileEvent::whereIn('profile_id', $perfiles);

        $contar = fn (array $acciones, $desde = null, $hasta = null) => $base()
            ->whereIn('accion', $acciones)
            ->when($desde, fn ($q) => $q->where('created_at', '>=', $desde))
            ->when($hasta, fn ($q) => $q->where('created_at', '<', $hasta))
            ->count();

        $vistas = $contar(['vista_perfil']);
        $vcf = $contar(['descarga_vcf']);
        $semana = $contar(['vista_perfil'], now()->subDays(7));
        $semanaAnterior = $contar(['vista_perfil'], now()->subDays(14), now()->subDays(7));

        $redes = $base()->where('accion', 'like', 'red_social_%')
            ->selectRaw('accion, count(*) as total')->groupBy('accion')->orderByDesc('total')
            ->pluck('total', 'accion')
            ->mapWithKeys(fn ($total, $accion) => [config('redes.' . substr($accion, 11) . '.nombre', $accion) => $total]);

        $porDia = $base()->where('accion', 'vista_perfil')->where('created_at', '>=', now()->subDays(13)->startOfDay())
            ->selectRaw('DATE(created_at) as dia, count(*) as total')->groupBy('dia')->pluck('total', 'dia');

        return [
            'vistas' => $vistas,
            'visitantes' => $base()->where('accion', 'vista_perfil')->distinct()->count('visitante'),
            'vcf' => $vcf,
            'tasaVcf' => $vistas ? round($vcf * 100 / $vistas) : null,
            'whatsapp' => $contar(['whatsapp']),
            'llamadas' => $contar(['llamada']),
            'pagos' => $contar(array_keys(config('pagos.medios'))),
            'semana' => $semana,
            'cambioSemanal' => $semanaAnterior ? round(($semana - $semanaAnterior) * 100 / $semanaAnterior) : null,
            'redes' => $redes,
            'ultimos14' => collect(range(13, 0))->mapWithKeys(fn ($d) => [
                now()->subDays($d)->toDateString() => (int) ($porDia[now()->subDays($d)->toDateString()] ?? 0),
            ]),
            'desde' => $base()->min('created_at'),
        ];
    }
}
