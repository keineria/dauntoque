{{-- Diseño "Boutique": elegante y cálido, para belleza y servicios (estilo Kathe mejorado): los trabajos van primero y el botón principal es reservar. --}}
<div class="bq">
    <style>
        .bq { max-width:440px; margin:0 auto; padding:22px 14px 96px; }
        .bq-card { position:relative; overflow:hidden; background:var(--card); border:1px solid var(--line); border-radius:34px; padding:40px 20px 24px; display:flex; flex-direction:column; gap:22px; box-shadow:0 40px 80px -40px rgba(0,0,0,.8); }
        .bq-card::before { content:""; position:absolute; left:50%; top:-120px; width:360px; height:300px; transform:translateX(-50%); border-radius:50%; background:radial-gradient(closest-side, color-mix(in srgb, var(--acc) 34%, transparent), transparent); pointer-events:none; }
        .bq-id { position:relative; display:flex; flex-direction:column; align-items:center; text-align:center; gap:16px; }
        .bq-aro { width:138px; height:138px; border-radius:50%; padding:3px; background:linear-gradient(140deg, var(--acc), #f3d9a4 45%, var(--acc)); box-shadow:0 0 0 8px color-mix(in srgb, var(--acc) 10%, transparent), 0 24px 50px -20px var(--acc); }
        .bq-aro > div { width:100%; height:100%; border-radius:50%; overflow:hidden; border:4px solid var(--card); background:var(--chip); display:grid; place-items:center; font:700 44px var(--titulo); color:var(--muted); }
        .bq-aro img { width:100%; height:100%; object-fit:cover; }
        .bq-nombre { margin:0; font:700 28px/1.12 var(--titulo-bq, Georgia), "Times New Roman", serif; letter-spacing:-.01em; }
        .bq-prof { margin:10px auto 0; font-size:11px; font-weight:800; letter-spacing:.2em; text-transform:uppercase; color:var(--acc); line-height:1.6; max-width:30ch; }
        .pp-corporativo .bq-prof, .pp-minimalista .bq-prof { color:color-mix(in srgb, var(--acc) 85%, var(--text)); }
        .bq-bio { position:relative; margin:0; text-align:center; font-size:14.5px; line-height:1.7; color:var(--muted); }
        .bq-h { display:flex; align-items:center; gap:12px; margin:0 0 14px; font-size:11px; font-weight:800; letter-spacing:.2em; text-transform:uppercase; color:var(--acc); }
        .bq-h::before, .bq-h::after { content:""; flex:1; height:1px; background:var(--line); }
        .bq-gal { display:grid; grid-template-columns:repeat(3, 1fr); gap:8px; }
        .bq-gal.pocas { grid-template-columns:1fr 1fr; }
        .bq-gal button { position:relative; padding:0; border:0; aspect-ratio:4/5; overflow:hidden; cursor:zoom-in; background:var(--chip); border-radius:18px; }
        .bq-gal img { width:100%; height:100%; object-fit:cover; transition:transform .35s; }
        .bq-gal button:hover img { transform:scale(1.05); }
        .bq-gal .lupa { position:absolute; right:7px; bottom:7px; width:26px; height:26px; border-radius:50%; display:grid; place-items:center; background:rgba(0,0,0,.5); color:#fff; }
        .bq-btn { height:54px; display:flex; align-items:center; justify-content:center; gap:8px; padding:0 14px; font-size:15px; font-weight:800; text-decoration:none; border:1px solid transparent; cursor:pointer; min-width:0; }
        .bq-btn.pri { background:var(--acc); color:var(--sobre); box-shadow:0 18px 36px -16px var(--acc); }
        .bq-btn.borde { height:50px; font-size:14px; background:transparent; border-color:color-mix(in srgb, var(--acc) 55%, transparent); color:var(--text); }
        .bq-redes { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
        .bq-red { height:46px; display:flex; align-items:center; justify-content:center; gap:8px; padding:0 10px; background:var(--chip); border:1px solid var(--line); color:var(--text); font-size:12px; font-weight:700; letter-spacing:.06em; text-transform:uppercase; text-decoration:none; min-width:0; }
        .bq-red svg { width:17px; height:17px; flex:none; }
        .bq-datos { display:flex; justify-content:center; flex-wrap:wrap; gap:8px; }
        .bq-datos a, .bq-datos span { display:inline-flex; align-items:center; gap:6px; max-width:100%; padding:8px 12px; border-radius:999px; background:var(--chip); border:1px solid var(--line); color:var(--text); font-size:12.5px; text-decoration:none; }
        .bq-pagos { display:flex; flex-direction:column; gap:8px; }
        .bq-pago { height:46px; display:flex; align-items:center; justify-content:center; font-size:14px; font-weight:800; text-decoration:none; border:0; cursor:pointer; color:#fff; padding:0 12px; }
        .bq-pie { text-align:center; font-size:12px; color:var(--muted); display:flex; flex-direction:column; align-items:center; gap:8px; margin-top:16px; }
        .bq-pie a { color:inherit; text-decoration:none; } .bq-pie b { color:var(--text); }
        .bq-pie .crea { border:1px solid var(--line); border-radius:999px; padding:6px 14px; color:var(--text); font-weight:600; }
    </style>

    <div class="bq-card">
        <div class="bq-id">
            <div class="bq-aro"><div>
                <template x-if="p.foto"><img :src="p.foto" :alt="p.nombre"></template>
                <template x-if="!p.foto"><span x-text="(p.nombre || '?').charAt(0).toUpperCase()"></span></template>
            </div></div>
            <div style="max-width:100%">
                <h1 class="bq-nombre pp-nombre" :style="(p.nombre || '').length > 20 ? 'font-size:23px' : ''" x-text="p.nombre || t('tuNombre')"></h1>
                <p class="bq-prof" x-show="p.tipo !== 'empresa' && (p.profesion || p.preview)" x-text="p.profesion || t('tuProfesion')"></p>
            </div>
        </div>

        <p class="bq-bio" x-show="p.bio || p.preview" x-text="p.bio || t('tuBio')"></p>
        @include('partials.perfil._agenda', ['lugar' => 'prioridad'])

        <section x-show="p.galeria && p.galeria.length">
            <h2 class="bq-h" x-text="t('trabajos')">Mis trabajos</h2>
            <div class="bq-gal" :class="(p.galeria || []).length < 3 ? 'pocas' : ''">
                <template x-for="(img, i) in dtGal(p.galeria)" :key="i">
                    <button type="button" class="pp-gal-item" @click="if (!p.preview) fotoGrande = img" :aria-label="img.titulo || (t('verImagen') + ' ' + (i + 1))">
                        <img :src="img.url" :alt="img.titulo || ''" loading="lazy"><span class="pp-gal-tit" x-show="img.titulo" x-text="img.titulo"></span>
                        <span class="lupa"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg></span>
                    </button>
                </template>
            </div>
        </section>

        <div style="display:flex;flex-direction:column;gap:10px" x-ref="acciones">
            @include('partials.perfil._agenda')
            <a class="bq-btn pri" :class="p.forma" x-show="p.telefono" :href="'https://wa.me/' + dtSoloDigitos(p.telefono)" target="_blank" rel="noopener" @click="accion('whatsapp', $event)">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" style="flex:none"><path d="M12.05 0C5.5 0 .16 5.34.16 11.89c0 2.1.55 4.14 1.59 5.95L.06 24l6.3-1.65a11.88 11.88 0 0 0 5.68 1.45h.01c6.55 0 11.89-5.34 11.89-11.89A11.82 11.82 0 0 0 12.05 0zm6.94 16.88c-.29.82-1.71 1.57-2.36 1.67-.6.09-1.37.13-2.21-.14-.51-.16-1.16-.38-2-.74-3.51-1.52-5.81-5.06-5.98-5.29-.17-.23-1.43-1.9-1.43-3.63s.9-2.57 1.23-2.93c.32-.35.7-.44.94-.44l.67.01c.21.01.5-.08.79.6.29.7.99 2.42 1.08 2.6.09.17.15.38.03.61-.12.23-.17.38-.35.58-.17.2-.36.45-.52.61-.17.17-.35.36-.15.7.2.35.9 1.48 1.93 2.4 1.33 1.18 2.45 1.55 2.8 1.72.35.17.55.15.75-.09.2-.23.87-1.01 1.1-1.36.23-.35.46-.29.78-.17.32.12 2.03.96 2.38 1.13.35.17.58.26.67.41.09.14.09.84-.2 1.66z"/></svg>
                <span class="ajusta" x-text="t('reservar')">Reservar por WhatsApp</span>
            </a>
            <a class="bq-btn" :class="[p.forma, p.telefono ? 'borde' : 'pri']" :href="p.vcf" @click="accion('descarga_vcf', $event)">
                <span class="ajusta" x-text="t('guardar')">Guardar contacto</span>
            </a>
        </div>

        <div class="bq-datos" x-show="p.telefono || p.correo || p.direccion">
            <a x-show="p.telefono" :href="'tel:' + p.telefono" @click="accion('llamada', $event)"><span x-text="t('llamar')"></span></a>
            <a x-show="p.correo" :href="'mailto:' + p.correo" @click="accion('correo', $event)"><span x-text="t('correo')"></span></a>
            <span x-show="p.direccion" x-text="p.direccion"></span>
            <a href="#" @click.prevent="compartir($event)"><span x-text="copiado ? t('copiadoEnlace') : t('compartir')"></span></a>
        </div>

        <section x-show="p.redes && p.redes.length">
            <h2 class="bq-h" x-text="t('redes')">Redes sociales</h2>
            <div class="bq-redes">
                <template x-for="red in (p.redes || [])" :key="red.id">
                    <a class="bq-red" :class="p.forma" :href="dtUrlRed(red.id, red.usuario)" target="_blank" rel="noopener" @click="accion('red_social_' + red.id, $event)">
                        <span x-html="red.svg" style="display:grid"></span><span class="ajusta" x-text="red.nombre"></span>
                    </a>
                </template>
            </div>
        </section>

        <section x-show="(p.pagos || []).length">
            <h2 class="bq-h" x-text="t('pagos')">Pagos</h2>
            <div class="bq-pagos">
                <template x-for="m in (p.pagos || [])" :key="m.type">
                    <a class="bq-pago" :class="p.forma" :style="dtPagoEstilo(m)" :href="dtPagoUrl(m) || '#'" :target="dtPagoUrl(m) ? '_blank' : null" rel="noopener" @click="pagar(m, $event)"><span class="ajusta" x-text="t('pagarCon') + ' ' + dtPago(m).nombre"></span></a>
                </template>
            </div>
        </section>
    </div>

    @include('partials.perfil._musica')

    @include('partials.perfil._calificame')

    <footer class="bq-pie">
        <a href="https://dauntoque.com" target="_blank" rel="noopener" @click="if (p.preview) $event.preventDefault()"><span x-text="t('tarjetaPor')"></span> <b>dauntoque</b></a>
        <a class="crea" href="https://dauntoque.com" target="_blank" rel="noopener" @click="if (p.preview) $event.preventDefault()" x-text="t('quieresUna')"></a>
    </footer>
</div>
