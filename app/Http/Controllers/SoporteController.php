<?php

namespace App\Http\Controllers;

use App\Models\SoporteConversacion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/** Ayuda del cliente: tickets y chat en vivo con un agente (cola por orden de llegada). */
class SoporteController extends Controller
{
    public function index(Request $request): View
    {
        return view('dashboard.ayuda', [
            'conversaciones' => SoporteConversacion::where('user_id', $request->user()->id)->latest('id')->limit(30)->get(),
        ]);
    }

    public function crear(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'tipo' => ['required', 'in:ticket,chat'],
            'asunto' => ['required', 'string', 'max:150'],
            'mensaje' => ['required', 'string', 'max:3000'],
        ], [], ['asunto' => 'asunto', 'mensaje' => 'mensaje']);
        $usuario = $request->user();

        // Un solo chat a la vez: si ya tiene uno esperando o en curso, sigue en ese
        if ($datos['tipo'] === 'chat' && $abierto = SoporteConversacion::where('user_id', $usuario->id)->where('tipo', 'chat')->where('estado', '!=', 'cerrada')->first()) {
            $abierto->escribir($usuario, $datos['mensaje'], false);

            return redirect('/dashboard/ayuda/'.$abierto->id);
        }

        $conversacion = DB::transaction(function () use ($datos, $usuario) {
            $conversacion = SoporteConversacion::create([
                'user_id' => $usuario->id,
                'tipo' => $datos['tipo'],
                'asunto' => $datos['asunto'],
                'estado' => $datos['tipo'] === 'chat' ? 'en_cola' : 'abierta',
            ]);
            $conversacion->escribir($usuario, $datos['mensaje'], false);

            return $conversacion;
        });

        return redirect('/dashboard/ayuda/'.$conversacion->id);
    }

    public function ver(Request $request, int $id): View
    {
        return view('soporte.conversacion', ['c' => $this->propia($request, $id), 'comoAgente' => false]);
    }

    /** Mensajes nuevos desde el último que ya tiene la pantalla, estado y puesto en la cola. */
    public function mensajes(Request $request, int $id): JsonResponse
    {
        return response()->json(self::estado($this->propia($request, $id), (int) $request->query('desde', 0)));
    }

    public function escribir(Request $request, int $id): JsonResponse
    {
        $datos = $request->validate(['texto' => ['required', 'string', 'max:3000']]);
        $conversacion = $this->propia($request, $id);
        abort_if($conversacion->estado === 'cerrada', 422, 'Esta conversación ya se cerró. Abre una nueva si necesitas más ayuda.');
        $conversacion->escribir($request->user(), $datos['texto'], false);

        return response()->json(self::estado($conversacion->fresh(), (int) $request->input('desde', 0)));
    }

    public function cerrar(Request $request, int $id): RedirectResponse
    {
        $this->propia($request, $id)->update(['estado' => 'cerrada', 'cerrada_en' => now()]);

        return redirect('/dashboard/ayuda')->with('status', 'Conversación cerrada. ¡Gracias por escribirnos!');
    }

    private function propia(Request $request, int $id): SoporteConversacion
    {
        return SoporteConversacion::where('user_id', $request->user()->id)->findOrFail($id);
    }

    /** @return array{estado: string, puesto: ?int, agente: ?string, mensajes: array<int, array<string, mixed>>} */
    public static function estado(SoporteConversacion $c, int $desde): array
    {
        return [
            'estado' => $c->estado,
            'puesto' => $c->puestoEnCola(),
            'agente' => $c->agente?->name ? strtok($c->agente->name, ' ') : null,
            'mensajes' => $c->mensajes()->where('id', '>', $desde)->orderBy('id')->get()->map->paraVista()->all(),
        ];
    }
}
