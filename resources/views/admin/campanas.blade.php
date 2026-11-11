@extends('layouts.admin')
@section('titulo', 'Enlaces UTM y QR')

@section('contenido')
    @php
        $fmt = fn ($n) => number_format((float) $n, 0, ',', '.');
        $pct = fn ($n) => $n === null ? '—' : str_replace('.', ',', (string) $n) . ' %';
        $estadoChip = ['pagado' => 'ok', 'rechazado' => 'mal', 'pendiente' => 'aviso'];
        $estadoTexto = ['pagado' => 'Compró', 'rechazado' => 'Pago rechazado', 'pendiente' => 'No terminó de pagar'];
    @endphp
    <div class="dt-head">
        <div>
            <h1 class="dt-h1">Enlaces UTM y QR</h1>
            <p class="dt-sub">Crea un enlace o QR para cada lugar donde compartes el sitio y mira cuántas personas llegaron por él y cuántas compraron.</p>
        </div>
        <nav class="dt-seg ad-tabs" aria-label="Periodo" style="margin:0">
            @foreach ($periodos as $d => $nombre)
                <a href="/admin/campanas?dias={{ $d }}" class="{{ $dias === $d ? 'on' : '' }}" @if ($dias === $d) aria-current="page" @endif>{{ $nombre }}</a>
            @endforeach
        </nav>
    </div>

    @if ($errors->any())<p class="dt-panel ad-err" role="alert" style="margin:0 0 18px">{{ $errors->first() }}</p>@endif

    <section class="dt-panel" aria-labelledby="nueva-h">
        <div class="dt-panel-h"><h2 id="nueva-h">Nuevo enlace o QR</h2></div>
        @include('admin._campana-form', ['c' => null])
    </section>

    <section class="dt-panel" style="margin-top:18px" aria-labelledby="lista-h" x-data="{ qr: null }">
        <div class="dt-panel-h"><h2 id="lista-h">Resultados por enlace · {{ strtolower($periodos[$dias]) }}</h2><a class="dt-btn" href="/admin/rendimiento?dias={{ $dias }}">Ver por fuente</a></div>
        @if ($enlaces->isEmpty())
            <p class="dt-sub" style="margin:0">Aún no hay enlaces. Crea el primero arriba, por ejemplo «Bio de Instagram», y ponlo en tu perfil.</p>
        @else
            <div class="ad-scroll">
                <table class="ad-t" style="min-width:820px">
                    <thead>
                        <tr><th scope="col">Enlace</th><th scope="col">Personas que llegaron</th><th scope="col">Llegaron al pago</th><th scope="col">Compraron</th><th scope="col">Conversión</th><th scope="col">Ventas</th><th scope="col"><span class="sr-only">Acciones</span></th></tr>
                    </thead>
                    <tbody>
                        @foreach ($enlaces as $f)
                            @php($c = $f['campana'])
                            @php($compras = $pedidos->get($f['utm'], collect()))
                            <tr @if ($c) id="campana-{{ $c->id }}" @endif>
                                <td>
                                    <b>{{ $c?->nombre ?? $f['utm'] }}</b>
                                    <small>{{ $c ? $c->canalNombre() : 'UTM creado por fuera del Centro de mando' }} · <code>{{ $f['utm'] }}</code></small>
                                    @if ($c && $c->presupuesto !== null)
                                        <small>Inversión ${{ $fmt($c->presupuesto) }} {{ $c->moneda }} · costo por compra {{ $f['costoCompra'] !== null ? '$' . $fmt($f['costoCompra']) : '—' }} · retorno {{ $f['roas'] !== null ? str_replace('.', ',', (string) $f['roas']) . ' x' : '—' }}</small>
                                    @endif
                                </td>
                                <td><b>{{ $fmt($f['personas']) }}</b><small>{{ $fmt($f['paginas']) }} páginas vistas</small></td>
                                <td>{{ $fmt($f['pedidos']) }}</td>
                                <td><b class="{{ $f['pagados'] > 0 ? 'ad-ok' : '' }}">{{ $fmt($f['pagados']) }}</b></td>
                                <td>{{ $pct($f['conversion']) }}</td>
                                <td>@forelse ($f['ventas'] as $moneda => $total)${{ $fmt($total) }} {{ $moneda }}<br>@empty <span class="dt-sub">$0</span> @endforelse</td>
                                <td>
                                    @if ($c)
                                        <div class="ad-acciones" style="flex-wrap:nowrap" x-data="{ copiado: false }">
                                            <button type="button" class="dt-btn" @click="navigator.clipboard.writeText({{ Js::from($c->enlace()) }}).then(() => { copiado = true; setTimeout(() => copiado = false, 2000) }).catch(() => prompt('Copia el enlace:', {{ Js::from($c->enlace()) }}))" x-text="copiado ? '¡Copiado!' : 'Copiar'">Copiar</button>
                                            <button type="button" class="dt-btn" @click="qr = {{ Js::from(['url' => $c->enlace(), 'nombre' => $c->nombre]) }}">QR</button>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td colspan="7" style="border-top:0;padding-top:0">
                                    <div class="ad-acciones" style="gap:16px;align-items:flex-start">
                                        @if ($compras->isNotEmpty())
                                            <details>
                                                <summary class="dt-sub" style="cursor:pointer;font-size:12.5px">Ver quién llegó al pago ({{ $compras->count() }})</summary>
                                                <div class="ad-tabla" style="margin-top:6px;max-width:720px">
                                                    @foreach ($compras as $p)
                                                        <div class="ad-fila">
                                                            <span><b>{{ $p->codigo }}</b> · {{ $p->nombre }}@if ($p->utm_content)<small>variante {{ $p->utm_content }}</small>@endif</span>
                                                            <span><span class="ad-chip {{ $estadoChip[$p->estado] ?? 'neutro' }}">{{ $estadoTexto[$p->estado] ?? $p->estado }}</span></span>
                                                            <span style="text-align:right">${{ $fmt($p->monto) }} {{ $p->moneda }}<small>{{ $p->created_at->timezone('America/Bogota')->format('d/m/Y h:i a') }}</small></span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </details>
                                        @endif
                                        @if ($c)
                                            <details>
                                                <summary class="dt-sub" style="cursor:pointer;font-size:12.5px">Editar o eliminar</summary>
                                                <div style="padding:12px 0">
                                                    @include('admin._campana-form', ['c' => $c])
                                                    <form method="POST" action="/admin/campanas/{{ $c->id }}" style="margin-top:10px" onsubmit="return confirm('¿Eliminar el enlace «{{ e($c->nombre) }}»? Sus visitas y compras se siguen viendo por su UTM.')">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="dt-btn danger">Eliminar enlace</button>
                                                    </form>
                                                </div>
                                            </details>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="dt-sub" style="margin:10px 0 0;font-size:12.5px">Cuenta cualquier enlace con <code>utm_campaign</code>, también los que armes por fuera. Una persona queda asociada al último enlace por el que llegó durante 30 días, así compre días después.</p>
        @endif

        <div x-show="qr" x-cloak @keydown.escape.window="qr = null" @click.self="qr = null" style="position:fixed;inset:0;z-index:90;background:rgba(0,0,0,.6);display:grid;place-items:center;padding:16px" role="dialog" aria-modal="true" aria-label="QR del enlace">
            <div class="dt-panel" style="width:min(360px,100%);text-align:center">
                <div class="dt-panel-h"><h2 x-text="qr?.nombre || 'QR del enlace'">QR del enlace</h2><button type="button" class="dt-btn" @click="qr = null" aria-label="Cerrar">✕</button></div>
                <x-qr modelo="qr?.url" nombre="qr-dauntoque" tam="220" style="display:inline-block" />
                <p class="dt-sub" style="margin:12px 0 0;font-family:ui-monospace,Consolas,monospace;font-size:12px;overflow-wrap:anywhere" x-text="qr?.url"></p>
                <p class="dt-sub" style="margin:10px 0 0;font-size:13px">Cada escaneo cuenta como una visita de este enlace.</p>
            </div>
        </div>
    </section>
@endsection
