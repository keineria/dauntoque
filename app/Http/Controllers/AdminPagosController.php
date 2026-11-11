<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Bold;
use App\Services\EstadoPagos;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/** Centro de mando · pagos: conexión con Bold, avisos recibidos, cobros y Stripe (próximamente). */
class AdminPagosController extends Controller
{
    public function index(EstadoPagos $estado): View
    {
        return view('admin.pagos', [
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
}
