{{-- Raíz del sitio: lleva a la versión del visitante (elección guardada, zona horaria o idioma) y deja los 3 enlaces visibles --}}
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DaUnToque · Tarjeta de presentación NFC</title>
    <meta name="description" content="Tarjeta de presentación NFC con perfil web. Colombia (COP) y Estados Unidos (USD). NFC business card with web profile.">
    <meta name="robots" content="{{ $indexar ? 'noindex, follow' : 'noindex, nofollow' }}">
    @foreach ($versiones as $c => $v)
        <link rel="alternate" hreflang="{{ $v['hreflang'] }}" href="{{ $dominio }}/{{ $c }}/">
    @endforeach
    <link rel="alternate" hreflang="x-default" href="{{ $dominio }}/">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,700;12..96,800&family=Figtree:wght@400;600;700&display=swap">
    <link rel="stylesheet" href="{{ $base }}/sitio.css">
    <script>
        (function () {
            var v = null;
            try { v = localStorage.getItem('dt-version'); } catch (e) {}
            if (!/^(es-co|es-us|en-us)$/.test(v || '')) {
                var zona = '', idioma = (navigator.language || '').toLowerCase();
                try { zona = Intl.DateTimeFormat().resolvedOptions().timeZone || ''; } catch (e) {}
                if (zona === 'America/Bogota' || /-co$/.test(idioma)) v = 'es-co';
                else v = /^es/.test(idioma) ? 'es-us' : 'en-us';
            }
            location.replace('{{ $base }}/' + v + '/' + location.search);
        })();
    </script>
</head>
<body>
    <main class="raiz">
        <div>
            <a class="logo" href="{{ $base }}/"><i aria-hidden="true"></i><span>dauntoque<em>.com</em></span></a>
            <h1>Elige tu país · Choose your country</h1>
            <nav>
                <a class="btn btn-pri" href="{{ $base }}/es-co/" data-version="es-co">Colombia · Español (COP)</a>
                <a class="btn btn-sec" href="{{ $base }}/es-us/" data-version="es-us">Estados Unidos · Español (USD)</a>
                <a class="btn btn-sec" href="{{ $base }}/en-us/" data-version="en-us">United States · English (USD)</a>
            </nav>
        </div>
    </main>
</body>
</html>
