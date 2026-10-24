{{-- Diseño "Enlaces": una columna de botones grandes, ideal para el enlace de la biografía de Instagram o TikTok. --}}
<div class="pe">
    <style>
        .pe { max-width:420px; margin:0 auto; padding:40px 18px 96px; text-align:center; display:flex; flex-direction:column; align-items:center; gap:10px; }
        .pe-av { width:96px; height:96px; border-radius:50%; overflow:hidden; display:grid; place-items:center; background:var(--chip); font:800 34px var(--titulo); color:var(--muted);
                 box-shadow:0 0 0 3px var(--bg), 0 0 0 5px var(--acc), 0 20px 50px -20px var(--acc); }
        .pe-av.cuadro { border-radius:24px; }
        .pe-av img { width:100%; height:100%; object-fit:cover; }
        .pe-nombre { margin:8px 0 0; font:800 23px/1.2 var(--titulo); }
        .pe-prof { margin:0; font-size:12px; font-weight:700; letter-spacing:.12em; text-transform:uppercase; color:var(--acc); }
        .pp-corporativo .pe-prof, .pp-minimalista .pe-prof { color:color-mix(in srgb, var(--acc) 85%, var(--text)); }
        .pe-bio { margin:4px 0 0; font-size:14px; line-height:1.55; color:var(--muted); max-width:34ch; }
        .pe-lista { width:100%; display:flex; flex-direction:column; gap:12px; margin-top:16px; }
        .pe-btn { position:relative; width:100%; height:56px; display:flex; align-items:center; justify-content:center; padding:0 52px; background:var(--card); border:1px solid var(--line); color:var(--text);
                  font-size:15px; font-weight:650; text-decoration:none; cursor:pointer; transition:transform .12s, border-color .12s; }
        .pe-btn:hover { transform:translateY(-1px); border-color:color-mix(in srgb, var(--acc) 60%, transparent); }
        .pe-btn .ic { position:absolute; left:16px; top:50%; transform:translateY(-50%); width:24px; height:24px; display:grid; place-items:center; }
        .pe-btn .ic svg { width:20px; height:20px; }
        .pe-btn.pri { background:var(--acc); color:var(--sobre); border-color:transparent; box-shadow:0 14px 30px -14px var(--acc); }
        .pe-btn.wa { background:#25d366; color:#06210f; border-color:transparent; }
        .pe-sep { width:100%; margin:8px 0 0; font-size:11px; font-weight:700; letter-spacing:.14em; text-transform:uppercase; color:var(--muted); }
        .pe-gal { width:100%; display:grid; grid-template-columns:repeat(3, 1fr); gap:8px; }
        .pe-gal button { padding:0; border:0; aspect-ratio:1; overflow:hidden; cursor:zoom-in; background:var(--chip); }
        .pe-gal img { width:100%; height:100%; object-fit:cover; }
        .pe-pie { margin-top:22px; font-size:12px; color:var(--muted); display:flex; flex-direction:column; gap:8px; align-items:center; }
        .pe-pie a { color:inherit; text-decoration:none; } .pe-pie b { color:var(--text); }
    </style>

    <div class="pe-av" :class="p.tipo === 'empresa' ? 'cuadro' : ''">
        <template x-if="p.foto"><img :src="p.foto" :alt="p.nombre"></template>
        <template x-if="!p.foto"><span x-text="(p.nombre || '?').charAt(0).toUpperCase()"></span></template>
    </div>
    <h1 class="pe-nombre pp-nombre" x-text="p.nombre || t('tuNombre')"></h1>
    <p class="pe-prof" x-show="p.tipo !== 'empresa' && (p.profesion || p.preview)" x-text="p.profesion || t('tuProfesion')"></p>
    <p class="pe-bio" x-show="p.bio || p.preview" x-text="p.bio || t('tuBio')"></p>
    @include('partials.perfil._agenda', ['lugar' => 'prioridad'])

    <div class="pe-lista">
        <div x-ref="acciones" style="display:flex;flex-direction:column;gap:12px">
            @include('partials.perfil._agenda')
            <a class="pe-btn pri" :class="p.forma" :href="p.vcf" @click="accion('descarga_vcf', $event)">
                <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6M22 11h-6"/></svg></span><span class="ajusta" x-text="t('guardar')">Guardar contacto</span></a>
            <a class="pe-btn wa" :class="p.forma" x-show="p.telefono" :href="'https://wa.me/' + dtSoloDigitos(p.telefono)" target="_blank" rel="noopener" @click="accion('whatsapp', $event)">
                <span class="ic"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12.05 0C5.5 0 .16 5.34.16 11.89c0 2.1.55 4.14 1.59 5.95L.06 24l6.3-1.65a11.88 11.88 0 0 0 5.68 1.45h.01c6.55 0 11.89-5.34 11.89-11.89A11.82 11.82 0 0 0 12.05 0zm6.94 16.88c-.29.82-1.71 1.57-2.36 1.67-.6.09-1.37.13-2.21-.14-.51-.16-1.16-.38-2-.74-3.51-1.52-5.81-5.06-5.98-5.29-.17-.23-1.43-1.9-1.43-3.63s.9-2.57 1.23-2.93c.32-.35.7-.44.94-.44l.67.01c.21.01.5-.08.79.6.29.7.99 2.42 1.08 2.6.09.17.15.38.03.61-.12.23-.17.38-.35.58-.17.2-.36.45-.52.61-.17.17-.35.36-.15.7.2.35.9 1.48 1.93 2.4 1.33 1.18 2.45 1.55 2.8 1.72.35.17.55.15.75-.09.2-.23.87-1.01 1.1-1.36.23-.35.46-.29.78-.17.32.12 2.03.96 2.38 1.13.35.17.58.26.67.41.09.14.09.84-.2 1.66z"/></svg></span><span class="ajusta" x-text="t('escribeme')">Escríbeme por WhatsApp</span></a>
        </div>
        <a class="pe-btn" :class="p.forma" x-show="p.telefono" :href="'tel:' + p.telefono" @click="accion('llamada', $event)">
            <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/></svg></span><span class="ajusta" x-text="t('llamar')">Llamar</span></a>
        <a class="pe-btn" :class="p.forma" x-show="p.correo" :href="'mailto:' + p.correo" @click="accion('correo', $event)">
            <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M22 6l-10 7L2 6"/></svg></span><span class="ajusta" x-text="t('enviarCorreo')">Enviar un correo</span></a>
        <template x-for="red in (p.redes || [])" :key="red.id">
            <a class="pe-btn" :class="p.forma" :href="dtUrlRed(red.id, red.usuario)" target="_blank" rel="noopener" @click="accion('red_social_' + red.id, $event)">
                <span class="ic" x-html="red.svg"></span><span x-text="red.nombre"></span>
            </a>
        </template>
        <template x-for="m in (p.pagos || [])" :key="m.type">
            <a class="pe-btn" :class="p.forma" :href="dtPagoUrl(m) || '#'" :target="dtPagoUrl(m) ? '_blank' : null" rel="noopener" @click="pagar(m, $event)">
                <span class="ic" :style="'font-weight:800;color:' + dtPago(m).acento" x-text="dtPago(m).icono"></span><span class="ajusta" x-text="t('pagarCon') + ' ' + dtPago(m).nombre"></span></a>
        </template>
        <button type="button" class="pe-btn" :class="p.forma" @click="compartir($event)">
            <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="M8.6 13.5l6.8 4M15.4 6.5l-6.8 4"/></svg></span><span x-text="copiado ? t('copiadoEnlace') : t('compartirTarjeta')"></span>
        </button>
    </div>

    <template x-if="p.galeria && p.galeria.length">
        <div style="width:100%">
            <p class="pe-sep"><span x-text="t('portafolio')">Portafolio</span></p>
            <div class="pe-gal" style="margin-top:10px">
                <template x-for="(img, i) in dtGal(p.galeria)" :key="i">
                    <button type="button" class="pp-gal-item" style="border-radius:14px" @click="if (!p.preview) fotoGrande = img" :aria-label="img.titulo || (t('verImagen') + ' ' + (i + 1))"><img :src="img.url" :alt="img.titulo || ''" loading="lazy"><span class="pp-gal-tit" x-show="img.titulo" x-text="img.titulo"></span></button>
                </template>
            </div>
        </div>
    </template>

    @include('partials.perfil._calificame')

    <footer class="pe-pie">
        <a href="https://dauntoque.com" target="_blank" rel="noopener" @click="if (p.preview) $event.preventDefault()"><span x-text="t('tarjetaPor')">Tarjeta digital por</span> <b>dauntoque</b> · Crea la tuya</a>
    </footer>
</div>
