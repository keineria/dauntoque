{{-- Diseño "Clásico" (el original, más limpio y ordenado). También cubre "Feria": la foto grande arriba. --}}
<div class="pc">
    <style>
        .pc { max-width:440px; margin:0 auto; padding:18px 14px 96px; display:flex; flex-direction:column; gap:14px; }
        .pc-card { position:relative; background:var(--card); border:1px solid var(--line); border-radius:28px; padding:28px 20px 22px; display:flex; flex-direction:column; gap:18px; box-shadow:0 30px 60px -32px rgba(0,0,0,.7); }
        .pc-compartir { position:absolute; top:14px; right:14px; width:38px; height:38px; border-radius:50%; display:grid; place-items:center; background:var(--chip); border:1px solid var(--line); color:var(--text); cursor:pointer; z-index:2; }
        .pc-id { display:flex; flex-direction:column; align-items:center; text-align:center; gap:12px; }
        .pc-id.izq { flex-direction:row; text-align:left; gap:16px; padding-right:40px; }
        .pc-av { width:116px; height:116px; border-radius:50%; overflow:hidden; flex:none; display:grid; place-items:center; background:var(--chip); font:800 40px var(--titulo); color:var(--muted);
                 border:4px solid var(--card); box-shadow:0 0 0 2px color-mix(in srgb, var(--acc) 75%, transparent), 0 18px 40px -18px var(--acc); }
        .pc-id.izq .pc-av { width:84px; height:84px; font-size:30px; }
        .pc-av.cuadro { border-radius:24px; }
        .pc-av img, .pc-hero img { width:100%; height:100%; object-fit:cover; }
        .pc-hero { margin:-28px -20px 0; aspect-ratio:1/1; overflow:hidden; background:var(--chip); display:grid; place-items:center; font:800 72px var(--titulo); color:var(--muted); border-radius:28px 28px 0 0; }
        .pc-nombre { margin:0; font:800 25px/1.15 var(--titulo); letter-spacing:-.01em; }
        .pc-prof { margin:6px 0 0; font-size:11.5px; font-weight:700; letter-spacing:.14em; text-transform:uppercase; color:var(--acc); }
        .pp-corporativo .pc-prof, .pp-minimalista .pc-prof { color:color-mix(in srgb, var(--acc) 85%, var(--text)); }
        .pc-lugar { margin:6px 0 0; font-size:12.5px; color:var(--muted); }
        .pc-bio { margin:0; font-size:14.5px; line-height:1.65; color:color-mix(in srgb, var(--text) 82%, transparent); text-align:center; }
        .pc-bio.izq { text-align:left; }
        .pc-grid { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
        .pc-red { height:46px; display:flex; align-items:center; justify-content:center; gap:8px; background:var(--chip); border:1px solid var(--line); color:var(--text); font-size:11px; font-weight:700; letter-spacing:.06em; text-transform:uppercase; text-decoration:none; }
        .pc-red svg { width:17px; height:17px; }
        .pc-red:hover { border-color:color-mix(in srgb, var(--acc) 60%, transparent); }
        .pc-gal button { padding:0; border:0; aspect-ratio:1; overflow:hidden; cursor:zoom-in; background:var(--chip); }
        .pc-gal img { width:100%; height:100%; object-fit:cover; }
        .pc-btn { height:48px; display:flex; align-items:center; justify-content:center; gap:8px; font-size:14px; font-weight:700; text-decoration:none; border:1px solid transparent; cursor:pointer; white-space:nowrap; }
        .pc-btn.sec { background:var(--chip); border-color:var(--line); color:var(--text); }
        .pc-btn.wa { background:color-mix(in srgb, #25d366 18%, transparent); border-color:color-mix(in srgb, #25d366 45%, transparent); color:#4ade80; }
        .pp-corporativo .pc-btn.wa, .pp-minimalista .pc-btn.wa { color:#15803d; }
        .pc-btn.zelle { background:linear-gradient(#8621a1, #741b8c); color:#fff; }
        .pc-btn.cash { background:#00d632; color:#06210f; }
        .pc-btn.venmo { background:#008cff; color:#fff; }
        .pc-btn.pri { height:54px; font-size:15px; background:var(--acc); color:var(--sobre); box-shadow:0 14px 30px -14px var(--acc); }
        .pc-pagos { display:flex; flex-direction:column; gap:10px; }
        .pc-pie { text-align:center; font-size:12px; color:var(--muted); display:flex; flex-direction:column; align-items:center; gap:8px; }
        .pc-pie a { color:inherit; text-decoration:none; } .pc-pie b { color:var(--text); }
        .pc-pie .crea { border:1px solid var(--line); border-radius:999px; padding:6px 14px; color:var(--text); font-weight:600; }
    </style>

    <div class="pc-card">
        <button type="button" class="pc-compartir" @click="compartir($event)" :aria-label="copiado ? 'Enlace copiado' : 'Compartir perfil'">
            <svg x-show="!copiado" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="M8.6 13.5l6.8 4M15.4 6.5l-6.8 4"/></svg>
            <svg x-show="copiado" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 13l4 4L19 7"/></svg>
        </button>

        {{-- Feria: la foto manda --}}
        <template x-if="p.estructura === 'feria'">
            <div class="pc-hero">
                <template x-if="p.foto"><img :src="p.foto" :alt="p.nombre"></template>
                <template x-if="!p.foto"><span x-text="(p.nombre || '?').charAt(0).toUpperCase()"></span></template>
            </div>
        </template>

        <div class="pc-id" :class="p.estructura !== 'feria' && p.alineacion === 'izquierda' ? 'izq' : ''">
            <template x-if="p.estructura !== 'feria'">
                <div class="pc-av" :class="p.tipo === 'empresa' ? 'cuadro' : ''">
                    <template x-if="p.foto"><img :src="p.foto" :alt="p.nombre"></template>
                    <template x-if="!p.foto"><span x-text="(p.nombre || '?').charAt(0).toUpperCase()"></span></template>
                </div>
            </template>
            <div style="min-width:0">
                <h1 class="pc-nombre pp-nombre" x-text="p.nombre || 'Tu nombre'"></h1>
                <p class="pc-prof" x-show="p.tipo !== 'empresa' && (p.profesion || p.preview)" x-text="p.profesion || 'Tu profesión'"></p>
                <p class="pc-lugar" x-show="p.direccion" x-text="p.direccion"></p>
            </div>
        </div>

        <template x-if="p.estructura === 'feria'">
            <a class="pc-btn pri" :class="p.forma" :href="p.vcf" @click="accion('descarga_vcf', $event)">Guardar contacto</a>
        </template>

        <p class="pc-bio" :class="p.estructura !== 'feria' && p.alineacion === 'izquierda' ? 'izq' : ''" x-show="p.bio || p.preview" x-text="p.bio || 'Tu biografía aparecerá aquí.'"></p>

        <div class="pc-grid" x-show="p.redes && p.redes.length">
            <template x-for="red in (p.redes || [])" :key="red.id">
                <a class="pc-red" :class="p.forma" :href="dtUrlRed(red.id, red.usuario)" target="_blank" rel="noopener" @click="accion('red_social_' + red.id, $event)">
                    <span x-html="red.svg" style="display:grid"></span><span x-text="red.nombre"></span>
                </a>
            </template>
        </div>

        <div class="pc-grid pc-gal" x-show="p.galeria && p.galeria.length">
            <template x-for="(img, i) in (p.galeria || [])" :key="i">
                <button type="button" style="border-radius:14px" @click="if (!p.preview) fotoGrande = img" :aria-label="'Ver imagen ' + (i + 1)"><img :src="img" alt="" loading="lazy"></button>
            </template>
        </div>

        <div class="pc-grid" x-show="p.telefono">
            <a class="pc-btn sec" :class="p.forma" :href="'tel:' + p.telefono" @click="accion('llamada', $event)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/></svg>Llamar
            </a>
            <a class="pc-btn wa" :class="p.forma" :href="'https://wa.me/' + dtSoloDigitos(p.telefono)" target="_blank" rel="noopener" @click="accion('whatsapp', $event)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12.05 0C5.5 0 .16 5.34.16 11.89c0 2.1.55 4.14 1.59 5.95L.06 24l6.3-1.65a11.88 11.88 0 0 0 5.68 1.45h.01c6.55 0 11.89-5.34 11.89-11.89A11.82 11.82 0 0 0 12.05 0zm6.94 16.88c-.29.82-1.71 1.57-2.36 1.67-.6.09-1.37.13-2.21-.14-.51-.16-1.16-.38-2-.74-3.51-1.52-5.81-5.06-5.98-5.29-.17-.23-1.43-1.9-1.43-3.63s.9-2.57 1.23-2.93c.32-.35.7-.44.94-.44l.67.01c.21.01.5-.08.79.6.29.7.99 2.42 1.08 2.6.09.17.15.38.03.61-.12.23-.17.38-.35.58-.17.2-.36.45-.52.61-.17.17-.35.36-.15.7.2.35.9 1.48 1.93 2.4 1.33 1.18 2.45 1.55 2.8 1.72.35.17.55.15.75-.09.2-.23.87-1.01 1.1-1.36.23-.35.46-.29.78-.17.32.12 2.03.96 2.38 1.13.35.17.58.26.67.41.09.14.09.84-.2 1.66z"/></svg>WhatsApp
            </a>
        </div>

        <div class="pc-pagos" x-show="p.zelle || p.cashapp || p.venmo">
            <button type="button" class="pc-btn zelle" :class="p.forma" x-show="p.zelle" @click="accion('zelle', $event); if (!p.preview) zelleAbierto = true">Pagar con Zelle</button>
            <a class="pc-btn cash" :class="p.forma" x-show="p.cashapp" :href="'https://cash.app/$' + encodeURIComponent(String(p.cashapp || '').replace(/^\$/, ''))" target="_blank" rel="noopener" @click="accion('cashapp', $event)">Pagar con Cash App</a>
            <a class="pc-btn venmo" :class="p.forma" x-show="p.venmo" :href="'https://venmo.com/u/' + encodeURIComponent(String(p.venmo || '').replace(/^@/, ''))" target="_blank" rel="noopener" @click="accion('venmo', $event)">Pagar con Venmo</a>
        </div>

        <template x-if="p.estructura !== 'feria'">
            <div x-ref="acciones">
                <a class="pc-btn pri" :class="p.forma" :href="p.vcf" @click="accion('descarga_vcf', $event)">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6M22 11h-6"/></svg>
                    Guardar contacto
                </a>
            </div>
        </template>
    </div>

    <footer class="pc-pie">
        <a href="https://dauntoque.com" target="_blank" rel="noopener" @click="if (p.preview) $event.preventDefault()">Tarjeta digital por <b>dauntoque</b></a>
        <a class="crea" href="https://dauntoque.com" target="_blank" rel="noopener" @click="if (p.preview) $event.preventDefault()">¿Quieres una así? Crea la tuya</a>
    </footer>
</div>
