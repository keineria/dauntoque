{{-- Diseño "Deportivo" (id ejecutiva): arriba una tarjeta de presentación como la física, debajo los datos de contacto a la vista. --}}
<div class="px">
    <style>
        .px { max-width:460px; margin:0 auto; padding:20px 14px 96px; display:flex; flex-direction:column; gap:12px; }
        .px-tarjeta { position:relative; overflow:hidden; background:var(--card); border:1px solid var(--line); border-radius:20px; padding:22px 20px 22px 26px; display:flex; align-items:center; gap:16px; min-height:168px;
                      box-shadow:0 30px 60px -34px rgba(0,0,0,.75); }
        .px-tarjeta::before { content:""; position:absolute; left:0; top:0; bottom:0; width:6px; background:var(--acc); }
        .px-tarjeta::after { content:""; position:absolute; right:-60px; top:-60px; width:180px; height:180px; border-radius:50%; background:radial-gradient(circle, color-mix(in srgb, var(--acc) 30%, transparent), transparent 70%); pointer-events:none; }
        .px-foto { width:86px; height:86px; border-radius:18px; overflow:hidden; flex:none; display:grid; place-items:center; background:var(--chip); font:800 32px var(--titulo); color:var(--muted); position:relative; z-index:1; }
        .px-foto img { width:100%; height:100%; object-fit:cover; }
        .px-nombre { margin:0; font:800 22px/1.15 var(--titulo); }
        .px-prof { margin:6px 0 0; font-size:11.5px; font-weight:700; letter-spacing:.12em; text-transform:uppercase; color:var(--acc); }
        .pp-corporativo .px-prof, .pp-minimalista .px-prof { color:color-mix(in srgb, var(--acc) 85%, var(--text)); }
        .px-marca { margin:12px 0 0; font-size:11px; color:var(--muted); letter-spacing:.04em; }
        .px-bloque { background:var(--card); border:1px solid var(--line); border-radius:18px; padding:6px; }
        .px-dato { display:flex; align-items:center; gap:12px; padding:11px 10px; text-decoration:none; color:var(--text); border-radius:12px; }
        .px-dato + .px-dato { border-top:1px solid var(--line); border-radius:0; }
        .px-dato .ic { width:38px; height:38px; flex:none; border-radius:11px; display:grid; place-items:center; background:color-mix(in srgb, var(--acc) 16%, transparent); color:var(--acc); }
        .px-dato small { display:block; font-size:10.5px; font-weight:700; letter-spacing:.1em; text-transform:uppercase; color:var(--muted); }
        .px-dato b { display:block; font-size:14.5px; font-weight:600; overflow-wrap:anywhere; } .px-dato > span:last-child { flex:1; min-width:0; }
        .px-acciones { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
        .px-acciones.una { grid-template-columns:1fr; }
        .px-btn { height:52px; display:flex; align-items:center; justify-content:center; gap:8px; font-size:14px; font-weight:700; text-decoration:none; border:0; cursor:pointer; white-space:nowrap; }
        .px-btn.pri { background:var(--acc); color:var(--sobre); box-shadow:0 14px 30px -14px var(--acc); }
        .px-btn.wa { background:#25d366; color:#06210f; }
        .px-redes { display:flex; justify-content:center; gap:10px; flex-wrap:wrap; }
        .px-redes a { width:48px; height:48px; border-radius:50%; display:grid; place-items:center; background:var(--card); border:1px solid var(--line); color:var(--text); }
        .px-redes a:hover { border-color:var(--acc); color:var(--acc); }
        .px-redes svg { width:20px; height:20px; }
        .px-h { margin:4px 6px 8px; font-size:10.5px; font-weight:700; letter-spacing:.14em; text-transform:uppercase; color:var(--muted); }
        .px-texto { margin:0; padding:4px 8px 8px; font-size:14px; line-height:1.6; }
        .px-gal { display:grid; grid-template-columns:repeat(3, 1fr); gap:6px; padding:4px; }
        .px-gal button { padding:0; border:0; aspect-ratio:1; overflow:hidden; cursor:zoom-in; background:var(--chip); border-radius:10px; }
        .px-gal img { width:100%; height:100%; object-fit:cover; }
        .px-pagos { display:flex; gap:8px; flex-wrap:wrap; padding:4px; }
        .px-pagos a, .px-pagos button { flex:1; min-width:110px; height:44px; display:flex; align-items:center; justify-content:center; gap:6px; font-size:13px; font-weight:700; text-decoration:none; border:0; cursor:pointer; color:#fff; }
        .px-pie { text-align:center; font-size:12px; color:var(--muted); padding-top:6px; }
        .px-pie a { color:inherit; text-decoration:none; } .px-pie b { color:var(--text); }
    </style>

    <section class="px-tarjeta">
        <div class="px-foto">
            <template x-if="p.foto"><img :src="p.foto" :alt="p.nombre"></template>
            <template x-if="!p.foto"><span x-text="(p.nombre || '?').charAt(0).toUpperCase()"></span></template>
        </div>
        <div style="min-width:0;position:relative;z-index:1">
            <h1 class="px-nombre pp-nombre" x-text="p.nombre || t('tuNombre')"></h1>
            <p class="px-prof" x-show="p.tipo !== 'empresa' && (p.profesion || p.preview)" x-text="p.profesion || t('tuProfesion')"></p>
            <p class="px-marca" x-text="p.preview ? @js(rtrim(\App\Models\Profile::prefijoPublico(), '/')) : String(p.enlace || '').replace(/^https?:\/\//, '')"></p>
        </div>
    </section>

    <div class="px-acciones" :class="p.telefono ? '' : 'una'" x-ref="acciones">
        <a class="px-btn pri" :class="p.forma" :href="p.vcf" @click="accion('descarga_vcf', $event)">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6M22 11h-6"/></svg><span class="ajusta" x-text="t('guardar')">Guardar contacto</span></a>
        <a class="px-btn wa" :class="p.forma" x-show="p.telefono" :href="'https://wa.me/' + dtSoloDigitos(p.telefono)" target="_blank" rel="noopener" @click="accion('whatsapp', $event)"><span class="ajusta" x-text="t('whatsapp')">WhatsApp</span></a>
    </div>

    <section class="px-bloque" x-show="p.telefono || p.correo || p.direccion">
        <a class="px-dato" x-show="p.telefono" :href="'tel:' + p.telefono" @click="accion('llamada', $event)">
            <span class="ic"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/></svg></span>
            <span><small><span x-text="t('telefono')">Teléfono</span></small><b class="ajusta" x-text="p.telefono"></b></span>
        </a>
        <a class="px-dato" x-show="p.correo" :href="'mailto:' + p.correo" @click="accion('correo', $event)">
            <span class="ic"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M22 6l-10 7L2 6"/></svg></span>
            <span><small><span class="ajusta" x-text="t('correo')">Correo</span></small><b class="ajusta" x-text="p.correo"></b></span>
        </a>
        <div class="px-dato" x-show="p.direccion">
            <span class="ic"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 21s-7-6.2-7-11a7 7 0 0 1 14 0c0 4.8-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg></span>
            <span><small><span x-text="t('ubicacion')">Ubicación</span></small><b x-text="p.direccion"></b></span>
        </div>
    </section>

    <div class="px-redes" x-show="p.redes && p.redes.length">
        <template x-for="red in (p.redes || [])" :key="red.id">
            <a :href="dtUrlRed(red.id, red.usuario)" target="_blank" rel="noopener" :title="red.nombre" :aria-label="red.nombre" @click="accion('red_social_' + red.id, $event)" x-html="red.svg"></a>
        </template>
    </div>

    <section class="px-bloque" x-show="p.bio || p.preview">
        <h2 class="px-h"><span x-text="t('perfil')">Perfil</span></h2>
        <p class="px-texto" x-text="p.bio || t('tuBio')"></p>
    </section>

    <section class="px-bloque" x-show="p.galeria && p.galeria.length">
        <h2 class="px-h"><span x-text="t('portafolio')">Portafolio</span></h2>
        <div class="px-gal">
            <template x-for="(img, i) in (p.galeria || [])" :key="i">
                <button type="button" @click="if (!p.preview) fotoGrande = img" :aria-label="t('verImagen') + ' ' + (i + 1)"><img :src="img" alt="" loading="lazy"></button>
            </template>
        </div>
    </section>

    <section class="px-bloque" x-show="(p.pagos || []).length">
        <h2 class="px-h"><span x-text="t('pagos')">Pagos</span></h2>
        <div class="px-pagos">
            <template x-for="m in (p.pagos || [])" :key="m.type">
                <a :class="p.forma" :style="dtPagoEstilo(m)" :href="dtPagoUrl(m) || '#'" :target="dtPagoUrl(m) ? '_blank' : null" rel="noopener" @click="pagar(m, $event)" x-text="dtPago(m).nombre"></a>
            </template>
        </div>
    </section>

    <footer class="px-pie">
        <a href="https://dauntoque.com" target="_blank" rel="noopener" @click="if (p.preview) $event.preventDefault()"><span x-text="t('tarjetaPor')">Tarjeta digital por</span> <b>dauntoque</b> · Crea la tuya</a>
    </footer>
</div>
