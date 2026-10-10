{{-- Plantilla del sitio de ventas (generado con php artisan sitio:generar). Variables: ver GenerarSitio::pagina() --}}
<!doctype html>
<html lang="{{ $version['hreflang'] }}" data-version="{{ $codigo }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titulo')</title>
    <meta name="description" content="@yield('descripcion')">
    @if (!$indexar)<meta name="robots" content="noindex, nofollow">@endif
    <link rel="canonical" href="{{ $canonica($codigo) }}">
    @foreach ($versiones as $c => $v)
        <link rel="alternate" hreflang="{{ $v['hreflang'] }}" href="{{ $canonica($c) }}">
    @endforeach
    <link rel="alternate" hreflang="x-default" href="{{ $dominio }}/">
    <meta name="theme-color" content="#fdfdfc">
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
    @yield('precarga')
    <link rel="stylesheet" href="{{ $base }}/sitio.css">
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='8' fill='%23141414'/%3E%3Cpath d='M11 10.5a7.5 7.5 0 0 1 0 11M15 8a11 11 0 0 1 0 16M19 6a14.5 14.5 0 0 1 0 20' stroke='%239eb8a7' stroke-width='2.4' fill='none' stroke-linecap='round'/%3E%3C/svg%3E">
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
    <header class="nav">
        <div class="envoltura">
            <a class="logo" href="{{ $ir($codigo, 'inicio') }}" aria-label="DaUnToque">dauntoque<span>.com</span></a>
            <nav class="nav-links" aria-label="{{ $t['nav']['inicio'] }}">
                <a href="{{ $ir($codigo, 'inicio') }}" @if ($vista === 'inicio') aria-current="page" @endif>{{ $t['nav']['inicio'] }}</a>
                <a href="{{ $ir($codigo, 'tarjeta', 'matte-black') }}" @if ($vista === 'tarjeta') aria-current="page" @endif>{{ $t['nav']['tarjetas'] }}</a>
                <a href="{{ $ir($codigo, 'preguntas') }}" @if ($vista === 'preguntas') aria-current="page" @endif>{{ $t['nav']['preguntas'] }}</a>
            </nav>
            <div class="nav-der">
                <details class="selector">
                    <summary aria-label="{{ $t['nav']['pais'] }}">@include('sitio._ico', ['n' => 'globo']) {{ strtoupper($version['mercado']) }} · {{ strtoupper($version['idioma']) }}</summary>
                    <div class="selector-menu">
                        @foreach ($versiones as $c => $v)
                            <a href="{{ $ir($c) }}" hreflang="{{ $v['hreflang'] }}" data-version="{{ $c }}" @if ($c === $codigo) aria-current="true" @endif>
                                <span>{{ $t['versiones'][$c] }}</span><span class="muted">{{ $c === 'es-co' ? 'COP' : 'USD' }}</span>
                            </a>
                        @endforeach
                    </div>
                </details>
                <a class="btn btn-pri" href="{{ $wa($t['wa']) }}" target="_blank" rel="noopener">{{ $t['nav']['comprar'] }}</a>
            </div>
        </div>
    </header>

    <main>
        @yield('contenido')
    </main>

    <footer class="pie">
        <div class="envoltura">
            <div>
                <a class="logo" href="{{ $ir($codigo, 'inicio') }}">dauntoque<span>.com</span></a>
                <p style="margin-top:8px">{{ $t['pie']['lema'] }}</p>
                <p style="margin-top:14px">&copy; {{ date('Y') }} DaUnToque. {{ $t['pie']['derechos'] }}</p>
            </div>
            <nav aria-label="DaUnToque">
                <a href="{{ $ir($codigo, 'inicio') }}">{{ $t['nav']['inicio'] }}</a>
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

    <div class="compra-fija"><a class="btn btn-pri" href="{{ $wa($t['wa']) }}" target="_blank" rel="noopener">{{ $t['nav']['comprar'] }} · {{ $mercado['precioTexto'] }}</a></div>
    <a class="burbuja-wa" href="{{ $wa($t['waDuda']) }}" target="_blank" rel="noopener" aria-label="{{ $t['escribenos'] }}">@include('sitio._ico', ['n' => 'wa'])</a>

    <script src="{{ $base }}/sitio.js" defer></script>
</body>
</html>
