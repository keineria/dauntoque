@extends('layouts.app')

@section('contenido')
    <div class="dt-head">
        <div>
            <h1 class="dt-h1">Chips</h1>
            <p class="dt-sub">Solo los chips registrados aquí se pueden activar. Cada compra pagada crea su código y se lo enviamos al cliente en el correo. {{ $total }} registrados · {{ $activados }} activados.</p>
        </div>
    </div>
    @include('admin._tabs')

    @if ($generados)
        <section class="dt-panel" style="margin-bottom:18px" x-data="{ copiado: false }">
            <div class="dt-panel-h">
                <h2>Códigos nuevos para grabar</h2>
                <button type="button" class="dt-btn" @click="navigator.clipboard.writeText($refs.lista.value); copiado = true" x-text="copiado ? 'Copiado' : 'Copiar todos'">Copiar todos</button>
            </div>
            <p class="dt-sub" style="margin:0 0 10px">Graba cada enlace en un chip con la app NFC Tools (Escribir → Añadir registro → URL). El cliente también puede escribir el código (lo que va después de /activar/) en su panel.</p>
            <textarea x-ref="lista" class="dt-input" rows="{{ min(count($generados), 10) }}" readonly style="font-family:ui-monospace,Consolas,monospace;font-size:13px">{{ implode("\n", $generados) }}</textarea>
        </section>
    @endif

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:18px;margin-bottom:18px">
        <section class="dt-panel">
            <div class="dt-panel-h"><h2>Generar códigos del sistema</h2></div>
            <p class="dt-sub" style="margin:0 0 10px;font-size:12.5px">Códigos fáciles de escribir (k7mq-2xpa). Cada compra pagada ya crea el suyo sola.</p>
            <form method="POST" action="/admin/chips/generar" style="display:grid;gap:12px">
                @csrf
                <div><label class="dt-label" for="g-cantidad">Cantidad</label><input id="g-cantidad" name="cantidad" type="number" min="1" max="200" value="10" class="dt-input" required></div>
                <div><label class="dt-label" for="g-nota">Nota (opcional)</label><input id="g-nota" name="nota" type="text" maxlength="120" placeholder="Lote octubre, pedido DT-00012…" class="dt-input"></div>
                <button type="submit" class="dt-btn pri" style="justify-self:start">Generar</button>
            </form>
        </section>
        <section class="dt-panel">
            <div class="dt-panel-h"><h2>Registrar códigos propios</h2></div>
            <p class="dt-sub" style="margin:0 0 10px;font-size:12.5px">Para chips que grabaste tú con tu propio código. Escribe solo el código (lo que va después de /activar/).</p>
            <form method="POST" action="/admin/chips" style="display:grid;gap:12px">
                @csrf
                <div><label class="dt-label" for="r-codigos">Códigos, uno por línea</label><textarea id="r-codigos" name="codigos" rows="4" class="dt-input" placeholder="abc123&#10;tienda-001" required style="font-family:ui-monospace,Consolas,monospace"></textarea></div>
                <div><label class="dt-label" for="r-nota">Nota (opcional)</label><input id="r-nota" name="nota" type="text" maxlength="120" class="dt-input"></div>
                <button type="submit" class="dt-btn pri" style="justify-self:start">Registrar</button>
            </form>
        </section>
    </div>

    <section class="dt-panel" x-data="{ qr: null, copiado: null }">
        <div class="dt-panel-h">
            <h2>Códigos <span class="dt-chip">{{ $chips->total() }}</span></h2>
            <a class="dt-btn" href="/admin/chips/exportar?{{ http_build_query(request()->only('buscar', 'origen', 'estado')) }}">Descargar en Excel (CSV)</a>
        </div>
        <form method="GET" action="/admin/chips" class="ad-filtros">
            <select class="dt-input" name="estado" aria-label="Estado" style="width:auto;height:38px" onchange="this.form.submit()">
                @foreach (\App\Http\Controllers\AdminChipsController::ESTADOS as $id => $nombre)<option value="{{ $id }}" @selected($estado === $id)>{{ $nombre }}</option>@endforeach
            </select>
            <select class="dt-input" name="origen" aria-label="Origen" style="width:auto;height:38px" onchange="this.form.submit()">
                <option value="todos" @selected($origen === 'todos')>Sistema y propios</option>
                @foreach (\App\Models\Chip::ORIGENES as $id => $nombre)<option value="{{ $id }}" @selected($origen === $id)>{{ $nombre }} ({{ $porOrigen[$id] ?? 0 }})</option>@endforeach
            </select>
            <input name="buscar" value="{{ $buscar }}" type="search" placeholder="Código, nota, pedido, cliente o correo" aria-label="Buscar" class="dt-input" style="flex:1;min-width:200px;height:38px">
            <select class="dt-input" name="por" aria-label="Por página" style="width:auto;height:38px" onchange="this.form.submit()">
                @foreach ([20, 50, 100] as $n)<option value="{{ $n }}" @selected($por === $n)>{{ $n }} por página</option>@endforeach
            </select>
            <button class="dt-btn" type="submit" style="height:38px">Buscar</button>
        </form>
        <div class="ad-scroll">
            <table class="ad-t" style="min-width:760px">
                <thead><tr><th>Código</th><th>Origen</th><th>Nota o pedido</th><th>Estado</th><th></th></tr></thead>
                <tbody>
                @forelse ($chips as $chip)
                    @php($t = $chip->tarjeta)
                    <tr>
                        <td><code style="font-size:14px">{{ $chip->codigo }}</code><small>Creado {{ $chip->created_at?->format('d/m/Y') }}</small></td>
                        <td><span class="dt-chip" style="font-size:11px;white-space:nowrap">{{ \App\Models\Chip::ORIGENES[$chip->origen] ?? $chip->origen }}</span></td>
                        <td>
                            {{ $chip->nota ?: '—' }}
                            @if ($chip->pedido)<small>{{ $chip->pedido->correo }} · {{ $chip->pedido->acabadoNombre() }}@if($chip->pedido->nombre_tarjeta) · {{ $chip->pedido->nombre_tarjeta }}@endif</small>@endif
                        </td>
                        <td>
                            @if ($t)
                                <span class="dt-chip up">Activado</span>
                                <small>@if ($t->user)<a href="/admin/clientes/{{ $t->user->id }}" style="color:#60a5fa">{{ $t->user->name }}</a> · {{ $t->user->email }}@endif</small>
                                <small>{{ $t->activada_en?->format('d/m/Y H:i') }} · {{ $t->activacion_medio === 'codigo' ? 'escribió el código' : ($t->activacion_medio ? 'acercó la tarjeta' : 'sin dato del medio') }}</small>
                                <small>{{ $t->activacion_lugar ?: 'Lugar sin dato' }} · {{ $t->activacion_dispositivo ?: 'dispositivo sin dato' }}</small>
                            @else
                                <span class="dt-chip">Sin activar</span>
                            @endif
                        </td>
                        <td style="text-align:right;white-space:nowrap">
                            <button type="button" class="dt-btn" @click="navigator.clipboard.writeText(@js($chip->url())); copiado = {{ $chip->id }}" x-text="copiado === {{ $chip->id }} ? 'Copiado' : 'Copiar enlace'">Copiar enlace</button>
                            <button type="button" class="dt-btn" @click="qr = {{ Js::from(['url' => $chip->url(), 'codigo' => $chip->codigo]) }}">QR</button>
                            @if (! $t && $chip->pedido)
                                <form method="POST" action="/admin/chips/{{ $chip->id }}/reenviar" style="display:inline">@csrf<button type="submit" class="dt-btn" title="Le vuelve a llegar el correo de su compra con este código">Reenviar al cliente</button></form>
                            @endif
                            @unless ($t)
                                <form method="POST" action="/admin/chips/{{ $chip->id }}" style="display:inline" onsubmit="return confirm('¿Borrar este código del registro?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="dt-btn">Borrar</button>
                                </form>
                            @endunless
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="dt-sub">{{ $buscar !== '' ? 'Ningún código coincide con «'.$buscar.'».' : 'No hay códigos con este filtro.' }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:12px">{{ $chips->links() }}</div>

        {{-- QR del enlace del chip, para imprimirlo en el respaldo de la tarjeta --}}
        <div x-show="qr" x-cloak @keydown.escape.window="qr = null" @click.self="qr = null" style="position:fixed;inset:0;z-index:50;background:rgba(0,0,0,.6);display:grid;place-items:center;padding:16px">
            <div class="dt-panel" style="width:min(360px,100%);text-align:center">
                <div class="dt-panel-h"><h2>QR del chip</h2><button type="button" class="dt-btn" @click="qr = null" aria-label="Cerrar">✕</button></div>
                {{-- Sin <template>: el QR trae su script y dentro de un <template> no se ejecutaría --}}
                <x-qr modelo="qr?.url" nombre="qr-chip" tam="220" style="display:inline-block" />
                <p class="dt-sub" style="margin:12px 0 0;font-family:ui-monospace,Consolas,monospace;font-size:12px;overflow-wrap:anywhere" x-text="qr?.url"></p>
                <p class="dt-sub" style="margin:10px 0 0;font-size:13px">Lleva al mismo enlace grabado en el chip. Imprímelo atrás de la tarjeta con el SVG.</p>
            </div>
        </div>
    </section>
@endsection
