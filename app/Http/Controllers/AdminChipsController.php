<?php

namespace App\Http\Controllers;

use App\Models\Chip;
use App\Models\Tarjeta;
use App\Mail\PedidoConfirmado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Panel del dueño: registrar los chips que se envían, para que solo esos se puedan activar. */
class AdminChipsController extends Controller
{
    public const ESTADOS = ['sin_activar' => 'Sin activar', 'activos' => 'Activados', 'todos' => 'Todos'];

    public function index(Request $request)
    {
        $filtros = $this->filtros($request);
        $chips = $this->consulta($filtros)->with(['pedido', 'tarjeta.user:id,name,email'])
            ->latest('id')->paginate($filtros['por'])->withQueryString();

        return view('admin.chips', $filtros + [
            'chips' => $chips,
            'total' => Chip::count(),
            'activados' => Chip::has('tarjeta')->count(),
            'porOrigen' => Chip::selectRaw('origen, COUNT(*) as n')->groupBy('origen')->pluck('n', 'origen'),
            'generados' => session('generados', []),
        ]);
    }

    /** La lista filtrada en CSV (se abre en Excel), con el enlace para grabar cada chip. */
    public function exportar(Request $request): StreamedResponse
    {
        $chips = $this->consulta($this->filtros($request))->with(['pedido', 'tarjeta.user:id,email'])->latest('id');

        return response()->streamDownload(function () use ($chips) {
            $salida = fopen('php://output', 'w');
            fwrite($salida, "\u{FEFF}");
            fputcsv($salida, ['Código', 'Enlace para grabar', 'Origen', 'Nota', 'Pedido', 'Estado', 'Cliente', 'Activado el', 'Lugar', 'Dispositivo'], ';');
            $chips->chunk(500, fn ($lote) => $lote->each(fn (Chip $c) => fputcsv($salida, [
                $c->codigo, $c->url(), Chip::ORIGENES[$c->origen] ?? $c->origen, $c->nota, $c->pedido?->codigo,
                $c->tarjeta ? 'Activado' : 'Sin activar', $c->tarjeta?->user?->email, $c->tarjeta?->activada_en?->format('d/m/Y H:i'),
                $c->tarjeta?->activacion_lugar, $c->tarjeta?->activacion_dispositivo,
            ], ';')));
            fclose($salida);
        }, 'chips-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Reenvía al cliente el correo de su compra con el código (solo chips de compras sin activar). */
    public function reenviar(Chip $chip)
    {
        if (! $chip->pedido || $chip->tarjeta()->exists()) {
            return back()->with('error', 'Solo se reenvían códigos de compras que aún no se activan.');
        }
        try {
            Mail::to($chip->pedido->correo)->send(new PedidoConfirmado($chip->pedido));
        } catch (\Throwable $e) {
            Log::error('No se pudo reenviar el código', ['chip' => $chip->id, 'error' => $e->getMessage()]);

            return back()->with('error', 'No se pudo enviar el correo. Revisa el servidor en Correos.');
        }

        return back()->with('status', 'Código '.$chip->codigo.' reenviado a '.$chip->pedido->correo.'.');
    }

    private function filtros(Request $request): array
    {
        return [
            'buscar' => trim((string) $request->query('buscar')),
            'origen' => array_key_exists($request->query('origen'), Chip::ORIGENES) ? $request->query('origen') : 'todos',
            'estado' => array_key_exists($request->query('estado'), self::ESTADOS) ? $request->query('estado') : 'sin_activar',
            'por' => in_array((int) $request->query('por'), [20, 50, 100], true) ? (int) $request->query('por') : 50,
        ];
    }

    private function consulta(array $f)
    {
        $buscar = $f['buscar'];

        return Chip::query()
            ->when($buscar !== '', fn ($q) => $q->where(fn ($q) => $q->where('codigo', 'like', "%$buscar%")->orWhere('nota', 'like', "%$buscar%")
                ->orWhereHas('pedido', fn ($p) => $p->where('correo', 'like', "%$buscar%")->orWhere('nombre', 'like', "%$buscar%")->orWhere('codigo', 'like', "%$buscar%"))
                ->orWhereHas('tarjeta.user', fn ($u) => $u->where('email', 'like', "%$buscar%")->orWhere('name', 'like', "%$buscar%"))))
            ->when($f['origen'] !== 'todos', fn ($q) => $q->where('origen', $f['origen']))
            ->when($f['estado'] === 'sin_activar', fn ($q) => $q->doesntHave('tarjeta'))
            ->when($f['estado'] === 'activos', fn ($q) => $q->has('tarjeta'));
    }

    /** Registra códigos propios (uno por línea; si pegan la URL completa, se toma solo el código). */
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
                Chip::create(['codigo' => $codigo, 'nota' => $datos['nota'] ?? null, 'origen' => 'propio']);
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
            $urls[] = Chip::create(['codigo' => Chip::nuevoCodigo(), 'nota' => $datos['nota'] ?? null, 'origen' => 'sistema'])->url();
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
