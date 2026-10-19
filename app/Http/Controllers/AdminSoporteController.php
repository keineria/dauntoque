<?php

namespace App\Http\Controllers;

use App\Models\SoporteConversacion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/** Panel del agente: cola de chats por orden de llegada, chats en curso y tickets. */
class AdminSoporteController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.soporte', [
            'cola' => SoporteConversacion::enCola()->with('user:id,name,email')->get(),
            'misChats' => SoporteConversacion::where('tipo', 'chat')->where('estado', 'abierta')->with(['user:id,name,email', 'agente:id,name'])->latest('ultimo_mensaje_en')->get(),
            'tickets' => SoporteConversacion::where('tipo', 'ticket')->where('estado', 'abierta')->with('user:id,name,email')->latest('ultimo_mensaje_en')->get(),
            'cerradas' => SoporteConversacion::where('estado', 'cerrada')->with('user:id,name,email')->latest('cerrada_en')->limit(20)->get(),
        ]);
    }

    /** Estado de la cola para refrescar el panel sin recargar. */
    public function resumen(): JsonResponse
    {
        $enCola = SoporteConversacion::enCola()->count();

        // La firma cambia con cada chat, mensaje o cierre: el panel se recarga solo cuando hay novedades
        return response()->json([
            'enCola' => $enCola,
            'firma' => md5($enCola.'|'.SoporteConversacion::count().'|'.SoporteConversacion::max('ultimo_mensaje_en').'|'.SoporteConversacion::max('updated_at')),
        ]);
    }

    /** El agente toma el chat que más lleva esperando. */
    public function tomarSiguiente(Request $request): RedirectResponse
    {
        $tomado = DB::transaction(function () use ($request) {
            $siguiente = SoporteConversacion::enCola()->lockForUpdate()->first();
            $siguiente?->update(['estado' => 'abierta', 'agente_id' => $request->user()->id, 'tomada_en' => now()]);

            return $siguiente;
        });

        return $tomado
            ? redirect('/admin/soporte/'.$tomado->id)
            : back()->with('status', 'No hay nadie esperando en la cola.');
    }

    public function ver(int $id): View
    {
        $c = SoporteConversacion::with('user:id,name,email')->findOrFail($id);

        return view('soporte.conversacion', ['c' => $c, 'comoAgente' => true]);
    }

    public function mensajes(Request $request, int $id): JsonResponse
    {
        return response()->json(SoporteController::estado(SoporteConversacion::findOrFail($id), (int) $request->query('desde', 0)));
    }

    public function responder(Request $request, int $id): JsonResponse
    {
        $datos = $request->validate(['texto' => ['required', 'string', 'max:3000']]);
        $c = SoporteConversacion::findOrFail($id);
        abort_if($c->estado === 'cerrada', 422, 'Esta conversación ya se cerró.');
        // Responder un chat en cola lo saca de la cola y lo asigna a quien responde
        if ($c->estado === 'en_cola' || ! $c->agente_id) {
            $c->update(['estado' => 'abierta', 'agente_id' => $request->user()->id, 'tomada_en' => $c->tomada_en ?? now()]);
        }
        $c->escribir($request->user(), $datos['texto'], true);

        return response()->json(SoporteController::estado($c->fresh(), (int) $request->input('desde', 0)));
    }

    public function cerrar(int $id): RedirectResponse
    {
        SoporteConversacion::findOrFail($id)->update(['estado' => 'cerrada', 'cerrada_en' => now()]);

        return redirect('/admin/soporte')->with('status', 'Conversación cerrada.');
    }
}
