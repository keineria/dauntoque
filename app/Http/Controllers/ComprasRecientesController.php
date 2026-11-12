<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Marketing\ComprasRecientesRequest;
use App\Services\Marketing\ComprasRecientes;
use Illuminate\Http\JsonResponse;

/** /compras-recientes.json: compras pagadas reales y anónimas para los avisos de la portada del sitio de ventas. */
class ComprasRecientesController extends Controller
{
    public function __invoke(ComprasRecientesRequest $request, ComprasRecientes $compras): JsonResponse
    {
        return response()
            ->json(['compras' => $compras->lista($request->pais())])
            ->header('Cache-Control', 'public, max-age=300')
            ->header('X-Robots-Tag', 'noindex');
    }
}
