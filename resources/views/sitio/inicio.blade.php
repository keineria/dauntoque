@extends('sitio.layout')
@php
    $i = $t['inicio'];
    $casos = [
        ['foto' => 'caso-kathe', 'nombre' => 'Kathe Glam & Nails', 'url' => 'https://kathe.dauntoque.com',
         'es' => 'Desde que uso la tarjeta, la percepción de mi salón cambió por completo. Las clientas ven mis fotos de uñas al instante y guardan mi contacto sin excusas. Mi agenda se llena sola.',
         'en' => 'Since I started using the card, the way people see my salon changed completely. Clients see my nail photos instantly and save my contact, no excuses. My calendar fills itself.'],
        ['foto' => 'caso-brey', 'nombre' => 'Breynner Logistics & Transportation', 'url' => 'https://brey.dauntoque.com',
         'es' => 'En el mundo del transporte no hay tiempo que perder. Toco el teléfono del cliente, se abre mi perfil, cobro el flete y arrancamos. Eficiencia absoluta.',
         'en' => 'In transportation there is no time to waste. I tap the client\'s phone, my profile opens, I get paid for the job and we roll. Pure efficiency.'],
        ['foto' => 'caso-carlos', 'nombre' => 'Carlos Constructor', 'url' => 'https://carlos.dauntoque.com',
         'es' => 'Llegar a cotizar una remodelación, sacar la Matte Black y que el cliente vea mis obras pasadas en un segundo justifica mis precios. Me da autoridad total.',
         'en' => 'Showing up to quote a remodel, pulling out the Matte Black and letting the client see my past work in a second justifies my prices. Total authority.'],
    ];
    $perfiles = ['Kathe' => 'https://kathe.dauntoque.com', 'Breynner' => 'https://brey.dauntoque.com', 'Camilo' => 'https://camilo.dauntoque.com', 'Angela' => 'https://angela.dauntoque.com'];
    $iconos = ['contacto', 'chat', 'pago', 'galeria', 'grafica', 'nfc'];
    $producto = [
        '@context' => 'https://schema.org', '@type' => 'Product', 'name' => $i['producto'], 'brand' => ['@type' => 'Brand', 'name' => 'DaUnToque'],
        'description' => $x($i['descripcion']), 'image' => [$dominio . $base . '/media/og-' . $version['idioma'] . '.jpg'],
        'offers' => ['@type' => 'Offer', 'price' => $mercado['precio'], 'priceCurrency' => $mercado['moneda'], 'availability' => 'https://schema.org/InStock', 'url' => $canonica($codigo)],
    ];
    $como = ['@context' => 'https://schema.org', '@type' => 'HowTo', 'name' => $i['comoH'],
        'step' => array_map(fn ($p, $n) => ['@type' => 'HowToStep', 'position' => $n + 1, 'name' => $p[0], 'text' => $x($p[1])], $i['como'], array_keys($i['como']))];
@endphp

@section('titulo', $i['titulo'])
@section('descripcion', $x($i['descripcion']))
@section('precarga')
    <link rel="preload" as="image" href="{{ $base }}/media/heroe-sm.webp" imagesrcset="{{ $base }}/media/heroe-sm.webp 640w, {{ $base }}/media/heroe.webp 1280w" imagesizes="(min-width: 920px) 540px, 100vw" fetchpriority="high">
