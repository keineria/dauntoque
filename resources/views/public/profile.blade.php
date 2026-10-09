@php
    $descripcion = \Illuminate\Support\Str::limit(trim(($datos['profesion'] ? $datos['profesion'] . '. ' : '') . ($datos['bio'] ?? '')), 155) ?: 'Tarjeta digital de ' . $datos['nombre'];
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $datos['nombre'] }} | DaUnToque</title>
    <meta name="description" content="{{ $descripcion }}">
    <meta name="theme-color" content="{{ $datos['color'] }}">
    <link rel="canonical" href="{{ $datos['enlace'] }}">
    {{-- Vista previa al compartir el enlace (WhatsApp, redes, mensajes) --}}
    <meta property="og:type" content="profile">
    <meta property="og:title" content="{{ $datos['nombre'] }}{{ $datos['profesion'] ? ' · ' . $datos['profesion'] : '' }}">
    <meta property="og:description" content="{{ $descripcion }}">
    <meta property="og:url" content="{{ $datos['enlace'] }}">
    @if($datos['foto'])<meta property="og:image" content="{{ url($datos['foto']) }}">@endif
    <meta name="twitter:card" content="summary">

    @vite('resources/css/panel.css')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
    <style>html, body { margin:0; min-height:100%; } [x-cloak] { display:none !important; }</style>
</head>
<body x-data="{ p: @js($datos) }">
    @include('partials.perfil-publico')

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
