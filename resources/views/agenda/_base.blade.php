{{-- Página pública de la agenda (reserva, cita, Mis citas) y de Califícame: ligera, primero celular, con el color del perfil. --}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>@yield('titulo') · DaUnToque</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
    <style>
        :root { --acc:{{ $negocio['color'] ?? '#3b82f6' }}; --bg:#0b0d12; --card:#14171f; --line:rgba(255,255,255,.09); --text:#f4f5f7; --muted:#9aa1ad; --ok:#22c55e; --warn:#f59e0b; --bad:#ef4444; color-scheme:dark; }
        * { box-sizing:border-box; }
        body { margin:0; min-height:100vh; background:var(--bg) radial-gradient(90% 40% at 50% 0%, color-mix(in srgb, var(--acc) 22%, transparent), transparent 70%); color:var(--text); font:15px/1.5 Inter, ui-sans-serif, system-ui, -apple-system, "Segoe UI", sans-serif; }
        [x-cloak] { display:none !important; }
        .ag { max-width:520px; margin:0 auto; padding:24px 16px 56px; }
        .ag-neg { display:flex; align-items:center; gap:12px; margin-bottom:20px; text-decoration:none; color:inherit; }
        .ag-av { width:52px; height:52px; border-radius:50%; background:var(--acc); display:grid; place-items:center; font-weight:800; font-size:20px; overflow:hidden; flex:none; color:#fff; }
        .ag-av img { width:100%; height:100%; object-fit:cover; }
        .ag-neg b { display:block; font-size:17px; }
        .ag-neg small { color:var(--muted); }
        h1 { font-size:24px; line-height:1.2; margin:0 0 6px; letter-spacing:-.02em; }
        h2 { font-size:13px; text-transform:uppercase; letter-spacing:.1em; color:var(--muted); margin:22px 0 10px; font-weight:700; }
        .ag-card { background:var(--card); border:1px solid var(--line); border-radius:18px; padding:16px; }
        .ag-op { display:flex; justify-content:space-between; align-items:center; gap:12px; width:100%; text-align:left; background:var(--card); border:1px solid var(--line); border-radius:16px; padding:14px 16px; color:inherit; font:inherit; cursor:pointer; margin-bottom:10px; }
        .ag-op:hover, .ag-op.on { border-color:var(--acc); box-shadow:0 0 0 1px var(--acc); }
        .ag-op small { display:block; color:var(--muted); font-size:13px; }
        .ag-dias { display:flex; gap:8px; overflow-x:auto; padding-bottom:6px; scrollbar-width:thin; }
        .ag-dia { flex:none; width:64px; padding:10px 4px; border-radius:14px; border:1px solid var(--line); background:var(--card); color:inherit; font:inherit; text-align:center; cursor:pointer; }
        .ag-dia small { display:block; font-size:11px; color:var(--muted); text-transform:uppercase; }
        .ag-dia b { display:block; font-size:20px; }
        .ag-dia.on { background:var(--acc); border-color:var(--acc); color:#fff; }
        .ag-dia.on small { color:rgba(255,255,255,.85); }
        .ag-horas { display:grid; grid-template-columns:repeat(auto-fill, minmax(92px, 1fr)); gap:8px; }
        .ag-hora { height:44px; border-radius:12px; border:1px solid var(--line); background:var(--card); color:inherit; font:600 14px Inter, sans-serif; cursor:pointer; }
        .ag-hora.on, .ag-hora:hover { border-color:var(--acc); background:color-mix(in srgb, var(--acc) 22%, var(--card)); }
        label { display:block; font-size:13px; font-weight:600; margin:14px 0 6px; }
        .ag-in { width:100%; height:48px; border-radius:12px; border:1px solid var(--line); background:#0d1016; color:var(--text); padding:0 14px; font:inherit; outline:none; }
        textarea.ag-in { height:auto; padding:12px 14px; resize:vertical; }
        .ag-in:focus { border-color:var(--acc); box-shadow:0 0 0 3px color-mix(in srgb, var(--acc) 30%, transparent); }
        .ag-btn { display:flex; align-items:center; justify-content:center; gap:8px; width:100%; height:52px; border-radius:14px; border:0; background:var(--acc); color:#fff; font:700 15px Inter, sans-serif; cursor:pointer; text-decoration:none; margin-top:16px; }
        .ag-btn.sec { background:transparent; border:1px solid var(--line); color:var(--text); }
        .ag-btn.wa { background:#25d366; color:#06210f; }
        .ag-btn.bad { background:transparent; border:1px solid rgba(239,68,68,.5); color:#f87171; }
        .ag-btn[disabled] { opacity:.5; cursor:wait; }
        .ag-row { display:flex; gap:10px; }
        .ag-row > * { flex:1; }
        .ag-muted { color:var(--muted); font-size:13.5px; }
        .ag-aviso { border-radius:14px; padding:12px 14px; margin-bottom:16px; font-size:14px; background:color-mix(in srgb, var(--ok) 15%, transparent); border:1px solid color-mix(in srgb, var(--ok) 45%, transparent); }
        .ag-aviso.err { background:color-mix(in srgb, var(--bad) 15%, transparent); border-color:color-mix(in srgb, var(--bad) 45%, transparent); }
        .ag-chip { display:inline-block; font-size:12px; font-weight:700; padding:3px 10px; border-radius:999px; background:rgba(255,255,255,.08); }
        .ag-chip.confirmada { background:rgba(34,197,94,.16); color:#4ade80; }
        .ag-chip.pendiente, .ag-chip.cambio { background:rgba(245,158,11,.16); color:#fbbf24; }
        .ag-chip.cancelada { background:rgba(239,68,68,.16); color:#f87171; }
        .ag-resumen { display:flex; justify-content:space-between; gap:10px; align-items:center; }
        .ag-link { color:var(--acc); text-decoration:none; font-weight:600; font-size:14px; background:none; border:0; cursor:pointer; padding:0; font-family:inherit; }
        .ag-pie { text-align:center; margin-top:28px; font-size:12.5px; color:var(--muted); }
        .ag-pie a { color:var(--muted); }
        .ag-oculto { position:absolute; left:-9999px; width:1px; height:1px; overflow:hidden; }
    </style>
</head>
<body>
<main class="ag">
    @isset($negocio)
        <a class="ag-neg" href="{{ $urlPerfil ?? '#' }}">
            <span class="ag-av">@if($negocio['foto'])<img src="{{ $negocio['foto'] }}" alt="">@else{{ mb_strtoupper(mb_substr($negocio['nombre'], 0, 1)) }}@endif</span>
            <span><b>{{ $negocio['nombre'] }}</b>@if($negocio['profesion'])<small>{{ $negocio['profesion'] }}</small>@endif</span>
        </a>
    @endisset
    @yield('contenido')
    @hasSection('pie')
        @yield('pie')
    @else
        <p class="ag-pie"><a href="/mis-citas">{{ __('agenda.mis_citas') }}</a> · DaUnToque</p>
    @endif
</main>
</body>
</html>
