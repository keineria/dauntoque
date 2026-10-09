{{--
    Cuerpo del perfil público. Una sola plantilla para la página pública y para la vista previa del editor.
    Necesita en Alpine un objeto `p` con: nombre, profesion, bio, foto, tipo, alineacion, estructura, tema, color, forma,
    telefono, correo, direccion, zelle, cashapp, venmo, redes [{id, nombre, svg, usuario}], galeria [], enlace, vcf, preview.
--}}
@once
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    .pp { --acc:#3b82f6; --sobre:#fff; --bg:#0a0a0b; --card:#131316; --text:#f4f4f5; --muted:#a1a1aa; --line:rgba(255,255,255,.08); --chip:rgba(255,255,255,.06);
          --titulo:"Inter", ui-sans-serif, system-ui, sans-serif;
          min-height:100%; background:var(--bg); color:var(--text); font-family:"Inter", ui-sans-serif, system-ui, -apple-system, "Segoe UI", sans-serif; position:relative; }
    .pp-crystal { --bg:#0b0d12; --card:rgba(22,24,32,.62); --line:rgba(255,255,255,.09);
                  background:linear-gradient(180deg, rgba(9,10,14,.82), rgba(9,10,14,.97)), url('https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?q=80&w=1000&auto=format&fit=crop') center/cover fixed; }
    .pp-crystal .pp-card { backdrop-filter:blur(18px); -webkit-backdrop-filter:blur(18px); }
    .pp-matte { --bg:#09090b; --card:#121214; --line:rgba(255,255,255,.06); --muted:#8b8b94; }
    .pp-luxury { --bg:#070707; --card:#101010; --text:#f5ecd7; --muted:#b5a47c; --line:color-mix(in srgb, var(--acc) 35%, transparent); --chip:rgba(255,255,255,.04); --titulo:Georgia, "Times New Roman", serif; }
    .pp-corporativo { --bg:#eef2f7; --card:#ffffff; --text:#0f172a; --muted:#64748b; --line:#e2e8f0; --chip:#f1f5f9; --titulo:Georgia, "Times New Roman", serif; }
    .pp-sport { --bg:#0c0c0c; --card:#000; --muted:#9ca3af; --line:rgba(255,255,255,.08); background:#0c0c0c radial-gradient(#252b36 1px, transparent 1px) 0 0/18px 18px; }
    .pp-sport .pp-nombre { text-transform:uppercase; font-style:italic; letter-spacing:.01em; font-weight:900; }
    .pp-sport .pp-card { border-left:3px solid var(--acc); }
    .pp-minimalista { --bg:#fafafa; --card:#ffffff; --text:#111111; --muted:#6b7280; --line:#ececec; --chip:#f4f4f5; }

    .pp-wrap { max-width:440px; margin:0 auto; padding:18px 14px 96px; display:flex; flex-direction:column; gap:12px; }
    .pp-card { background:var(--card); border:1px solid var(--line); border-radius:20px; padding:18px; }
    .pp-hero { position:relative; overflow:hidden; padding-top:26px; text-align:center; }
    .pp-hero::before { content:""; position:absolute; inset:0 0 auto 0; height:86px; background:radial-gradient(120% 100% at 50% 0%, color-mix(in srgb, var(--acc) 45%, transparent), transparent 70%); pointer-events:none; }
    .pp-hero.izq { text-align:left; }
    .pp-id { position:relative; display:flex; flex-direction:column; align-items:center; gap:10px; }
    .pp-hero.izq .pp-id { flex-direction:row; align-items:center; gap:14px; }
    .pp-av { width:104px; height:104px; border-radius:50%; flex:none; overflow:hidden; display:grid; place-items:center; background:var(--chip); font:800 36px var(--titulo); color:var(--muted);
             box-shadow:0 0 0 4px var(--card), 0 0 0 6px color-mix(in srgb, var(--acc) 70%, transparent); }
    .pp-hero.izq .pp-av { width:78px; height:78px; font-size:28px; }
    .pp-av.cuadro { border-radius:22px; }
    .pp-av img, .pp-portada img { width:100%; height:100%; object-fit:cover; }
    .pp-nombre { font:800 24px/1.15 var(--titulo); letter-spacing:-.01em; margin:0; }
    .pp-prof { margin:4px 0 0; font-size:12px; font-weight:700; letter-spacing:.12em; text-transform:uppercase; color:var(--acc); }
    .pp-luxury .pp-prof, .pp-corporativo .pp-prof, .pp-minimalista .pp-prof { color:color-mix(in srgb, var(--acc) 85%, var(--text)); }
    .pp-lugar { margin:6px 0 0; font-size:12.5px; color:var(--muted); display:flex; align-items:center; gap:4px; justify-content:center; }
    .pp-hero.izq .pp-lugar { justify-content:flex-start; }
    .pp-portada { margin:-18px -18px 16px; aspect-ratio:1/1; overflow:hidden; background:var(--chip); display:grid; place-items:center; font:800 64px var(--titulo); color:var(--muted); }

    .pp-acciones { display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-top:18px; }
    .pp-acciones.una { grid-template-columns:1fr; }
    .pp-btn { height:50px; display:flex; align-items:center; justify-content:center; gap:8px; padding:0 10px; font-weight:700; font-size:14.5px; white-space:nowrap; text-decoration:none; border:0; cursor:pointer; transition:transform .12s, filter .12s; }
    .pp-acciones .pp-btn { font-size:14px; gap:6px; }
    .pp-btn:active { transform:scale(.98); }
    .pp-btn:hover { filter:brightness(1.06); }
    .pp-btn.pri { background:var(--acc); color:var(--sobre); box-shadow:0 10px 24px -10px var(--acc); }
    .pp-btn.wa { background:#25d366; color:#06210f; }
    .pp-btn.sec { background:var(--chip); color:var(--text); border:1px solid var(--line); height:46px; font-size:13.5px; }
    .pp-mini { display:flex; justify-content:center; gap:8px; margin-top:10px; }
    .pp-mini a, .pp-mini button { flex:1; height:42px; display:flex; align-items:center; justify-content:center; gap:6px; background:var(--chip); border:1px solid var(--line); color:var(--text); font-size:12.5px; font-weight:600; text-decoration:none; cursor:pointer; }

    .pp-h { margin:0 0 10px; font-size:11px; font-weight:700; letter-spacing:.14em; text-transform:uppercase; color:var(--muted); }
    .pp-bio { margin:0; font-size:14.5px; line-height:1.6; }
    .pp-fila { display:flex; align-items:center; gap:12px; padding:11px 12px; background:var(--chip); border:1px solid var(--line); color:var(--text); text-decoration:none; cursor:pointer; width:100%; text-align:left; }
    .pp-fila + .pp-fila { margin-top:8px; }
    .pp-fila .ic { width:36px; height:36px; display:grid; place-items:center; flex:none; border-radius:10px; background:color-mix(in srgb, var(--acc) 18%, transparent); color:var(--acc); }
    .pp-fila .ic svg { width:18px; height:18px; }
    .pp-fila b { display:block; font-size:14px; } .pp-fila small { display:block; font-size:12px; color:var(--muted); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .pp-fila .tx { flex:1; min-width:0; } .pp-fila .fl { color:var(--muted); flex:none; }
    .pp-gal { display:grid; grid-template-columns:1fr 1fr; gap:8px; }
    .pp-gal button { padding:0; border:0; aspect-ratio:1; overflow:hidden; cursor:zoom-in; background:var(--chip); }
    .pp-gal img { width:100%; height:100%; object-fit:cover; transition:transform .3s; }
    .pp-gal button:hover img { transform:scale(1.04); }
    .pp-pie { text-align:center; padding:8px 0 4px; display:flex; flex-direction:column; align-items:center; gap:10px; }
    .pp-pie a { color:var(--muted); text-decoration:none; font-size:12px; display:inline-flex; align-items:center; gap:6px; }
    .pp-pie .marca b { color:var(--text); font-weight:800; }
    .pp-pie .crea { border:1px solid var(--line); border-radius:999px; padding:7px 14px; color:var(--text); font-weight:600; }
    .pp-barra { position:sticky; bottom:0; z-index:20; padding:10px 14px calc(10px + env(safe-area-inset-bottom, 0px)); background:linear-gradient(transparent, var(--bg) 35%); display:flex; justify-content:center; pointer-events:none; transition:opacity .25s, transform .25s; }
    .pp-barra .pp-btn { pointer-events:auto; width:100%; max-width:412px; }
    .pp-barra.oculta { opacity:0; transform:translateY(20px); }
    .pp-modal { position:fixed; inset:0; z-index:60; background:rgba(0,0,0,.72); display:flex; align-items:flex-end; justify-content:center; }
    .pp-modal.centro { align-items:center; padding:16px; }
    .pp-hoja { width:100%; max-width:440px; background:var(--card); color:var(--text); border-radius:22px 22px 0 0; padding:20px 18px calc(20px + env(safe-area-inset-bottom, 0px)); border:1px solid var(--line); }
    .pp-copia { margin:12px 0; padding:14px; border-radius:14px; background:var(--chip); border:1px dashed var(--line); text-align:center; font-weight:700; font-size:16px; word-break:break-all; }
    .pp-preview .pp-modal { position:absolute; }
    .pp-preview .pp-crystal, .pp-preview.pp-crystal { background-attachment:scroll; }
</style>
<script>
    // Enlace de cada red a partir del usuario escrito (si ya es un enlace completo, se respeta)
    function dtUrlRed(id, usuario) {
        usuario = String(usuario || '').trim();
        if (/^https?:\/\//i.test(usuario)) return usuario;
        usuario = usuario.replace(/^@/, '');
        const base = { ig: 'https://instagram.com/', fb: 'https://facebook.com/', tk: 'https://tiktok.com/@', in: 'https://linkedin.com/in/', x: 'https://x.com/', yt: 'https://youtube.com/@' };
        return (base[id] || 'https://') + encodeURIComponent(usuario);
    }
    // Texto legible (blanco o casi negro) sobre el color de acento
    function dtSobre(hex) {
        const h = String(hex || '').replace('#', '');
        const n = h.length === 3 ? h.split('').map(c => c + c).join('') : h.slice(0, 6);
        const [r, g, b] = [0, 2, 4].map(i => parseInt(n.substr(i, 2), 16) / 255).map(c => c <= .03928 ? c / 12.92 : ((c + .055) / 1.055) ** 2.4);
        return (0.2126 * r + 0.7152 * g + 0.0722 * b) > 0.45 ? '#0b0b0c' : '#ffffff';
    }
    function dtSoloDigitos(t) { return String(t || '').replace(/[^0-9]/g, ''); }
</script>
@endonce

<div class="pp" :class="['pp-' + (p.tema || 'crystal'), p.preview ? 'pp-preview' : '']"
     :style="`--acc:${p.color || '#3b82f6'}; --sobre:${dtSobre(p.color || '#3b82f6')}`"
     x-data="{ zelleAbierto: false, fotoGrande: null, copiado: false, barraVisible: false,
               accion(nombre, e) { if (p.preview) { e && e.preventDefault(); return; } if (window.trackClick) trackClick(nombre); },
               async compartir(e) {
                   this.accion('compartir', e); if (p.preview) return;
                   const datos = { title: p.nombre, text: p.profesion || p.nombre, url: p.enlace };
                   try { if (navigator.share) { await navigator.share(datos); return; } await navigator.clipboard.writeText(p.enlace); this.copiado = true; setTimeout(() => this.copiado = false, 2000); } catch (err) {}
               },
               async copiar(texto) { try { await navigator.clipboard.writeText(texto); this.copiado = true; setTimeout(() => this.copiado = false, 2000); } catch (err) {} } }"
     x-init="if (!p.preview) { const o = new IntersectionObserver(([en]) => barraVisible = !en.isIntersecting); $nextTick(() => $refs.acciones && o.observe($refs.acciones)); }">

    <div class="pp-wrap">
        {{-- Encabezado: identidad y acciones principales --}}
        <section class="pp-card pp-hero" :class="p.estructura !== 'feria' && p.alineacion === 'izquierda' ? 'izq' : ''">
            <template x-if="p.estructura === 'feria'">
                <div class="pp-portada" :class="p.forma">
                    <template x-if="p.foto"><img :src="p.foto" :alt="p.nombre"></template>
                    <template x-if="!p.foto"><span x-text="(p.nombre || '?').charAt(0).toUpperCase()"></span></template>
                </div>
            </template>
            <div class="pp-id">
                <template x-if="p.estructura !== 'feria'">
                    <div class="pp-av" :class="p.tipo === 'empresa' ? 'cuadro' : ''">
                        <template x-if="p.foto"><img :src="p.foto" :alt="p.nombre"></template>
                        <template x-if="!p.foto"><span x-text="(p.nombre || '?').charAt(0).toUpperCase()"></span></template>
                    </div>
                </template>
                <div style="min-width:0">
                    <h1 class="pp-nombre" x-text="p.nombre || 'Tu nombre'"></h1>
                    <p class="pp-prof" x-show="p.tipo !== 'empresa' && (p.profesion || p.preview)" x-text="p.profesion || 'Tu profesión'"></p>
                    <p class="pp-lugar" x-show="p.direccion">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 21s-7-6.2-7-11a7 7 0 0 1 14 0c0 4.8-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>
                        <span x-text="p.direccion"></span>
                    </p>
                </div>
            </div>

            <div x-ref="acciones">
                <div class="pp-acciones" :class="p.telefono ? '' : 'una'">
                    <a class="pp-btn pri" :class="p.forma" :href="p.vcf" @click="accion('descarga_vcf', $event)">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6M22 11h-6"/></svg>
                        Guardar contacto
                    </a>
                    <a class="pp-btn wa" :class="p.forma" x-show="p.telefono" :href="'https://wa.me/' + dtSoloDigitos(p.telefono)" target="_blank" rel="noopener" @click="accion('whatsapp', $event)">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12.05 0C5.5 0 .16 5.34.16 11.89c0 2.1.55 4.14 1.59 5.95L.06 24l6.3-1.65a11.88 11.88 0 0 0 5.68 1.45h.01c6.55 0 11.89-5.34 11.89-11.89A11.82 11.82 0 0 0 12.05 0zm6.94 16.88c-.29.82-1.71 1.57-2.36 1.67-.6.09-1.37.13-2.21-.14-.51-.16-1.16-.38-2-.74-3.51-1.52-5.81-5.06-5.98-5.29-.17-.23-1.43-1.9-1.43-3.63s.9-2.57 1.23-2.93c.32-.35.7-.44.94-.44l.67.01c.21.01.5-.08.79.6.29.7.99 2.42 1.08 2.6.09.17.15.38.03.61-.12.23-.17.38-.35.58-.17.2-.36.45-.52.61-.17.17-.35.36-.15.7.2.35.9 1.48 1.93 2.4 1.33 1.18 2.45 1.55 2.8 1.72.35.17.55.15.75-.09.2-.23.87-1.01 1.1-1.36.23-.35.46-.29.78-.17.32.12 2.03.96 2.38 1.13.35.17.58.26.67.41.09.14.09.84-.2 1.66z"/></svg>
                        WhatsApp
                    </a>
                </div>
                <div class="pp-mini">
                    <a x-show="p.telefono" :class="p.forma" :href="'tel:' + p.telefono" @click="accion('llamada', $event)">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/></svg>Llamar
                    </a>
                    <a x-show="p.correo" :class="p.forma" :href="'mailto:' + p.correo" @click="accion('correo', $event)">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M22 6l-10 7L2 6"/></svg>Correo
                    </a>
                    <button type="button" :class="p.forma" @click="compartir($event)">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="M8.6 13.5l6.8 4M15.4 6.5l-6.8 4"/></svg><span x-text="copiado ? 'Copiado' : 'Compartir'"></span>
                    </button>
                </div>
            </div>
        </section>

        <section class="pp-card" x-show="p.bio || p.preview">
            <h2 class="pp-h">Sobre mí</h2>
            <p class="pp-bio" x-text="p.bio || 'Tu biografía aparecerá aquí.'"></p>
        </section>

        <section class="pp-card" x-show="p.redes && p.redes.length">
            <h2 class="pp-h">Redes sociales</h2>
            <template x-for="red in (p.redes || [])" :key="red.id">
                <a class="pp-fila" :class="p.forma" :href="dtUrlRed(red.id, red.usuario)" target="_blank" rel="noopener" @click="accion('red_social_' + red.id, $event)">
                    <span class="ic" x-html="red.svg"></span>
                    <span class="tx"><b x-text="red.nombre"></b><small x-text="red.usuario ? '@' + String(red.usuario).replace(/^@/, '').replace(/^https?:\/\/(www\.)?/, '') : 'Ver perfil'"></small></span>
                    <svg class="fl" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
                </a>
            </template>
        </section>

        <section class="pp-card" x-show="p.galeria && p.galeria.length">
            <h2 class="pp-h">Portafolio</h2>
            <div class="pp-gal">
                <template x-for="(img, i) in (p.galeria || [])" :key="i">
                    <button type="button" :class="p.forma" @click="if (!p.preview) fotoGrande = img" :aria-label="'Ver imagen ' + (i + 1)"><img :src="img" alt="" loading="lazy"></button>
                </template>
            </div>
        </section>

        <section class="pp-card" x-show="p.zelle || p.cashapp || p.venmo">
            <h2 class="pp-h">Pagos</h2>
            <button type="button" class="pp-fila" :class="p.forma" x-show="p.zelle" @click="accion('zelle', $event); if (!p.preview) zelleAbierto = true">
                <span class="ic" style="background:#6d1ed41f;color:#8b3fe0"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2"/></svg></span>
                <span class="tx"><b>Zelle</b><small x-text="p.zelle"></small></span>
                <svg class="fl" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
            </button>
            <a class="pp-fila" :class="p.forma" x-show="p.cashapp" :href="'https://cash.app/$' + encodeURIComponent(String(p.cashapp || '').replace(/^\$/, ''))" target="_blank" rel="noopener" @click="accion('cashapp', $event)">
                <span class="ic" style="background:#00d6321f;color:#00b82b"><b style="font-size:16px">$</b></span>
                <span class="tx"><b>Cash App</b><small x-text="p.cashapp"></small></span>
                <svg class="fl" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
            </a>
            <a class="pp-fila" :class="p.forma" x-show="p.venmo" :href="'https://venmo.com/u/' + encodeURIComponent(String(p.venmo || '').replace(/^@/, ''))" target="_blank" rel="noopener" @click="accion('venmo', $event)">
                <span class="ic" style="background:#008cff1f;color:#008cff"><b style="font-size:15px">V</b></span>
                <span class="tx"><b>Venmo</b><small x-text="p.venmo"></small></span>
                <svg class="fl" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
            </a>
        </section>

        <footer class="pp-pie">
            <a class="marca" href="https://dauntoque.com" target="_blank" rel="noopener" @click="if (p.preview) $event.preventDefault()">Tarjeta digital por <b>dauntoque</b></a>
            <a class="crea" href="https://dauntoque.com" target="_blank" rel="noopener" @click="if (p.preview) $event.preventDefault()">¿Quieres una así? Crea la tuya</a>
        </footer>
    </div>

    {{-- Barra fija: "Guardar contacto" siempre a mano cuando el encabezado sale de pantalla --}}
    <div class="pp-barra" :class="barraVisible && !p.preview ? '' : 'oculta'" x-show="!p.preview">
        <a class="pp-btn pri" :class="p.forma" :href="p.vcf" @click="accion('descarga_vcf', $event)">Guardar contacto</a>
    </div>

    {{-- Zelle: dato para copiar --}}
    <div class="pp-modal" x-show="zelleAbierto" x-transition.opacity x-cloak @click.self="zelleAbierto = false">
        <div class="pp-hoja">
            <h2 class="pp-h" style="margin:0">Pagar con Zelle</h2>
            <p style="margin:6px 0 0;font-size:13px;color:var(--muted)">Abre tu banco y envía el pago a:</p>
            <div class="pp-copia" x-text="p.zelle"></div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
                <button type="button" class="pp-btn sec" @click="zelleAbierto = false">Cerrar</button>
                <button type="button" class="pp-btn pri" @click="copiar(p.zelle)" x-text="copiado ? 'Copiado ✓' : 'Copiar'"></button>
            </div>
        </div>
    </div>

    {{-- Galería a pantalla completa --}}
    <div class="pp-modal centro" x-show="fotoGrande" x-transition.opacity x-cloak @click="fotoGrande = null">
        <img :src="fotoGrande" alt="" style="max-width:100%;max-height:90vh;border-radius:14px">
    </div>
</div>
