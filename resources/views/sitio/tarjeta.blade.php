@extends('sitio.layout')
@php
    $a = $t['acabados'][$acabado];
    $otro = $acabado === 'matte-black' ? 'crystal' : 'matte-black';
    $rep = [':acabado' => $a['nombre']];
    $producto = [
        '@context' => 'https://schema.org', '@type' => 'Product', 'name' => 'DaUnToque ' . $a['nombre'], 'brand' => ['@type' => 'Brand', 'name' => 'DaUnToque'],
        'description' => $x($t['tarjeta']['descripcion'], $rep), 'image' => [$dominio . $base . '/media/og-' . $version['idioma'] . '.jpg'],
        'offers' => ['@type' => 'Offer', 'price' => $mercado['precio'], 'priceCurrency' => $mercado['moneda'], 'availability' => 'https://schema.org/InStock', 'url' => $canonica($codigo)],
    ];
    $migas = ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'DaUnToque', 'item' => $dominio . '/' . $codigo . '/'],
        ['@type' => 'ListItem', 'position' => 2, 'name' => $a['nombre'], 'item' => $canonica($codigo)],
    ]];
@endphp

@section('titulo', $a['titulo'])
@section('descripcion', $x($t['tarjeta']['descripcion'], $rep))
@section('schema')
    <script type="application/ld+json">{!! json_encode($producto, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
    <script type="application/ld+json">{!! json_encode($migas, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
@endsection

@section('contenido')
    <div class="envoltura">
        <nav class="migas" aria-label="Breadcrumb"><a href="{{ $ir($codigo, 'inicio') }}">DaUnToque</a> / {{ $t['tarjeta']['migas'] }} / {{ $a['nombre'] }}</nav>
        <div class="producto">
            <div style="display:grid;gap:14px">
                <div class="acabado {{ $acabado === 'matte-black' ? 'matte' : 'crystal' }}">
                    <span class="muestra" aria-hidden="true"></span>
                    <h2 style="font-size:22px">{{ $a['nombre'] }}</h2>
                    <p>{{ $a['corto'] }}</p>
                </div>
                <div class="marco-video" data-video="{{ $base }}/media/{{ $a['video'] }}" @if ($a['video'] === 'florista') style="aspect-ratio:9/16;max-width:360px;width:100%;margin:0 auto" @endif>
                    <img class="portada" src="{{ $base }}/media/{{ $a['video'] }}-sm.webp" alt="" loading="lazy" width="{{ $a['video'] === 'florista' ? 360 : 640 }}" height="{{ $a['video'] === 'florista' ? 640 : 360 }}">
                </div>
            </div>
            <div>
                @if ($mercado['ahorroTexto'])<span class="cinta">{{ $x($t['inicio']['oferta']) }}</span>@endif
                <h1 style="margin-top:14px">{{ $a['nombre'] }}</h1>
                <p class="lema">{{ $a['texto'] }}</p>
                <div class="precio"><b>{{ $mercado['precioTexto'] }}</b><small>{{ $mercado['moneda'] }}</small>@if ($mercado['antesTexto'])<s>{{ $mercado['antesTexto'] }} {{ $mercado['moneda'] }}</s>@endif</div>
                <a class="btn btn-pri btn-ancho" data-compra href="{{ $comprar($acabado, $x($t['waAcabado'], $rep + [':precio' => $mercado['precioTexto'] . ' ' . $mercado['moneda']])) }}" @if($compraExterna) target="_blank" rel="noopener" @endif>{{ $x($t['tarjeta']['cta'], $rep) }}</a>
                <p class="nota">{{ $t['inicio']['unico'] }}</p>

                <h2 style="font-size:20px;margin-top:30px">{{ $t['tarjeta']['incluyeH'] }}</h2>
                <ul class="incluye">@foreach ($t['inicio']['incluye'] as $inc)<li>{{ $x($inc) }}</li>@endforeach</ul>

                <h2 style="font-size:20px">{{ $t['tarjeta']['detallesH'] }}</h2>
                <dl class="detalles">@foreach ($t['tarjeta']['detalles'] as $d)<dt>{{ $d[0] }}</dt><dd>{{ $d[1] }}</dd>@endforeach</dl>

                <div class="otro-acabado">
                    <span class="muted" style="align-self:center">{{ $t['tarjeta']['otro'] }}:</span>
                    <a class="btn btn-sec" href="{{ $ir($codigo, 'tarjeta', $otro) }}">{{ $t['acabados'][$otro]['nombre'] }}</a>
                </div>
            </div>
        </div>
    </div>

    <section class="seccion gris">
        <div class="envoltura">
            <div class="cabeza centro"><h2>{{ $t['inicio']['faqH'] }}</h2></div>
            <div class="faq">
                @foreach (array_slice($t['faq'], 0, 4) as $q)
                    <details><summary>{{ $q[0] }}</summary><p>{{ $x($q[1]) }}</p></details>
                @endforeach
            </div>
        </div>
    </section>
@endsection
