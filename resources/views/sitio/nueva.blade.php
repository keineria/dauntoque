{{--
    Portada nueva del sitio de ventas (versión de prueba, solo es-co): estatus, solución e innovación.
    Se genera con sitio:generar en /es-co/nueva/ (noindex, canónica a /es-co/) y, con --portada=nueva,
    reemplaza la portada de /es-co/. Estilos y scripts propios (nueva.css, nueva.js): no toca el sitio actual.
    Los avisos de compras salen de /compras-recientes.json (pedidos pagados reales; ver ComprasRecientes).
--}}
@php
    $i = $t['inicio'];
    $json = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG;
    $yo = $base . '/' . $codigo . '/' . ($ruta === '' ? '' : $ruta . '/');
    $url = $prueba ? $dominio . '/' . $codigo . '/' : $canonica($codigo);
    $envio = $t['envio'][$mercado['code']] ?? reset($t['envio']);
    $ext = $compraExterna ? 'target="_blank" rel="noopener"' : '';
    $precio = $mercado['precioTexto'];
    $moneda = $mercado['moneda'];
    $medio1 = $medios[0] ?? 'Nequi';
    $medio2 = $medios[1] ?? 'Llave Bre-B';
    $titulo = 'Tarjeta de presentación NFC con IA en Colombia | DaUnToque';
    $descripcion = "Tarjeta de presentación NFC con perfil web, agenda de citas y analíticas. Un toque y te guardan. Pago único de $precio $moneda, sin mensualidades. Envío gratis.";
    $imagen = $dominio . $base . '/media/og-es.jpg';

    // Módulos de la plataforma: pestaña => [icono, nombre, estado, titular, texto, puntos]
    $modulos = [
        'perfil' => ['user', 'Perfil inteligente', 'Incluido', 'Tu presentación completa, a un toque', 'Tu cliente acerca su celular y ve quién eres, qué haces y cómo contactarte. Te guarda en su agenda con foto, cargo y redes, sin instalar nada.', ['Guardar contacto con un toque', 'WhatsApp, llamada, redes y ubicación', 'Galería con tus trabajos']],
        'agenda' => ['cal', 'Agenda de citas', 'Disponible', 'Te reservan sin escribirte', 'Tus clientes eligen el servicio, el día y la hora desde tu perfil. Tú recibes la cita y ellos, la confirmación y el recordatorio por correo.', ['Horarios y servicios a tu medida', 'Confirmación y recordatorios automáticos', 'El cliente puede mover o cancelar su cita']],
        'calificame' => ['star', 'Califícame', 'Disponible', 'Tu reputación trabaja por ti', 'Después de atenderlo, tu cliente te califica de 1 a 5 estrellas. Las buenas se muestran en tu perfil y lo invitan a dejar su reseña en Google; las demás te llegan en privado.', ['Sello de calificación en tu perfil', 'Invitación a reseñar en Google', 'Los comentarios malos llegan solo a ti']],
        'musica' => ['music', 'Música', 'Disponible', 'Que tu perfil suene a ti', 'Para DJ, artistas y marcas con estilo: pon tu canción destacada y tu lista de Spotify dentro del perfil, con el reproductor oficial.', ['Canción destacada', 'Lista, álbum, artista o pódcast', 'Reproductor oficial de Spotify']],
        'cobros' => ['cash', 'Cobros', 'Incluido', 'Te pagan ahí mismo', "Tu perfil muestra tus medios de pago. Tu cliente toca, el dato se copia y te paga en el momento con $medio1 o $medio2.", ['Botones de pago en tu perfil', 'El dato se copia con un toque', 'Sin comisiones de DaUnToque']],
        'dos' => ['swap', 'Dos perfiles', 'Regalo de lanzamiento', 'Una tarjeta, dos versiones de ti', 'De lunes a viernes, el profesional que cierra negocios. El fin de semana, lo que te apasiona: motos, escalada, gimnasio o lo que te mueva. Cambias el perfil activo desde tu panel en segundos.', ['Perfil ejecutivo y perfil personal', 'Cambias el activo cuando quieras', 'Incluido por tiempo limitado']],
    ];
    // Apps y funciones: [icono, nombre, estado (ok = ya existe, pronto = próximamente), texto]
    $apps = [
        ['cal', 'Agenda de citas', 'ok', 'Reservas en línea con confirmación y recordatorios.'],
        ['star', 'Califícame', 'ok', 'Calificaciones de clientes y reseñas en Google.'],
        ['music', 'Música', 'ok', 'Tu canción y tu lista de Spotify en el perfil.'],
        ['chart', 'Analíticas', 'ok', 'Visitas, contactos guardados y clics, en tu panel.'],
        ['palette', 'Temas y diseños', 'ok', 'Diseños, colores y tipografías a tu estilo.'],
        ['spark', 'Presentación con IA', 'ok', 'La IA redacta tu presentación en segundos.'],
        ['brain', 'Analíticas con IA', 'pronto', 'Tu resumen semanal y qué hacer hoy, en palabras simples.'],
        ['chat', 'Asistente con IA', 'pronto', 'Responde las preguntas de tus clientes por ti.'],
        ['bag', 'Catálogo de productos', 'pronto', 'Servicios y productos con foto, precio y pedido por WhatsApp.'],
    ];
    $casos = [
        ['caso-kathe', 'Kathe Glam & Nails', 'https://kathe.dauntoque.com'],
        ['caso-brey', 'Breynner Logistics', 'https://brey.dauntoque.com'],
        ['caso-carlos', 'Carlos Mendoza', 'https://carlos.dauntoque.com'],
        ['caso-camilo', 'Palacio Barbershop', 'https://camilo.dauntoque.com'],
        ['caso-yasser', 'Yasser Alvarado', 'https://yass.dauntoque.com'],
    ];
    $oficios = ['Asesores comerciales', 'Inmobiliarios', 'Abogados', 'Médicos y odontólogos', 'Arquitectos', 'Barberías y salones', 'Entrenadores', 'DJ y artistas', 'Contratistas', 'Transportadores', 'Emprendedores', 'Consultores'];
    // Preguntas: primero las que buscan en Google, luego las del sitio (textos.php)
    $faq = array_merge([
        ['¿Qué es una tarjeta de presentación NFC?', 'Es una tarjeta física con un chip NFC. Cuando la acercas a un celular, abre tu perfil web con tu foto, tus datos, tus redes y tus medios de pago. Tu cliente te guarda en su agenda con un toque y no necesita instalar ninguna aplicación.'],
        ['¿Qué apps puedo activar en mi tarjeta?', 'Hoy puedes activar Agenda de citas, Califícame (calificaciones y reseñas en Google) y Música (Spotify). También tienes analíticas, temas de diseño y la IA que redacta tu presentación. Próximamente: analíticas con IA, asistente con IA y catálogo de productos.'],
        ['¿Qué hace la inteligencia artificial?', 'Le cuentas a qué te dedicas en una frase y la IA redacta la presentación de tu perfil, lista para usar. Tú la ajustas cuando quieras desde tu panel.'],
    ], array_map(fn ($p) => [$p[0], $x($p[1])], $t['faq']));

    $esquemas = [
        ['@context' => 'https://schema.org', '@graph' => [
            ['@type' => 'Organization', '@id' => $dominio . '/#org', 'name' => 'DaUnToque', 'alternateName' => ['Da Un Toque', 'dauntoque.com'], 'url' => $dominio . '/', 'logo' => $dominio . $base . '/media/logo-512.png', 'sameAs' => \App\Console\Commands\GenerarSitio::REDES, 'image' => $imagen, 'description' => $descripcion, 'areaServed' => ['CO', 'US'],
                'contactPoint' => ['@type' => 'ContactPoint', 'contactType' => 'sales', 'telephone' => '+' . $mercado['whatsapp'], 'areaServed' => $mercado['code'], 'availableLanguage' => ['es']]],
            ['@type' => 'WebSite', '@id' => $dominio . '/#web', 'name' => 'DaUnToque', 'url' => $dominio . '/', 'inLanguage' => $version['hreflang'], 'publisher' => ['@id' => $dominio . '/#org']],
            ['@type' => 'WebPage', '@id' => $url . '#pagina', 'url' => $url, 'name' => $titulo, 'description' => $descripcion, 'inLanguage' => $version['hreflang'], 'isPartOf' => ['@id' => $dominio . '/#web'], 'about' => ['@id' => $url . '#producto'], 'primaryImageOfPage' => $imagen],
        ]],
        ['@context' => 'https://schema.org', '@type' => 'Product', '@id' => $url . '#producto', 'name' => 'Tarjeta de presentación NFC DaUnToque', 'brand' => ['@type' => 'Brand', 'name' => 'DaUnToque'],
            'description' => $descripcion, 'image' => [$imagen], 'sku' => 'DT-NFC', 'category' => 'Tarjeta de presentación NFC', 'material' => 'PVC con chip NFC NTAG215',
            'additionalProperty' => array_map(fn ($a) => ['@type' => 'PropertyValue', 'name' => $a[1], 'value' => $a[3]], array_values(array_filter($apps, fn ($a) => $a[2] === 'ok'))),
            'offers' => \App\Console\Commands\GenerarSitio::oferta($mercado, $url)],
        ['@context' => 'https://schema.org', '@type' => 'HowTo', 'name' => $i['viajeH'],
            'step' => array_map(fn ($p, $n) => ['@type' => 'HowToStep', 'position' => $n + 1, 'name' => $p[0], 'text' => $x($p[1], [':envio' => $envio])], $i['viaje'], array_keys($i['viaje']))],
        ['@context' => 'https://schema.org', '@type' => 'FAQPage',
            'mainEntity' => array_map(fn ($p) => ['@type' => 'Question', 'name' => $p[0], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $p[1]]], $faq)],
    ];
    $ico = fn (string $n, int $s = 18) => '<svg width="' . $s . '" height="' . $s . '" aria-hidden="true"><use href="#i-' . $n . '"/></svg>';
