@extends('layouts.admin')
@section('titulo', 'Píxeles')

@section('contenido')
    <div class="dt-head">
        <div>
            <h1 class="dt-h1">Píxeles y etiquetas</h1>
            <p class="dt-sub">Pega el ID de cada plataforma y queda conectada en el sitio de ventas, el formulario de compra y la página del pedido.</p>
        </div>
    </div>

    <div class="ad-dos" style="margin-bottom:18px">
        <section class="dt-panel">
            <div class="dt-panel-h" style="margin-bottom:8px"><h2>Qué se mide solo</h2><span class="ad-chip {{ $ids ? 'ok' : 'neutro' }}">{{ count($ids) }} conectado{{ count($ids) === 1 ? '' : 's' }}</span></div>
            <ul class="dt-sub" style="margin:0;padding-left:18px;line-height:1.7;font-size:13px">
                <li><b>Visita</b> en cada página del sitio (PageView, page_view).</li>
                <li><b>Inicio de compra</b> al abrir el formulario de compra, con el precio (InitiateCheckout, begin_checkout).</li>
                <li><b>Compra</b> cuando Bold confirma el pago, con el valor y el número de pedido, una sola vez por pedido (Purchase, purchase, conversión de Google Ads, CompletePayment).</li>
            </ul>
        </section>
        <section class="dt-panel">
            <div class="dt-panel-h" style="margin-bottom:8px"><h2>Bueno saber</h2></div>
            <ul class="dt-sub" style="margin:0;padding-left:18px;line-height:1.7;font-size:13px">
                <li>Solo se guarda el ID público de cada píxel: ninguna clave ni contraseña.</li>
                <li>Al guardar, empieza a medir en unos 5 minutos, sin tocar el servidor.</li>
                <li>Leer el gasto y los clics de los anuncios desde aquí necesita la API de cada plataforma: llega en una siguiente etapa, con tu permiso.</li>
            </ul>
        </section>
    </div>

    <form method="POST" action="/admin/pixeles" x-data="{ guardando: false }" @submit="guardando = true">
        @csrf
        <div class="ad-dos">
            @foreach ($pixeles as $clave => $pixel)
                @continue($clave === 'gads_compra')
                <section class="ad-pixel" aria-labelledby="px-{{ $clave }}-h">
                    <div class="ad-pixel-h">
                        <b id="px-{{ $clave }}-h">{{ $pixel['nombre'] }}</b>
                        <span class="ad-chip {{ isset($ids[$clave]) ? 'ok' : 'neutro' }}">{{ isset($ids[$clave]) ? 'Conectado' : 'Sin conectar' }}</span>
                    </div>
                    <label class="ad-campo" for="px-{{ $clave }}">ID
                        <input id="px-{{ $clave }}" name="{{ $clave }}" class="dt-input" value="{{ old($clave, $ids[$clave] ?? '') }}" placeholder="{{ $pixel['ejemplo'] }}" autocomplete="off" spellcheck="false" @error($clave) aria-invalid="true" aria-describedby="px-{{ $clave }}-err" @enderror>
                    </label>
                    @error($clave)<p class="ad-err" id="px-{{ $clave }}-err">{{ $message }}</p>@enderror
                    @if ($clave === 'gads')
                        @php($etq = $pixeles['gads_compra'])
                        <label class="ad-campo" for="px-gads_compra">Etiqueta de la conversión «Compra»
                            <input id="px-gads_compra" name="gads_compra" class="dt-input" value="{{ old('gads_compra', $ids['gads_compra'] ?? '') }}" placeholder="{{ $etq['ejemplo'] }}" autocomplete="off" spellcheck="false" @error('gads_compra') aria-invalid="true" @enderror>
                        </label>
                        @error('gads_compra')<p class="ad-err">{{ $message }}</p>@enderror
                    @endif
                    <details>
                        <summary>Dónde encuentro el ID</summary>
                        <ol style="margin-top:8px">
                            @foreach ($pixel['pasos'] as $paso)<li>{{ $paso }}</li>@endforeach
                            @if ($clave === 'gads')@foreach ($pixeles['gads_compra']['pasos'] as $paso)<li>{{ $paso }}</li>@endforeach @endif
                        </ol>
                        @if ($pixel['comprobar'])<p class="dt-sub" style="margin:8px 0 0;font-size:12.5px"><b>Comprobar:</b> {{ $pixel['comprobar'] }}</p>@endif
                    </details>
                </section>
            @endforeach
        </div>
        <div class="ad-acciones" style="margin-top:18px">
            <button type="submit" class="dt-btn pri" :disabled="guardando" x-text="guardando ? 'Guardando…' : 'Guardar píxeles'">Guardar píxeles</button>
            <span class="dt-sub" style="font-size:12.5px">Para desconectar uno, borra su ID y guarda.</span>
        </div>
    </form>
@endsection
