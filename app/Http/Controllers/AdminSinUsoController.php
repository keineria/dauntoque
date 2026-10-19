<?php

namespace App\Http\Controllers;

use App\Mail\RecordatorioUso;
use App\Models\User;
use App\Services\ClientesAdmin;
use App\Services\MetricasAdmin;
use App\Support\Correos;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

/** Clientes que llevan meses sin visitas en su perfil: lista y recordatorio de uso por correo. */
class AdminSinUsoController extends Controller
{
    /** Días que deben pasar antes de mandarle otro recordatorio a la misma persona. */
    public const DIAS_ENTRE_RECORDATORIOS = 30;

    public function index(Request $request): View
    {
        $buscar = trim((string) $request->query('buscar'));
        $por = in_array((int) $request->query('por'), ClientesAdmin::POR_PAGINA, true) ? (int) $request->query('por') : 20;
        $clientes = ClientesAdmin::consulta('sin_uso', $buscar)->orderBy('created_at')->paginate($por)->withQueryString();

        return view('admin.sin-uso', [
            'clientes' => $clientes,
            'filas' => ClientesAdmin::filas($clientes),
            'buscar' => $buscar,
            'por' => $por,
            'meses' => MetricasAdmin::MESES_SIN_USO,
            'dias' => self::DIAS_ENTRE_RECORDATORIOS,
            'pendientes' => $this->pendientes()->count(),
            'activa' => Correos::activa('recordatorio_uso'),
        ]);
    }

    public function enviar(User $usuario): RedirectResponse
    {
        abort_unless(ClientesAdmin::sinUso()->whereKey($usuario->id)->exists(), 404);
        if (! Correos::activa('recordatorio_uso')) {
            return back()->with('error', 'El recordatorio de uso está apagado en Correos.');
        }
        if ($usuario->recordatorio_uso_en?->gt(now()->subDays(self::DIAS_ENTRE_RECORDATORIOS))) {
            return back()->with('error', $usuario->email.' ya recibió un recordatorio el '.$usuario->recordatorio_uso_en->format('d/m/Y').'.');
        }

        return $this->mandar($usuario)
            ? back()->with('status', 'Recordatorio enviado a '.$usuario->email.'.')
            : back()->with('error', 'No se pudo enviar el correo. Revisa el servidor de correo en Correos.');
    }

    public function enviarTodos(): RedirectResponse
    {
        if (! Correos::activa('recordatorio_uso')) {
            return back()->with('error', 'El recordatorio de uso está apagado en Correos.');
        }
        $enviados = $fallidos = 0;
        $this->pendientes()->limit(200)->get()->each(function (User $u) use (&$enviados, &$fallidos) {
            $this->mandar($u) ? $enviados++ : $fallidos++;
        });

        return back()->with($fallidos ? 'error' : 'status', "Recordatorios enviados: $enviados.".($fallidos ? " Fallaron $fallidos; revisa Correos." : ''));
    }

    /** Sin uso y sin recordatorio en los últimos DIAS_ENTRE_RECORDATORIOS días. */
    private function pendientes()
    {
        return ClientesAdmin::sinUso()->where(fn ($q) => $q->whereNull('recordatorio_uso_en')
            ->orWhere('recordatorio_uso_en', '<', now()->subDays(self::DIAS_ENTRE_RECORDATORIOS)));
    }

    private function mandar(User $usuario): bool
    {
        try {
            Mail::to($usuario->email)->send(new RecordatorioUso($usuario));
        } catch (\Throwable $e) {
            Log::error('No se pudo enviar el recordatorio de uso', ['user' => $usuario->id, 'error' => $e->getMessage()]);

            return false;
        }
        $usuario->forceFill(['recordatorio_uso_en' => now()])->save();

        return true;
    }
}
