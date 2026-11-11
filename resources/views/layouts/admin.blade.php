<!DOCTYPE html>
{{-- Centro de mando: módulo del administrador, con su propio menú (aparte del panel de los clientes) --}}
<html lang="es" {!! \App\Support\TemaPanel::atributos(Auth::user()) !!}>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('titulo', 'Centro de mando') · DaUnToque</title>

    @include('partials.panel-estilos')
    @include('admin._estilos')

    @vite('resources/css/panel.css')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
    <script>
        // Este navegador es del administrador: sus visitas al sitio de ventas no se cuentan en Rendimiento
        document.cookie = 'dt_yo=1; path=/; max-age=31536000; SameSite=Lax' + (/(^|\.)dauntoque\.com$/.test(location.hostname) ? '; domain=.dauntoque.com' : '') + (location.protocol === 'https:' ? '; Secure' : '');
    </script>
</head>
@php
    $usuarioPanel = Auth::user();
    // Menú del módulo: [ruta, nombre, ícono (trazo SVG), rutas que la marcan activa]
    $grupos = [
        'Negocio' => [
            ['admin', 'Resumen', '<path d="M3 13h8V3H3zM13 21h8V11h-8zM3 21h8v-6H3zM13 3v6h8V3z"/>', ['admin', 'admin/sin-uso', 'admin/clientes/*']],
            ['admin/pagos', 'Pagos', '<rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20M6 15h4"/>', ['admin/pagos*']],
            ['admin/chips', 'Chips y códigos', '<rect x="5" y="5" width="14" height="14" rx="2"/><path d="M9 1v4M15 1v4M9 19v4M15 19v4M1 9h4M1 15h4M19 9h4M19 15h4"/>', ['admin/chips*']],
            ['admin/carritos', 'Carritos', '<circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/><path d="M2 3h3l2.6 12.4a2 2 0 0 0 2 1.6h8.2a2 2 0 0 0 2-1.5L22 8H6"/>', ['admin/carritos*']],
            ['admin/soporte', 'Soporte', '<path d="M21 12a8 8 0 0 1-11.6 7.1L3 21l1.9-6.4A8 8 0 1 1 21 12z"/>', ['admin/soporte*']],
        ],
        'Marketing' => [
            ['admin/rendimiento', 'Rendimiento', '<path d="M3 3v18h18"/><path d="M7 15l4-4 3 3 6-7"/>', ['admin/rendimiento*']],
            ['admin/campanas', 'Campañas y UTM', '<path d="M3 11v2a1 1 0 0 0 1 1h2l5 4V6L6 10H4a1 1 0 0 0-1 1zM15.5 8.5a5 5 0 0 1 0 7M18.5 5.5a9 9 0 0 1 0 13"/>', ['admin/campanas*']],
            ['admin/pixeles', 'Píxeles', '<circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3M4.9 4.9l2.1 2.1M17 17l2.1 2.1M4.9 19.1L7 17M17 7l2.1-2.1"/>', ['admin/pixeles*']],
        ],
        'Ajustes' => [
            ['admin/correos', 'Correos', '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="M22 6l-10 7L2 6"/>', ['admin/correos*']],
        ],
    ];
    $activa = fn (array $rutas) => collect($rutas)->contains(fn ($r) => request()->is($r));
@endphp
<body x-data="{ menuCuenta: false }">
    <div class="dt-app">
        <aside class="dt-side" aria-label="Menú del Centro de mando">
            <div class="dt-brand"><i>D</i><span>dauntoque<small class="ad-marca">Centro de mando</small></span></div>
            @foreach ($grupos as $grupo => $items)
                <div class="dt-small" style="margin-top:{{ $loop->first ? '2px' : '12px' }}">{{ $grupo }}</div>
                @foreach ($items as [$ruta, $nombre, $icono, $rutas])
                    <a href="/{{ $ruta }}" class="dt-nav {{ $activa($rutas) ? 'on' : '' }}" @if ($activa($rutas)) aria-current="page" @endif>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icono !!}</svg>{{ $nombre }}
                    </a>
                @endforeach
            @endforeach

            <div style="margin-top:auto">
                <div class="dt-sep"></div>
                <a href="/dashboard" class="dt-nav">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>Volver a mi panel
                </a>
                <a href="https://dauntoque.com/" target="_blank" rel="noopener" class="dt-nav">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M14 4h6v6M10 14L20 4M19 13v6a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1h6"/></svg>Ver el sitio de ventas
                </a>
            </div>
        </aside>

        <div class="dt-main">
            <header class="dt-top">
                <div>
                    <div style="font-size:18px;font-weight:900;letter-spacing:-.02em">Centro de mando</div>
                    <div class="dt-sub" style="margin:0;font-size:12.5px">Ventas, pagos, clientes y marketing de DaUnToque.</div>
                </div>
                <div style="position:relative">
                    <button type="button" @click="menuCuenta = !menuCuenta" :aria-expanded="menuCuenta" class="dt-btn" style="border-radius:999px;padding:0 12px 0 4px;height:36px">
                        <span class="ad-ini">{{ mb_strtoupper(mb_substr($usuarioPanel->name ?? 'A', 0, 1)) }}</span>
                        <span>{{ \Illuminate\Support\Str::before($usuarioPanel->name ?? 'Administrador', ' ') }}</span>
                    </button>
                    <div x-show="menuCuenta" x-cloak @click.away="menuCuenta = false" class="dt-panel" style="position:absolute;right:0;top:44px;width:260px;z-index:60;box-shadow:var(--shadow-lg)">
                        <p style="margin:0;font-weight:600">{{ $usuarioPanel->name }}</p>
                        <p class="dt-sub" style="margin:0">{{ $usuarioPanel->email }}</p>
                        <div class="dt-sep" style="margin:12px 0 8px"></div>
                        <a href="/dashboard" class="dt-nav" style="padding:8px 10px">Volver a mi panel</a>
                        <a href="/dashboard/cuenta" class="dt-nav" style="padding:8px 10px">Mi cuenta y seguridad</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dt-nav" style="padding:8px 10px;width:100%;border:0;background:none;cursor:pointer;text-align:left">Cerrar sesión</button>
                        </form>
                    </div>
                </div>
            </header>

            {{-- En el celular el menú lateral se oculta: el módulo se recorre con esta barra deslizable --}}
            <nav class="ad-movil" aria-label="Centro de mando">
                @foreach ($grupos as $items)
                    @foreach ($items as [$ruta, $nombre, $icono, $rutas])
                        <a href="/{{ $ruta }}" class="{{ $activa($rutas) ? 'on' : '' }}">{{ $nombre }}</a>
                    @endforeach
                @endforeach
                <a href="/dashboard">Mi panel</a>
            </nav>

            <div class="dt-scroll">
                <div class="dt-page">
                    @yield('contenido')
                </div>
            </div>
        </div>
    </div>

    @if (session('status'))
        <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 5000)" class="dt-toast" role="status">{{ session('status') }}</div>
    @endif
    @if (session('error'))
        <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 7000)" class="dt-toast err" role="alert">{{ session('error') }}</div>
    @endif
</body>
</html>
