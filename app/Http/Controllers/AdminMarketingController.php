<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Admin\CampanaRequest;
use App\Http\Requests\Admin\PixelesRequest;
use App\Models\Campana;
use App\Services\Marketing\Etiquetas;
use App\Services\Marketing\Rendimiento;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Centro de mando · marketing: píxeles, enlaces UTM y QR, y rendimiento. */
class AdminMarketingController extends Controller
{
    public function pixeles(): View
    {
        return view('admin.pixeles', ['pixeles' => Etiquetas::PIXELES, 'ids' => Etiquetas::ids()]);
    }

    public function guardarPixeles(PixelesRequest $request): RedirectResponse
    {
        Etiquetas::guardar($request->validated());

        return back()->with('status', 'Píxeles guardados. Empiezan a medir en el sitio en unos 5 minutos.');
    }

    public function campanas(Request $request): View
    {
        $dias = (int) $request->query('dias', 30);
        $rendimiento = new Rendimiento($dias);

        return view('admin.campanas', [
            'dias' => array_key_exists($dias, Rendimiento::PERIODOS) ? $dias : 30,
            'periodos' => Rendimiento::PERIODOS,
            'enlaces' => $rendimiento->porCampana(),
            'pedidos' => $rendimiento->pedidosPorCampana(),
            'canales' => Campana::CANALES,
            'destinos' => $this->destinos(),
        ]);
    }

    public function guardarCampana(CampanaRequest $request): RedirectResponse
    {
        $campana = Campana::create($request->validated());

        return redirect('/admin/campanas#campana-' . $campana->id)->with('status', 'Enlace creado. Cópialo o descarga su QR y compártelo.');
    }

    public function actualizarCampana(CampanaRequest $request, Campana $campana): RedirectResponse
    {
        $campana->update($request->validated());

        return redirect('/admin/campanas#campana-' . $campana->id)->with('status', 'Enlace actualizado.');
    }

    public function eliminarCampana(Campana $campana): RedirectResponse
    {
        $campana->delete();

        return redirect('/admin/campanas')->with('status', 'Enlace eliminado. Sus visitas y compras se siguen viendo por su UTM.');
    }

    public function rendimiento(Request $request): View
    {
        $dias = (int) $request->query('dias', 30);
        $rendimiento = new Rendimiento($dias);

        return view('admin.rendimiento', [
            'dias' => array_key_exists($dias, Rendimiento::PERIODOS) ? $dias : 30,
            'periodos' => Rendimiento::PERIODOS,
            'totales' => $rendimiento->totales(),
            'fuentes' => $rendimiento->porFuente(),
            'campanas' => $rendimiento->porCampana(),
            'serie' => $rendimiento->porDia(),
            'paginas' => $rendimiento->paginas(),
            'audiencia' => $rendimiento->audiencia(),
            'pixeles' => Etiquetas::ids(),
        ]);
    }

    /** Páginas del sitio a las que puede llevar un enlace de campaña. @return array<string, string> */
    private function destinos(): array
    {
        $dominio = 'https://dauntoque.com';

        return [
            $dominio . '/' => 'Inicio (elige el país solo)',
            $dominio . '/es-co/' => 'Inicio Colombia',
            $dominio . '/es-us/' => 'Inicio Estados Unidos (español)',
            $dominio . '/en-us/' => 'Inicio Estados Unidos (inglés)',
            $dominio . '/es-co/tarjetas/matte-black/' => 'Tarjeta Matte Black (Colombia)',
            $dominio . '/es-co/tarjetas/crystal/' => 'Tarjeta Crystal (Colombia)',
            $dominio . '/comprar' => 'Directo a comprar (Colombia)',
        ];
    }
}
