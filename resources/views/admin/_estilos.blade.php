{{-- Estilos propios del Centro de mando (se suman al sistema visual dt-* del panel) --}}
<style>
    .sr-only { position:absolute; width:1px; height:1px; padding:0; margin:-1px; overflow:hidden; clip:rect(0,0,0,0); white-space:nowrap; border:0; }
    .ad-marca { display:block; font-size:11px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--muted); margin-top:1px; }
    .dt-brand span { line-height:1.15; }
    .ad-ini { width:30px; height:30px; border-radius:50%; background:linear-gradient(135deg,var(--primary),var(--primary-2)); color:var(--on-pri); display:grid; place-items:center; font-size:12px; font-weight:800; box-shadow:var(--glow); }
    .ad-movil { display:none; }
    @media (max-width: 860px) {
        .ad-movil { display:flex; gap:6px; overflow-x:auto; padding:8px 16px 10px; scrollbar-width:none; -webkit-overflow-scrolling:touch; border-bottom:1px solid var(--border); }
        .ad-movil::-webkit-scrollbar { display:none; }
        .ad-movil a { flex:none; padding:7px 13px; border-radius:999px; font-size:13px; font-weight:600; color:var(--muted); text-decoration:none; border:1px solid var(--border); background:var(--card); }
        .ad-movil a.on { color:var(--nav-on-text); background:var(--nav-on-bg); border-color:var(--primary-line); }
        .dt-page { padding-bottom:40px !important; }
    }
    .ad-pila { display:flex; flex-direction:column; gap:18px; margin-top:18px; }
    .ad-dos { display:grid; grid-template-columns:repeat(auto-fit, minmax(min(100%, 340px), 1fr)); gap:18px; align-items:start; }
    .ad-ok { color:var(--ok-t); } .ad-mal { color:var(--bad-t); } .ad-aviso { color:var(--warn-t); }
    .ad-chip { display:inline-flex; align-items:center; gap:6px; padding:3px 10px; border-radius:999px; font-size:12px; font-weight:700; border:1px solid var(--border); white-space:nowrap; }
    .ad-chip.ok { color:var(--ok-t); border-color:currentColor; } .ad-chip.mal { color:var(--bad-t); border-color:currentColor; } .ad-chip.aviso { color:var(--warn-t); border-color:currentColor; }
    .ad-chip::before { content:""; width:7px; height:7px; border-radius:50%; background:currentColor; }
    .ad-chip.neutro::before { background:var(--muted); }
    /* Lista «Listo para vender» */
    .ad-lista { list-style:none; margin:0; padding:0; display:grid; gap:2px; }
    .ad-lista li { display:grid; grid-template-columns:26px minmax(0,1fr) auto; gap:10px; align-items:start; padding:10px 0; border-top:1px solid var(--border); }
    .ad-lista li:first-child { border-top:0; }
    .ad-lista .ic { width:22px; height:22px; border-radius:50%; display:grid; place-items:center; font-size:12px; font-weight:900; border:1.5px solid var(--field-line); color:var(--muted); }
    .ad-lista .hecho .ic { background:var(--ok-t); border-color:var(--ok-t); color:#fff; }
    .ad-lista small { display:block; color:var(--muted); font-size:12px; }
    .ad-lista a { color:var(--link); font-size:12.5px; text-decoration:none; white-space:nowrap; }
    .ad-barra { height:8px; border-radius:99px; background:var(--sunken); overflow:hidden; margin:6px 0 14px; }
    .ad-barra i { display:block; height:100%; border-radius:inherit; background:linear-gradient(90deg,var(--primary),var(--primary-2)); }
    /* Embudo */
    .ad-embudo { display:grid; grid-template-columns:repeat(auto-fit, minmax(150px, 1fr)); gap:12px; }
    .ad-paso { position:relative; padding:14px 16px; border-radius:16px; background:var(--sunken); border:1px solid var(--border); }
    .ad-paso span { display:block; font-size:12px; color:var(--muted); font-weight:600; }
    .ad-paso b { display:block; font-size:26px; font-weight:900; letter-spacing:-.02em; color:var(--strong); font-variant-numeric:tabular-nums; }
    .ad-paso small { font-size:12px; color:var(--muted); }
    /* Gráfica de barras por día */
    .ad-graf { display:flex; align-items:flex-end; gap:2px; height:140px; padding-top:8px; }
    .ad-graf div { flex:1; min-width:2px; display:flex; flex-direction:column; justify-content:flex-end; gap:2px; height:100%; position:relative; }
    .ad-graf .p { background:var(--primary); opacity:.35; border-radius:3px 3px 0 0; }
    .ad-graf .c { background:var(--ok-t); border-radius:3px; }
    .ad-graf div:hover .p { opacity:.7; }
    .ad-leyenda { display:flex; gap:16px; font-size:12px; color:var(--muted); margin-top:8px; flex-wrap:wrap; }
    .ad-leyenda i { display:inline-block; width:10px; height:10px; border-radius:3px; margin-right:6px; vertical-align:-1px; }
    /* Píxeles */
    .ad-pixel { border:1px solid var(--border); border-radius:16px; padding:16px; display:grid; gap:10px; background:var(--card); }
    .ad-pixel-h { display:flex; justify-content:space-between; gap:10px; align-items:center; flex-wrap:wrap; }
    .ad-pixel-h b { font-size:14.5px; }
    .ad-pixel ol { margin:0; padding-left:20px; color:var(--muted); font-size:13px; line-height:1.6; }
    .ad-pixel details summary { cursor:pointer; font-size:13px; font-weight:600; color:var(--link); }
    .ad-pixel details summary:focus-visible { outline:2px solid var(--primary); outline-offset:2px; border-radius:6px; }
    .ad-err { color:var(--bad-t); font-size:12.5px; margin:0; }
    .ad-campo { display:grid; gap:6px; font-size:13px; font-weight:600; }
    .ad-campo small { font-weight:400; color:var(--muted); }
    .ad-form { display:grid; grid-template-columns:repeat(auto-fit, minmax(min(100%, 220px), 1fr)); gap:14px; align-items:end; }
    .ad-url { font-family:ui-monospace,Consolas,monospace; font-size:12.5px; overflow-wrap:anywhere; padding:12px 14px; border-radius:12px; background:var(--sunken); border:1px solid var(--border); }
    .ad-acciones { display:flex; gap:8px; flex-wrap:wrap; align-items:center; }
</style>
