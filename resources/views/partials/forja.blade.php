{{--
    La Forja: animación de la unidad DT-9 ensamblando el perfil mientras se guarda.
    Uso desde JS:  Forja.iniciar({ inicial: 'C', color: '#3b82f6', modo: 'crear' })
                   Forja.terminar({ ok: true, nickname: 'carla' })   o   Forja.terminar({ ok: false, mensaje: '...' })
--}}
<link href="https://fonts.googleapis.com/css2?family=Chakra+Petch:wght@500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
    .forja { position:fixed; inset:0; z-index:10000; background:#05070b; color:#cfe3ff; display:flex; flex-direction:column; font-family:"Chakra Petch", "Segoe UI", system-ui, sans-serif; overflow:auto; }
    .forja[hidden] { display:none !important; }
    .forja .f-top { display:flex; justify-content:space-between; align-items:center; gap:12px; padding:14px 20px; border-bottom:1px solid #132033; font:500 11px "JetBrains Mono", ui-monospace, monospace; letter-spacing:.14em; text-transform:uppercase; color:#6f8db3; flex-wrap:wrap; }
    .forja .f-top b { color:#e6f1ff; font:700 15px "Chakra Petch", system-ui, sans-serif; letter-spacing:.08em; }
    .forja .live { display:flex; align-items:center; gap:8px; }
    .forja .live i { width:8px; height:8px; border-radius:50%; background:#ff3b3b; box-shadow:0 0 10px #ff3b3b; animation:fj-blink 1s infinite; }
    .forja.lista .live i { background:#38f2a6; box-shadow:0 0 10px #38f2a6; animation:none; }
    .forja .f-body { flex:1; display:grid; grid-template-columns:minmax(0,1fr) 300px; min-height:0; }
    .forja .bay { position:relative; min-height:420px; display:grid; place-items:center; overflow:hidden;
        background:linear-gradient(#0b1220 1px, transparent 1px) 0 0/32px 32px, linear-gradient(90deg, #0b1220 1px, transparent 1px) 0 0/32px 32px, radial-gradient(70% 60% at 50% 55%, #0d1a2e 0%, #05070b 75%); }
    .forja .machine { width:min(640px, 94%); }
    .forja .machine svg { width:100%; height:auto; display:block; }
    .forja .f-log { border-left:1px solid #132033; background:#070b12; display:flex; flex-direction:column; min-width:0; }
    .forja .f-log h3 { margin:0; padding:14px 16px; font:600 12px "JetBrains Mono", monospace; letter-spacing:.12em; text-transform:uppercase; color:#6f8db3; border-bottom:1px solid #132033; }
    .forja .log { flex:1; padding:12px 16px; font:12px/1.7 "JetBrains Mono", ui-monospace, monospace; color:#7fa7d6; overflow:auto; min-height:150px; }
    .forja .log p { margin:0; } .forja .log .ok { color:#38f2a6; } .forja .log .warn { color:#ffc857; } .forja .log .err { color:#ff6b6b; }
    .forja .mods { padding:12px 16px; border-top:1px solid #132033; display:grid; gap:7px; }
    .forja .mod { display:flex; align-items:center; gap:10px; font:500 12px "JetBrains Mono", monospace; color:#4f6b8e; }
    .forja .mod i { width:10px; height:10px; border:1px solid currentColor; transform:rotate(45deg); }
    .forja .mod.run { color:#ffc857; } .forja .mod.run i { background:#ffc857; animation:fj-blink .5s infinite; }
    .forja .mod.done { color:#38f2a6; } .forja .mod.done i { background:#38f2a6; }
    .forja .f-foot { border-top:1px solid #132033; padding:14px 20px; display:flex; align-items:center; gap:16px; flex-wrap:wrap; }
    .forja .gauge { flex:1; min-width:180px; height:10px; background:#0c1626; border:1px solid #1b2b44; position:relative; overflow:hidden; }
    .forja .gauge span { position:absolute; inset:0 auto 0 0; width:0; background:repeating-linear-gradient(90deg, #2f8cff 0 10px, #1f64c4 10px 12px); box-shadow:0 0 12px #2f8cff; transition:width .4s; }
    .forja.fallo .gauge span { background:#ff3b3b; box-shadow:0 0 12px #ff3b3b; }
    .forja .pct { font:700 22px "Chakra Petch", system-ui, sans-serif; color:#e6f1ff; min-width:64px; text-align:right; font-variant-numeric:tabular-nums; }
    .forja .acciones { display:flex; gap:10px; flex-wrap:wrap; }
    .forja .fbtn { height:40px; padding:0 20px; border:1px solid #2f8cff; background:#0b1a33; color:#cfe3ff; font:600 13px "Chakra Petch", system-ui, sans-serif; letter-spacing:.1em; text-transform:uppercase; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center;
        clip-path:polygon(10px 0, 100% 0, 100% calc(100% - 10px), calc(100% - 10px) 100%, 0 100%, 0 10px); }
    .forja .fbtn:hover { background:#12305c; }
    .forja .fbtn.pri { background:#2f8cff; color:#04101f; }
    .forja .fbtn:focus-visible { outline:2px solid #7cc4ff; outline-offset:2px; }
    @keyframes fj-blink { 50% { opacity:.25; } }
    @keyframes fj-scan { 0% { transform:translateY(0); } 100% { transform:translateY(210px); } }
    @keyframes fj-armL { 0%,100% { transform:rotate(-8deg); } 50% { transform:rotate(14deg); } }
    @keyframes fj-armR { 0%,100% { transform:rotate(8deg); } 50% { transform:rotate(-14deg); } }
    @keyframes fj-piston { 0%,100% { transform:translateY(0); } 50% { transform:translateY(10px); } }
    @keyframes fj-spark { 0% { opacity:1; transform:scale(.3); } 100% { opacity:0; transform:scale(1.6); } }
    @keyframes fj-gear { to { transform:rotate(360deg); } }
    .forja .running .armL { animation:fj-armL 1.1s ease-in-out infinite; transform-origin:120px 90px; }
    .forja .running .armR { animation:fj-armR 1.1s ease-in-out infinite; transform-origin:520px 90px; }
    .forja .running .pist { animation:fj-piston .7s ease-in-out infinite; }
    .forja .running .beam { animation:fj-scan 1.6s linear infinite alternate; }
    .forja .running .spk { animation:fj-spark .5s ease-out infinite; transform-box:fill-box; transform-origin:center; }
    .forja .running .gear { animation:fj-gear 3s linear infinite; transform-box:fill-box; transform-origin:center; }
    .forja .beam { opacity:0; } .forja .running .beam { opacity:.9; }
    .forja .part { opacity:0; transition:opacity .5s, transform .5s; }
    .forja .part.in { opacity:1; }
    .forja .p-slide { transform:translateY(-30px); } .forja .p-slide.in { transform:none; }
    .forja .emerge { filter:drop-shadow(0 0 18px rgba(47,140,255,.55)); }
    @media (prefers-reduced-motion: reduce) { .forja .running * { animation:none !important; } .forja .part { transition:none; } }
    @media (max-width: 860px) { .forja .f-body { grid-template-columns:1fr; } .forja .f-log { border-left:0; border-top:1px solid #132033; } .forja .bay { min-height:300px; } }
</style>

<div class="forja" id="forja" hidden role="dialog" aria-modal="true" aria-labelledby="forja-titulo">
    <div class="f-top">
        <b id="forja-titulo">DAUNTOQUE // FORJA DE IDENTIDAD</b>
        <span class="live"><i></i><span id="forja-estado">Unidad DT-9 · Ensamblando</span></span>
        <span id="forja-serie">Serie DT-0000</span>
    </div>
    <div class="f-body">
        <div class="bay">
            <div class="machine" id="forja-maquina">
                <svg viewBox="0 0 640 420" role="img" aria-label="Máquina ensamblando la tarjeta del perfil">
                    <defs>
                        <linearGradient id="fj-steel" x1="0" x2="0" y1="0" y2="1"><stop offset="0" stop-color="#2a3a52"/><stop offset="1" stop-color="#121b29"/></linearGradient>
                        <linearGradient id="fj-beam" x1="0" x2="0" y1="0" y2="1"><stop offset="0" stop-color="#2f8cff" stop-opacity="0"/><stop offset=".5" stop-color="#7cc4ff"/><stop offset="1" stop-color="#2f8cff" stop-opacity="0"/></linearGradient>
                        <pattern id="fj-hz" width="14" height="14" patternUnits="userSpaceOnUse" patternTransform="rotate(45)"><rect width="7" height="14" fill="#ffc857"/><rect x="7" width="7" height="14" fill="#1a1405"/></pattern>
                    </defs>
                    <rect x="40" y="22" width="560" height="44" rx="4" fill="url(#fj-steel)" stroke="#3a5070"/>
                    <rect x="40" y="62" width="560" height="6" fill="url(#fj-hz)"/>
                    <g fill="#7fa7d6" font-family="JetBrains Mono, monospace" font-size="10"><text x="58" y="49">DT-9 FABRICATOR</text><text x="470" y="49">PRESIÓN 4.2 BAR</text></g>
                    <g><circle cx="300" cy="44" r="4" fill="#38f2a6"/><circle cx="316" cy="44" r="4" fill="#ffc857"/><circle cx="332" cy="44" r="4" fill="#ff3b3b"/></g>
                    <rect x="40" y="68" width="34" height="300" fill="url(#fj-steel)" stroke="#3a5070"/>
                    <rect x="566" y="68" width="34" height="300" fill="url(#fj-steel)" stroke="#3a5070"/>
                    <g fill="#0b1220" stroke="#3a5070"><circle cx="57" cy="100" r="5"/><circle cx="57" cy="330" r="5"/><circle cx="583" cy="100" r="5"/><circle cx="583" cy="330" r="5"/></g>
                    <circle class="gear" cx="57" cy="215" r="13" fill="#1a2a40" stroke="#5d7ea8" stroke-dasharray="4 3" stroke-width="4"/>
                    <circle class="gear" cx="583" cy="215" r="13" fill="#1a2a40" stroke="#5d7ea8" stroke-dasharray="4 3" stroke-width="4"/>
                    <g class="pist"><rect x="190" y="68" width="14" height="40" fill="#5d7ea8"/><rect x="184" y="106" width="26" height="10" fill="#2a3a52" stroke="#5d7ea8"/></g>
                    <g class="pist" style="animation-delay:.35s"><rect x="436" y="68" width="14" height="40" fill="#5d7ea8"/><rect x="430" y="106" width="26" height="10" fill="#2a3a52" stroke="#5d7ea8"/></g>
                    <g class="armL"><rect x="110" y="80" width="20" height="20" rx="4" fill="#2a3a52" stroke="#5d7ea8"/><path d="M120 92 L190 170" stroke="#5d7ea8" stroke-width="12" stroke-linecap="round"/><path d="M190 170 L232 190" stroke="#7fa7d6" stroke-width="8" stroke-linecap="round"/><circle cx="190" cy="170" r="9" fill="#1a2a40" stroke="#7fa7d6" stroke-width="2"/><circle class="spk" cx="236" cy="192" r="6" fill="#ffd27a"/></g>
                    <g class="armR"><rect x="510" y="80" width="20" height="20" rx="4" fill="#2a3a52" stroke="#5d7ea8"/><path d="M520 92 L450 170" stroke="#5d7ea8" stroke-width="12" stroke-linecap="round"/><path d="M450 170 L408 190" stroke="#7fa7d6" stroke-width="8" stroke-linecap="round"/><circle cx="450" cy="170" r="9" fill="#1a2a40" stroke="#7fa7d6" stroke-width="2"/><circle class="spk" style="animation-delay:.25s" cx="404" cy="192" r="6" fill="#7cc4ff"/></g>
                    <rect x="74" y="352" width="492" height="16" fill="#1a2a40" stroke="#3a5070"/>
                    <rect x="30" y="368" width="580" height="30" rx="3" fill="url(#fj-steel)" stroke="#3a5070"/>
                    <rect x="30" y="394" width="580" height="6" fill="url(#fj-hz)"/>
                    <g id="forja-tarjeta" transform="translate(250 120)">
                        <rect class="part p-slide" data-p="0" width="140" height="228" rx="14" fill="#0d1626" stroke="#2f8cff"/>
                        <circle class="part p-slide" data-p="1" cx="70" cy="50" r="24" id="forja-avatar" fill="#2f6fe4"/>
                        <text class="part" data-p="1" x="70" y="56" text-anchor="middle" fill="#fff" font-family="Chakra Petch, sans-serif" font-weight="700" font-size="16" id="forja-inicial">D</text>
                        <rect class="part p-slide" data-p="2" x="30" y="86" width="80" height="8" rx="4" fill="#e6f1ff"/>
                        <rect class="part p-slide" data-p="2" x="42" y="100" width="56" height="6" rx="3" fill="#6f8db3"/>
                        <g class="part p-slide" data-p="3"><rect x="16" y="120" width="52" height="16" rx="5" fill="#16243b"/><rect x="72" y="120" width="52" height="16" rx="5" fill="#16243b"/><rect x="16" y="140" width="52" height="16" rx="5" fill="#16243b"/><rect x="72" y="140" width="52" height="16" rx="5" fill="#16243b"/></g>
                        <rect class="part p-slide" data-p="4" x="16" y="166" width="108" height="18" rx="5" fill="#7a3fd6"/>
                        <rect class="part p-slide" data-p="5" x="16" y="192" width="108" height="22" rx="6" id="forja-cta" fill="#2f6fe4"/>
                    </g>
                    <rect class="beam" x="236" y="112" width="168" height="10" fill="url(#fj-beam)"/>
                </svg>
            </div>
        </div>
        <div class="f-log">
            <h3>Registro de ensamblaje</h3>
            <div class="log" id="forja-log" aria-live="polite"></div>
            <div class="mods" id="forja-mods"></div>
        </div>
    </div>
    <div class="f-foot">
        <div class="gauge" aria-hidden="true"><span id="forja-gauge"></span></div>
        <div class="pct" id="forja-pct">0%</div>
        <div class="acciones" id="forja-acciones"></div>
    </div>
</div>

<script>
    window.Forja = (function () {
        const PASOS = [
            ['Calibrando unidad DT-9', 'Chasis del perfil', 0],
            ['Moldeando identidad: avatar y nombre', 'Identidad', 1],
            ['Grabando titular y especialidad', 'Titular', 2],
            ['Soldando conectores de redes sociales', 'Redes', 3],
            ['Montando módulo de pagos', 'Pagos', 4],
            ['Aplicando acabado y botón de contacto', 'Acabado', 5],
        ];
        const $ = id => document.getElementById(id);
        let paso = 0, timer = null, resultado = null, modo = 'crear';

        const escribir = (texto, clase = '') => {
            const p = document.createElement('p');
            p.className = clase;
            p.textContent = '[' + new Date().toTimeString().slice(0, 8) + '] ' + texto;
            $('forja-log').appendChild(p);
            $('forja-log').scrollTop = $('forja-log').scrollHeight;
        };
        const progreso = pct => { $('forja-gauge').style.width = pct + '%'; $('forja-pct').textContent = pct + '%'; };
        const boton = (texto, accion, pri) => {
            const b = document.createElement(typeof accion === 'string' ? 'a' : 'button');
            b.className = 'fbtn' + (pri ? ' pri' : '');
            b.textContent = texto;
            if (typeof accion === 'string') { b.href = accion; if (accion.startsWith('/') && !accion.startsWith('/dashboard')) b.target = '_blank'; }
            else b.addEventListener('click', accion);
            $('forja-acciones').appendChild(b);
            return b;
        };

        function avanzar() {
            const mods = document.querySelectorAll('#forja-mods .mod');
            if (paso > 0) { mods[paso - 1].className = 'mod done'; escribir('✓ ' + PASOS[paso - 1][1] + ' listo', 'ok'); }
            if (paso === PASOS.length) { clearInterval(timer); timer = null; progreso(96); escribir('Esperando confirmación del servidor…'); if (resultado) cerrarCiclo(); return; }
            mods[paso].className = 'mod run';
            escribir(PASOS[paso][0] + '…', paso === 3 ? 'warn' : '');
            document.querySelectorAll('#forja-tarjeta [data-p="' + PASOS[paso][2] + '"]').forEach(x => x.classList.add('in'));
            progreso(Math.round(((paso + 1) / PASOS.length) * 92));
            paso++;
        }

        function cerrarCiclo() {
            const r = resultado;
            $('forja-maquina').classList.remove('running');
            $('forja-acciones').innerHTML = '';
            if (r.ok) {
                document.querySelectorAll('#forja-tarjeta .part').forEach(x => x.classList.add('in'));
                document.querySelectorAll('#forja-mods .mod').forEach(x => x.className = 'mod done');
                $('forja-tarjeta').classList.add('emerge');
                $('forja').classList.add('lista');
                $('forja-estado').textContent = 'Unidad DT-9 · Perfil listo';
                progreso(100);
                escribir(modo === 'editar' ? 'Cambios aplicados a tu perfil público.' : 'Perfil ensamblado y guardado.', 'ok');
                if (modo !== 'editar') escribir('Paso final: acerca tu tarjeta NFC a la parte superior de tu celular para activarla.', 'warn');
                boton('Ir a mis perfiles', '/dashboard/perfiles', true).focus();
                if (r.nickname) boton('Ver mi perfil', '/' + r.nickname);
            } else {
                $('forja').classList.add('fallo');
                $('forja-estado').textContent = 'Unidad DT-9 · Ensamblaje detenido';
                escribir('✕ ' + (r.mensaje || 'No se pudo guardar el perfil.'), 'err');
                boton('Volver al editor', () => { $('forja').hidden = true; }, true).focus();
            }
        }

        return {
            iniciar(opciones = {}) {
                modo = opciones.modo || 'crear';
                paso = 0; resultado = null; clearInterval(timer);
                const f = $('forja');
                f.hidden = false; f.classList.remove('lista', 'fallo');
                $('forja-maquina').classList.add('running');
                $('forja-estado').textContent = 'Unidad DT-9 · Ensamblando';
                $('forja-serie').textContent = 'Serie DT-' + String(Math.floor(1000 + Math.random() * 8999));
                $('forja-inicial').textContent = (opciones.inicial || 'D').charAt(0).toUpperCase();
                const color = /^#[0-9a-fA-F]{3,8}$/.test(opciones.color || '') ? opciones.color : '#2f6fe4';
                $('forja-avatar').setAttribute('fill', color); $('forja-cta').setAttribute('fill', color);
                document.querySelectorAll('#forja-tarjeta .part').forEach(x => x.classList.remove('in'));
                $('forja-tarjeta').classList.remove('emerge');
                $('forja-mods').innerHTML = PASOS.map(p => '<div class="mod"><i></i>' + p[1] + '</div>').join('');
                $('forja-log').innerHTML = ''; $('forja-acciones').innerHTML = ''; progreso(0);
                escribir('Unidad DT-9 en línea. Presión hidráulica estable.');
                avanzar();
                timer = setInterval(avanzar, matchMedia('(prefers-reduced-motion: reduce)').matches ? 300 : 900);
            },
            // Se llama cuando el servidor responde; la máquina termina su ciclo antes de mostrar el resultado
            terminar(r) {
                resultado = r || { ok: false };
                if (!resultado.ok) { clearInterval(timer); timer = null; cerrarCiclo(); return; }
                if (!timer) cerrarCiclo();
            },
        };
    })();
</script>
