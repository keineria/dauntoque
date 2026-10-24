<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Panel · DaUnToque</title>

    @include('partials.panel-estilos')

    @vite('resources/css/panel.css')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>

</head>
@php
    $usuarioPanel = Auth::user();
    $perfilesMenu = \App\Models\Profile::where('user_id', $usuarioPanel->id)->orderBy('id')->get(['id', 'nombre_perfil', 'design_config']);
    $appsMenu = \App\Models\AppAdicional::where('user_id', $usuarioPanel->id)->pluck('app');
@endphp
<body x-data="{ menuCuenta: false, nombreUsuario: {{ Js::from($usuarioPanel->name ?? 'Usuario') }}, correoUsuario: {{ Js::from($usuarioPanel->email ?? '') }} }">
    <div class="dt-app">
        <aside class="dt-side" aria-label="Menú principal">
            <div class="dt-brand"><i>D</i>dauntoque</div>
            <a href="/dashboard" class="dt-nav {{ request()->is('dashboard') ? 'on' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 11l9-8 9 8v9a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1z"/></svg>Inicio
            </a>
            <a href="/dashboard/perfiles" class="dt-nav {{ request()->is('dashboard/perfiles') ? 'on' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="11" r="2.5"/><path d="M14 9h4M14 13h4M6 17h12"/></svg>Mis perfiles
            </a>
            <a href="/dashboard/analiticas" class="dt-nav {{ request()->is('dashboard/analiticas') ? 'on' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/></svg>Analíticas
            </a>
            <a href="/dashboard/apps" class="dt-nav {{ request()->is('dashboard/apps') ? 'on' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><path d="M17.5 14v7M14 17.5h7"/></svg>Ver apps
            </a>
            <a href="/dashboard/configuracion" class="dt-nav {{ request()->is('dashboard/configuracion') ? 'on' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 20h4L19 9l-4-4L4 16z"/></svg>Datos del perfil
            </a>
            @if ($usuarioPanel?->esAdmin())
                <a href="/admin" class="dt-nav {{ request()->is('admin') || request()->is('admin/*') ? 'on' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3l8 3v6c0 4.5-3.4 8.3-8 9-4.6-.7-8-4.5-8-9V6z"/><path d="M9 12l2 2 4-4"/></svg>Administrador
                </a>
            @endif

            @if($perfilesMenu->isNotEmpty())
                <div class="dt-sep"></div>
                <div class="dt-small">Perfiles</div>
                @foreach($perfilesMenu as $pm)
                    @php $dc = is_array($pm->design_config) ? $pm->design_config : (json_decode($pm->design_config ?? '', true) ?: []); @endphp
                    <a href="/editor?id={{ $pm->id }}" class="dt-nav">
                        <span class="dot" style="background: {{ preg_match('/^#[0-9a-fA-F]{3,8}$/', $dc['color_primario'] ?? '') ? $dc['color_primario'] : '#2f6fe4' }}"></span>
                        <span class="txt">{{ $pm->nombre_perfil ?: 'Mi Tarjeta' }}</span>
                    </a>
                @endforeach
            @endif

            @if($appsMenu->intersect(['agenda', 'calificame', 'musica'])->isNotEmpty())
                <div class="dt-sep"></div>
                <div class="dt-small">Apps activas</div>
                @if($appsMenu->contains('agenda'))
                    <a href="/dashboard/agenda" class="dt-nav {{ request()->is('dashboard/agenda*') ? 'on' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>Agenda
                    </a>
                @endif
                @if($appsMenu->contains('calificame'))
                    <a href="/dashboard/calificame" class="dt-nav {{ request()->is('dashboard/calificame*') ? 'on' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"><path d="M12 2.5l2.9 6.1 6.6.8-4.9 4.6 1.3 6.6L12 17.3l-5.9 3.3 1.3-6.6L2.5 9.4l6.6-.8z"/></svg>Califícame
                    </a>
                @endif
                @if($appsMenu->contains('musica'))
                    <a href="/dashboard/musica" class="dt-nav {{ request()->is('dashboard/musica*') ? 'on' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></svg>Música
                    </a>
                @endif
            @endif

            <div style="margin-top:auto">
                <div class="dt-sep"></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="dt-nav" style="width:100%;border:0;background:none;cursor:pointer;text-align:left">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 16l4-4-4-4M21 12H9M13 20H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h7"/></svg>Cerrar sesión
                    </button>
                </form>
            </div>
        </aside>

        <div class="dt-main">
            <header class="dt-top">
                <div><div style="font-size:20px;font-weight:900;letter-spacing:-.02em" x-text="`Hola, ${nombreUsuario.split(' ')[0]} 👋`"></div><div class="dt-sub" style="margin:0;font-size:12.5px">Tu centro de mando digital.</div></div>
                <div style="position:relative">
                    <button @click="menuCuenta = !menuCuenta" class="dt-btn" style="border-radius:999px;padding:0 12px 0 4px;height:36px">
                        <span style="width:30px;height:30px;border-radius:50%;background:linear-gradient(135deg,#3b82f6,#8b5cf6);color:#fff;display:grid;place-items:center;font-size:12px;font-weight:800;box-shadow:0 0 14px rgba(59,130,246,.45)" x-text="nombreUsuario.charAt(0).toUpperCase()"></span>
                        <span x-text="nombreUsuario"></span>
                    </button>
                    <div x-show="menuCuenta" x-cloak @click.away="menuCuenta = false" class="dt-panel" style="position:absolute;right:0;top:44px;width:280px;z-index:60;box-shadow:0 12px 32px rgba(0,0,0,.12)">
                        <p class="dt-label" style="margin:0">Configuración</p>
                        <p style="margin:4px 0 0;font-weight:600" x-text="nombreUsuario"></p>
                        <p class="dt-sub" style="margin:0" x-text="correoUsuario"></p>
                        <div class="dt-sep" style="margin:12px 0 8px"></div>
                        <a href="/dashboard/cuenta" class="dt-nav" style="padding:8px 10px">Mi cuenta y seguridad</a>
                        <a href="/dashboard/ayuda" class="dt-nav" style="padding:8px 10px">Ayuda y preguntas frecuentes</a>
                        @if ($usuarioPanel?->esAdmin())<a href="/admin" class="dt-nav" style="padding:8px 10px">Administrador</a>@endif
                    </div>
                </div>
            </header>

            <div class="dt-scroll">
                <div class="dt-page">
                    @yield('contenido')
                </div>
            </div>
        </div>

        {{-- Dentro de .dt-app para que las ventanas (z-index 80+) queden encima de la barra en el celular --}}
        <nav class="dt-tabbar" aria-label="Menú">
            <a href="/dashboard" class="{{ request()->is('dashboard') ? 'on' : '' }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 11l9-8 9 8v9a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1z"/></svg>Inicio</a>
            <a href="/dashboard/perfiles" class="{{ request()->is('dashboard/perfiles') ? 'on' : '' }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="11" r="2.5"/><path d="M14 9h4M14 13h4M6 17h12"/></svg>Perfiles</a>
            <a href="/dashboard/analiticas" class="{{ request()->is('dashboard/analiticas') ? 'on' : '' }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/></svg>Analíticas</a>
            <a href="/dashboard/apps" class="{{ request()->is('dashboard/apps') || request()->is('dashboard/agenda*') || request()->is('dashboard/calificame*') || request()->is('dashboard/musica*') ? 'on' : '' }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><path d="M17.5 14v7M14 17.5h7"/></svg>Apps</a>
            <a href="/dashboard/configuracion" class="{{ request()->is('dashboard/configuracion') ? 'on' : '' }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 20h4L19 9l-4-4L4 16z"/></svg>Datos</a>
        </nav>
    </div>


    @if (session('status'))
        <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 5000)" class="dt-toast" role="status">{{ session('status') }}</div>
    @endif
    @if (session('error'))
        <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 7000)" class="dt-toast err" role="alert">{{ session('error') }}</div>
    @endif
</body>
</html>
