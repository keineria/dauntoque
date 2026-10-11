<?php

namespace App\Http\Controllers;

use App\Models\Chip;
use App\Models\Tarjeta;
use Illuminate\Http\Request;

/** Panel del dueño: registrar los chips que se envían, para que solo esos se puedan activar. */
class AdminChipsController extends Controller
{
    public function index(Request $request)
    {
        $buscar = trim((string) $request->query('buscar'));
        $chips = Chip::query()
            ->when($buscar !== '', fn ($q) => $q->where(fn ($q) => $q->where('codigo', 'like', "%$buscar%")->orWhere('nota', 'like', "%$buscar%")))
            ->latest('id')->paginate(50)->withQueryString();
        // Activación de cada chip de la página: correo del dueño y fecha
        $tarjetas = Tarjeta::with('user:id,email')->whereIn('nfc_uid', $chips->pluck('codigo'))->get()->keyBy('nfc_uid');

        return view('admin.chips', [
            'chips' => $chips,
            'tarjetas' => $tarjetas,
            'buscar' => $buscar,
            'total' => Chip::count(),
            'activados' => Tarjeta::whereIn('nfc_uid', Chip::select('codigo'))->count(),
            'generados' => session('generados', []),
        ]);
    }

    /** Registra códigos pegados (uno por línea, sueltos o con la URL completa). */
    public function guardar(Request $request)
    {
        $datos = $request->validate([
            'codigos' => ['required', 'string', 'max:20000'],
            'nota' => ['nullable', 'string', 'max:120'],
        ], [], ['codigos' => 'códigos']);

        $nuevos = $repetidos = $invalidos = 0;
        foreach (preg_split('/\R/', $datos['codigos']) as $linea) {
            if (trim($linea) === '') {
                continue;
            }
            $codigo = Chip::extraerCodigo($linea);
            if ($codigo === null) {
                $invalidos++;
            } elseif (Chip::where('codigo', $codigo)->exists()) {
                $repetidos++;
            } else {
                Chip::create(['codigo' => $codigo, 'nota' => $datos['nota'] ?? null]);
                $nuevos++;
            }
        }

        return back()->with('status', "Registrados: $nuevos." . ($repetidos ? " Ya estaban: $repetidos." : '') . ($invalidos ? " No válidos: $invalidos." : ''));
    }

    /** Crea códigos nuevos imposibles de adivinar, listos para grabar en los chips. */
    public function generar(Request $request)
    {
        $datos = $request->validate([
            'cantidad' => ['required', 'integer', 'min:1', 'max:200'],
            'nota' => ['nullable', 'string', 'max:120'],
        ]);

        $urls = [];
        for ($i = 0; $i < $datos['cantidad']; $i++) {
            $urls[] = Chip::create(['codigo' => Chip::nuevoCodigo(), 'nota' => $datos['nota'] ?? null])->url();
        }

        return back()->with('status', count($urls) . ' códigos nuevos listos para grabar.')->with('generados', $urls);
    }

    /** Solo se borran chips que nadie ha activado (por ejemplo, uno registrado por error). */
    public function eliminar(Chip $chip)
    {
        if (Tarjeta::where('nfc_uid', $chip->codigo)->exists()) {
            return back()->with('status', 'Ese chip ya está activado; no se puede borrar.');
        }
        $chip->delete();

        return back()->with('status', 'Chip borrado.');
    }
}
