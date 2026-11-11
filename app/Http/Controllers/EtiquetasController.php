<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Marketing\VisitaRequest;
use App\Services\Marketing\Etiquetas;
use App\Services\Marketing\Origen;
use Illuminate\Http\Response;

/** /etiquetas.js (píxeles del Centro de mando) y la visita que manda ese script. */
class EtiquetasController extends Controller
{
    public function script(): Response
    {
        // 5 minutos en caché: un píxel nuevo empieza a medir a más tardar 5 minutos después de guardarlo
        return response(Etiquetas::script(), 200, [
            'Content-Type' => 'application/javascript; charset=UTF-8',
            'Cache-Control' => 'public, max-age=300',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function visita(VisitaRequest $request, Origen $origen): Response
    {
        $origen->registrarVisita($request, $request->validated());

        return response()->noContent();
    }
}
