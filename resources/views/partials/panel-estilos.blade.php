{{-- Sistema visual compartido del panel y el editor: componentes dt-* + miniaturas.
     Colores en variables: la base (:root) es DaUnToque oscuro (vidrio, brillos azul y violeta); las demás paletas
     y el modo claro salen de config/apariencia.php según lo que el usuario eligió en Mi cuenta → Apariencia. --}}
@php $aparienciaPanel = \App\Support\Apariencia::de(Auth::user()); @endphp
    <script>
        // Paleta y modo antes de pintar (sin parpadeo). "auto" sigue el modo claro/oscuro del celular o computador.
        (function (raiz) {
            var oscuro = window.matchMedia ? window.matchMedia('(prefers-color-scheme: dark)') : null;
            function esquema() { raiz.dataset.esquema = raiz.dataset.modo === 'auto' ? (oscuro && oscuro.matches ? 'oscuro' : 'claro') : raiz.dataset.modo; }
            window.dtApariencia = function (paleta, modo) { raiz.dataset.paleta = paleta; raiz.dataset.modo = modo; esquema(); };
            window.dtApariencia(@json($aparienciaPanel['paleta']), @json($aparienciaPanel['modo']));
            if (oscuro && oscuro.addEventListener) oscuro.addEventListener('change', esquema);
        })(document.documentElement);
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --sidebar-bg:rgba(12,12,16,.78); --sidebar-text:#d1d5db; --sidebar-muted:#6b7280;
            --bg:#050507; --card:rgba(20,20,26,.62); --card-solid:#121218; --surface:rgba(255,255,255,.04); --border:rgba(255,255,255,.07); --text:#f5f5f7; --muted:#9ca3af;
            --primary:#3b82f6; --primary-2:#8b5cf6; --on-primary:#fff; --link:#60a5fa; --success:#22c55e; --danger:#ef4444;
            --primary-soft:color-mix(in srgb, var(--primary) 15%, transparent); --primary-line:color-mix(in srgb, var(--primary) 40%, transparent);
            --glow:0 0 22px rgba(59,130,246,.35); --orb1:rgba(37,99,235,.13); --orb2:rgba(124,58,237,.12);
            /* Tonos que cambian entre claro y oscuro */
            --hover:rgba(255,255,255,.05); --hover-2:rgba(255,255,255,.09); --field:rgba(0,0,0,.5); --field-border:#1f2937; --seg:rgba(0,0,0,.45); --seg-on:rgba(255,255,255,.1);
            --stat-bg:linear-gradient(145deg, rgba(30,30,36,.85), rgba(14,14,18,.85)); --shadow:0 16px 40px rgba(0,0,0,.45); --overlay:rgba(0,0,0,.6);
            --ok-text:#4ade80; --warn-text:#fbbf24; --danger-text:#f87171;
            --font:"Inter", ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            color-scheme:dark;
        }
        /* Paletas sólidas (todas menos DaUnToque oscuro): sin vidrio */
        html[data-esquema="oscuro"]:not([data-paleta="dauntoque"]) { --stat-bg:var(--card); --field:rgba(0,0,0,.22); --field-border:var(--border); --seg:rgba(0,0,0,.25); }
        html[data-esquema="claro"] {
            --hover:rgba(0,0,0,.04); --hover-2:rgba(0,0,0,.07); --field:var(--card); --field-border:var(--border); --seg:var(--surface); --seg-on:var(--card);
            --stat-bg:var(--card); --shadow:0 16px 40px rgba(20,20,30,.12); --overlay:rgba(20,20,30,.4);
            --ok-text:#15803d; --warn-text:#b45309; --danger-text:#dc2626; --success:#16a34a; --danger:#dc2626;
            color-scheme:light;
        }
        {!! \App\Support\Apariencia::css() !!}
        html[data-esquema="claro"] .dt-panel, html[data-esquema="claro"] .dt-stat { box-shadow:0 1px 2px rgba(20,20,30,.04); }
        html[data-esquema="claro"] .dt-seg button.on, html[data-esquema="claro"] .ad-tabs a.on { box-shadow:0 1px 3px rgba(20,20,30,.12); }
        body { margin:0; background:var(--bg); color:var(--text); font:14px/1.5 var(--font); }
        /* Brillos de fondo, como el panel original */
        body::before, body::after { content:""; position:fixed; width:520px; height:520px; border-radius:50%; filter:blur(120px); pointer-events:none; z-index:0; }
        body::before { top:-18%; left:-10%; background:var(--orb1); }
        body::after { bottom:-20%; right:-10%; background:var(--orb2); }
        [x-cloak] { display:none !important; }

        .dt-app { position:relative; z-index:1; display:flex; height:100vh; overflow:hidden; }
        .dt-side { width:240px; flex:none; background:var(--sidebar-bg); backdrop-filter:blur(20px); -webkit-backdrop-filter:blur(20px); border-right:1px solid var(--border); color:var(--sidebar-text); padding:18px 14px; display:flex; flex-direction:column; gap:4px; overflow-y:auto; }
        .dt-brand { display:flex; align-items:center; gap:10px; padding:2px 8px 18px; margin-bottom:6px; border-bottom:1px solid var(--border); font-weight:800; font-size:19px; letter-spacing:-.02em; color:var(--text); }
        .dt-brand i { width:32px; height:32px; border-radius:9px; background:linear-gradient(135deg, var(--primary), var(--primary-2)); display:grid; place-items:center; font-style:normal; color:var(--on-primary); font-size:15px; box-shadow:0 8px 20px -6px var(--primary-line); }
        .dt-nav { display:flex; align-items:center; gap:12px; padding:10px 14px; border-radius:12px; color:var(--muted); font-size:14px; font-weight:600; text-decoration:none; border:1px solid transparent; transition:background .15s, color .15s; }
        .dt-nav svg { width:18px; height:18px; flex:none; }
        .dt-nav:hover { background:var(--hover); color:var(--text); }
        .dt-nav.on { background:var(--primary-soft); color:var(--link); border-color:color-mix(in srgb, var(--primary) 22%, transparent); }
        .dt-nav .dot { width:9px; height:9px; border-radius:3px; flex:none; box-shadow:0 0 10px currentColor; }
        .dt-nav .txt { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; font-weight:500; }
        .dt-sep { height:1px; background:var(--border); margin:12px 4px; }
        .dt-small { font-size:10.5px; font-weight:700; letter-spacing:.14em; text-transform:uppercase; color:var(--sidebar-muted); padding:4px 14px; }
        .dt-main { flex:1; min-width:0; display:flex; flex-direction:column; overflow:hidden; }
        .dt-top { height:64px; flex:none; display:flex; align-items:center; justify-content:space-between; gap:12px; padding:0 32px; position:relative; }
        .dt-scroll { flex:1; overflow-y:auto; scrollbar-width:none; }
        .dt-scroll::-webkit-scrollbar { display:none; }
        .dt-page { padding:8px 36px 64px; max-width:1200px; }
        .dt-h1 { font-size:30px; font-weight:900; margin:0; letter-spacing:-.025em; }
        .dt-sub { color:var(--muted); margin:4px 0 0; font-size:13.5px; }
        .dt-head { display:flex; flex-wrap:wrap; align-items:flex-end; justify-content:space-between; gap:16px; margin-bottom:24px; }
        .dt-panel { border:1px solid var(--border); border-radius:24px; background:var(--card); backdrop-filter:blur(20px); -webkit-backdrop-filter:blur(20px); padding:22px; }
        .dt-panel + .dt-panel { margin-top:18px; }
        /* Cada panel es una capa propia (por el desenfoque): el que tiene un menú abierto o el foco pasa encima de los
           de abajo, para que los desplegables no queden tapados por la caja siguiente */
        .dt-panel:focus-within, .dt-panel:has(.dt-pop:not([style*="display: none"])) { position:relative; z-index:40; }
        .dt-pop { position:absolute; right:0; top:calc(100% + 6px); z-index:30; min-width:170px; padding:6px; border:1px solid var(--border); border-radius:14px; background:var(--card-solid); box-shadow:var(--shadow); }
        .dt-panel-h { display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; gap:12px; margin-bottom:18px; }
        .dt-panel-h h2 { font-size:15px; font-weight:800; margin:0; letter-spacing:-.01em; }
        .dt-btn { height:38px; border-radius:12px; border:1px solid var(--border); background:var(--hover); color:var(--text); padding:0 16px; font-weight:700; font-size:13px; display:inline-flex; align-items:center; justify-content:center; gap:6px; text-decoration:none; cursor:pointer; white-space:nowrap; transition:background .15s, box-shadow .15s, transform .15s; }
        .dt-btn:hover { background:var(--hover-2); }
        .dt-btn.pri { background:var(--primary); border-color:var(--primary); color:var(--on-primary); box-shadow:var(--glow); }
        .dt-btn.pri:hover { background:var(--primary); filter:brightness(1.12); }
        .dt-btn.danger { color:var(--danger-text); }
        .dt-btn[disabled] { opacity:.45; cursor:not-allowed; box-shadow:none; }
        .dt-seg { display:inline-flex; background:var(--seg); border:1px solid var(--border); border-radius:12px; padding:3px; }
        .dt-seg button { border:0; background:none; border-radius:9px; padding:6px 14px; font-size:12.5px; font-weight:600; color:var(--muted); cursor:pointer; }
        .dt-seg button.on { background:var(--seg-on); color:var(--text); }
        .dt-label { display:block; font-size:10.5px; font-weight:700; letter-spacing:.12em; text-transform:uppercase; color:var(--muted); margin-bottom:8px; }
        .dt-input { width:100%; height:44px; border:1px solid var(--field-border); background:var(--field); color:var(--text); border-radius:12px; padding:0 14px; font-size:14px; outline:none; transition:border-color .15s, box-shadow .15s; }
        textarea.dt-input { height:auto; padding:12px 14px; resize:vertical; }
        .dt-input:focus, .dt-inline:focus-within { border-color:var(--primary); box-shadow:0 0 0 3px var(--primary-soft); }
        .dt-inline { display:flex; align-items:center; border:1px solid var(--field-border); border-radius:12px; background:var(--field); overflow:hidden; }
        .dt-inline > span { padding:0 12px; font-size:12.5px; font-weight:700; color:var(--muted); white-space:nowrap; }
        .dt-inline input, .dt-inline select { border:0; background:transparent; color:var(--text); height:44px; padding:0 12px; outline:none; width:100%; font-size:14px; }
        .dt-inline select { width:auto; border-right:1px solid var(--field-border); background:var(--card-solid); }
        .dt-stat { position:relative; overflow:hidden; border:1px solid var(--border); border-radius:24px; background:var(--stat-bg); padding:20px 22px; transition:transform .25s, border-color .25s; }
        .dt-stat:hover { transform:translateY(-4px); border-color:var(--primary-line); }
        .dt-stat b { display:block; font-size:32px; font-weight:900; letter-spacing:-.03em; font-variant-numeric:tabular-nums; margin-top:8px; }
        .dt-stat span { color:var(--muted); font-size:13px; }
        .dt-stat .ic { width:38px; height:38px; border-radius:50%; display:grid; place-items:center; }
        .dt-chip { display:inline-flex; align-items:center; gap:4px; font-size:11px; font-weight:700; padding:3px 9px; border-radius:999px; background:var(--hover-2); color:var(--muted); }
        .dt-chip.up { background:rgba(34,197,94,.12); color:var(--ok-text); }
        .dt-chip.down { background:rgba(239,68,68,.12); color:var(--danger-text); }
        .dt-ico { width:34px; height:34px; border-radius:10px; border:1px solid var(--border); background:var(--field); display:grid; place-items:center; color:var(--muted); cursor:pointer; }
        .dt-ico:hover { color:var(--text); border-color:var(--primary-line); }
        .dt-ico svg { width:15px; height:15px; }
        .dt-toast { position:fixed; left:50%; top:22px; transform:translateX(-50%); background:var(--card-solid); backdrop-filter:blur(14px); color:var(--text); border:1px solid rgba(34,197,94,.45); border-radius:16px; padding:12px 18px; font-size:13px; font-weight:700; z-index:9000; box-shadow:0 0 30px rgba(34,197,94,.18); }
        .dt-toast.err { border-color:rgba(239,68,68,.55); box-shadow:0 0 30px rgba(239,68,68,.2); }
        button:focus-visible, a:focus-visible { outline:2px solid var(--primary); outline-offset:2px; }

        /* Fichas de perfil (miniatura con el estilo propio de cada perfil) */
        .dt-tiles { display:grid; grid-template-columns:repeat(auto-fill, minmax(260px, 1fr)); gap:20px; }
        .dt-tile { display:flex; flex-direction:column; gap:10px; min-width:0; }
        .dt-thumb { position:relative; display:block; width:100%; aspect-ratio:16/10; border-radius:18px; overflow:hidden; border:1px solid var(--border); padding:0; cursor:pointer; transition:transform .2s, border-color .2s, box-shadow .2s; background:none; }
        .dt-tile:hover .dt-thumb { transform:translateY(-3px); border-color:var(--primary-line); }
        .dt-tile.sel .dt-thumb { border-color:var(--primary); box-shadow:0 0 0 1px var(--primary), 0 0 30px var(--primary-soft); }
        .dt-check { position:absolute; right:10px; bottom:10px; width:26px; height:26px; border-radius:50%; background:var(--primary); color:var(--on-primary); display:grid; place-items:center; box-shadow:0 0 14px var(--primary-line); }
        .dt-badge { position:absolute; left:10px; top:10px; font-size:10px; font-weight:800; letter-spacing:.06em; text-transform:uppercase; background:#22c55e; color:#04130a; border-radius:999px; padding:3px 9px; display:flex; align-items:center; gap:4px; box-shadow:0 0 16px rgba(34,197,94,.45); }
        .dt-new { aspect-ratio:16/10; border:1.5px dashed var(--border); border-radius:18px; display:grid; place-items:center; color:var(--muted); background:var(--surface); text-align:center; cursor:pointer; transition:border-color .2s, color .2s; }
        .dt-new:hover { color:var(--text); border-color:var(--primary); }
        .dt-styles { display:grid; grid-template-columns:repeat(auto-fill, minmax(150px, 1fr)); gap:14px; }
        .dt-styles .dt-thumb { aspect-ratio:16/11; }
        .dt-sw { width:28px; height:28px; border-radius:50%; border:2px solid var(--card-solid); box-shadow:0 0 0 1px var(--border); cursor:pointer; padding:0; }
        .dt-sw.on { box-shadow:0 0 0 2px var(--text); }
        /* Administrador: tarjetas de números y tablas */
        .ad-tabs { margin-bottom:20px; flex-wrap:wrap; }
        .ad-tabs a { border-radius:9px; padding:6px 14px; font-size:12.5px; font-weight:600; color:var(--muted); text-decoration:none; }
        .ad-tabs a.on { background:var(--seg-on); color:var(--text); }
        .ad-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(210px, 1fr)); gap:14px; }
        .ad-grid .dt-stat small { display:block; color:var(--muted); font-size:12px; margin-top:4px; }
        .ad-link { color:inherit; text-decoration:none; }
        .ad-tabla { display:flex; flex-direction:column; }
        .ad-fila { display:grid; grid-template-columns:minmax(0, 2fr) minmax(0, 1fr) minmax(0, 1.2fr); gap:12px; align-items:center; padding:11px 0; border-top:1px solid var(--border); font-size:13.5px; }
        .ad-fila:first-child { border-top:0; }
        .ad-fila small { display:block; color:var(--muted); font-size:12px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .ad-fila > span { min-width:0; }
        @media (max-width: 640px) { .ad-fila { grid-template-columns:1fr; gap:4px; } .ad-fila > span[style] { text-align:left !important; } }
        .ad-link.ad-on { border-color:var(--primary-line); box-shadow:0 0 0 1px var(--primary-line); }
        .ad-filtros { display:flex; flex-wrap:wrap; gap:10px; align-items:center; margin-bottom:6px; }
        .ad-scroll { overflow-x:auto; margin-top:10px; }
        .ad-t { width:100%; border-collapse:collapse; font-size:13.5px; min-width:860px; }
        .ad-t th { text-align:left; font-size:12px; font-weight:600; color:var(--muted); padding:8px 10px; white-space:nowrap; }
        .ad-t td { padding:11px 10px; border-top:1px solid var(--border); vertical-align:top; }
        .ad-t small { display:block; color:var(--muted); font-size:12px; margin-top:2px; }
        .ad-t code { font-family:ui-monospace,Consolas,monospace; font-size:12.5px; }
        .ad-t a { color:var(--link); text-decoration:none; }
        .ad-t a.dt-btn { color:inherit; }
        .ad-t tbody tr.ad-clic { cursor:pointer; transition:background .15s; }
        .ad-t tbody tr.ad-clic:hover { background:var(--hover); }
        .ad-t td a.ad-nombre { color:var(--text); font-weight:700; }
        .ad-t td a.ad-nombre:hover { color:var(--link); }
        .ad-volver { display:inline-block; margin-bottom:10px; color:var(--muted); font-size:13px; text-decoration:none; }
        .ad-volver:hover { color:var(--text); }
        .ad-avatar { width:52px; height:52px; flex:none; border-radius:50%; display:grid; place-items:center; font-size:22px; font-weight:800; color:var(--on-primary); background:linear-gradient(135deg, var(--primary), var(--primary-2)); box-shadow:0 0 18px var(--primary-soft); }
        .ad-ficha { display:grid; grid-template-columns:minmax(0, 1.1fr) minmax(0, 1fr); gap:18px; align-items:start; }
        .ad-ficha-col { display:flex; flex-direction:column; gap:18px; min-width:0; }
        .ad-tarjeta { padding:14px; border:1px solid var(--border); border-radius:14px; display:grid; gap:12px; }
        .ad-tarjeta + .ad-tarjeta { margin-top:12px; }
        .ad-datos { display:grid; grid-template-columns:auto 1fr; gap:4px 14px; margin:0; font-size:13px; }
        .ad-datos dt { color:var(--muted); }
        .ad-datos dd { margin:0; overflow-wrap:anywhere; }
        .ad-vence { display:flex; flex-wrap:wrap; gap:10px; align-items:flex-end; }
        .ad-mini { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:10px; }
        .ad-mini div { padding:12px; border:1px solid var(--border); border-radius:12px; display:flex; flex-direction:column; gap:2px; }
        .ad-mini b { font-size:22px; }
        .ad-mini span { color:var(--muted); font-size:12px; }
        @media (max-width: 900px) { .ad-ficha { grid-template-columns:1fr; } }
        /* Chat de ayuda */
        .sp-chat { display:flex; flex-direction:column; gap:12px; }
        .sp-aviso { display:flex; flex-direction:column; gap:2px; padding:12px 14px; border-radius:14px; background:var(--primary-soft); border:1px solid var(--primary-line); font-size:13.5px; }
        .sp-mensajes { display:flex; flex-direction:column; gap:10px; min-height:220px; max-height:55vh; overflow-y:auto; padding:4px; }
        .sp-msj { max-width:78%; align-self:flex-start; background:var(--hover-2); border:1px solid var(--border); border-radius:16px 16px 16px 4px; padding:10px 12px; }
        .sp-msj.mio { align-self:flex-end; background:var(--primary-soft); border-color:var(--primary-line); border-radius:16px 16px 4px 16px; }
        .sp-msj p { margin:0; white-space:pre-wrap; overflow-wrap:anywhere; font-size:14px; }
        .sp-msj small { display:block; margin-top:4px; color:var(--muted); font-size:11px; }
        .sp-escribir { display:flex; gap:8px; align-items:flex-end; }
        .sp-escribir textarea { resize:none; }
        /* Tipografías del perfil */
        .dt-fuentes { display:grid; grid-template-columns:repeat(auto-fill, minmax(112px, 1fr)); gap:10px; }
        .dt-fuente { display:flex; flex-direction:column; align-items:flex-start; gap:2px; padding:10px 12px; border:1px solid var(--border); border-radius:14px; background:var(--surface); color:var(--text); cursor:pointer; text-align:left; transition:border-color .15s, box-shadow .15s; }
        .dt-fuente b { font-size:26px; line-height:1.1; font-weight:700; }
        .dt-fuente span { font-size:12px; color:var(--muted); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:100%; }
        .dt-fuente:hover { border-color:var(--primary-line); }
        .dt-fuente.on { border-color:var(--primary); box-shadow:0 0 0 1px var(--primary), 0 0 20px var(--primary-soft); }

        /* Miniaturas del perfil público (mismos estilos y diseños que la página pública) */
        .mini { position:absolute; inset:0; display:flex; align-items:center; justify-content:center; padding:12px; }
        .mini .card { width:46%; height:92%; border-radius:10px; display:flex; flex-direction:column; align-items:center; padding:9px 8px; gap:5px; overflow:hidden; }
        .mini .av { width:30%; aspect-ratio:1; border-radius:50%; background:var(--acc); display:grid; place-items:center; color:#fff; font-weight:700; font-size:12px; overflow:hidden; flex:none; }
        .mini .av img, .mini .hero img { width:100%; height:100%; object-fit:cover; }
        .mini .hero { width:100%; aspect-ratio:1.4; border-radius:6px; background:var(--acc); overflow:hidden; flex:none; }
        .mini .ln { height:5px; border-radius:9px; background:currentColor; opacity:.85; }
        .mini .ln.s { opacity:.35; height:4px; }
        .mini .row { display:grid; grid-template-columns:1fr 1fr; gap:4px; width:100%; margin-top:auto; }
        .mini .row i { height:9px; border-radius:var(--fb); background:currentColor; opacity:.16; }
        .mini .stack { display:flex; flex-direction:column; gap:4px; width:100%; margin-top:auto; }
        .mini .stack i { height:9px; border-radius:var(--fb); background:currentColor; opacity:.16; }
        .mini .cta { width:100%; height:11px; border-radius:var(--fb); background:var(--acc); flex:none; }
        .mini .biz { width:100%; display:flex; gap:5px; align-items:center; padding:5px; border-radius:6px; background:rgba(127,127,127,.12); border-left:3px solid var(--acc); }
        .mini .biz .av { width:34%; border-radius:5px; }
        .mini .sp { width:28%; height:70%; margin-left:8px; border-radius:8px; display:flex; flex-direction:column; gap:5px; padding:8px; }
        .mini .sp i { height:22%; border-radius:5px; background:currentColor; opacity:.12; }
        .mini.solo .sp { display:none; }
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
        .t-onyx { background:#080808; color:#f5f5f4; }
        .t-onyx .card, .t-onyx .sp { background:#141414; border:1px solid rgba(255,255,255,.08); }
        .t-medianoche { background:radial-gradient(120% 90% at 50% 0%, #1e3a8a 0%, #060a14 60%); color:#f1f5f9; }
        .t-medianoche .card, .t-medianoche .sp { background:rgba(15,23,42,.8); border:1px solid rgba(148,163,184,.18); }
        .t-rose { background:radial-gradient(120% 90% at 50% 0%, #4a1d33 0%, #130b10 60%); color:#fdf2f8; }
        .t-rose .card, .t-rose .sp { background:rgba(34,19,28,.85); border:1px solid rgba(244,194,214,.16); }
        .t-esmeralda { background:radial-gradient(120% 90% at 50% 0%, #064e3b 0%, #05110d 60%); color:#ecfdf5; }
        .t-esmeralda .card, .t-esmeralda .sp { background:rgba(10,30,23,.85); border:1px solid rgba(167,243,208,.14); }
        .t-minimalista { background:#f4f4f4; color:#111; }
        .t-minimalista .card, .t-minimalista .sp { background:#fff; border:1px solid #eaeaea; }

        /* Barra de pestañas inferior: navegación del panel en el celular */
        .dt-tabbar { display:none; }
        @media (max-width: 860px) {
            .dt-side { display:none; }
            .dt-page { padding:4px 16px 100px; }
            .dt-top { padding:0 16px; height:58px; }
            .dt-h1 { font-size:26px; }
            .dt-tabbar { display:grid; grid-template-columns:repeat(5, 1fr); position:fixed; left:10px; right:10px; bottom:calc(10px + env(safe-area-inset-bottom, 0px)); z-index:50; background:color-mix(in srgb, var(--card-solid) 88%, transparent); backdrop-filter:blur(20px); -webkit-backdrop-filter:blur(20px); border:1px solid var(--border); border-radius:22px; padding:6px; box-shadow:var(--shadow); }
            .dt-tabbar a { display:flex; flex-direction:column; align-items:center; gap:3px; padding:7px 2px; border-radius:16px; color:var(--muted); text-decoration:none; font-size:10.5px; font-weight:700; }
            .dt-tabbar a svg { width:20px; height:20px; }
            .dt-tabbar a.on { color:var(--link); background:var(--primary-soft); }
            .dt-tiles { grid-template-columns:1fr; }
        }
    </style>
