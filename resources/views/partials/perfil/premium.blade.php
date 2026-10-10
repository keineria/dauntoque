{{-- Diseño "Premium": una tarjeta sobria con aro en degradado, datos de contacto en un recuadro y secciones con título (estilo de los perfiles de clientes). --}}
<div class="pr">
    <style>
        .pr { max-width:440px; margin:0 auto; padding:18px 14px 96px; }
        .pr-card { position:relative; background:var(--card); border:1px solid var(--line); border-radius:30px; padding:34px 20px 24px; display:flex; flex-direction:column; gap:20px; box-shadow:0 40px 80px -40px rgba(0,0,0,.8); }
        .pr-id { display:flex; flex-direction:column; align-items:center; text-align:center; gap:14px; }
        .pr-aro { width:124px; height:124px; border-radius:50%; padding:4px; flex:none;
                  background:conic-gradient(from 210deg, var(--acc), color-mix(in srgb, var(--acc) 35%, #ffffff), color-mix(in srgb, var(--acc) 55%, #6d28d9), var(--acc)); box-shadow:0 18px 44px -18px var(--acc); }
        .pr-aro > div { width:100%; height:100%; border-radius:50%; overflow:hidden; border:4px solid var(--card); background:var(--chip); display:grid; place-items:center; font:800 40px var(--titulo); color:var(--muted); }
        .pr-aro.cuadro, .pr-aro.cuadro > div { border-radius:28px; }
        .pr-aro img { width:100%; height:100%; object-fit:cover; }
        .pr-nombre { margin:0; font:900 26px/1.12 var(--titulo); letter-spacing:-.02em; max-width:100%; }
        .pr-prof { margin:8px 0 0; font-size:11.5px; font-weight:800; letter-spacing:.16em; text-transform:uppercase; color:var(--acc); line-height:1.5; }
        .pp-corporativo .pr-prof, .pp-minimalista .pr-prof { color:color-mix(in srgb, var(--acc) 85%, var(--text)); }
        .pr-bio { margin:0; text-align:center; font-size:14.5px; line-height:1.65; color:var(--muted); }
        .pr-btn { height:52px; display:flex; align-items:center; justify-content:center; gap:8px; padding:0 12px; font-size:15px; font-weight:800; text-decoration:none; border:1px solid transparent; cursor:pointer; min-width:0; }
        .pr-btn.pri { background:var(--acc); color:var(--sobre); box-shadow:0 16px 34px -16px var(--acc); }
        .pr-btn.wa { height:48px; font-size:14px; background:color-mix(in srgb, #25d366 16%, transparent); border-color:color-mix(in srgb, #25d366 45%, transparent); color:#4ade80; }
        .pp-corporativo .pr-btn.wa, .pp-minimalista .pr-btn.wa { color:#15803d; }
        .pr-btn.sec { height:48px; font-size:14px; background:var(--chip); border-color:var(--line); color:var(--text); }
        .pr-dos { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
        .pr-caja { background:rgba(0,0,0,.22); border:1px solid var(--line); border-radius:20px; padding:16px 16px 6px; }
        .pp-corporativo .pr-caja, .pp-minimalista .pr-caja { background:var(--chip); }
        .pr-h { margin:0 0 12px; padding-bottom:10px; border-bottom:1px solid var(--line); font-size:11px; font-weight:800; letter-spacing:.16em; text-transform:uppercase; color:var(--muted); }
        .pr-h.centro { text-align:center; color:var(--acc); border:0; padding:0; margin:0 0 4px; }
        .pr-linea { height:1px; background:var(--line); margin:0 0 14px; }
        .pr-dato { display:flex; align-items:center; gap:12px; padding:0 0 12px; color:var(--text); text-decoration:none; min-width:0; }
        .pr-dato .ic { width:36px; height:36px; flex:none; border-radius:11px; display:grid; place-items:center; background:color-mix(in srgb, var(--acc) 16%, transparent); color:var(--acc); }
        .pr-dato small { display:block; font-size:10.5px; font-weight:700; letter-spacing:.1em; text-transform:uppercase; color:var(--muted); }
        .pr-dato .tx { flex:1; min-width:0; }
        .pr-dato b { font-size:14.5px; font-weight:600; }
        .pr-redes { display:flex; justify-content:center; flex-wrap:wrap; gap:10px; }
        .pr-redes a { width:48px; height:48px; border-radius:50%; display:grid; place-items:center; background:var(--chip); border:1px solid var(--line); color:var(--text); transition:border-color .15s, color .15s, transform .15s; }
        .pr-redes a:hover { border-color:var(--acc); color:var(--acc); transform:translateY(-2px); }
        .pr-redes svg { width:20px; height:20px; }
        .pr-gal { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
        .pr-gal button { position:relative; padding:0; border:0; aspect-ratio:1; overflow:hidden; cursor:zoom-in; background:var(--chip); border-radius:16px; }
        .pr-gal img { width:100%; height:100%; object-fit:cover; transition:transform .3s; }
        .pr-gal button:hover img { transform:scale(1.04); }
        .pr-gal .lupa { position:absolute; right:8px; bottom:8px; width:28px; height:28px; border-radius:50%; display:grid; place-items:center; background:rgba(0,0,0,.55); color:#fff; }
        .pr-pagos { display:flex; flex-direction:column; gap:8px; }
        .pr-pago { height:46px; display:flex; align-items:center; justify-content:center; gap:8px; font-size:14px; font-weight:800; text-decoration:none; border:0; cursor:pointer; color:#fff; }
        .pr-pie { text-align:center; font-size:12px; color:var(--muted); display:flex; flex-direction:column; align-items:center; gap:8px; padding-top:4px; }
        .pr-pie a { color:inherit; text-decoration:none; } .pr-pie b { color:var(--text); }
        .pr-pie .crea { border:1px solid var(--line); border-radius:999px; padding:6px 14px; color:var(--text); font-weight:600; }
    </style>

    <div class="pr-card">
        <div class="pr-id">
            <div class="pr-aro" :class="p.tipo === 'empresa' ? 'cuadro' : ''"><div>
                <template x-if="p.foto"><img :src="p.foto" :alt="p.nombre"></template>
                <template x-if="!p.foto"><span x-text="(p.nombre || '?').charAt(0).toUpperCase()"></span></template>
            </div></div>
            <div style="min-width:0;max-width:100%">
                <h1 class="pr-nombre pp-nombre" :style="(p.nombre || '').length > 22 ? 'font-size:21px' : ''" x-text="p.nombre || t('tuNombre')"></h1>
                <p class="pr-prof" x-show="p.tipo !== 'empresa' && (p.profesion || p.preview)" x-text="p.profesion || t('tuProfesion')"></p>
            </div>
        </div>

        <p class="pr-bio" x-show="p.bio || p.preview" x-text="p.bio || t('tuBio')"></p>

        <div style="display:flex;flex-direction:column;gap:10px" x-ref="acciones">
            <a class="pr-btn pri" :class="p.forma" :href="p.vcf" @click="accion('descarga_vcf', $event)">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="flex:none"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6M22 11h-6"/></svg>
                <span class="ajusta" x-text="t('guardar')">Guardar contacto</span>
            </a>
            <div class="pr-dos" x-show="p.telefono">
                <a class="pr-btn wa" :class="p.forma" :href="'https://wa.me/' + dtSoloDigitos(p.telefono)" target="_blank" rel="noopener" @click="accion('whatsapp', $event)">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="flex:none"><path d="M12.05 0C5.5 0 .16 5.34.16 11.89c0 2.1.55 4.14 1.59 5.95L.06 24l6.3-1.65a11.88 11.88 0 0 0 5.68 1.45h.01c6.55 0 11.89-5.34 11.89-11.89A11.82 11.82 0 0 0 12.05 0zm6.94 16.88c-.29.82-1.71 1.57-2.36 1.67-.6.09-1.37.13-2.21-.14-.51-.16-1.16-.38-2-.74-3.51-1.52-5.81-5.06-5.98-5.29-.17-.23-1.43-1.9-1.43-3.63s.9-2.57 1.23-2.93c.32-.35.7-.44.94-.44l.67.01c.21.01.5-.08.79.6.29.7.99 2.42 1.08 2.6.09.17.15.38.03.61-.12.23-.17.38-.35.58-.17.2-.36.45-.52.61-.17.17-.35.36-.15.7.2.35.9 1.48 1.93 2.4 1.33 1.18 2.45 1.55 2.8 1.72.35.17.55.15.75-.09.2-.23.87-1.01 1.1-1.36.23-.35.46-.29.78-.17.32.12 2.03.96 2.38 1.13.35.17.58.26.67.41.09.14.09.84-.2 1.66z"/></svg>
                    <span class="ajusta" x-text="t('whatsapp')">WhatsApp</span>
                </a>
                <a class="pr-btn sec" :class="p.forma" :href="'tel:' + p.telefono" @click="accion('llamada', $event)">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex:none"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/></svg>
                    <span class="ajusta" x-text="t('llamar')">Llamar</span>
                </a>
            </div>
        </div>

        <section class="pr-caja" x-show="p.correo || p.direccion || p.telefono">
            <h2 class="pr-h" x-text="t('contacto')">Contacto</h2>
            <a class="pr-dato" x-show="p.telefono" :href="'tel:' + p.telefono" @click="accion('llamada', $event)">
                <span class="ic"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/></svg></span>
                <span class="tx"><small x-text="t('telefono')"></small><b class="ajusta" x-text="p.telefono"></b></span>
            </a>
            <a class="pr-dato" x-show="p.correo" :href="'mailto:' + p.correo" @click="accion('correo', $event)">
                <span class="ic"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M22 6l-10 7L2 6"/></svg></span>
                <span class="tx"><small x-text="t('correo')"></small><b class="ajusta" x-text="p.correo"></b></span>
            </a>
            <div class="pr-dato" x-show="p.direccion">
                <span class="ic"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 21s-7-6.2-7-11a7 7 0 0 1 14 0c0 4.8-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg></span>
                <span class="tx"><small x-text="t('ubicacion')"></small><b style="display:block;font-size:14px;line-height:1.4" x-text="p.direccion"></b></span>
            </div>
        </section>

        <section x-show="p.redes && p.redes.length">
            <h2 class="pr-h centro" x-text="t('redes')">Redes sociales</h2>
            <div class="pr-linea"></div>
            <div class="pr-redes">
                <template x-for="red in (p.redes || [])" :key="red.id">
                    <a :href="dtUrlRed(red.id, red.usuario)" target="_blank" rel="noopener" :title="red.nombre" :aria-label="red.nombre" @click="accion('red_social_' + red.id, $event)" x-html="red.svg"></a>
                </template>
            </div>
        </section>

        <section x-show="p.galeria && p.galeria.length">
            <h2 class="pr-h centro" x-text="t('portafolio')">Portafolio</h2>
            <div class="pr-linea"></div>
            <div class="pr-gal">
                <template x-for="(img, i) in (p.galeria || [])" :key="i">
                    <button type="button" @click="if (!p.preview) fotoGrande = img" :aria-label="t('verImagen') + ' ' + (i + 1)">
                        <img :src="img" alt="" loading="lazy">
                        <span class="lupa"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg></span>
                    </button>
                </template>
            </div>
        </section>

        <section x-show="p.zelle || p.cashapp || p.venmo">
            <h2 class="pr-h centro" x-text="t('pagos')">Pagos</h2>
            <div class="pr-linea"></div>
            <div class="pr-pagos">
                <button type="button" class="pr-pago" :class="p.forma" style="background:linear-gradient(#8621a1,#741b8c)" x-show="p.zelle" @click="accion('zelle', $event); if (!p.preview) zelleAbierto = true"><span class="ajusta" x-text="t('pagarCon') + ' Zelle'"></span></button>
                <a class="pr-pago" :class="p.forma" style="background:#00d632;color:#06210f" x-show="p.cashapp" :href="'https://cash.app/$' + encodeURIComponent(String(p.cashapp || '').replace(/^\$/, ''))" target="_blank" rel="noopener" @click="accion('cashapp', $event)"><span class="ajusta" x-text="t('pagarCon') + ' Cash App'"></span></a>
                <a class="pr-pago" :class="p.forma" style="background:#008cff" x-show="p.venmo" :href="'https://venmo.com/u/' + encodeURIComponent(String(p.venmo || '').replace(/^@/, ''))" target="_blank" rel="noopener" @click="accion('venmo', $event)"><span class="ajusta" x-text="t('pagarCon') + ' Venmo'"></span></a>
            </div>
        </section>
    </div>

    <footer class="pr-pie" style="margin-top:16px">
        <a href="https://dauntoque.com" target="_blank" rel="noopener" @click="if (p.preview) $event.preventDefault()"><span x-text="t('tarjetaPor')"></span> <b>dauntoque</b></a>
        <a class="crea" href="https://dauntoque.com" target="_blank" rel="noopener" @click="if (p.preview) $event.preventDefault()" x-text="t('quieresUna')"></a>
    </footer>
</div>
