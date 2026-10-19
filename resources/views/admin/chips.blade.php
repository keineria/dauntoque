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
            <div class="dt-panel-h"><h2>Generar códigos nuevos</h2></div>
            <form method="POST" action="/admin/chips/generar" style="display:grid;gap:12px">
                @csrf
                <div><label class="dt-label" for="g-cantidad">Cantidad</label><input id="g-cantidad" name="cantidad" type="number" min="1" max="200" value="10" class="dt-input" required></div>
                <div><label class="dt-label" for="g-nota">Nota (opcional)</label><input id="g-nota" name="nota" type="text" maxlength="120" placeholder="Lote octubre, pedido DT-00012…" class="dt-input"></div>
                <button type="submit" class="dt-btn pri" style="justify-self:start">Generar</button>
            </form>
        </section>
        <section class="dt-panel">
            <div class="dt-panel-h"><h2>Registrar chips que ya tienes</h2></div>
            <form method="POST" action="/admin/chips" style="display:grid;gap:12px">
                @csrf
                <div><label class="dt-label" for="r-codigos">Códigos o enlaces, uno por línea</label><textarea id="r-codigos" name="codigos" rows="4" class="dt-input" placeholder="https://portal.dauntoque.com/activar/abc123" required></textarea></div>
                <div><label class="dt-label" for="r-nota">Nota (opcional)</label><input id="r-nota" name="nota" type="text" maxlength="120" class="dt-input"></div>
                <button type="submit" class="dt-btn pri" style="justify-self:start">Registrar</button>
            </form>
        </section>
    </div>

    <section class="dt-panel" x-data="{ qr: null }">
        <div class="dt-panel-h">
            <h2>Chips registrados</h2>
            <form method="GET" action="/admin/chips"><input name="buscar" value="{{ $buscar }}" type="search" placeholder="Buscar código o nota" class="dt-input" style="height:36px"></form>
        </div>
        <div style="overflow-x:auto">
            <table style="width:100%;border-collapse:collapse;font-size:14px">
                <thead><tr style="text-align:left" class="dt-sub"><th style="padding:8px">Código</th><th style="padding:8px">Nota o pedido</th><th style="padding:8px">Estado</th><th></th></tr></thead>
                <tbody>
                @forelse ($chips as $chip)
                    @php $t = $tarjetas[$chip->codigo] ?? null; @endphp
                    <tr style="border-top:1px solid rgba(127,127,127,.18)">
                        <td style="padding:8px;font-family:ui-monospace,Consolas,monospace;word-break:break-all">{{ $chip->codigo }}</td>
                        <td style="padding:8px">
                            {{ $chip->nota }}
                            @if ($chip->pedido)
                                <span class="dt-sub" style="display:block;font-size:12px">{{ $chip->pedido->correo }} · {{ $chip->pedido->acabadoNombre() }}@if($chip->pedido->nombre_tarjeta) · {{ $chip->pedido->nombre_tarjeta }}@endif</span>
                            @endif
                        </td>
                        <td style="padding:8px">
                            @if ($t)
                                <span class="dt-chip up">Activado</span>
                                <span class="dt-sub" style="display:block;font-size:12px">{{ $t->user?->email }} · {{ $t->activada_en?->format('d/m/Y') }}</span>
                            @else
                                <span class="dt-chip">Sin activar</span>
                            @endif
                        </td>
                        <td style="padding:8px;text-align:right;white-space:nowrap">
                            <button type="button" class="dt-btn" @click="qr = {{ Js::from(['url' => $chip->url(), 'codigo' => $chip->codigo]) }}">QR</button>
                            @unless ($t)
                                <form method="POST" action="/admin/chips/{{ $chip->id }}" style="display:inline" onsubmit="return confirm('¿Borrar este chip del registro?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="dt-btn">Borrar</button>
                                </form>
                            @endunless
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" style="padding:16px" class="dt-sub">Todavía no hay chips registrados.</td></tr>
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
