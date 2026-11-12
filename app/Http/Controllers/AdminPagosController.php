<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Ajuste;
use App\Services\Bold;
use App\Services\Marketing\ComprasRecientes;
use App\Services\EstadoPagos;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Centro de mando · pagos: conexión con Bold, avisos recibidos, cobros y Stripe (próximamente). */
class AdminPagosController extends Controller
{
    public function index(EstadoPagos $estado, ComprasRecientes $compras): View
    {
        return view('admin.pagos', [
            'avisos' => ['activos' => ComprasRecientes::activos(), 'dias' => (int) config('pedidos.avisos.dias'), 'compras' => $compras->lista('CO', false)],
            'bold' => $estado->bold(),
            'cobros' => $estado->cobros(),
            'listo' => $estado->listoParaVender(),
        ]);
    }

    public function probarBold(): RedirectResponse
    {
        $prueba = Bold::probarConexion();

        return back()->with($prueba['ok'] ? 'status' : 'error', $prueba['mensaje']);
    }

    /** Enciende o apaga los avisos «X compró la tarjeta» del sitio de ventas (solo muestran pedidos pagados reales). */
    public function avisosCompras(Request $request): RedirectResponse
    {
        Ajuste::guardar(['avisos_compras' => $request->boolean('activos')]);
        ComprasRecientes::olvidar();

        return back()->with('status', $request->boolean('activos') ? 'Avisos de compras encendidos en el sitio.' : 'Avisos de compras apagados.');
    }
}
