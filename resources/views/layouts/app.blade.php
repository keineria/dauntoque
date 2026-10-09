<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Panel · DaUnToque</title>

    {{-- Aplica el tema guardado antes de pintar, para que no parpadee --}}
    <script>
        (function () {
            var r = document.documentElement, modo = 'auto', tema = '';
            try { modo = localStorage.getItem('dt-mode') || 'auto'; tema = localStorage.getItem('dt-theme') || ''; } catch (e) {}
            if (tema) r.dataset.theme = tema;
            if (modo === 'dark' || (modo === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches)) r.dataset.mode = 'dark';
        })();
    </script>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>

    <style>
        /* Sistema visual del panel (mismo lenguaje que Kanba): tokens por tema y modo */
        :root {
            --sidebar-bg:#1e1f21; --sidebar-text:#e8e8e8; --sidebar-active:#3a3b3d; --sidebar-muted:#9a9b9d;
            --bg:#fff; --card:#fff; --surface:#f6f7f8; --border:#e6e8eb; --text:#1e1f21; --muted:#6d6e6f;
            --primary:#2f6fe4; --primary-soft:#e8f0fe; --success:#2eaa6e; --danger:#d6342a;
            --font: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color-scheme: light;
        }
        :root[data-theme="paper"]{--sidebar-bg:#2b241b;--sidebar-active:#40372b;--bg:#fffdf8;--card:#fffefb;--surface:#f6f1e7;--border:#ebe3d3;--text:#2b241b;--muted:#7a6f60}
        :root[data-theme="sage"]{--sidebar-bg:#1f2a22;--sidebar-active:#33423a;--bg:#fbfcfa;--card:#fff;--surface:#eef2ec;--border:#dde5da;--text:#1f2a22;--muted:#64735f}
        :root[data-theme="tide"]{--sidebar-bg:#102230;--sidebar-active:#1f3647;--bg:#fafcfe;--card:#fff;--surface:#eaf2f8;--border:#d8e4ee;--text:#14212c;--muted:#5c6f80}
        :root[data-theme="plum"]{--sidebar-bg:#2a1b25;--sidebar-active:#3f2b38;--bg:#fdfafc;--card:#fff;--surface:#f5edf2;--border:#e8dbe3;--text:#2a1b25;--muted:#7a6472}
        :root[data-theme="clay"]{--sidebar-bg:#2d1d14;--sidebar-active:#45301f;--bg:#fefaf7;--card:#fff;--surface:#f7ece4;--border:#ecdccf;--text:#2d1d14;--muted:#80675a}
        :root[data-theme="wisteria"]{--sidebar-bg:#1d1c2e;--sidebar-active:#302f48;--bg:#fbfbfe;--card:#fff;--surface:#efeff9;--border:#dfdff0;--text:#1d1c2e;--muted:#66658a}
        :root[data-mode="dark"]{color-scheme:dark;--sidebar-bg:#111214;--sidebar-active:#2a2b2e;--bg:#17181a;--card:#1f2023;--surface:#26272a;--border:#34363a;--text:#e9eaec;--muted:#9b9da1;--primary-soft:#1d2a44}
        :root[data-mode="dark"][data-theme="paper"]{--sidebar-bg:#15120e;--sidebar-active:#2e271e;--bg:#1c1914;--card:#24201a;--surface:#2c2720;--border:#3b342a;--text:#efe7da;--muted:#a89c8a}
        :root[data-mode="dark"][data-theme="sage"]{--sidebar-bg:#0f1310;--sidebar-active:#243027;--bg:#151a16;--card:#1c231e;--surface:#232b25;--border:#313b33;--text:#e3ebe3;--muted:#97a597}
        :root[data-mode="dark"][data-theme="tide"]{--sidebar-bg:#0a1218;--sidebar-active:#1a2c3a;--bg:#0f1820;--card:#15212b;--surface:#1b2935;--border:#283846;--text:#e0ebf3;--muted:#8ea3b5}
        :root[data-mode="dark"][data-theme="plum"]{--sidebar-bg:#120d11;--sidebar-active:#2e222a;--bg:#1a1318;--card:#22191f;--surface:#2a1f27;--border:#3a2c35;--text:#f0e4ec;--muted:#a891a0}
        :root[data-mode="dark"][data-theme="clay"]{--sidebar-bg:#130d0a;--sidebar-active:#33251c;--bg:#1b1410;--card:#241a14;--surface:#2c2019;--border:#3d2d23;--text:#f2e5dc;--muted:#ad9384}
        :root[data-mode="dark"][data-theme="wisteria"]{--sidebar-bg:#0e0e17;--sidebar-active:#25253a;--bg:#14141f;--card:#1b1b29;--surface:#222233;--border:#2f2f45;--text:#e6e6f5;--muted:#9b9bbd}

        body { margin:0; background:var(--bg); color:var(--text); font:14px/1.5 var(--font); }
        [x-cloak] { display:none !important; }
        .dt-app { display:flex; height:100vh; overflow:hidden; }
        .dt-side { width:224px; flex:none; background:var(--sidebar-bg); color:var(--sidebar-text); padding:14px 10px; display:flex; flex-direction:column; gap:2px; overflow-y:auto; }
        .dt-brand { display:flex; align-items:center; gap:10px; padding:4px 8px 14px; font-weight:700; font-size:17px; letter-spacing:-.01em; color:#fff; }
        .dt-brand i { width:28px; height:28px; border-radius:7px; background:var(--primary); display:grid; place-items:center; font-style:normal; color:#fff; font-size:14px; }
        .dt-nav { display:flex; align-items:center; gap:10px; padding:7px 10px; border-radius:6px; color:var(--sidebar-text); font-size:13.5px; text-decoration:none; }
        .dt-nav svg { width:16px; height:16px; flex:none; opacity:.85; }
        .dt-nav:hover { background:var(--sidebar-active); }
        .dt-nav.on { background:var(--sidebar-active); font-weight:600; }
        .dt-nav .dot { width:8px; height:8px; border-radius:2px; flex:none; }
        .dt-nav .txt { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .dt-sep { height:1px; background:rgba(255,255,255,.08); margin:10px 4px; }
        .dt-small { font-size:11px; letter-spacing:.06em; text-transform:uppercase; color:var(--sidebar-muted); padding:4px 10px; }
        .dt-main { flex:1; min-width:0; display:flex; flex-direction:column; overflow:hidden; }
        .dt-top { height:56px; flex:none; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; gap:12px; padding:0 28px; position:relative; }
        .dt-scroll { flex:1; overflow-y:auto; }
        .dt-page { padding:28px 32px 64px; max-width:1180px; }
        .dt-h1 { font-size:24px; font-weight:700; margin:0; letter-spacing:-.01em; }
        .dt-sub { color:var(--muted); margin:4px 0 0; font-size:13.5px; }
        .dt-head { display:flex; flex-wrap:wrap; align-items:flex-end; justify-content:space-between; gap:16px; margin-bottom:22px; }
        .dt-panel { border:1px solid var(--border); border-radius:12px; background:var(--card); padding:20px; }
        .dt-panel + .dt-panel { margin-top:18px; }
        .dt-panel-h { display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; gap:12px; margin-bottom:16px; }
        .dt-panel-h h2 { font-size:14px; font-weight:600; margin:0; }
        .dt-btn { height:34px; border-radius:7px; border:1px solid var(--border); background:var(--card); color:var(--text); padding:0 14px; font-weight:600; font-size:13px; display:inline-flex; align-items:center; justify-content:center; gap:6px; text-decoration:none; cursor:pointer; white-space:nowrap; }
        .dt-btn:hover { background:var(--surface); }
        .dt-btn.pri { background:var(--primary); border-color:var(--primary); color:#fff; }
        .dt-btn.pri:hover { filter:brightness(1.07); }
        .dt-btn.danger { color:var(--danger); }
        .dt-btn[disabled] { opacity:.5; cursor:not-allowed; }
        .dt-seg { display:inline-flex; background:var(--surface); border:1px solid var(--border); border-radius:999px; padding:3px; }
        .dt-seg button { border:0; background:none; border-radius:999px; padding:5px 14px; font-size:13px; color:var(--muted); cursor:pointer; }
        .dt-seg button.on { background:var(--card); color:var(--text); font-weight:600; box-shadow:0 1px 2px rgba(0,0,0,.08); }
        .dt-label { display:block; font-size:12px; font-weight:600; color:var(--muted); margin-bottom:6px; }
        .dt-input { width:100%; height:38px; border:1px solid var(--border); background:var(--card); color:var(--text); border-radius:7px; padding:0 12px; font-size:14px; outline:none; }
        textarea.dt-input { height:auto; padding:10px 12px; resize:vertical; }
        .dt-input:focus, .dt-inline:focus-within { border-color:var(--primary); box-shadow:0 0 0 3px var(--primary-soft); }
        .dt-inline { display:flex; align-items:center; border:1px solid var(--border); border-radius:7px; background:var(--card); overflow:hidden; }
        .dt-inline > span { padding:0 10px; font-size:12.5px; font-weight:600; color:var(--muted); white-space:nowrap; }
        .dt-inline input, .dt-inline select { border:0; background:transparent; color:var(--text); height:38px; padding:0 10px; outline:none; width:100%; font-size:14px; }
        .dt-inline select { width:auto; border-right:1px solid var(--border); }
        .dt-stat { border:1px solid var(--border); border-radius:12px; background:var(--card); padding:16px 18px; }
        .dt-stat b { display:block; font-size:28px; font-weight:700; letter-spacing:-.02em; font-variant-numeric:tabular-nums; }
        .dt-stat span { color:var(--muted); font-size:13px; }
        .dt-chip { display:inline-flex; align-items:center; gap:4px; font-size:11.5px; font-weight:600; padding:2px 8px; border-radius:999px; background:var(--surface); color:var(--muted); }
        .dt-chip.up { background:rgba(46,170,110,.12); color:var(--success); }
        .dt-chip.down { background:rgba(214,52,42,.1); color:var(--danger); }
        .dt-ico { width:30px; height:30px; border-radius:7px; border:1px solid var(--border); background:var(--card); display:grid; place-items:center; color:var(--muted); cursor:pointer; }
        .dt-ico:hover { color:var(--text); background:var(--surface); }
        .dt-ico svg { width:15px; height:15px; }
        .dt-toast { position:fixed; left:50%; bottom:24px; transform:translateX(-50%); background:#1e1f21; color:#fff; border-radius:8px; padding:9px 16px; font-size:13px; z-index:9000; box-shadow:0 8px 24px rgba(0,0,0,.2); }
        .dt-toast.err { background:var(--danger); }
        button:focus-visible, a:focus-visible { outline:2px solid var(--primary); outline-offset:2px; }

        /* Fichas de perfil (miniatura con el estilo propio de cada perfil) */
        .dt-tiles { display:grid; grid-template-columns:repeat(auto-fill, minmax(250px, 1fr)); gap:18px; }
        .dt-tile { display:flex; flex-direction:column; gap:8px; min-width:0; }
        .dt-thumb { position:relative; display:block; width:100%; aspect-ratio:16/10; border-radius:12px; overflow:hidden; border:2px solid transparent; padding:0; cursor:pointer; transition:transform .15s, border-color .15s; background:none; }
        .dt-tile:hover .dt-thumb { transform:translateY(-2px); }
        .dt-tile.sel .dt-thumb { border-color:var(--text); }
        .dt-check { position:absolute; right:8px; bottom:8px; width:24px; height:24px; border-radius:50%; background:#1e1f21; color:#fff; display:grid; place-items:center; border:2px solid #fff; }
        .dt-badge { position:absolute; left:8px; top:8px; font-size:10.5px; font-weight:700; letter-spacing:.04em; text-transform:uppercase; background:rgba(46,170,110,.95); color:#fff; border-radius:999px; padding:2px 8px; display:flex; align-items:center; gap:4px; }
        .dt-new { aspect-ratio:16/10; border:1.5px dashed var(--border); border-radius:12px; display:grid; place-items:center; color:var(--muted); background:var(--surface); text-align:center; cursor:pointer; }
        .dt-new:hover { color:var(--text); }
        .dt-styles { display:grid; grid-template-columns:repeat(auto-fill, minmax(150px, 1fr)); gap:14px; }
        .dt-styles .dt-thumb { aspect-ratio:16/11; }
        .dt-sw { width:26px; height:26px; border-radius:50%; border:2px solid var(--card); box-shadow:0 0 0 1px var(--border); cursor:pointer; padding:0; }
        .dt-sw.on { box-shadow:0 0 0 2px var(--text); }

        .mini { position:absolute; inset:0; display:flex; align-items:center; justify-content:center; padding:12px; }
        .mini .card { width:46%; height:92%; border-radius:10px; display:flex; flex-direction:column; align-items:center; padding:9px 8px; gap:5px; overflow:hidden; }
        .mini .av { width:30%; aspect-ratio:1; border-radius:50%; background:var(--acc); display:grid; place-items:center; color:#fff; font-weight:700; font-size:12px; overflow:hidden; flex:none; }
        .mini .av img { width:100%; height:100%; object-fit:cover; }
        .mini .hero { width:100%; aspect-ratio:1.4; border-radius:6px; background:var(--acc); overflow:hidden; flex:none; }
        .mini .hero img { width:100%; height:100%; object-fit:cover; }
        .mini .ln { height:5px; border-radius:9px; background:currentColor; opacity:.85; }
        .mini .ln.s { opacity:.35; height:4px; }
        .mini .row { display:grid; grid-template-columns:1fr 1fr; gap:4px; width:100%; margin-top:auto; }
        .mini .row i { height:9px; border-radius:var(--fb); background:currentColor; opacity:.16; }
        .mini .cta { width:100%; height:11px; border-radius:var(--fb); background:var(--acc); flex:none; }
        .mini .sp { width:28%; height:70%; margin-left:8px; border-radius:8px; display:flex; flex-direction:column; gap:5px; padding:8px; }
        .mini .sp i { height:22%; border-radius:5px; background:currentColor; opacity:.12; }
        .mini.solo .sp, .mini.solo .row { display:none; }
        .t-crystal { background:radial-gradient(120% 90% at 20% 0%, #24304a 0%, #0b0d12 60%); color:#fff; }
        .t-crystal .card, .t-crystal .sp { background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.08); }
        .t-matte { background:#0b0b0d; color:#f4f4f5; }
        .t-matte .card, .t-matte .sp { background:#141416; border:1px solid rgba(255,255,255,.04); }
        .t-luxury { background:#060606; color:#f3e7c9; }
        .t-luxury .card, .t-luxury .sp { background:#121212; border:1px solid var(--acc); }
        .t-corporativo { background:#eef2f7; color:#0f172a; }
        .t-corporativo .card, .t-corporativo .sp { background:#fff; border:1px solid #e2e8f0; }
        .t-sport { background:#111 radial-gradient(#2b3342 1px, transparent 1px) 0 0/9px 9px; color:#fff; }
        .t-sport .card, .t-sport .sp { background:#000; border-left:3px solid var(--acc); }
        .t-minimalista { background:#f4f4f4; color:#111; }
        .t-minimalista .card, .t-minimalista .sp { background:#fff; border:1px solid #eaeaea; }

        @media (max-width: 860px) {
            .dt-side { display:none; }
            .dt-page { padding:20px 16px 56px; }
            .dt-top { padding:0 16px; }
        }
    </style>
</head>
@php
    $usuarioPanel = Auth::user();
    $perfilesMenu = \App\Models\Profile::where('user_id', $usuarioPanel->id)->orderBy('id')->get(['id', 'nombre_perfil', 'design_config']);
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
            <a href="/dashboard/configuracion" class="dt-nav {{ request()->is('dashboard/configuracion') ? 'on' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 20h4L19 9l-4-4L4 16z"/></svg>Datos del perfil
            </a>

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
                <div style="font-weight:600" x-text="`Hola, ${nombreUsuario.split(' ')[0]}`"></div>
                <div style="position:relative">
                    <button @click="menuCuenta = !menuCuenta" class="dt-btn" style="border-radius:999px;padding:0 12px 0 4px;height:36px">
                        <span style="width:28px;height:28px;border-radius:50%;background:var(--primary);color:#fff;display:grid;place-items:center;font-size:12px" x-text="nombreUsuario.charAt(0).toUpperCase()"></span>
                        <span x-text="nombreUsuario"></span>
                    </button>
                    <div x-show="menuCuenta" x-cloak @click.away="menuCuenta = false" class="dt-panel" style="position:absolute;right:0;top:44px;width:280px;z-index:60;box-shadow:0 12px 32px rgba(0,0,0,.12)">
                        <p class="dt-label" style="margin:0">Cuenta</p>
                        <p style="margin:4px 0 0;font-weight:600" x-text="nombreUsuario"></p>
                        <p class="dt-sub" style="margin:0" x-text="correoUsuario"></p>
                    </div>
                </div>
            </header>

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
