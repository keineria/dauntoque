{{-- Sistema visual compartido del panel y el editor: temas de color (Da un Toque y paletas, claro u oscuro) + componentes dt-* + miniaturas --}}
    <script>
        // Tema del panel: "Automático" sigue el modo claro u oscuro del celular o computador (sin destello al cargar).
        // El selector de Mi cuenta lo llama al elegir para cambiar los colores al instante.
        window.dtTema = function (paleta, apariencia) {
            var d = document.documentElement;
            if (paleta) d.dataset.paleta = paleta;
            if (apariencia) d.dataset.apariencia = apariencia;
            if (d.dataset.apariencia === 'auto') d.dataset.modo = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'oscuro' : 'claro';
            else if (d.dataset.apariencia) d.dataset.modo = d.dataset.apariencia;
        };
        window.dtTema();
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function () { window.dtTema(); });
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        /* Temas del panel. <html> trae data-paleta (dauntoque, grafito, papel…) y data-modo (claro u oscuro) desde la cuenta
           (App\Support\TemaPanel). Sin atributos (inicio de sesión) se ve Da un Toque oscuro, el aspecto de siempre.
           Cada paleta solo define su tinta (--p-ink, oscura) y su papel (--p-paper, claro); el resto de colores se deriva. */
        :root {
            --sidebar-bg:rgba(12,12,16,.78); --sidebar-text:#d1d5db; --sidebar-active:rgba(59,130,246,.12); --sidebar-muted:#6b7280;
            --bg:#050507; --card:rgba(20,20,26,.62); --card-solid:#121218; --surface:rgba(255,255,255,.04); --border:rgba(255,255,255,.07); --text:#f5f5f7; --muted:#9ca3af; --strong:#fff;
            --primary:#3b82f6; --primary-2:#8b5cf6; --primary-soft:rgba(59,130,246,.16); --primary-line:rgba(59,130,246,.45); --success:#22c55e; --danger:#ef4444;
            --pri-bg:#2563eb; --pri-line:#3b82f6; --pri-hover:#3b82f6; --on-pri:#fff; --link:#60a5fa;
            --nav-on-bg:rgba(59,130,246,.12); --nav-on-text:#60a5fa; --nav-on-line:rgba(59,130,246,.22); --nav-on-shadow:inset 0 0 18px rgba(59,130,246,.08);
            --hover:rgba(255,255,255,.05); --soft:rgba(255,255,255,.05); --soft-2:rgba(255,255,255,.09); --sunken:rgba(0,0,0,.4);
            --field:rgba(0,0,0,.5); --field-line:#1f2937; --seg-on:rgba(255,255,255,.1); --seg-on-shadow:none; --ring:0 0 0 3px rgba(59,130,246,.18);
            --stat-bg:linear-gradient(145deg, rgba(30,30,36,.85), rgba(14,14,18,.85)); --stat-line:rgba(255,255,255,.04);
            --toast-bg:rgba(20,20,26,.92); --tab-bg:rgba(16,16,22,.85); --shadow-lg:0 16px 40px rgba(0,0,0,.45); --mine-bg:rgba(37,99,235,.22);
            --ok-t:#4ade80; --warn-t:#fbbf24; --bad-t:#f87171;
            --glow:0 0 22px rgba(59,130,246,.35); --glow-a:rgba(37,99,235,.13); --glow-b:rgba(124,58,237,.12);
            --font:"Inter", ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            color-scheme:dark;
        }
        [data-paleta="dauntoque"] { --p-ink:#1e3a8a; --p-paper:#eef2fb; }
        [data-paleta="grafito"] { --p-ink:#1f2023; --p-paper:#f3f3f4; }
        [data-paleta="papel"] { --p-ink:#2e241d; --p-paper:#f4ede5; }
        [data-paleta="salvia"] { --p-ink:#232c25; --p-paper:#edf0ea; }
        [data-paleta="marea"] { --p-ink:#152233; --p-paper:#eaeff7; }
        [data-paleta="ciruela"] { --p-ink:#2a1e28; --p-paper:#f2ebf0; }
        [data-paleta="arcilla"] { --p-ink:#2e2016; --p-paper:#f4eae1; }
        [data-paleta="glicina"] { --p-ink:#1d1d2e; --p-paper:#edecf7; }
        /* Claro: fondo del color papel, tarjetas blancas y botones principales con la tinta */
        html[data-modo="claro"] {
            --sidebar-bg:color-mix(in srgb, var(--p-paper) 55%, #fff); --sidebar-text:var(--p-ink); --sidebar-active:var(--nav-on-bg); --sidebar-muted:color-mix(in srgb, var(--p-ink) 55%, var(--p-paper));
            --bg:var(--p-paper); --card:#fff; --card-solid:#fff; --surface:color-mix(in srgb, var(--p-ink) 4%, transparent); --border:color-mix(in srgb, var(--p-ink) 11%, transparent);
            --text:var(--p-ink); --muted:color-mix(in srgb, var(--p-ink) 64%, var(--p-paper)); --strong:var(--p-ink);
            --primary:var(--p-ink); --primary-2:var(--p-ink); --primary-soft:color-mix(in srgb, var(--p-ink) 8%, transparent); --primary-line:color-mix(in srgb, var(--p-ink) 35%, transparent);
            --pri-bg:var(--p-ink); --pri-line:var(--p-ink); --pri-hover:color-mix(in srgb, var(--p-ink) 82%, #fff); --on-pri:#fff; --link:var(--p-ink);
            --nav-on-bg:var(--p-ink); --nav-on-text:#fff; --nav-on-line:transparent; --nav-on-shadow:none;
            --hover:color-mix(in srgb, var(--p-ink) 6%, transparent); --soft:color-mix(in srgb, var(--p-ink) 4%, transparent); --soft-2:color-mix(in srgb, var(--p-ink) 9%, transparent); --sunken:var(--p-paper);
            --field:#fff; --field-line:color-mix(in srgb, var(--p-ink) 18%, transparent); --seg-on:#fff; --seg-on-shadow:0 1px 3px color-mix(in srgb, var(--p-ink) 22%, transparent); --ring:0 0 0 3px color-mix(in srgb, var(--p-ink) 16%, transparent);
            --stat-bg:#fff; --stat-line:var(--border);
            --toast-bg:#fff; --tab-bg:color-mix(in srgb, #fff 90%, transparent); --shadow-lg:0 16px 40px color-mix(in srgb, var(--p-ink) 16%, transparent); --mine-bg:color-mix(in srgb, var(--p-ink) 9%, #fff);
            --ok-t:#15803d; --warn-t:#b45309; --bad-t:#b91c1c;
            --glow:none; --glow-a:transparent; --glow-b:transparent;
            color-scheme:light;
        }
        /* Oscuro: fondo con la tinta oscurecida y botones principales con el papel */
        html[data-modo="oscuro"]:not([data-paleta="dauntoque"]) {
            --sidebar-bg:color-mix(in srgb, var(--p-ink) 82%, #000); --sidebar-text:var(--p-paper); --sidebar-active:var(--nav-on-bg); --sidebar-muted:color-mix(in srgb, var(--p-paper) 45%, var(--p-ink));
            --bg:color-mix(in srgb, var(--p-ink) 70%, #000); --card:color-mix(in srgb, var(--p-ink) 94%, #fff); --card-solid:color-mix(in srgb, var(--p-ink) 94%, #fff); --surface:color-mix(in srgb, var(--p-paper) 5%, transparent); --border:color-mix(in srgb, var(--p-paper) 11%, transparent);
            --text:var(--p-paper); --muted:color-mix(in srgb, var(--p-paper) 64%, var(--p-ink)); --strong:#fff;
            --primary:var(--p-paper); --primary-2:var(--p-paper); --primary-soft:color-mix(in srgb, var(--p-paper) 10%, transparent); --primary-line:color-mix(in srgb, var(--p-paper) 35%, transparent);
            --pri-bg:var(--p-paper); --pri-line:var(--p-paper); --pri-hover:#fff; --on-pri:var(--p-ink); --link:var(--p-paper);
            --nav-on-bg:var(--p-paper); --nav-on-text:var(--p-ink); --nav-on-line:transparent; --nav-on-shadow:none;
            --hover:color-mix(in srgb, var(--p-paper) 6%, transparent); --soft:color-mix(in srgb, var(--p-paper) 6%, transparent); --soft-2:color-mix(in srgb, var(--p-paper) 11%, transparent); --sunken:color-mix(in srgb, var(--p-ink) 60%, #000);
            --field:color-mix(in srgb, var(--p-ink) 55%, #000); --field-line:color-mix(in srgb, var(--p-paper) 15%, transparent); --seg-on:color-mix(in srgb, var(--p-paper) 14%, transparent); --seg-on-shadow:none; --ring:0 0 0 3px color-mix(in srgb, var(--p-paper) 22%, transparent);
            --stat-bg:var(--card); --stat-line:var(--border);
            --toast-bg:var(--card-solid); --tab-bg:color-mix(in srgb, var(--p-ink) 88%, transparent); --shadow-lg:0 16px 40px rgba(0,0,0,.45); --mine-bg:color-mix(in srgb, var(--p-paper) 14%, transparent);
            --ok-t:#4ade80; --warn-t:#fbbf24; --bad-t:#f87171;
            --glow:none; --glow-a:transparent; --glow-b:transparent;
            color-scheme:dark;
        }
        /* Da un Toque claro: blanco azulado con el azul y el violeta de la marca */
        html[data-paleta="dauntoque"][data-modo="claro"] {
            --sidebar-bg:rgba(255,255,255,.82); --sidebar-text:#0f172a; --sidebar-muted:#94a3b8;
            --bg:#f5f7fb; --card:rgba(255,255,255,.86); --surface:rgba(15,23,42,.035); --border:rgba(15,23,42,.08); --text:#0f172a; --muted:#64748b; --strong:#0f172a;
            --primary:#2563eb; --primary-2:#7c3aed; --primary-soft:rgba(37,99,235,.1); --primary-line:rgba(37,99,235,.38);
            --pri-bg:#2563eb; --pri-line:#2563eb; --pri-hover:#1d4ed8; --on-pri:#fff; --link:#1d4ed8;
            --nav-on-bg:rgba(37,99,235,.1); --nav-on-text:#1d4ed8; --nav-on-line:rgba(37,99,235,.2); --nav-on-shadow:none;
            --hover:rgba(15,23,42,.045); --soft:rgba(15,23,42,.035); --soft-2:rgba(15,23,42,.07); --sunken:#eef1f6;
            --field:#fff; --field-line:#dbe1ea; --seg-on:#fff; --seg-on-shadow:0 1px 3px rgba(15,23,42,.14); --ring:0 0 0 3px rgba(37,99,235,.18);
            --stat-bg:linear-gradient(145deg, #fff, #f8faff); --stat-line:rgba(15,23,42,.07);
            --toast-bg:rgba(255,255,255,.95); --tab-bg:rgba(255,255,255,.9); --shadow-lg:0 16px 40px rgba(15,23,42,.14); --mine-bg:rgba(37,99,235,.1);
            --glow:0 6px 18px -6px rgba(37,99,235,.55); --glow-a:rgba(59,130,246,.10); --glow-b:rgba(139,92,246,.08);
        }
        body { margin:0; background:var(--bg); color:var(--text); font:14px/1.5 var(--font); }
        /* Brillos de fondo, como el panel original */
        body::before, body::after { content:""; position:fixed; width:520px; height:520px; border-radius:50%; filter:blur(120px); pointer-events:none; z-index:0; }
        body::before { top:-18%; left:-10%; background:var(--glow-a); }
        body::after { bottom:-20%; right:-10%; background:var(--glow-b); }
        [x-cloak] { display:none !important; }

        .dt-app { position:relative; z-index:1; display:flex; height:100vh; overflow:hidden; }
        .dt-side { width:240px; flex:none; background:var(--sidebar-bg); backdrop-filter:blur(20px); -webkit-backdrop-filter:blur(20px); border-right:1px solid var(--border); color:var(--sidebar-text); padding:18px 14px; display:flex; flex-direction:column; gap:4px; overflow-y:auto; }
        .dt-brand { display:flex; align-items:center; gap:10px; padding:2px 8px 18px; margin-bottom:6px; border-bottom:1px solid var(--border); font-weight:800; font-size:19px; letter-spacing:-.02em; color:var(--strong); }
        .dt-brand i { width:32px; height:32px; border-radius:9px; background:linear-gradient(135deg, var(--primary), var(--primary-2)); display:grid; place-items:center; font-style:normal; color:var(--on-pri); font-size:15px; box-shadow:var(--glow); }
        .dt-nav { display:flex; align-items:center; gap:12px; padding:10px 14px; border-radius:12px; color:var(--muted); font-size:14px; font-weight:600; text-decoration:none; border:1px solid transparent; transition:background .15s, color .15s; }
        .dt-nav svg { width:18px; height:18px; flex:none; }
        .dt-nav:hover { background:var(--hover); color:var(--strong); }
        .dt-nav.on { background:var(--nav-on-bg); color:var(--nav-on-text); border-color:var(--nav-on-line); box-shadow:var(--nav-on-shadow); }
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
        .dt-pop { position:absolute; right:0; top:calc(100% + 6px); z-index:30; min-width:170px; padding:6px; border:1px solid var(--border); border-radius:14px; background:var(--card-solid); box-shadow:var(--shadow-lg); }
        .dt-panel-h { display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; gap:12px; margin-bottom:18px; }
        .dt-panel-h h2 { font-size:15px; font-weight:800; margin:0; letter-spacing:-.01em; }
        .dt-btn { height:38px; border-radius:12px; border:1px solid var(--border); background:var(--soft); color:var(--text); padding:0 16px; font-weight:700; font-size:13px; display:inline-flex; align-items:center; justify-content:center; gap:6px; text-decoration:none; cursor:pointer; white-space:nowrap; transition:background .15s, box-shadow .15s, transform .15s; }
        .dt-btn:hover { background:var(--soft-2); }
        .dt-btn.pri { background:var(--pri-bg); border-color:var(--pri-line); color:var(--on-pri); box-shadow:var(--glow); }
        .dt-btn.pri:hover { background:var(--pri-hover); }
        .dt-btn.danger { color:var(--bad-t); }
        .dt-btn[disabled] { opacity:.45; cursor:not-allowed; box-shadow:none; }
        .dt-seg { display:inline-flex; background:var(--sunken); border:1px solid var(--border); border-radius:12px; padding:3px; }
        .dt-seg button { border:0; background:none; border-radius:9px; padding:6px 14px; font-size:12.5px; font-weight:600; color:var(--muted); cursor:pointer; }
        .dt-seg button.on { background:var(--seg-on); color:var(--strong); box-shadow:var(--seg-on-shadow); }
        .dt-label { display:block; font-size:10.5px; font-weight:700; letter-spacing:.12em; text-transform:uppercase; color:var(--muted); margin-bottom:8px; }
        .dt-input { width:100%; height:44px; border:1px solid var(--field-line); background:var(--field); color:var(--text); border-radius:12px; padding:0 14px; font-size:14px; outline:none; transition:border-color .15s, box-shadow .15s; }
        textarea.dt-input { height:auto; padding:12px 14px; resize:vertical; }
        .dt-input:focus, .dt-inline:focus-within { border-color:var(--primary); box-shadow:var(--ring); }
        .dt-inline { display:flex; align-items:center; border:1px solid var(--field-line); border-radius:12px; background:var(--field); overflow:hidden; }
        .dt-inline > span { padding:0 12px; font-size:12.5px; font-weight:700; color:var(--muted); white-space:nowrap; }
        .dt-inline input, .dt-inline select { border:0; background:transparent; color:var(--text); height:44px; padding:0 12px; outline:none; width:100%; font-size:14px; }
        .dt-inline select { width:auto; border-right:1px solid var(--field-line); background:var(--card-solid); }
        .dt-stat { position:relative; overflow:hidden; border:1px solid var(--stat-line); border-radius:24px; background:var(--stat-bg); padding:20px 22px; transition:transform .25s, border-color .25s; }
        .dt-stat:hover { transform:translateY(-4px); border-color:var(--primary-line); }
        .dt-stat b { display:block; font-size:32px; font-weight:900; letter-spacing:-.03em; font-variant-numeric:tabular-nums; margin-top:8px; }
        .dt-stat span { color:var(--muted); font-size:13px; }
        .dt-stat .ic { width:38px; height:38px; border-radius:50%; display:grid; place-items:center; }
        .dt-chip { display:inline-flex; align-items:center; gap:4px; font-size:11px; font-weight:700; padding:3px 9px; border-radius:999px; background:var(--soft-2); color:var(--muted); }
        .dt-chip.up { background:rgba(34,197,94,.12); color:var(--ok-t); }
        .dt-chip.down { background:rgba(239,68,68,.12); color:var(--bad-t); }
        .dt-ico { width:34px; height:34px; border-radius:10px; border:1px solid var(--border); background:var(--sunken); display:grid; place-items:center; color:var(--muted); cursor:pointer; }
        .dt-ico:hover { color:var(--strong); border-color:var(--field-line); }
        .dt-ico svg { width:15px; height:15px; }
        .dt-toast { position:fixed; left:50%; top:22px; transform:translateX(-50%); background:var(--toast-bg); backdrop-filter:blur(14px); color:var(--text); border:1px solid rgba(34,197,94,.45); border-radius:16px; padding:12px 18px; font-size:13px; font-weight:700; z-index:9000; box-shadow:0 0 30px rgba(34,197,94,.18); }
        .dt-toast.err { border-color:rgba(239,68,68,.55); box-shadow:0 0 30px rgba(239,68,68,.2); }
        button:focus-visible, a:focus-visible { outline:2px solid var(--primary); outline-offset:2px; }

        /* Fichas de perfil (miniatura con el estilo propio de cada perfil) */
        .dt-tiles { display:grid; grid-template-columns:repeat(auto-fill, minmax(260px, 1fr)); gap:20px; }
        .dt-tile { display:flex; flex-direction:column; gap:10px; min-width:0; }
        .dt-thumb { position:relative; display:block; width:100%; aspect-ratio:16/10; border-radius:18px; overflow:hidden; border:1px solid var(--border); padding:0; cursor:pointer; transition:transform .2s, border-color .2s, box-shadow .2s; background:none; }
        .dt-tile:hover .dt-thumb { transform:translateY(-3px); border-color:var(--primary-line); }
        .dt-tile.sel .dt-thumb { border-color:var(--primary); box-shadow:0 0 0 1px var(--primary), var(--glow); }
        .dt-check { position:absolute; right:10px; bottom:10px; width:26px; height:26px; border-radius:50%; background:var(--pri-bg); color:var(--on-pri); display:grid; place-items:center; box-shadow:var(--glow); }
        .dt-badge { position:absolute; left:10px; top:10px; font-size:10px; font-weight:800; letter-spacing:.06em; text-transform:uppercase; background:#22c55e; color:#04130a; border-radius:999px; padding:3px 9px; display:flex; align-items:center; gap:4px; box-shadow:0 0 16px rgba(34,197,94,.45); }
        .dt-new { aspect-ratio:16/10; border:1.5px dashed var(--field-line); border-radius:18px; display:grid; place-items:center; color:var(--muted); background:var(--sunken); text-align:center; cursor:pointer; transition:border-color .2s, color .2s; }
        .dt-new:hover { color:var(--strong); border-color:var(--primary); }
        .dt-styles { display:grid; grid-template-columns:repeat(auto-fill, minmax(150px, 1fr)); gap:14px; }
        .dt-styles .dt-thumb { aspect-ratio:16/11; }
        .dt-sw { width:28px; height:28px; border-radius:50%; border:2px solid var(--card-solid); box-shadow:0 0 0 1px var(--field-line); cursor:pointer; padding:0; }
        .dt-sw.on { box-shadow:0 0 0 2px var(--text); }
        /* Administrador: tarjetas de números y tablas */
        .ad-tabs { margin-bottom:20px; flex-wrap:wrap; }
        .ad-tabs a { border-radius:9px; padding:6px 14px; font-size:12.5px; font-weight:600; color:var(--muted); text-decoration:none; }
        .ad-tabs a.on { background:var(--seg-on); color:var(--strong); box-shadow:var(--seg-on-shadow); }
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
        .ad-avatar { width:52px; height:52px; flex:none; border-radius:50%; display:grid; place-items:center; font-size:22px; font-weight:800; color:var(--on-pri); background:linear-gradient(135deg, var(--primary), var(--primary-2)); box-shadow:var(--glow); }
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
        .sp-msj { max-width:78%; align-self:flex-start; background:var(--soft-2); border:1px solid var(--border); border-radius:16px 16px 16px 4px; padding:10px 12px; }
        .sp-msj.mio { align-self:flex-end; background:var(--mine-bg); border-color:var(--primary-line); border-radius:16px 16px 4px 16px; }
        .sp-msj p { margin:0; white-space:pre-wrap; overflow-wrap:anywhere; font-size:14px; }
        .sp-msj small { display:block; margin-top:4px; color:var(--muted); font-size:11px; }
        .sp-escribir { display:flex; gap:8px; align-items:flex-end; }
        .sp-escribir textarea { resize:none; }
        /* Tipografías del perfil */
        .dt-fuentes { display:grid; grid-template-columns:repeat(auto-fill, minmax(112px, 1fr)); gap:10px; }
        .dt-fuente { display:flex; flex-direction:column; align-items:flex-start; gap:2px; padding:10px 12px; border:1px solid var(--border); border-radius:14px; background:var(--sunken); color:var(--text); cursor:pointer; text-align:left; transition:border-color .15s, box-shadow .15s; }
        .dt-fuente b { font-size:26px; line-height:1.1; font-weight:700; }
        .dt-fuente span { font-size:12px; color:var(--muted); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:100%; }
        .dt-fuente:hover { border-color:var(--primary-line); }
        .dt-fuente.on { border-color:var(--primary); box-shadow:0 0 0 1px var(--primary), var(--glow); }

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
            .dt-tabbar { display:grid; grid-template-columns:repeat(5, 1fr); position:fixed; left:10px; right:10px; bottom:calc(10px + env(safe-area-inset-bottom, 0px)); z-index:50; background:var(--tab-bg); backdrop-filter:blur(20px); -webkit-backdrop-filter:blur(20px); border:1px solid var(--border); border-radius:22px; padding:6px; box-shadow:var(--shadow-lg); }
            .dt-tabbar a { display:flex; flex-direction:column; align-items:center; gap:3px; padding:7px 2px; border-radius:16px; color:var(--muted); text-decoration:none; font-size:10.5px; font-weight:700; }
            .dt-tabbar a svg { width:20px; height:20px; }
            .dt-tabbar a.on { color:var(--nav-on-text); background:var(--nav-on-bg); }
            .dt-tiles { grid-template-columns:1fr; }
        }
    </style>
