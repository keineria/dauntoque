<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $titulo ?? 'Acceso' }} · DaUnToque</title>
    <meta name="theme-color" content="#050507">

    @include('partials.panel-estilos')
    @vite('resources/css/panel.css')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>

    <style>
        /* Acceso: presentación de la marca a la izquierda, formulario de vidrio a la derecha (una columna en el celular) */
        .ac { position:relative; z-index:1; min-height:100vh; display:grid; grid-template-columns:minmax(0, 1.05fr) minmax(0, 1fr); }
        .ac-marca { position:relative; overflow:hidden; padding:48px 56px; display:flex; flex-direction:column; justify-content:space-between; border-right:1px solid var(--border);
                    background:radial-gradient(80% 60% at 20% 10%, rgba(37,99,235,.18), transparent 60%), radial-gradient(70% 60% at 90% 90%, rgba(124,58,237,.16), transparent 60%); }
        .ac-marca h1 { margin:0; font-size:44px; line-height:1.05; font-weight:900; letter-spacing:-.035em; max-width:13ch; }
        .ac-marca h1 span { background:linear-gradient(90deg, #60a5fa, #a78bfa); -webkit-background-clip:text; background-clip:text; color:transparent; }
        .ac-marca p { color:var(--muted); font-size:15px; max-width:40ch; margin:14px 0 0; }
        .ac-ventajas { display:flex; flex-direction:column; gap:12px; margin-top:28px; }
        .ac-ventajas div { display:flex; align-items:center; gap:12px; font-size:14px; font-weight:600; }
        .ac-ventajas i { width:34px; height:34px; border-radius:11px; display:grid; place-items:center; background:rgba(59,130,246,.14); color:#60a5fa; border:1px solid rgba(59,130,246,.25); font-style:normal; flex:none; }

        /* Tarjeta NFC flotante con ondas */
        .ac-escena { position:relative; height:250px; margin:28px 0; }
        .ac-tarjeta { position:absolute; left:8%; top:24px; width:300px; aspect-ratio:1.6; border-radius:20px; padding:22px; color:#fff;
                      background:linear-gradient(135deg, #111827 0%, #1e1b4b 55%, #2563eb 130%); border:1px solid rgba(255,255,255,.12);
                      box-shadow:0 40px 80px -30px rgba(37,99,235,.55), inset 0 1px 0 rgba(255,255,255,.12); transform:rotate(-8deg); animation:ac-flotar 6s ease-in-out infinite; }
        .ac-tarjeta b { font-size:20px; letter-spacing:-.02em; } .ac-tarjeta small { display:block; color:#a5b4fc; font-size:11px; letter-spacing:.14em; text-transform:uppercase; margin-top:4px; }
        .ac-chip { position:absolute; left:22px; bottom:22px; width:40px; height:30px; border-radius:6px; background:linear-gradient(135deg, #fde68a, #b45309); opacity:.9; }
        .ac-ondas { position:absolute; right:22px; bottom:18px; width:46px; height:46px; color:#93c5fd; }
        .ac-telefono { position:absolute; left:calc(8% + 250px); top:0; width:120px; height:230px; border-radius:26px; background:#0b0b10; border:6px solid #1f2937;
                       box-shadow:0 30px 60px -20px rgba(0,0,0,.8); overflow:hidden; display:flex; flex-direction:column; align-items:center; padding:28px 10px 10px; gap:8px; animation:ac-flotar 6s ease-in-out infinite reverse; }
        .ac-telefono .av { width:44px; height:44px; border-radius:50%; background:linear-gradient(135deg, #3b82f6, #8b5cf6); box-shadow:0 0 18px rgba(99,102,241,.6); }
        .ac-telefono i { display:block; height:7px; border-radius:9px; background:rgba(255,255,255,.15); }
        .ac-telefono .bt { width:100%; height:18px; border-radius:7px; background:#2563eb; box-shadow:0 0 14px rgba(37,99,235,.6); margin-top:auto; }
        .ac-pulso { position:absolute; left:calc(8% + 236px); top:110px; width:36px; height:36px; border-radius:50%; border:2px solid rgba(96,165,250,.7); animation:ac-pulso 2s ease-out infinite; }
        .ac-pulso.b { animation-delay:.7s; }
        @keyframes ac-flotar { 0%,100% { translate:0 0; } 50% { translate:0 -10px; } }
        @keyframes ac-pulso { 0% { transform:scale(.4); opacity:1; } 100% { transform:scale(2.6); opacity:0; } }

        .ac-lado { display:flex; align-items:center; justify-content:center; padding:40px 24px; }
        .ac-form { width:100%; max-width:420px; }
        .ac-form .dt-panel { padding:30px 28px; border-radius:28px; box-shadow:0 40px 80px -40px rgba(0,0,0,.8); }
        .ac-lado .ac-logo { display:none; }
        .ac-logo { display:flex; align-items:center; gap:10px; font-weight:800; font-size:20px; letter-spacing:-.02em; margin-bottom:26px; text-decoration:none; color:#fff; }
        .ac-logo i { width:36px; height:36px; border-radius:10px; background:linear-gradient(135deg, var(--primary), var(--primary-2)); display:grid; place-items:center; font-style:normal; box-shadow:0 8px 20px -6px rgba(99,102,241,.6); }
        .ac-form h2 { margin:0; font-size:26px; font-weight:900; letter-spacing:-.02em; }
        .ac-form .dt-sub { margin:6px 0 24px; }
        .ac-campo { margin-bottom:16px; }
        .ac-error { margin:6px 0 0; padding:0; list-style:none; color:#f87171; font-size:12.5px; font-weight:600; }
        .ac-aviso { margin:0 0 18px; padding:12px 14px; border-radius:14px; font-size:13px; font-weight:600; background:rgba(34,197,94,.1); border:1px solid rgba(34,197,94,.35); color:#86efac; }
        .ac-ojo { border:0; background:none; color:var(--muted); padding:0 12px; cursor:pointer; height:44px; display:grid; place-items:center; }
        .ac-ojo:hover { color:#fff; }
        .ac-enviar { width:100%; height:48px; font-size:14.5px; border-radius:14px; margin-top:6px; }
        .ac-pie { margin-top:20px; text-align:center; font-size:13.5px; color:var(--muted); }
        .ac-pie a, .ac-link { color:#60a5fa; font-weight:700; text-decoration:none; }
        .ac-pie a:hover, .ac-link:hover { text-decoration:underline; }
        .ac-check { display:flex; align-items:center; gap:8px; font-size:13px; color:var(--muted); cursor:pointer; }
        .ac-check input { width:16px; height:16px; accent-color:#3b82f6; }

        @media (max-width: 960px) {
            .ac { grid-template-columns:1fr; }
            .ac-marca { display:none; }
            .ac-lado .ac-logo { display:flex; }
            .ac-lado { min-height:100vh; padding:28px 16px; align-items:flex-start; padding-top:max(28px, env(safe-area-inset-top)); }
            .ac-form .dt-panel { padding:26px 20px; }
        }
        @media (prefers-reduced-motion: reduce) { .ac-tarjeta, .ac-telefono, .ac-pulso { animation:none; } }
    </style>
</head>
<body>
    <div class="ac">
        <aside class="ac-marca" aria-hidden="true">
            <div class="ac-logo" style="margin:0"><i>D</i>dauntoque</div>
            <div>
                <h1>Tu tarjeta de presentación, <span>a un toque.</span></h1>
                <p>Acerca tu tarjeta NFC a cualquier celular y comparte tu contacto, tus redes y tus pagos al instante.</p>
                <div class="ac-escena">
                    <div class="ac-tarjeta">
                        <b>Tu nombre</b><small>Tu profesión</small>
                        <span class="ac-chip"></span>
                        <svg class="ac-ondas" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M6 8a8 8 0 0 1 0 8M9.5 6a12 12 0 0 1 0 12M13 4a16 16 0 0 1 0 16"/></svg>
                    </div>
                    <span class="ac-pulso"></span><span class="ac-pulso b"></span>
                    <div class="ac-telefono"><span class="av"></span><i style="width:70%"></i><i style="width:50%"></i><span class="bt"></span></div>
                </div>
                <div class="ac-ventajas">
                    <div><i>⚡</i>Comparte tu perfil con un solo toque</div>
                    <div><i>📇</i>Tus clientes guardan tu contacto al instante</div>
                    <div><i>📈</i>Mide visitas y clics de tu tarjeta</div>
                </div>
            </div>
            <small style="color:var(--sidebar-muted)">© {{ date('Y') }} DaUnToque</small>
        </aside>

        <main class="ac-lado">
            <div class="ac-form">
                <a class="ac-logo" href="https://dauntoque.com"><i>D</i>dauntoque</a>
                <div class="dt-panel">
                    {{ $slot }}
                </div>
            </div>
        </main>
    </div>
</body>
</html>