@endphp
<!doctype html>
<html lang="{{ $version['hreflang'] }}" data-version="{{ $codigo }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ $titulo }}</title>
    <meta name="description" content="{{ $descripcion }}">
    @if ($prueba)
        {{-- Versión de prueba: no compite en Google con la portada actual --}}
        <meta name="robots" content="noindex, follow">
    @elseif ($indexar)
        <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    @else
        <meta name="robots" content="noindex, nofollow">
    @endif
    <link rel="canonical" href="{{ $url }}">
    @unless ($prueba)
        @foreach ($versiones as $c => $v)
            <link rel="alternate" hreflang="{{ $v['hreflang'] }}" href="{{ $canonica($c) }}">
        @endforeach
        @include('sitio._hreflang')
    @endunless
    <meta name="theme-color" content="#09090a">
    <meta name="geo.region" content="{{ $mercado['code'] }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="DaUnToque">
    <meta property="og:locale" content="{{ str_replace('-', '_', $version['hreflang']) }}">
    <meta property="og:title" content="{{ $titulo }}">
    <meta property="og:description" content="{{ $descripcion }}">
    <meta property="og:url" content="{{ $url }}">
    <meta property="og:image" content="{{ $imagen }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="Tarjeta de presentación NFC DaUnToque tocando un celular">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $titulo }}">
    <meta name="twitter:description" content="{{ $descripcion }}">
    <meta name="twitter:image" content="{{ $imagen }}">
    @php $fuentes = 'https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,800&family=Figtree:wght@400;500;600;700&family=Instrument+Serif:ital@1&display=swap'; @endphp
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="{{ $fuentes }}" media="print" onload="this.media='all'">
    <noscript><link rel="stylesheet" href="{{ $fuentes }}"></noscript>
    <link rel="stylesheet" href="{{ $base }}/nueva.css">
    @include('sitio._iconos')
    @foreach ($esquemas as $esquema)
        <script type="application/ld+json">{!! json_encode($esquema, $json) !!}</script>
    @endforeach
    <script src="/etiquetas.js" async></script>
