@php
    $descripcion = \App\Services\Seo\SeoPerfil::descripcion($datos);
    $tituloSeo = $datos['nombre'] . ($datos['profesion'] ? ' · ' . $datos['profesion'] : '');
    $fotoUrl = $datos['foto'] ? url($datos['foto']) : null;
    $soloDigitos = preg_replace('/\D/', '', (string) $datos['telefono']);
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ \Illuminate\Support\Str::limit($tituloSeo, 58) }} | DaUnToque</title>
    <meta name="description" content="{{ $descripcion }}">
    <meta name="theme-color" content="{{ $datos['color'] }}">
    <link rel="canonical" href="{{ $datos['enlace'] }}">
    {{-- Vista previa al compartir el enlace (WhatsApp, redes, mensajes) --}}
    <meta name="robots" content="index, follow, max-image-preview:large">
    <meta property="og:type" content="profile">
    <meta property="og:site_name" content="DaUnToque">
    <meta property="og:locale" content="es_CO">
    <meta property="og:title" content="{{ $tituloSeo }}">
    <meta property="og:description" content="{{ $descripcion }}">
    <meta property="og:url" content="{{ $datos['enlace'] }}">
    @if($fotoUrl)
        <meta property="og:image" content="{{ $fotoUrl }}">
        <meta property="og:image:alt" content="{{ $datos['nombre'] }}">
        {{-- La foto es lo primero que se ve: se pide de una vez --}}
        <link rel="preload" as="image" href="{{ $datos['foto'] }}" fetchpriority="high">
    @endif
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ $tituloSeo }}">
    <meta name="twitter:description" content="{{ $descripcion }}">
    @if($fotoUrl)<meta name="twitter:image" content="{{ $fotoUrl }}">@endif
    {{-- Datos estructurados: Google entiende quién es, a qué se dedica y cómo contactarlo --}}
    <script type="application/ld+json">{!! json_encode(\App\Services\Seo\SeoPerfil::schema($datos), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>

    @if($fuente = \App\Support\Tipografias::urlGoogle(array_filter([$datos['tipografia']])))
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link rel="stylesheet" href="{{ $fuente }}">
    @endif
    @vite('resources/css/panel.css')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
    <style>html, body { margin:0; min-height:100%; } [x-cloak] { display:none !important; }</style>
    <noscript><style>
        body > :not(noscript) { display:none !important; }
        .pp-sinjs { max-width:480px; margin:0 auto; padding:32px 20px; font:16px/1.6 system-ui, sans-serif; text-align:center; color:#111; }
        .pp-sinjs img { border-radius:50%; object-fit:cover; }
        .pp-sinjs h1 { margin:12px 0 4px; font-size:26px; }
        .pp-sinjs ul { list-style:none; padding:0; margin:20px 0 0; display:grid; gap:10px; }
        .pp-sinjs a { display:block; padding:13px 16px; border-radius:14px; background:{{ $datos['color'] }}; color:#fff; font-weight:700; text-decoration:none; }
    </style></noscript>
</head>
<body x-data="{ p: @js($datos) }">
    @include('partials.perfil-publico')

    {{-- Sin JavaScript (algunos buscadores, vistas previas y lectores): el contacto completo sigue ahí --}}
    <noscript>
        <main class="pp-sinjs">
            @if($datos['foto'])<img src="{{ $datos['foto'] }}" alt="{{ $datos['nombre'] }}" width="112" height="112">@endif
            <h1>{{ $datos['nombre'] }}</h1>
            @if($datos['profesion'])<p><strong>{{ $datos['profesion'] }}</strong></p>@endif
            @if($datos['bio'])<p>{{ $datos['bio'] }}</p>@endif
            <ul>
                <li><a href="{{ $datos['vcf'] }}">Guardar contacto</a></li>
                @if($datos['telefono'])
                    <li><a href="tel:{{ $datos['telefono'] }}">Llamar: {{ $datos['telefono'] }}</a></li>
                    @if($soloDigitos)<li><a href="https://wa.me/{{ $soloDigitos }}" rel="noopener">WhatsApp</a></li>@endif
                @endif
                @if($datos['correo'])<li><a href="mailto:{{ $datos['correo'] }}">{{ $datos['correo'] }}</a></li>@endif
                @if($datos['direccion'])<li>{{ $datos['direccion'] }}</li>@endif
                @if($datos['agenda'])<li><a href="{{ $datos['agenda'] }}">Agendar cita</a></li>@endif
                @foreach(\App\Services\Seo\SeoPerfil::redes($datos['redes']) as $red)
                    <li><a href="{{ $red['url'] }}" rel="noopener">{{ $red['nombre'] }}</a></li>
                @endforeach
            </ul>
        </main>
    </noscript>

    <script>
        // Registra visitas y clics del perfil público sin interrumpir al visitante
        function trackClick(accion) {
            fetch('/api/analiticas/registrar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ perfil_id: {{ (int) $perfil->id }}, accion: accion }),
            }).catch(() => {});
        }
        window.addEventListener('load', () => trackClick('vista_perfil'));
    </script>
</body>
</html>
