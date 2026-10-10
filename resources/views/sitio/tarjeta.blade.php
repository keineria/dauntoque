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
    <section class="producto-sec">
        <div class="wrap">
            <nav class="migas" aria-label="Breadcrumb"><a href="{{ $ir($codigo, 'inicio') }}">DaUnToque</a> / {{ $t['tarjeta']['migas'] }} / {{ $a['nombre'] }}</nav>
            <div class="producto">
                <div class="producto-media">
                    <div class="estudio {{ $acabado === 'crystal' ? 'crystal' : '' }}">
                        <div class="t3d">
                            <div class="fila"><span>dauntoque</span><svg class="nfc-ic" aria-hidden="true"><use href="#nfc"/></svg></div>
                            <div><h2 class="nom">{{ $a['nombre'] }}</h2><div class="car">{{ $a['corto'] }}</div></div>
                        </div>
                    </div>
                    <div class="marco-video {{ $a['video'] === 'florista' ? 'vertical' : '' }}" data-video="{{ $base }}/media/{{ $a['video'] }}">
                        <img class="portada" src="{{ $base }}/media/{{ $a['video'] }}-sm.webp" alt="" loading="lazy" width="{{ $a['video'] === 'florista' ? 360 : 640 }}" height="{{ $a['video'] === 'florista' ? 640 : 360 }}">
                    </div>
                </div>
                <div class="producto-txt">
                    @if ($mercado['ahorroTexto'])<span class="badge"><svg width="14" height="14" aria-hidden="true"><use href="#spark"/></svg>{{ $x($t['inicio']['oferta']) }}</span>@endif
                    <h1>{{ $a['nombre'] }}</h1>
                    <p class="lead mut">{{ $a['texto'] }}</p>
                    <div class="precio-hero"><strong class="num">{{ $mercado['precioTexto'] }}</strong><small>{{ $mercado['moneda'] }}</small>@if ($mercado['antesTexto'])<s class="price-old num">{{ $mercado['antesTexto'] }}</s>@endif</div>
                    <a class="btn btn-pri btn-ancho" data-compra href="{{ $comprar($acabado, $x($t['waAcabado'], $rep + [':precio' => $mercado['precioTexto'] . ' ' . $mercado['moneda']])) }}" @if($compraExterna) target="_blank" rel="noopener" @endif>{{ $x($t['tarjeta']['cta'], $rep) }}</a>
                    <p class="nota">{{ $t['inicio']['unico'] }}</p>

                    <h2 class="sub-h">{{ $t['tarjeta']['incluyeH'] }}</h2>
                    <ul class="incluye">@foreach ($t['inicio']['incluye'] as $inc)<li><svg width="16" height="16" aria-hidden="true"><use href="#ok"/></svg>{{ $x($inc) }}</li>@endforeach</ul>

                    <h2 class="sub-h">{{ $t['tarjeta']['detallesH'] }}</h2>
                    <dl class="detalles">@foreach ($t['tarjeta']['detalles'] as $d)<dt>{{ $d[0] }}</dt><dd>{{ $d[1] }}</dd>@endforeach</dl>

                    <div class="otro-acabado">
                        <span class="mut">{{ $t['tarjeta']['otro'] }}:</span>
                        <a class="btn btn-sec" href="{{ $ir($codigo, 'tarjeta', $otro) }}">{{ $t['acabados'][$otro]['nombre'] }}</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="sec light">
        <div class="wrap">
            <div class="head"><span class="eyebrow">{{ $t['inicio']['faqEyebrow'] }}</span><h2>{{ $t['inicio']['faqH'] }}</h2></div>
            <div class="faq">
                @foreach (array_slice($t['faq'], 0, 4) as $q)
                    <details><summary>{{ $q[0] }}</summary><p>{{ $x($q[1]) }}</p></details>
                @endforeach
            </div>
            <p style="margin-top:22px"><a class="btn btn-sec" href="{{ $ir($codigo, 'preguntas') }}">{{ $t['inicio']['faqTodas'] }}</a></p>
        </div>
    </section>
@endsection