@endsection
@section('schema')
    <script type="application/ld+json">{!! json_encode($producto, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
    <script type="application/ld+json">{!! json_encode($como, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
@endsection

@section('contenido')
    {{-- 1. Héroe: el dolor, el precio del país y el botón --}}
    <section class="heroe">
        <div class="envoltura">
            <div>
                @if ($mercado['ahorroTexto'])<span class="cinta">{{ $x($i['oferta']) }}</span>@endif
                <h1>{{ $i['h1a'] }} <span>{{ $i['h1b'] }}</span></h1>
                <p class="sub">{{ $i['sub'] }}</p>
                <div class="precio" aria-label="{{ $mercado['precioTexto'] }} {{ $mercado['moneda'] }}">
                    <b>{{ $mercado['precioTexto'] }}</b><small>{{ $mercado['moneda'] }}</small>
                    @if ($mercado['antesTexto'])<s>{{ $mercado['antesTexto'] }} {{ $mercado['moneda'] }}</s>@endif
                </div>
                <div class="acciones" data-compra>
                    <a class="btn btn-pri" href="{{ $comprar() }}" @if($compraExterna) target="_blank" rel="noopener" @endif>{{ $i['cta'] }} @include('sitio._ico', ['n' => 'flecha'])</a>
                    <a class="btn btn-sec" href="#toque">{{ $i['toqueH'] }}</a>
                </div>
                <p class="nota">{{ $i['unico'] }}</p>
                <div class="compat">
                    <span>{{ $i['compatible'] }}</span>
                    @foreach ($i['compatibles'] as $c)<span class="chip">{{ $c }}</span>@endforeach
                    @foreach ($medios as $m)<span class="chip"><i style="background:{{ collect(config('pagos.medios'))->firstWhere('nombre', $m)['acento'] ?? '#999' }}"></i>{{ $m }}</span>@endforeach
                </div>
            </div>
            <div class="marco-video" data-video="{{ $base }}/media/heroe">
                <img class="portada" src="{{ $base }}/media/heroe-sm.webp" srcset="{{ $base }}/media/heroe-sm.webp 640w, {{ $base }}/media/heroe.webp 1280w" sizes="(min-width: 920px) 540px, 100vw" width="1280" height="720" alt="{{ $i['videoAlt'] }}" fetchpriority="high">
            </div>
        </div>
    </section>

    {{-- 2. Demostración del toque y perfiles reales --}}
    <section class="seccion" id="toque">
        <div class="envoltura toque">
            <div class="marco-video vertical" data-video="{{ $base }}/media/constructor">
                <img class="portada" src="{{ $base }}/media/constructor-sm.webp" width="360" height="640" alt="" loading="lazy">
            </div>
            <div>
                <div class="cabeza" style="margin-bottom:0">
                    <h2>{{ $i['toqueH'] }}</h2>
                    <p>{{ $i['toqueP'] }}</p>
                </div>
                <ol class="pasos">@foreach ($i['pasos'] as $p)<li>{{ $p }}</li>@endforeach</ol>
            </div>
        </div>
    </section>

    {{-- 2b. Gancho: segundo perfil de regalo (ejecutivo y personal) --}}
    <section class="seccion negra" id="dos-perfiles">
        <div class="envoltura dos-perfiles" data-dos>
            <div class="dp-texto">
                <span class="cinta">{{ $i['dosOferta'] }}</span>
                <div class="cabeza" style="margin:14px 0 0">
                    <h2>{{ $i['dosH'] }}</h2>
                    <p>{{ $i['dosP'] }}</p>
                </div>
                <div class="dp-modos" role="tablist">
                    @foreach ($i['dosModos'] as $n => $modo)
                        <button type="button" role="tab" data-modo="{{ $n }}" aria-selected="{{ $n === 0 ? 'true' : 'false' }}">{{ $modo }}</button>
                    @endforeach
                </div>
            </div>
            <div class="dp-telefono" aria-live="polite">
                @foreach ($i['dosPerfiles'] as $n => $perfil)
                    <div class="dp-perfil {{ $n === 0 ? 'ejecutivo' : 'personal' }}" data-perfil-modo="{{ $n }}" @if($n !== 0) hidden @endif>
                        <div class="dp-avatar">{{ \Illuminate\Support\Str::substr($perfil['nombre'], 0, 1) }}</div>
                        <b>{{ $perfil['nombre'] }}</b>
                        <span>{{ $perfil['rol'] }}</span>
                        <ul>@foreach ($perfil['chips'] as $c)<li>{{ $c }}</li>@endforeach</ul>
                    </div>
                @endforeach
            </div>
            <ul class="dp-puntos">@foreach ($i['dosPuntos'] as $punto)<li>{{ $punto }}</li>@endforeach</ul>
        </div>
    </section>

    <section class="seccion gris">
        <div class="envoltura visor" data-visor>
            <div>
                <div class="cabeza">
                    <h2>{{ $i['visorH'] }}</h2>
                    <p>{{ $i['visorP'] }}</p>
                </div>
                <div class="visor-tabs" role="tablist">
                    @foreach ($perfiles as $nombre => $url)
                        <button type="button" role="tab" data-perfil="{{ $url }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}">{{ $nombre }}</button>
                    @endforeach
                </div>
                <a class="visor-abrir" href="{{ reset($perfiles) }}" target="_blank" rel="noopener">{{ $i['visorAbrir'] }} →</a>
            </div>
            <div class="telefono">
                <span class="vacio">dauntoque</span>
                <iframe title="{{ $i['visorH'] }}" loading="lazy" style="position:relative"></iframe>
            </div>
        </div>
    </section>

    {{-- 3. Beneficios --}}
    <section class="seccion">
        <div class="envoltura">
            <div class="cabeza"><h2>{{ $i['beneficiosH'] }}</h2></div>
            <div class="rejilla dos tres-lg">
                @foreach ($i['beneficios'] as $n => $b)
                    <article class="beneficio">@include('sitio._ico', ['n' => $iconos[$n]])<h3>{{ $b[0] }}</h3><p>{{ $x($b[1]) }}</p></article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- 4. Acabados --}}
    <section class="seccion gris">
        <div class="envoltura">
            <div class="cabeza"><h2>{{ $i['acabadosH'] }}</h2><p>{{ $i['acabadosP'] }}</p></div>
            <div class="rejilla dos">
                @foreach (['matte-black' => 'matte', 'crystal' => 'crystal'] as $a => $clase)
                    <a class="acabado {{ $clase }}" href="{{ $ir($codigo, 'tarjeta', $a) }}">
                        <span class="muestra" aria-hidden="true"></span>
                        <h3>{{ $t['acabados'][$a]['nombre'] }}</h3>
                        <p>{{ $t['acabados'][$a]['corto'] }}</p>
                        <span class="ir">{{ $t['nav']['tarjetas'] }} @include('sitio._ico', ['n' => 'flecha'])</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- 5. Casos reales --}}
    <section class="seccion">
        <div class="envoltura">
            <div class="cabeza"><h2>{{ $i['casosH'] }}</h2><p>{{ $i['casosP'] }}</p></div>
            <div class="rejilla tres">
                @foreach ($casos as $c)
                    <figure class="caso" style="margin:0">
                        <blockquote>“{{ $c[$version['idioma']] }}”</blockquote>
                        <figcaption class="quien">
                            <img src="{{ $base }}/media/{{ $c['foto'] }}-sm.webp" width="104" height="104" alt="{{ $c['nombre'] }}" loading="lazy">
                            <span><b>{{ $c['nombre'] }}</b><a href="{{ $c['url'] }}" target="_blank" rel="noopener">{{ $i['verPerfil'] }} →</a></span>
                        </figcaption>
                    </figure>
                @endforeach
            </div>
        </div>
    </section>

    {{-- 6. Comparador y calculadora --}}
    <section class="seccion gris">
        <div class="envoltura">
            <div class="cabeza"><h2>{{ $i['comparaH'] }}</h2></div>
            <div class="tabla-scroll">
                <table class="tabla">
                    <thead><tr>@foreach ($i['comparaCols'] as $col)<th scope="col">{{ $col }}</th>@endforeach</tr></thead>
                    <tbody>
                        @foreach ($i['comparaFilas'] as $fila)
                            <tr><th scope="row">{{ $fila[0] }}</th><td>{{ $fila[1] }}</td><td>{{ $fila[2] }}</td><td>{{ $fila[3] }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="calc" data-calc data-locale="{{ $version['hreflang'] }}" data-moneda="{{ $mercado['moneda'] }}">
                <label>{{ $i['calcTarjetas'] }}<input type="number" name="tarjetas" value="100" min="0" step="10" inputmode="numeric"></label>
                <label>{{ $i['calcCosto'] }} ({{ $mercado['moneda'] }})<input type="number" name="costo" value="{{ $mercado['moneda'] === 'COP' ? 40000 : 25 }}" min="0" inputmode="numeric"></label>
                <div class="resultado">
                    <div><small>{{ $i['calcAnio'] }}</small><b data-anio>—</b></div>
                    <div><small>{{ $i['calcVs'] }}</small><b>{{ $mercado['precioTexto'] }}</b></div>
                </div>
            </div>
        </div>
    </section>

    {{-- 7. Cómo funciona --}}
    <section class="seccion negra">
        <div class="envoltura">
            <div class="cabeza"><h2>{{ $i['comoH'] }}</h2></div>
            <ol class="como">@foreach ($i['como'] as $p)<li><h3>{{ $p[0] }}</h3><p>{{ $x($p[1]) }}</p></li>@endforeach</ol>
        </div>
    </section>

    {{-- 8. Precio y qué incluye --}}
    <section class="seccion" id="precio">
        <div class="envoltura">
            <div class="cabeza centro"><h2>{{ $i['precioH'] }}</h2><p>{{ $i['precioP'] }}</p></div>
            <div class="ficha">
                @if ($mercado['ahorroTexto'])<span class="cinta">{{ $x($i['oferta']) }}</span>@endif
                <h3>{{ $i['producto'] }}</h3>
                <p class="muted">{{ $i['productoSub'] }}</p>
                <div class="precio"><b>{{ $mercado['precioTexto'] }}</b><small>{{ $mercado['moneda'] }}</small>@if ($mercado['antesTexto'])<s>{{ $mercado['antesTexto'] }} {{ $mercado['moneda'] }}</s>@endif</div>
                <ul class="incluye">@foreach ($i['incluye'] as $inc)<li>{{ $x($inc) }}</li>@endforeach</ul>
                <a class="btn btn-pri btn-ancho" data-compra href="{{ $comprar() }}" @if($compraExterna) target="_blank" rel="noopener" @endif>{{ $i['ctaPrecio'] }}</a>
                <p class="nota" style="text-align:center">{{ $i['unico'] }}</p>
                <div class="pagos-pais">@foreach ($medios as $m)<span class="chip">{{ $m }}</span>@endforeach</div>
            </div>
        </div>
    </section>

    {{-- 9. Preguntas frecuentes (las primeras) --}}
    <section class="seccion gris">
        <div class="envoltura">
            <div class="cabeza centro"><h2>{{ $i['faqH'] }}</h2></div>
            <div class="faq">
                @foreach (array_slice($t['faq'], 0, 5) as $q)
                    <details><summary>{{ $q[0] }}</summary><p>{{ $x($q[1]) }}</p></details>
                @endforeach
            </div>
            <p style="text-align:center;margin-top:20px"><a class="btn btn-sec" href="{{ $ir($codigo, 'preguntas') }}">{{ $i['faqTodas'] }}</a></p>
        </div>
    </section>

    {{-- 10. Cierre --}}
    <section class="seccion cierre">
        <div class="envoltura">
            <h2>{{ $i['cierreH'] }}</h2>
            <a class="btn btn-pri" data-compra href="{{ $comprar() }}" @if($compraExterna) target="_blank" rel="noopener" @endif>{{ $i['cta'] }} · {{ $mercado['precioTexto'] }} {{ $mercado['moneda'] }}</a>
        </div>
    </section>
@endsection
