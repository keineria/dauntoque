@extends('layouts.admin')
@section('titulo', 'Campañas y UTM')

@section('contenido')
    @php($fmt = fn ($n) => number_format((float) $n, 0, ',', '.'))
    <div class="dt-head">
        <div>
            <h1 class="dt-h1">Campañas y UTM</h1>
            <p class="dt-sub">Crea cada campaña con su enlace. Así sabes qué anuncio, publicación o volante trae cada visita y cada venta.</p>
        </div>
    </div>

    @if ($errors->any())<p class="dt-panel ad-err" role="alert" style="margin:0 0 18px">{{ $errors->first() }}</p>@endif

    <section class="dt-panel" aria-labelledby="nueva-h">
        <div class="dt-panel-h"><h2 id="nueva-h">Nueva campaña</h2></div>
        @include('admin._campana-form', ['c' => null])
    </section>

    <section class="dt-panel" style="margin-top:18px" aria-labelledby="lista-h" x-data="{ qr: null }">
        <div class="dt-panel-h"><h2 id="lista-h">Campañas</h2><a class="dt-btn" href="/admin/rendimiento">Ver rendimiento completo</a></div>
        @if ($campanas->isEmpty())
            <p class="dt-sub" style="margin:0">Aún no hay campañas. Crea la primera arriba y usa su enlace en el anuncio o en el QR del volante.</p>
        @else
            <div class="ad-scroll">
                <table class="ad-t">
                    <thead>
                        <tr><th scope="col">Campaña</th><th scope="col">Canal</th><th scope="col">Personas</th><th scope="col">Compras</th><th scope="col">Ventas</th><th scope="col">Costo por compra</th><th scope="col">Retorno</th><th scope="col"><span class="sr-only">Acciones</span></th></tr>
                    </thead>
                    <tbody>
                        @foreach ($campanas as $c)
                            @php($r = $resultados->get($c->utm_campaign))
                            <tr id="campana-{{ $c->id }}">
                                <td><b>{{ $c->nombre }}</b><small><code>{{ $c->utm_campaign }}</code>@if ($c->inicia_en) · {{ $c->inicia_en->format('d/m/Y') }}{{ $c->termina_en ? ' a ' . $c->termina_en->format('d/m/Y') : '' }}@endif</small></td>
                                <td>{{ $c->canalNombre() }}<small>{{ $c->utm_source }} / {{ $c->utm_medium }}</small></td>
                                <td>{{ $fmt($r['personas'] ?? 0) }}</td>
                                <td>{{ $fmt($r['pagados'] ?? 0) }}<small>{{ $fmt($r['pedidos'] ?? 0) }} llegaron al pago</small></td>
                                <td>@forelse (($r['ventas'] ?? []) as $moneda => $total)${{ $fmt($total) }} {{ $moneda }}<br>@empty <span class="dt-sub">$0</span> @endforelse</td>
                                <td>{{ isset($r['costoCompra']) && $r['costoCompra'] !== null ? '$' . $fmt($r['costoCompra']) . ' ' . $c->moneda : '—' }}<small>{{ $c->presupuesto !== null ? 'presupuesto $' . $fmt($c->presupuesto) . ' ' . $c->moneda : 'sin presupuesto' }}</small></td>
                                <td>{{ isset($r['roas']) && $r['roas'] !== null ? str_replace('.', ',', (string) $r['roas']) . ' x' : '—' }}</td>
                                <td>
                                    <div class="ad-acciones" style="flex-wrap:nowrap" x-data="{ copiado: false }">
                                        <button type="button" class="dt-btn" @click="navigator.clipboard.writeText({{ Js::from($c->enlace()) }}).then(() => { copiado = true; setTimeout(() => copiado = false, 2000) }).catch(() => prompt('Copia el enlace:', {{ Js::from($c->enlace()) }}))" x-text="copiado ? '¡Copiado!' : 'Copiar enlace'">Copiar enlace</button>
                                        <button type="button" class="dt-btn" @click="qr = {{ Js::from(['url' => $c->enlace(), 'nombre' => $c->utm_campaign]) }}">QR</button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="8" style="border-top:0;padding-top:0">
                                    <details>
                                        <summary class="dt-sub" style="cursor:pointer;font-size:12.5px">Editar o eliminar</summary>
                                        <div style="padding:12px 0">
                                            @include('admin._campana-form', ['c' => $c])
                                            <form method="POST" action="/admin/campanas/{{ $c->id }}" style="margin-top:10px" onsubmit="return confirm('¿Eliminar la campaña «{{ e($c->nombre) }}»? Sus visitas y ventas siguen contándose por su UTM.')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="dt-btn danger">Eliminar campaña</button>
                                            </form>
                                        </div>
                                    </details>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="margin-top:12px">{{ $campanas->links() }}</div>
            <p class="dt-sub" style="margin:10px 0 0;font-size:12.5px">Cifras de los últimos 30 días. Retorno = ventas ÷ presupuesto (en la moneda de la campaña). El presupuesto lo escribes tú; cuando conectemos las APIs de anuncios, saldrá del gasto real.</p>
        @endif

        <div x-show="qr" x-cloak @keydown.escape.window="qr = null" @click.self="qr = null" style="position:fixed;inset:0;z-index:90;background:rgba(0,0,0,.6);display:grid;place-items:center;padding:16px" role="dialog" aria-modal="true" aria-label="QR de la campaña">
            <div class="dt-panel" style="width:min(360px,100%);text-align:center">
                <div class="dt-panel-h"><h2>QR de la campaña</h2><button type="button" class="dt-btn" @click="qr = null" aria-label="Cerrar">✕</button></div>
                <x-qr modelo="qr?.url" nombre="qr-campana" tam="220" style="display:inline-block" />
                <p class="dt-sub" style="margin:12px 0 0;font-family:ui-monospace,Consolas,monospace;font-size:12px;overflow-wrap:anywhere" x-text="qr?.url"></p>
                <p class="dt-sub" style="margin:10px 0 0;font-size:13px">Para volantes, pendones y eventos: cada escaneo cuenta como visita de esta campaña.</p>
            </div>
        </div>
    </section>
@endsection