</head>
<body>
    <svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
        <symbol id="i-ok" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" d="M5 12.5l4.2 4.2L19 7"/></symbol>
        <symbol id="i-nfc" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M8 7a7 7 0 0 1 0 10"/><path d="M12 4.5a11 11 0 0 1 0 15"/><path d="M16 2.5a14 14 0 0 1 0 19"/></g></symbol>
        <symbol id="i-user" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="8" r="4"/><path d="M4 21c1.5-4 4.5-6 8-6s6.5 2 8 6"/></g></symbol>
        <symbol id="i-cal" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3.5" y="5" width="17" height="15" rx="2.5"/><path d="M3.5 10h17M8 3v4M16 3v4"/></g></symbol>
        <symbol id="i-star" viewBox="0 0 24 24"><path fill="currentColor" d="M12 2.8l2.8 5.9 6.4.8-4.7 4.4 1.2 6.4L12 17.2l-5.7 3.1 1.2-6.4L2.8 9.5l6.4-.8z"/></symbol>
        <symbol id="i-music" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18V5.5l11-2V16"/><circle cx="6.5" cy="18" r="2.5"/><circle cx="17.5" cy="16" r="2.5"/></g></symbol>
        <symbol id="i-cash" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="2"><rect x="2.5" y="6" width="19" height="12" rx="2.5"/><circle cx="12" cy="12" r="2.6"/></g></symbol>
        <symbol id="i-swap" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M4 8h14l-3.5-3.5M20 16H6l3.5 3.5"/></symbol>
        <symbol id="i-chart" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/></g></symbol>
        <symbol id="i-palette" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 3a9 9 0 1 0 0 18c1.4 0 2-1 1.6-2.200-.5-1.500.4-2.800 2-2.800H18a3 3 0 0 0 3-3c0-5-4-10-9-10z"/><circle cx="7.800" cy="11" r=".8"/><circle cx="11" cy="7.500" r=".8"/><circle cx="15.500" cy="8.500" r=".8"/></g></symbol>
        <symbol id="i-spark" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" d="M12 3l2.2 6.800L21 12l-6.800 2.200L12 21l-2.200-6.800L3 12l6.800-2.200z"/></symbol>
        <symbol id="i-brain" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19l4.500-5 3.500 3 4-6 4 4"/><path d="M17.500 3l.9 2.600L21 6.500l-2.600.9-.9 2.600-.9-2.600L14 6.500l2.600-.9z"/></g></symbol>
        <symbol id="i-chat" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" d="M4 19.500l1.400-4A8 8 0 1 1 8.600 19z"/></symbol>
        <symbol id="i-bag" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" stroke-linecap="round"><path d="M5 8h14l-1 12H6z"/><path d="M9 8V6.500a3 3 0 0 1 6 0V8"/></g></symbol>
        <symbol id="i-truck" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"><path d="M2 6h11v10H2zM13 9h4.500l3.500 3.500V16h-8"/><circle cx="6" cy="17.500" r="1.800"/><circle cx="17" cy="17.500" r="1.800"/></g></symbol>
        <symbol id="i-shield" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" d="M12 3l8 3v6c0 4.500-3.400 8-8 9-4.600-1-8-4.500-8-9V6z"/></symbol>
        <symbol id="i-phone" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" d="M5 3.500h3.500l1.800 4.500-2.300 1.500a11 11 0 0 0 6.500 6.500l1.500-2.300 4.500 1.800V19a2 2 0 0 1-2 2A17 17 0 0 1 3 5.500a2 2 0 0 1 2-2z"/></symbol>
        <symbol id="i-play" viewBox="0 0 24 24"><path fill="currentColor" d="M8 5.500v13l11-6.500z"/></symbol>
        <symbol id="i-pause" viewBox="0 0 24 24"><path fill="currentColor" d="M7 5h4v14H7zM13 5h4v14h-4z"/></symbol>
        <symbol id="i-x" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-width="2.200" stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></symbol>
        <symbol id="i-in" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M14 4h4a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-4M10 8l4 4-4 4M14 12H4"/></symbol>
        <symbol id="i-flecha" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-width="2.200" stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></symbol>
        <symbol id="i-wa" viewBox="0 0 24 24"><path fill="currentColor" d="M12 2a10 10 0 0 0-8.600 15.100L2 22l5-1.300A10 10 0 1 0 12 2zm5.300 14.100c-.2.600-1.300 1.200-1.800 1.300-.5.100-1 .1-3.300-.8-2.800-1.100-4.500-3.900-4.600-4.100-.1-.2-1.100-1.500-1.100-2.800 0-1.300.7-2 1-2.300.3-.3.6-.3.8-.3h.6c.2 0 .4 0 .6.500l.9 2.100c.1.2.1.4 0 .6l-.4.6-.4.500c-.1.1-.3.3-.1.600.2.300.8 1.300 1.700 2.100 1.200 1 2.100 1.400 2.400 1.500.3.200.5.100.6-.1l.9-1c.2-.3.4-.2.7-.1l2 1c.3.1.5.2.5.300.1.200.1.700-.1 1.300z"/></symbol>
    </svg>

    <a class="sr saltar" href="#contenido">Saltar al contenido</a>

    <header class="top" data-top>
        <div class="wrap">
            <a class="logo" href="{{ $yo }}" aria-label="DaUnToque, inicio"><i aria-hidden="true"></i><span>dauntoque</span></a>
            <nav class="nav" aria-label="Secciones">
                <a href="#plataforma">Plataforma</a>
                <a href="#panel">Tu panel</a>
                <a href="#apps">Apps</a>
                <a href="#casos">Casos</a>
                <a href="#precio">Precio</a>
                <a href="#preguntas">Preguntas</a>
            </nav>
            <div class="top-r">
                <a class="btn btn-fantasma entrar" href="{{ $portal }}/login">{!! $ico('in', 16) !!}<span>Iniciar sesión</span></a>
                <a class="btn btn-pri" data-cta href="{{ $comprar() }}" {!! $ext !!}>Quiero mi tarjeta</a>
                <details class="menu">
                    <summary aria-label="Menú"><svg width="20" height="20" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-width="2.200" stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/></svg></summary>
                    <nav aria-label="Menú">
                        <a href="#plataforma">Plataforma</a>
                        <a href="#panel">Tu panel</a>
                        <a href="#apps">Apps</a>
                        <a href="#casos">Casos</a>
                        <a href="#precio">Precio</a>
                        <a href="#preguntas">Preguntas</a>
                        <a class="menu-entrar" href="{{ $portal }}/login">{!! $ico('in', 16) !!}Iniciar sesión</a>
                    </nav>
                </details>
            </div>
        </div>
    </header>

    <main id="contenido">
        {{-- 1. Héroe: promesa, precio y la tarjeta con el nombre del visitante --}}
        <section class="hero">
            <div class="hero-luz" aria-hidden="true"></div>
            <div class="wrap hero-grid">
                <div class="hero-txt">
                    <h1><span class="sobre">Tarjeta de presentación NFC con inteligencia artificial</span>Un toque. Y el negocio ya <em>empezó.</em></h1>
                    <p class="lead">La tarjeta inteligente para quienes cierran rápido. Acercas tu tarjeta al celular de tu cliente y se abre tu perfil: te guarda, te escribe, agenda contigo y te paga. Tú ves en tu panel si quedó bien atendido.</p>
                    <div class="hero-precio">
                        <strong class="num">{{ $precio }}</strong><span>{{ $moneda }}</span>
                        @if ($mercado['antesTexto'])<s class="num">{{ $mercado['antesTexto'] }}</s>@endif
                        <b>Pago único · Sin mensualidades</b>
                    </div>
                    <div class="ctas" data-compra>
                        <a class="btn btn-pri btn-xl" data-cta href="{{ $comprar() }}" {!! $ext !!}>Quiero mi tarjeta {!! $ico('flecha') !!}</a>
                        <a class="btn btn-sec btn-xl" href="#plataforma">Ver la plataforma</a>
                    </div>
                    <ul class="sellos">
                        <li>{!! $ico('truck', 16) !!}{{ $envio }}</li>
                        <li>{!! $ico('shield', 16) !!}Garantía de 30 días</li>
                        <li>{!! $ico('spark', 16) !!}2º perfil de regalo por lanzamiento</li>
                    </ul>
                </div>

                <div class="hero-vis" data-estudio>
                    <div class="escenario" data-inclinar>
                        <div class="tarjeta" data-tarjeta data-acabado="matte-black">
                            <span class="brillo" aria-hidden="true"></span>
                            <div class="t-fila"><span class="t-marca">dauntoque</span>{!! $ico('nfc', 26) !!}</div>
                            <div><div class="t-nombre" data-nombre data-vacio="Tu nombre aquí">Tu nombre aquí</div><div class="t-acabado"><span data-acabado-txt>Matte Black</span> · NFC</div></div>
                        </div>
                        {{-- Lo que pasa después del toque (ejemplos de la plataforma, no datos reales) --}}
                        <div class="evento ev1" aria-hidden="true"><i>{!! $ico('user', 15) !!}</i><span><b>Contacto guardado</b>en su agenda</span></div>
                        <div class="evento ev2" aria-hidden="true"><i>{!! $ico('cal', 15) !!}</i><span><b>Cita agendada</b>jueves, 3:00 p. m.</span></div>
                        <div class="evento ev3" aria-hidden="true"><i>{!! $ico('star', 15) !!}</i><span><b>5 estrellas</b>nueva calificación</span></div>
                        <div class="evento ev4" aria-hidden="true"><i>{!! $ico('cash', 15) !!}</i><span><b>Pago iniciado</b>con {{ $medio1 }}</span></div>
                    </div>
                    <div class="estudio">
                        <label class="sr" for="nombreTarjeta">Escribe tu nombre para verlo en la tarjeta</label>
                        <input id="nombreTarjeta" data-nombre-in maxlength="28" placeholder="Escribe tu nombre y mírala" autocomplete="off">
                        <div class="seg" role="group" aria-label="Acabado de la tarjeta">
                            <button type="button" data-acabado-btn="matte-black" aria-pressed="true">Matte Black</button>
                            <button type="button" data-acabado-btn="crystal" aria-pressed="false">Crystal</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="cinta" aria-label="Profesionales que usan tarjetas de presentación NFC">
                <div class="cinta-pista">
                    @foreach ([0, 1] as $vuelta)
                        <ul @if ($vuelta) aria-hidden="true" @endif>@foreach ($oficios as $oficio)<li>{{ $oficio }}</li>@endforeach</ul>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- 2. El problema y la solución --}}
        <section class="sec" id="solucion">
            <div class="wrap">
                <div class="head ver">
                    <span class="eyebrow">El problema</span>
                    <h2>Te presentas bien. Y después, <em>¿qué pasó con ese contacto?</em></h2>
                    <p class="lead mut">La tarjeta de papel se pierde, el número se anota mal y nunca sabes si te guardaron. Con DaUnToque, cada presentación deja rastro.</p>
                </div>
                <div class="antes-despues">
                    <article class="ver ad antes">
                        <h3>Con papel</h3>
                        <ul>
                            <li>Tu tarjeta termina en un cajón.</li>
                            <li>Si cambias de número, hay que reimprimir.</li>
                            <li>No sabes quién te guardó ni quién te buscó.</li>
                            <li>Para pagarte o agendar, te tienen que escribir.</li>
                        </ul>
                    </article>
                    <article class="ver ad despues">
                        <h3>Con DaUnToque</h3>
                        <ul>
                            <li>{!! $ico('ok', 16) !!}<span><b>Te guardan en la agenda</b> con foto, cargo y redes.</span></li>
                            <li>{!! $ico('ok', 16) !!}<span><b>Cambias tus datos</b> cuando quieras, sin cambiar la tarjeta.</span></li>
                            <li>{!! $ico('ok', 16) !!}<span><b>Tu panel te avisa</b> quién te vio, te guardó y te escribió.</span></li>
                            <li>{!! $ico('ok', 16) !!}<span><b>Te agendan y te pagan</b> desde tu perfil, en el momento.</span></li>
                        </ul>
                    </article>
                </div>
            </div>
        </section>

        {{-- 3. La plataforma: explorador de módulos con un celular que se puede tocar --}}
        <section class="sec claro" id="plataforma">
            <div class="wrap">
                <div class="head ver">
                    <span class="eyebrow">La plataforma</span>
                    <h2>Detrás de tu tarjeta hay <em>todo un equipo trabajando.</em></h2>
                    <p class="lead mut">Tu perfil no es una página quieta. Agenda, califica, cobra y suena. Toca cada módulo y pruébalo en el celular.</p>
                </div>
                <div class="explorador" data-explorador>
                    <div class="mod-lista" role="tablist" aria-label="Módulos de la plataforma">
                        @foreach ($modulos as $clave => [$icono, $nombre, $estado])
                            <button type="button" role="tab" id="tab-{{ $clave }}" aria-controls="mod-{{ $clave }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}" data-mod="{{ $clave }}" @unless ($loop->first) tabindex="-1" @endunless>
                                <span class="mod-ic">{!! $ico($icono, 20) !!}</span>
                                <span class="mod-n">{{ $nombre }}<small>{{ $estado }}</small></span>
                            </button>
                        @endforeach
                    </div>

                    <div class="telefono" aria-label="Demostración en un celular">
                        <div class="tel-barra" aria-hidden="true"><span>9:41</span><i></i><span>5G</span></div>
                        <div class="tel-pantalla">
                            {{-- Perfil --}}
                            <div class="pan" data-pan="perfil">
                                <div class="pf-cab">
                                    <img src="{{ $base }}/media/caso-kathe-sm.webp" width="84" height="84" alt="Foto de perfil de ejemplo" loading="lazy">
                                    <b>Kathe Glam &amp; Nails</b><span>Uñas en acrílico y gel · Trenzas infantiles</span>
                                    <em class="pf-sello">{!! $ico('star', 12) !!}4,9 · 37 calificaciones</em>
                                </div>
                                <p class="pf-bio">Realza tu belleza con uñas hermosas, delicadas y duraderas. Acrílico, gel y peinados infantiles hechos con amor.</p>
                                <button type="button" class="pb pri" data-toque="Contacto guardado en la agenda">{!! $ico('user', 16) !!}Guardar contacto</button>
                                <button type="button" class="pb" data-toque="Se abre WhatsApp con tu número">{!! $ico('wa', 16) !!}Escribir por WhatsApp</button>
                                <div class="pb-fila">
                                    <button type="button" class="pb" data-ir="agenda">{!! $ico('cal', 16) !!}Agendar</button>
                                    <button type="button" class="pb" data-ir="cobros">{!! $ico('cash', 16) !!}Pagar</button>
                                </div>
                                <div class="pf-lbl">Mis trabajos</div>
                                <div class="pf-gal">@for ($f = 1; $f <= 3; $f++)<img src="{{ $base }}/media/kathe-trabajo-{{ $f }}.webp" width="360" height="360" alt="Trabajo de uñas de Kathe Glam &amp; Nails" loading="lazy">@endfor</div>
                            </div>
                            {{-- Agenda --}}
                            <div class="pan" data-pan="agenda" hidden>
                                <div class="pan-t">Agenda tu cita</div>
                                <div class="ag-serv"><b>Manicure semipermanente</b><span>60 min</span></div>
                                <div class="ag-dias" role="group" aria-label="Día">
                                    @foreach ([['Mar', 17], ['Mié', 18], ['Jue', 19], ['Vie', 20]] as $n => [$d, $num])
                                        <button type="button" data-dia="{{ $d }} {{ $num }}" aria-pressed="{{ $n === 2 ? 'true' : 'false' }}"><small>{{ $d }}</small>{{ $num }}</button>
                                    @endforeach
                                </div>
                                <div class="ag-horas" role="group" aria-label="Hora">
                                    @foreach (['9:00', '10:30', '2:00', '3:00', '4:30', '6:00'] as $n => $h)
                                        <button type="button" data-hora="{{ $h }}" @if ($n === 1) disabled @endif aria-pressed="false">{{ $h }}</button>
                                    @endforeach
                                </div>
                                <button type="button" class="pb pri" data-reservar disabled>Elige una hora</button>
                                <p class="ag-ok" data-reservado hidden>{!! $ico('ok', 16) !!}<span></span></p>
                            </div>
                            {{-- Califícame --}}
                            <div class="pan" data-pan="calificame" hidden>
                                <div class="pan-t">¿Cómo te atendieron?</div>
                                <p class="pan-p">Califica a Kathe Glam &amp; Nails</p>
                                <div class="estrellas" role="group" aria-label="Calificación de 1 a 5 estrellas">
                                    @for ($e = 1; $e <= 5; $e++)<button type="button" data-estrella="{{ $e }}" aria-label="{{ $e }} {{ $e === 1 ? 'estrella' : 'estrellas' }}" aria-pressed="false">{!! $ico('star', 30) !!}</button>@endfor
                                </div>
                                <p class="cal-res" data-cal-res aria-live="polite">Toca las estrellas</p>
                                <div class="cal-google" data-cal-google hidden><b>G</b><span>¿Nos dejas tu reseña en Google?</span></div>
                            </div>
                            {{-- Música --}}
                            <div class="pan" data-pan="musica" hidden>
                                <div class="pan-t">Suena en mi perfil</div>
                                <div class="mu-play" data-musica>
                                    <div class="mu-arte" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i></div>
                                    <div class="mu-info"><b>Canción destacada</b><span>Tu artista · Spotify</span><div class="mu-linea"><i></i></div></div>
                                    <button type="button" aria-label="Reproducir o pausar" aria-pressed="false">{!! $ico('play', 20) !!}{!! $ico('pause', 20) !!}</button>
                                </div>
                                <div class="mu-lista" aria-hidden="true">
                                    @foreach (['Mi lista de sets', 'Lo último que lancé', 'Para entrenar'] as $n => $pista)<div><em>{{ $n + 1 }}</em><span>{{ $pista }}</span><i></i></div>@endforeach
                                </div>
                            </div>
                            {{-- Cobros --}}
                            <div class="pan" data-pan="cobros" hidden>
                                <div class="pan-t">Págame aquí</div>
                                <p class="pan-p">Toca y el dato queda copiado</p>
                                @foreach (array_slice($medios ?: ['Nequi', 'Llave Bre-B'], 0, 3) as $m)
                                    <button type="button" class="pb cobro" data-toque="Copiado: {{ $m }}">{!! $ico('cash', 16) !!}{{ $m }}<small>Copiar</small></button>
                                @endforeach
                            </div>
                            {{-- Dos perfiles: el ejecutivo completo y el personal con la pasión que se elija --}}
                            @php $ej = $i['dosEjecutivo']; @endphp
                            <div class="pan pan-dos" data-pan="dos" hidden>
                                <div class="dos-conm" role="group" aria-label="Perfil activo">
                                    <button type="button" data-cara="ejec" aria-pressed="true">Ejecutivo</button>
                                    @foreach ($i['dosPasiones'] as $clave => $pasion)
                                        <button type="button" data-cara="{{ $clave }}" aria-pressed="false" aria-label="Perfil personal: {{ trim(preg_replace('/[^\p{L} ]/u', '', $pasion['boton'])) }}">{{ mb_substr($pasion['boton'], 0, 2) }}</button>
                                    @endforeach
                                </div>
                                <div class="dos-cara ejec" data-vista-cara="ejec">
                                    <img class="dos-portada" src="{{ $base }}/media/dp-ejec-portada.webp" width="640" height="400" alt="" loading="lazy">
                                    <div class="dos-cuerpo">
                                        <img class="dos-avatar" src="{{ $base }}/media/dp-ejec-avatar.webp" width="220" height="220" alt="{{ $ej['nombre'] }}" loading="lazy">
                                        <b class="dos-nombre">{{ $ej['nombre'] }} <i aria-hidden="true">✓</i></b>
                                        <span class="dos-rol">{{ $ej['rol'] }}</span>
                                        <div class="dos-cifras">@foreach ($ej['cifras'] as $c)<div><b>{{ $c[0] }}</b><span>{{ $c[1] }}</span></div>@endforeach</div>
                                        <span class="pb pri">{!! $ico('user', 16) !!}{{ $ej['botones'][0] }}</span>
                                        <div class="pb-fila"><span class="pb">{{ $ej['botones'][1] }}</span><span class="pb">{{ $ej['botones'][2] }}</span></div>
                                        <div class="pf-lbl">{{ $ej['galeria'] }}</div>
                                        <div class="pf-gal">@for ($f = 1; $f <= 3; $f++)<img src="{{ $base }}/media/dp-ejec-{{ $f }}.webp" width="240" height="240" alt="" loading="lazy">@endfor</div>
                                    </div>
                                </div>
                                @foreach ($i['dosPasiones'] as $clave => $pasion)
                                    <div class="dos-cara pers" data-vista-cara="{{ $clave }}" hidden>
                                        <div class="dos-heroe">
                                            <img src="{{ $base }}/media/dp-{{ $clave }}-portada.webp" width="640" height="760" alt="{{ $pasion['alt'] }}" loading="lazy">
                                            <div><small>{{ $i['dosFinde'] }}</small><b class="dos-nombre">{{ $pasion['nombre'] }}</b><span class="dos-rol">{{ $pasion['lema'] }}</span></div>
                                        </div>
                                        <div class="dos-cuerpo">
                                            <div class="dos-tags">@foreach ($pasion['tags'] as $etiqueta)<span>{{ $etiqueta }}</span>@endforeach</div>
                                            <div class="pb-fila"><span class="pb pri">{{ $pasion['cta'] }}</span><span class="pb">Instagram</span></div>
                                            <div class="pf-gal">@for ($f = 1; $f <= 3; $f++)<img src="{{ $base }}/media/dp-{{ $clave }}-{{ $f }}.webp" width="240" height="240" alt="" loading="lazy">@endfor</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="tel-aviso" role="status" aria-live="polite" data-aviso><i>{!! $ico('ok', 14) !!}</i><span></span></div>
                        </div>
                    </div>

                    <div class="mod-info">
                        @foreach ($modulos as $clave => [$icono, $nombre, $estado, $h, $txt, $puntos])
                            <div role="tabpanel" id="mod-{{ $clave }}" aria-labelledby="tab-{{ $clave }}" data-info="{{ $clave }}" @unless ($loop->first) hidden @endunless>
                                <span class="estado">{{ $estado }}</span>
                                <h3>{{ $h }}</h3>
                                <p class="mut">{{ $txt }}</p>
                                <ul>@foreach ($puntos as $punto)<li>{!! $ico('ok', 16) !!}{{ $punto }}</li>@endforeach</ul>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        {{-- 4. Tu panel y la IA --}}
        <section class="sec" id="panel">
            <div class="wrap">
                <div class="head ver">
                    <span class="eyebrow">Tu panel</span>
                    <h2>Sabes si tu contacto <em>quedó bien servido.</em></h2>
                    <p class="lead mut">Cada toque queda registrado: quién abrió tu perfil, quién te guardó y qué botón tocó. Así sabes a quién escribirle hoy.</p>
                </div>
                <div class="tablero ver" data-contar role="img" aria-label="Ejemplo del panel de DaUnToque con visitas, contactos guardados, citas y calificación">
                    <div class="kpis">
                        @foreach ([[1284, 'Visitas al perfil', ''], [412, 'Contactos guardados', ''], [58, 'Citas agendadas', ''], [49, 'Calificación promedio', 'dec']] as [$n, $txt, $tipo])
                            <div><b class="num" data-hasta="{{ $n }}" data-tipo="{{ $tipo }}">{{ $tipo === 'dec' ? '4,9' : number_format($n, 0, ',', '.') }}</b><span>{{ $txt }}</span></div>
                        @endforeach
                    </div>
                    <div class="tab-grid">
                        <div class="tab-caja">
                            <div class="tab-top"><h3>Visitas · últimos 14 días</h3><span>Ejemplo</span></div>
                            <div class="barras" aria-hidden="true">@foreach ([32, 45, 38, 60, 52, 28, 22, 48, 66, 58, 74, 69, 41, 88] as $alto)<i style="--h:{{ $alto }}%"></i>@endforeach</div>
                        </div>
                        <div class="tab-caja">
                            <div class="tab-top"><h3>Lo último</h3><span>Hoy</span></div>
                            <ul class="actividad">
                                <li><i>{!! $ico('user', 14) !!}</i><span>Guardaron tu contacto</span><time>10:42</time></li>
                                <li><i>{!! $ico('cal', 14) !!}</i><span>Nueva cita para el jueves</span><time>10:15</time></li>
                                <li><i>{!! $ico('star', 14) !!}</i><span>Te calificaron con 5 estrellas</span><time>09:58</time></li>
                                <li><i>{!! $ico('cash', 14) !!}</i><span>Iniciaron un pago con {{ $medio1 }}</span><time>09:31</time></li>
                            </ul>
                        </div>
                    </div>
                    <p class="tab-nota">Datos de ejemplo. En tu panel ves las visitas y los clics reales de cada perfil.</p>
                </div>

                <div class="ia" id="ia">
                    <div class="ia-txt ver">
                        <span class="eyebrow oro">Inteligencia artificial</span>
                        <h2>Tú dices a qué te dedicas. <em>La IA te presenta.</em></h2>
                        <p class="lead mut">Escribe tu oficio en una frase y la inteligencia artificial redacta tu presentación, lista para usar. Tú eliges el diseño, los colores y cada detalle.</p>
                        <ul class="ia-prox">
                            <li><span class="pronto">Próximamente</span><b>Analíticas con IA</b> Tu resumen de la semana y qué hacer hoy.</li>
                            <li><span class="pronto">Próximamente</span><b>Asistente con IA</b> Responde a tus clientes por ti.</li>
                        </ul>
                    </div>
                    <div class="ia-demo ver" data-ia data-pide="Soy asesor inmobiliario en Bogotá, apartamentos sobre planos." data-responde="Asesor inmobiliario en Bogotá. Te acompaño a comprar sobre planos con información clara y sin afanes. Agenda una visita o escríbeme por WhatsApp.">
                        <div class="ia-barra" aria-hidden="true"><i></i><i></i><i></i><span>Editor de tu perfil</span></div>
                        <div class="ia-msg tu"><small>Tú</small><p data-ia-pide>Soy asesor inmobiliario en Bogotá, apartamentos sobre planos.</p></div>
                        <div class="ia-msg bot"><small>{!! $ico('spark', 12) !!}IA</small><p data-ia-responde>Asesor inmobiliario en Bogotá. Te acompaño a comprar sobre planos con información clara y sin afanes. Agenda una visita o escríbeme por WhatsApp.</p></div>
                        <div class="ia-temas" aria-hidden="true">@foreach (['#0d0f10', '#f5f4ef', '#1e3a5f', '#2fae6e', '#c9a227', '#7c3aed', '#e11d48', '#0ea5e9'] as $n => $color)<i @if ($n === 4) class="on" @endif style="background:{{ $color }}"></i>@endforeach</div>
                    </div>
                </div>
            </div>
        </section>

        {{-- 5. Apps: lo que ya existe y lo que viene --}}
        <section class="sec claro" id="apps">
            <div class="wrap">
                <div class="head ver">
                    <span class="eyebrow">Apps de tu tarjeta</span>
                    <h2>Empieza con lo esencial. <em>Suma lo que tu negocio pida.</em></h2>
                    <p class="lead mut">Activa solo las apps que uses y deja de pagar otras plataformas por aparte.</p>
                </div>
                <div class="apps">
                    @foreach ($apps as [$icono, $nombre, $estado, $txt])
                        <article class="app ver {{ $estado === 'pronto' ? 'app-pronto' : '' }}">
                            <div class="app-top"><span class="app-ic">{!! $ico($icono, 22) !!}</span><span class="{{ $estado === 'ok' ? 'listo' : 'pronto' }}">{{ $estado === 'ok' ? 'Disponible' : 'Próximamente' }}</span></div>
                            <h3>{{ $nombre }}</h3>
                            <p class="mut">{{ $txt }}</p>
                        </article>
                    @endforeach
                </div>
                <p class="apps-nota mut">{!! $ico('ok', 16) !!}Tu tarjeta sigue sin mensualidades. Las apps son opcionales y las activas desde tu panel.</p>
            </div>
        </section>

        {{-- 6. De la compra al primer toque --}}
        <section class="sec" id="como-funciona">
            <div class="wrap">
                <div class="head ver">
                    <span class="eyebrow">Cómo funciona</span>
                    <h2>{{ $i['viajeH'] }}</h2>
                </div>
                <ol class="pasos">
                    @foreach ($i['viaje'] as $n => [$h, $txt])
                        <li class="ver"><span class="paso-n">{{ $n + 1 }}</span><h3>{{ $h }}</h3><p class="mut">{{ $x($txt, [':envio' => $envio]) }}</p></li>
                    @endforeach
                </ol>
            </div>
        </section>

        {{-- 7. Casos reales --}}
        <section class="sec claro" id="casos">
            <div class="wrap">
                <div class="head ver">
                    <span class="eyebrow">Casos reales</span>
                    <h2>Ya se presentan <em>con un toque.</em></h2>
                    <p class="lead mut">Perfiles reales de clientes. Ábrelos y mira cómo se ve el tuyo.</p>
                </div>
                <div class="casos">
                    @foreach ($casos as $n => [$foto, $nombre, $enlace])
                        @php [$dato, $cita, $rol] = $i['casos'][$n]; @endphp
                        <article class="caso ver">
                            <img src="{{ $base }}/media/{{ $foto }}.webp" width="480" height="480" alt="{{ $nombre }}, {{ $rol }}" loading="lazy">
                            <div class="caso-c">
                                <span class="dato">{{ $dato }}</span>
                                <blockquote>“{{ $cita }}”</blockquote>
                                <div class="quien"><span><b>{{ $nombre }}</b>{{ $rol }}</span><a href="{{ $enlace }}" target="_blank" rel="noopener">Ver perfil {!! $ico('flecha', 14) !!}</a></div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- 8. Precio --}}
        <section class="sec" id="precio">
            <div class="wrap precio">
                <div class="precio-txt ver">
                    <span class="eyebrow">Precio</span>
                    <h2>Una sola compra. <em>Cero mensualidades.</em></h2>
                    <p class="lead mut">Con un cliente que te guarde y te pague, la tarjeta ya se pagó.</p>
                    <table class="compara">
                        <caption class="sr">{{ $i['comparaH'] }}</caption>
                        <thead><tr>@foreach ($i['comparaCols'] as $col)<th scope="col">@if ($loop->first)<span class="sr">{{ $col }}</span>@else{{ $loop->last ? 'DaUnToque' : $col }}@endif</th>@endforeach</tr></thead>
                        <tbody>
                            @foreach ($i['comparaFilas'] as $fila)
                                <tr><th scope="row">{{ $fila[0] }}</th>@for ($k = 1; $k <= 3; $k++)<td>{{ $fila[$k] }}</td>@endfor</tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="oferta ver">
                    <div class="oferta-top"><h3>Tarjeta DaUnToque + plataforma</h3>@if ($mercado['ahorroTexto'])<span class="listo">{{ $x($i['ahorroCorto']) }}</span>@endif</div>
                    <div class="monto"><strong class="num">{{ $precio }}</strong><span>{{ $moneda }}</span>@if ($mercado['antesTexto'])<s class="num">{{ $mercado['antesTexto'] }}</s>@endif</div>
                    <ul class="incluye">
                        <li>{!! $ico('ok', 16) !!}Tarjeta física con chip NFC (Matte Black o Crystal)</li>
                        <li>{!! $ico('ok', 16) !!}Perfil web con tu foto, WhatsApp, redes y portafolio</li>
                        <li>{!! $ico('ok', 16) !!}Presentación redactada con IA</li>
                        <li>{!! $ico('ok', 16) !!}Botones de cobro: {{ implode(', ', $medios) }}</li>
                        <li>{!! $ico('ok', 16) !!}Panel con analíticas de visitas y contactos</li>
                        <li>{!! $ico('ok', 16) !!}Apps: Agenda de citas, Califícame y Música</li>
                        <li>{!! $ico('ok', 16) !!}<span><b>Segundo perfil de regalo</b> · solo por lanzamiento</span></li>
                        <li>{!! $ico('ok', 16) !!}{{ $envio }}</li>
                    </ul>
                    <a class="btn btn-pri btn-xl btn-ancho" data-compra data-cta href="{{ $comprar() }}" {!! $ext !!}>@if ($compraExterna){!! $ico('wa', 20) !!}@endif{{ $i['pagar'][$compraExterna ? 'whatsapp' : 'linea'] }}</a>
                    <div class="pasarela"><span>{{ $i['pagoSeguro'][$compraExterna ? 'whatsapp' : 'linea'] }}</span>@foreach ($compraExterna ? $medios : $i['pasarela'] as $m)<span class="chip">{{ $m }}</span>@endforeach</div>
                    <p class="garantia">{!! $ico('shield', 18) !!}<span>{{ $i['garantia'] }}</span></p>
                    <p class="renueva">{{ $i['renueva'] }}</p>
                </div>
            </div>
        </section>

        {{-- 9. Preguntas --}}
        <section class="sec claro" id="preguntas">
            <div class="wrap preguntas">
                <div class="head ver">
                    <span class="eyebrow">Preguntas</span>
                    <h2>Lo que nos preguntan <em>antes de comprar.</em></h2>
                    <p class="lead mut">¿Otra duda? <a href="{{ $wa($t['waDuda']) }}" target="_blank" rel="noopener">Escríbenos por WhatsApp</a> y te responde una persona.</p>
                </div>
                <div class="faq">
                    @foreach ($faq as [$pregunta, $respuesta])
                        <details @if ($loop->first) open @endif><summary>{{ $pregunta }}</summary><p>{{ $respuesta }}</p></details>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- 10. Cierre --}}
        <section class="sec cierre">
            <div class="wrap ver">
                <h2>Que tu próximo cliente te guarde <em>antes de despedirse.</em></h2>
                <a class="btn btn-pri btn-xl" data-compra data-cta href="{{ $comprar() }}" {!! $ext !!}>Quiero mi tarjeta · {{ $precio }} {{ $moneda }}</a>
                <span class="mut">{{ $envio }} · Sin mensualidades · Garantía de 30 días</span>
            </div>
        </section>
    </main>

    <footer class="pie">
        <div class="wrap">
            <div>
                <a class="logo" href="{{ $yo }}"><i aria-hidden="true"></i><span>dauntoque</span></a>
                <p>Tarjetas de presentación NFC inteligentes, con perfil web, agenda y analíticas.</p>
                <p>&copy; {{ date('Y') }} DaUnToque. Todos los derechos reservados.</p>
            </div>
            <nav aria-label="Tarjetas">
                <b>Tarjetas</b>
                <a href="{{ $ir($codigo, 'tarjeta', 'matte-black') }}">Tarjeta NFC Matte Black</a>
                <a href="{{ $ir($codigo, 'tarjeta', 'crystal') }}">Tarjeta NFC Crystal</a>
                <a href="{{ $ir($codigo, 'preguntas') }}">Preguntas frecuentes</a>
            </nav>
            <nav aria-label="Tu cuenta">
                <b>Tu cuenta</b>
                <a href="{{ $portal }}/login">Iniciar sesión</a>
                <a href="{{ $portal }}/mis-citas">Mis citas</a>
                <a href="{{ $wa($t['waDuda']) }}" target="_blank" rel="noopener">WhatsApp de ventas</a>
            </nav>
            <nav aria-label="País e idioma">
                <b>País e idioma</b>
                @foreach ($versiones as $c => $v)
                    <a href="{{ $base }}/{{ $c }}/" hreflang="{{ $v['hreflang'] }}">{{ $t['versiones'][$c] }}</a>
                @endforeach
            </nav>
        </div>
    </footer>

    <div class="barra-movil" data-barra>
        <div><b class="num">{{ $precio }}</b><small>{{ $moneda }} · sin mensualidades</small></div>
        <a class="btn btn-pri" data-cta href="{{ $comprar() }}" {!! $ext !!}>Quiero mi tarjeta</a>
    </div>

    {{-- Aviso de compras reales: nueva.js lo llena con /compras-recientes.json y no aparece si no hay compras --}}
    <aside class="compra-aviso" data-compras="/compras-recientes.json?pais={{ $mercado['code'] }}" aria-label="Compras recientes" hidden>
        <i aria-hidden="true">{!! $ico('nfc', 20) !!}</i>
        <p><b data-c-quien></b><span data-c-que></span><small data-c-cuando></small></p>
        <button type="button" data-c-cerrar aria-label="Cerrar avisos de compras">{!! $ico('x', 14) !!}</button>
    </aside>

    <script src="{{ $base }}/nueva.js" defer></script>
</body>
</html>
