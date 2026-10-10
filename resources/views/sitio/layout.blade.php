{{-- Plantilla del sitio de ventas (generado con php artisan sitio:generar). Variables: ver GenerarSitio::pagina() --}}
@php
    $inicioUrl = $ir($codigo, 'inicio');
    // En el inicio las anclas son locales; en las demás páginas llevan al inicio
    $ancla = fn (string $id) => ($vista === 'inicio' ? '' : $inicioUrl) . '#' . $id;
@endphp
<!doctype html>
<html lang="{{ $version['hreflang'] }}" data-version="{{ $codigo }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>@yield('titulo')</title>
    <meta name="description" content="@yield('descripcion')">
    @if (!$indexar)<meta name="robots" content="noindex, nofollow">@endif
    <link rel="canonical" href="{{ $canonica($codigo) }}">
    @foreach ($versiones as $c => $v)
        <link rel="alternate" hreflang="{{ $v['hreflang'] }}" href="{{ $canonica($c) }}">
    @endforeach
    <link rel="alternate" hreflang="x-default" href="{{ $dominio }}/">
    <meta name="theme-color" content="#0d0f10">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="DaUnToque">
    <meta property="og:locale" content="{{ str_replace('-', '_', $version['hreflang']) }}">
    <meta property="og:title" content="@yield('titulo')">
    <meta property="og:description" content="@yield('descripcion')">
    <meta property="og:url" content="{{ $canonica($codigo) }}">
    <meta property="og:image" content="{{ $dominio . $base }}/media/og-{{ $version['idioma'] }}.jpg">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,700;12..96,800&family=Figtree:wght@400;500;600;700&family=JetBrains+Mono:wght@500;700&display=swap">
    @yield('precarga')
    <link rel="stylesheet" href="{{ $base }}/sitio.css">
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='8' fill='%230d0f10'/%3E%3Cpath d='M11 10.5a7.5 7.5 0 0 1 0 11M15 8a11 11 0 0 1 0 16M19 6a14.5 14.5 0 0 1 0 20' stroke='%232fae6e' stroke-width='2.4' fill='none' stroke-linecap='round'/%3E%3C/svg%3E">
    @php
        $org = ['@context' => 'https://schema.org', '@graph' => [
            ['@type' => 'Organization', '@id' => $dominio . '/#org', 'name' => 'DaUnToque', 'url' => $dominio . '/', 'sameAs' => ['https://portal.dauntoque.com']],
            ['@type' => 'WebSite', '@id' => $dominio . '/#web', 'name' => 'DaUnToque', 'url' => $dominio . '/', 'inLanguage' => $version['hreflang'], 'publisher' => ['@id' => $dominio . '/#org']],
        ]];
    @endphp
    <script type="application/ld+json">{!! json_encode($org, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
    @yield('schema')
</head>
<body>
    {{-- Iconos del sitio: <svg><use href="#ok"/></svg> --}}
    <svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
        <symbol id="ok" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" d="M5 12.5l4.2 4.2L19 7"/></symbol>
        <symbol id="nfc" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M8 7a7 7 0 0 1 0 10"/><path d="M12 4.5a11 11 0 0 1 0 15"/><path d="M16 2.5a14 14 0 0 1 0 19"/></g></symbol>
        <symbol id="truck" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"><path d="M2 6h11v10H2zM13 9h4.5l3.5 3.5V16h-8"/><circle cx="6" cy="17.5" r="1.8"/><circle cx="17" cy="17.5" r="1.8"/></g></symbol>
        <symbol id="shield" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" d="M12 3l8 3v6c0 4.5-3.4 8-8 9-4.6-1-8-4.5-8-9V6z"/></symbol>
        <symbol id="cal" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3.5" y="5" width="17" height="15" rx="2.5"/><path d="M3.5 10h17M8 3v4M16 3v4"/></g></symbol>
        <symbol id="user" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="8" r="4"/><path d="M4 21c1.5-4 4.5-6 8-6s6.5 2 8 6"/></g></symbol>
        <symbol id="chat" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" d="M4 19.5l1.4-4A8 8 0 1 1 8.6 19z"/></symbol>
        <symbol id="phone" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" d="M5 3.5h3.5l1.8 4.5-2.3 1.5a11 11 0 0 0 6.5 6.5l1.5-2.3 4.5 1.8V19a2 2 0 0 1-2 2A17 17 0 0 1 3 5.5a2 2 0 0 1 2-2z"/></symbol>
        <symbol id="cash" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="2"><rect x="2.5" y="6" width="19" height="12" rx="2.5"/><circle cx="12" cy="12" r="2.6"/></g></symbol>
        <symbol id="chart" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/></g></symbol>
        <symbol id="spark" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" d="M12 3l2.2 6.8L21 12l-6.8 2.2L12 21l-2.2-6.8L3 12l6.8-2.2z"/></symbol>
        <symbol id="share" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 15V3M7.5 7.5L12 3l4.5 4.5M5 12v7.5h14V12"/></g></symbol>
        <symbol id="wa" viewBox="0 0 24 24"><path fill="currentColor" d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2zm5.3 14.1c-.2.6-1.3 1.2-1.8 1.3-.5.1-1 .1-3.3-.8-2.8-1.1-4.5-3.9-4.6-4.1-.1-.2-1.1-1.5-1.1-2.8 0-1.3.7-2 1-2.3.3-.3.6-.3.8-.3h.6c.2 0 .4 0 .6.5l.9 2.1c.1.2.1.4 0 .6l-.4.6-.4.5c-.1.1-.3.3-.1.6.2.3.8 1.3 1.7 2.1 1.2 1 2.1 1.4 2.4 1.5.3.2.5.1.6-.1l.9-1c.2-.3.4-.2.7-.1l2 1c.3.1.5.2.5.3.1.2.1.7-.1 1.3z"/></symbol>
    </svg>

    <a class="sr saltar" href="#contenido">{{ $t['nav']['saltar'] }}</a>

    <header class="top">
        <div class="wrap">
            <a class="logo" href="{{ $inicioUrl }}" aria-label="DaUnToque"><i aria-hidden="true"></i><span>dauntoque<em>.com</em></span></a>
            <nav class="nav" aria-label="{{ $t['nav']['inicio'] }}">
                <a href="{{ $ancla('pruebalo') }}">{{ $t['nav']['pruebalo'] }}</a>
                <a href="{{ $ancla('panel') }}">{{ $t['nav']['panel'] }}</a>
                <a href="{{ $ir($codigo, 'tarjeta', 'matte-black') }}" @if ($vista === 'tarjeta') aria-current="page" @endif>{{ $t['nav']['tarjetas'] }}</a>
                <a href="{{ $ancla('precio') }}">{{ $t['nav']['precio'] }}</a>
                <a href="{{ $ir($codigo, 'preguntas') }}" @if ($vista === 'preguntas') aria-current="page" @endif>{{ $t['nav']['preguntas'] }}</a>
            </nav>
            <div class="top-r">
                <details class="selector">
                    <summary aria-label="{{ $t['nav']['pais'] }}">@include('sitio._ico', ['n' => 'globo']) {{ strtoupper($version['mercado']) }} · {{ strtoupper($version['idioma']) }}</summary>
                    <div class="selector-menu">
                        @foreach ($versiones as $c => $v)
                            <a href="{{ $ir($c) }}" hreflang="{{ $v['hreflang'] }}" data-version="{{ $c }}" @if ($c === $codigo) aria-current="true" @endif>
                                <span>{{ $t['versiones'][$c] }}</span><span class="mut">{{ $c === 'es-co' ? 'COP' : 'USD' }}</span>
                            </a>
                        @endforeach
                    </div>
                </details>
                <a class="btn btn-pri" href="{{ $comprar() }}" @if($compraExterna) target="_blank" rel="noopener" @endif>{{ $t['nav']['comprar'] }}</a>
            </div>
        </div>
    </header>

    <main id="contenido">
        @yield('contenido')
    </main>

    <footer class="pie">
        <div class="wrap">
            <div>
                <a class="logo" href="{{ $inicioUrl }}"><i aria-hidden="true"></i><span>dauntoque<em>.com</em></span></a>
                <p style="margin-top:10px">{{ $t['pie']['lema'] }}</p>
                <p style="margin-top:14px">&copy; {{ date('Y') }} DaUnToque. {{ $t['pie']['derechos'] }}</p>
            </div>
            <nav aria-label="DaUnToque">
                <a href="{{ $inicioUrl }}">{{ $t['nav']['inicio'] }}</a>
                <a href="{{ $ir($codigo, 'tarjeta', 'matte-black') }}">{{ $t['acabados']['matte-black']['nombre'] }}</a>
                <a href="{{ $ir($codigo, 'tarjeta', 'crystal') }}">{{ $t['acabados']['crystal']['nombre'] }}</a>
                <a href="{{ $ir($codigo, 'preguntas') }}">{{ $t['nav']['preguntas'] }}</a>
            </nav>
            <nav aria-label="{{ $t['nav']['pais'] }}">
                @foreach ($versiones as $c => $v)
                    <a href="{{ $ir($c) }}" hreflang="{{ $v['hreflang'] }}" data-version="{{ $c }}">{{ $t['versiones'][$c] }}</a>
                @endforeach
                <a href="https://portal.dauntoque.com/login">{{ $t['pie']['portal'] }}</a>
            </nav>
        </div>
    </footer>

    {{-- Barra fija de compra en el celular: se esconde cuando hay un botón con data-compra en pantalla --}}
    <div class="barra-movil">
        <div><b>{{ $mercado['precioTexto'] }}</b><small>{{ $x($t['inicio']['barraNota'], [':moneda' => $mercado['moneda']]) }}</small></div>
        <a class="btn btn-pri" href="{{ $comprar() }}" @if($compraExterna) target="_blank" rel="noopener" @endif>{{ $t['nav']['comprar'] }}</a>
    </div>
    <a class="wa" href="{{ $wa($t['waDuda']) }}" target="_blank" rel="noopener" aria-label="{{ $t['escribenos'] }}"><svg width="30" height="30" aria-hidden="true"><use href="#wa"/></svg></a>

    <script src="{{ $base }}/sitio.js" defer></script>
</body>
</html>
