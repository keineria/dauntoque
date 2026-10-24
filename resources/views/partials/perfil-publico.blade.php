{{--
    Cuerpo del perfil público. Una sola plantilla para la página pública y para la vista previa del editor.
    Necesita en Alpine un objeto `p` con: nombre, profesion, bio, foto, tipo, alineacion, estructura, tema, color, forma,
    telefono, correo, direccion, pagos [{type, value}], redes [{id, nombre, svg, usuario}], galeria [{url, titulo}], enlace, vcf, preview.
    Cada diseño vive en partials/perfil/*.blade.php; aquí van los colores por estilo, el idioma (t()), el ajuste de textos
    y las ventanas compartidas.
--}}
@once
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
    .pp { --acc:#3b82f6; --sobre:#fff; --bg:#0a0a0b; --card:#131316; --text:#f4f4f5; --muted:#a1a1aa; --line:rgba(255,255,255,.08); --chip:rgba(255,255,255,.06);
          --titulo:"Inter", ui-sans-serif, system-ui, sans-serif;
          min-height:100vh; min-height:100dvh; background:var(--bg); color:var(--text); font-family:var(--texto, "Inter"), ui-sans-serif, system-ui, -apple-system, "Segoe UI", sans-serif; position:relative; }
    .pp-crystal { --bg:#0b0d12; --card:rgba(22,24,32,.62); --line:rgba(255,255,255,.09);
                  background:linear-gradient(180deg, rgba(9,10,14,.82), rgba(9,10,14,.97)), url('https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?q=80&w=1000&auto=format&fit=crop') center/cover fixed; }
    .pp-crystal .pp-card, .pp-medianoche .pp-card { backdrop-filter:blur(18px); -webkit-backdrop-filter:blur(18px); }
    .pp-matte { --bg:#09090b; --card:#121214; --line:rgba(255,255,255,.06); --muted:#8b8b94; }
    .pp-luxury { --bg:#070707; --card:#101010; --text:#f5ecd7; --muted:#b5a47c; --line:color-mix(in srgb, var(--acc) 35%, transparent); --chip:rgba(255,255,255,.04); --titulo:Georgia, "Times New Roman", serif; }
    .pp-corporativo { --bg:#eef2f7; --card:#ffffff; --text:#0f172a; --muted:#64748b; --line:#e2e8f0; --chip:#f1f5f9; --titulo:Georgia, "Times New Roman", serif; }
    .pp-sport { --bg:#0c0c0c; --card:#000; --muted:#9ca3af; --line:rgba(255,255,255,.08); background:#0c0c0c radial-gradient(#252b36 1px, transparent 1px) 0 0/18px 18px; }
    .pp-sport .pp-nombre { text-transform:uppercase; font-style:italic; letter-spacing:.01em; font-weight:900; }
    .pp-sport .pp-card { border-left:3px solid var(--acc); }
    .pp-minimalista { --bg:#fafafa; --card:#ffffff; --text:#111111; --muted:#6b7280; --line:#ececec; --chip:#f4f4f5; }
    /* Estilos sobrios para perfiles ejecutivos y premium */
    .pp-onyx { --bg:#080808; --card:#131313; --text:#f5f5f4; --muted:#a8a29e; --line:rgba(255,255,255,.08); --chip:rgba(255,255,255,.05);
               background:#080808 radial-gradient(90% 50% at 50% 0%, color-mix(in srgb, var(--acc) 14%, transparent), transparent 70%); }
    .pp-medianoche { --bg:#060a14; --card:rgba(15,23,42,.72); --text:#f1f5f9; --muted:#94a3b8; --line:rgba(148,163,184,.14); --chip:rgba(148,163,184,.08);
                     background:#060a14 radial-gradient(80% 50% at 50% 0%, rgba(37,99,235,.22), transparent 70%); }
    .pp-rose { --bg:#130b10; --card:rgba(34,19,28,.82); --text:#fdf2f8; --muted:#d8b4c4; --line:rgba(244,194,214,.14); --chip:rgba(244,194,214,.07); --titulo:Georgia, "Times New Roman", serif;
               background:#130b10 radial-gradient(90% 55% at 50% 0%, color-mix(in srgb, var(--acc) 22%, transparent), transparent 70%); }
    .pp-esmeralda { --bg:#05110d; --card:rgba(10,30,23,.8); --text:#ecfdf5; --muted:#9bc4b3; --line:rgba(167,243,208,.12); --chip:rgba(167,243,208,.06); --titulo:Georgia, "Times New Roman", serif;
                    background:#05110d radial-gradient(90% 55% at 50% 0%, rgba(16,185,129,.16), transparent 70%); }

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
    .pp-nombre { font:800 24px/1.15 var(--titulo); letter-spacing:-.01em; margin:0; overflow-wrap:anywhere; }
    .pp-prof { margin:4px 0 0; font-size:12px; font-weight:700; letter-spacing:.12em; text-transform:uppercase; color:var(--acc); }
    .pp-luxury .pp-prof, .pp-corporativo .pp-prof, .pp-minimalista .pp-prof { color:color-mix(in srgb, var(--acc) 85%, var(--text)); }
    .pp-lugar { margin:6px 0 0; font-size:12.5px; color:var(--muted); display:flex; align-items:center; gap:4px; justify-content:center; }
    .pp-hero.izq .pp-lugar { justify-content:flex-start; }
    .pp-portada { margin:-18px -18px 16px; aspect-ratio:1/1; overflow:hidden; background:var(--chip); display:grid; place-items:center; font:800 64px var(--titulo); color:var(--muted); }

    .pp-acciones { display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-top:18px; }
    .pp-acciones.una { grid-template-columns:1fr; }
    .pp-btn { height:50px; display:flex; align-items:center; justify-content:center; gap:8px; padding:0 10px; font-weight:700; font-size:14.5px; white-space:nowrap; text-decoration:none; border:0; cursor:pointer; transition:transform .12s, filter .12s; min-width:0; }
    .pp-acciones .pp-btn { font-size:14px; gap:6px; }
    .pp-btn:active { transform:scale(.98); }
    .pp-agenda { display:flex; align-items:center; justify-content:center; gap:8px; width:100%; height:52px; margin:0 0 10px; padding:0 12px; border-radius:16px; background:var(--acc); color:var(--sobre); font-weight:800; font-size:15px; text-decoration:none; box-shadow:0 12px 28px -12px var(--acc); white-space:nowrap; }
    .pp-agenda:active { transform:scale(.98); }
    .pp-btn:hover { filter:brightness(1.06); }
    .pp-btn.pri { background:var(--acc); color:var(--sobre); box-shadow:0 10px 24px -10px var(--acc); }
    .pp-btn.wa { background:#25d366; color:#06210f; }
    .pp-btn.sec { background:var(--chip); color:var(--text); border:1px solid var(--line); height:46px; font-size:13.5px; }
    .pp-mini { display:flex; justify-content:center; gap:8px; margin-top:10px; }
    .pp-mini a, .pp-mini button { flex:1; min-width:0; height:42px; display:flex; align-items:center; justify-content:center; gap:6px; background:var(--chip); border:1px solid var(--line); color:var(--text); font-size:12.5px; font-weight:600; text-decoration:none; cursor:pointer; }

    /* Textos que se encogen para caber en una línea (dtAjustar): botones, correos, teléfonos */
    .ajusta { display:block; min-width:0; max-width:100%; white-space:nowrap; overflow:hidden; }

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
    /* Nombre del producto o servicio sobre cada foto de la galería (todos los diseños) */
    .pp-gal-item { position:relative; }
    .pp-gal-tit { position:absolute; left:0; right:0; bottom:0; padding:22px 10px 0; border-bottom:8px solid transparent; background:linear-gradient(transparent, rgba(0,0,0,.72)) border-box; color:#fff; font-size:12px; font-weight:600; line-height:1.25; text-align:left; pointer-events:none;
                  display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
    .pp-gal-item:has(.lupa) .pp-gal-tit { padding-right:42px; }
    .pp-foto-grande { margin:0; display:flex; flex-direction:column; align-items:center; gap:10px; max-width:100%; }
    .pp-foto-grande figcaption { color:#fff; font-size:15px; font-weight:600; text-align:center; }
    .pp-hoja { width:100%; max-width:440px; background:var(--card); color:var(--text); border-radius:22px 22px 0 0; padding:20px 18px calc(20px + env(safe-area-inset-bottom, 0px)); border:1px solid var(--line); }
    .pp-copia { margin:12px 0; padding:14px; border-radius:14px; background:var(--chip); border:1px dashed var(--line); text-align:center; font-weight:700; font-size:16px; word-break:break-all; }
    .pp-preview { min-height:100%; }
    .pp-preview .pp-modal { position:absolute; }
    .pp-preview .pp-crystal, .pp-preview.pp-crystal { background-attachment:scroll; }

    /* Botón de idioma (como en los perfiles de clientes) */
    .pp-idioma { position:absolute; top:14px; right:14px; z-index:5; height:30px; padding:0 12px; border-radius:999px; background:var(--chip); border:1px solid var(--line); color:var(--text); font-size:11.5px; font-weight:800; letter-spacing:.08em; cursor:pointer; }

    /* Bienvenida: "Bienvenido al perfil de …" solo mientras carga (se quita apenas el contenido está listo) */
    .pp-bienvenida { position:fixed; inset:0; z-index:90; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:16px; background:var(--bg); color:var(--text); text-align:center; padding:24px; transition:opacity .2s ease, visibility .2s; }
    .pp-bienvenida.fuera { opacity:0; visibility:hidden; pointer-events:none; }
    .pp-bienvenida .aro { width:96px; height:96px; border-radius:50%; padding:3px; background:conic-gradient(from 0deg, var(--acc), transparent 70%, var(--acc)); animation:pp-gira 1.1s linear infinite; }
    .pp-bienvenida .aro > div { width:100%; height:100%; border-radius:50%; overflow:hidden; background:var(--card); display:grid; place-items:center; font:800 34px var(--titulo); color:var(--muted); animation:pp-gira 1.1s linear infinite reverse; }
    .pp-bienvenida .aro img { width:100%; height:100%; object-fit:cover; }
    .pp-bienvenida small { font-size:11.5px; font-weight:700; letter-spacing:.16em; text-transform:uppercase; color:var(--muted); }
    .pp-bienvenida b { font:800 24px/1.2 var(--titulo); max-width:18ch; }
    @keyframes pp-gira { to { transform:rotate(360deg); } }
    @media (prefers-reduced-motion: reduce) { .pp-bienvenida .aro, .pp-bienvenida .aro > div { animation:none; } }
</style>
<script>
    // Textos fijos del perfil en español e inglés (el contenido que escribe el usuario no se traduce)
    window.DT_TXT = {
        es: { guardar: 'Guardar contacto', whatsapp: 'WhatsApp', escribeme: 'Escríbeme por WhatsApp', reservar: 'Reservar por WhatsApp', llamar: 'Llamar', correo: 'Correo', enviarCorreo: 'Enviar un correo',
              compartir: 'Compartir', compartirTarjeta: 'Compartir mi tarjeta', copiadoEnlace: 'Enlace copiado', sobreMi: 'Sobre mí', perfil: 'Perfil', redes: 'Redes sociales', portafolio: 'Portafolio',
              trabajos: 'Mis trabajos', pagos: 'Pagos', contacto: 'Contacto', telefono: 'Teléfono', ubicacion: 'Ubicación', verPerfil: 'Ver perfil', pagarCon: 'Pagar con',
              abreBanco: 'Abre tu banco y envía el pago a:', cerrar: 'Cerrar', copiar: 'Copiar', copiado: 'Copiado ✓', tarjetaPor: 'Tarjeta digital por', quieresUna: '¿Quieres una así? Crea la tuya',
              creaLaTuya: 'Crea la tuya', bienvenida: 'Bienvenido al perfil de', tuNombre: 'Tu nombre', tuProfesion: 'Tu profesión', tuBio: 'Tu biografía aparecerá aquí.', verImagen: 'Ver imagen', otroIdioma: 'EN', agendar: 'Agendar cita' },
        en: { guardar: 'Save contact', whatsapp: 'WhatsApp', escribeme: 'Message me on WhatsApp', reservar: 'Book on WhatsApp', llamar: 'Call', correo: 'Email', enviarCorreo: 'Send an email',
              compartir: 'Share', compartirTarjeta: 'Share my card', copiadoEnlace: 'Link copied', sobreMi: 'About me', perfil: 'About', redes: 'Social media', portafolio: 'Portfolio',
              trabajos: 'My work', pagos: 'Payments', contacto: 'Contact', telefono: 'Phone', ubicacion: 'Location', verPerfil: 'View profile', pagarCon: 'Pay with',
              abreBanco: 'Open your bank app and send the payment to:', cerrar: 'Close', copiar: 'Copy', copiado: 'Copied ✓', tarjetaPor: 'Digital card by', quieresUna: 'Want one like this? Create yours',
              creaLaTuya: 'Create yours', bienvenida: 'Welcome to the profile of', tuNombre: 'Your name', tuProfesion: 'Your profession', tuBio: 'Your bio will appear here.', verImagen: 'View image', otroIdioma: 'ES', agendar: 'Book an appointment' },
    };
    // Idioma: el que eligió el visitante, si no el del celular (español si empieza por "es", si no inglés)
    function dtIdiomaInicial(preview) {
        if (preview) return 'es';
        try { const g = localStorage.getItem('dt-idioma'); if (g === 'es' || g === 'en') return g; } catch (e) {}
        return /^es\b/i.test(navigator.language || 'es') ? 'es' : 'en';
    }
    // Encoge la letra de cada .ajusta hasta que quepa en su espacio (mínimo 10.5 px)
    function dtAjustar(raiz) {
        if (!raiz) return;
        raiz.querySelectorAll('.ajusta').forEach(el => {
            el.style.fontSize = '';
            let t = parseFloat(getComputedStyle(el).fontSize);
            while (el.scrollWidth > el.clientWidth + 1 && t > 10.5) { t -= 0.5; el.style.fontSize = t + 'px'; }
        });
    }
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
        return (0.2126 * r + 0.7152 * g + 0.0722 * b) > 0.36 ? '#0b0b0c' : '#ffffff';
    }
    // Tipografía elegida (config/tipografias.php): cambia la letra de títulos y textos; sin elegir, queda la del estilo
    window.DT_FUENTES = @json(collect(config('tipografias'))->map(fn ($t) => ['titulo' => $t['titulo'], 'texto' => $t['texto']]));
    function dtFuente(id) {
        const f = window.DT_FUENTES[id];
        return f ? `; --titulo:${f.titulo}; --titulo-bq:${f.titulo}; --texto:${f.texto}; font-synthesis-weight:none` : '';
    }
    // Galería: [{url, titulo}]; también acepta la forma vieja (solo la ruta de cada foto)
    function dtGal(lista) { return (lista || []).map(g => typeof g === 'string' ? { url: g, titulo: '' } : g).filter(g => g && g.url); }
    function dtSoloDigitos(t) { return String(t || '').replace(/[^0-9]/g, ''); }
    // Medios de pago (config/pagos.php). Cada medio del perfil llega como { type, value }
    window.DT_PAGOS = @json(config('pagos.medios'));
    function dtPago(m) { return window.DT_PAGOS[m && m.type] || { nombre: '', icono: '', fondo: '#444', texto: '#fff', acento: '#888', accion: 'copiar' }; }
    function dtPagoEstilo(m) { const c = dtPago(m); return `background:${c.fondo};color:${c.texto}`; }
    // Enlace directo a la app (Cash App, Venmo); null si el dato se copia (Nequi, Llave Bre-B, Zelle)
    function dtPagoUrl(m) {
        const c = dtPago(m);
        if (c.accion !== 'enlace') return null;
        const valor = String(m.value || '').trim();
        return c.enlace + encodeURIComponent(c.quitar && valor.startsWith(c.quitar) ? valor.slice(c.quitar.length) : valor);
    }
</script>
@endonce

{{-- Diseños disponibles (design_config.layout_estructura): clasica (y feria), premium, boutique, moderna, enlaces, ejecutiva (Deportivo) --}}
<div class="pp" :class="['pp-' + (p.tema || 'crystal'), p.preview ? 'pp-preview' : '']"
     :style="`--acc:${p.color || '#3b82f6'}; --sobre:${dtSobre(p.color || '#3b82f6')}` + dtFuente(p.tipografia)"
     x-data="{ pagoAbierto: null, fotoGrande: null, copiado: false, barraVisible: false, idioma: dtIdiomaInicial(p.preview), bienvenida: !p.preview,
               t(clave) { return (window.DT_TXT[this.idioma] || window.DT_TXT.es)[clave] || clave; },
               cambiarIdioma() { this.idioma = this.idioma === 'es' ? 'en' : 'es'; try { localStorage.setItem('dt-idioma', this.idioma); } catch (e) {} this.$nextTick(() => dtAjustar(this.$el)); },
               accion(nombre, e) { if (p.preview) { e && e.preventDefault(); return; } if (window.trackClick) trackClick(nombre); },
               pagar(m, e) { this.accion(m.type, e); if (p.preview || dtPagoUrl(m)) return; e.preventDefault(); this.copiado = false; this.pagoAbierto = m; },
               async compartir(e) {
                   this.accion('compartir', e); if (p.preview) return;
                   const datos = { title: p.nombre, text: p.profesion || p.nombre, url: p.enlace };
                   try { if (navigator.share) { await navigator.share(datos); return; } await navigator.clipboard.writeText(p.enlace); this.copiado = true; setTimeout(() => this.copiado = false, 2000); } catch (err) {}
               },
               async copiar(texto) { try { await navigator.clipboard.writeText(texto); this.copiado = true; setTimeout(() => this.copiado = false, 2000); } catch (err) {} } }"
     x-init="document.documentElement.lang = idioma;
             if (!p.preview) { const o = new IntersectionObserver(([en]) => barraVisible = !en.isIntersecting); $nextTick(() => { $refs.acciones && o.observe($refs.acciones); bienvenida = false; }); }
             $nextTick(() => dtAjustar($el)); window.addEventListener('resize', () => dtAjustar($el));"
     x-effect="JSON.stringify(p); idioma; $nextTick(() => dtAjustar($el))">

    {{-- Bienvenida con el nombre de la persona, en el idioma del celular --}}
    <div class="pp-bienvenida" :class="bienvenida ? '' : 'fuera'" x-show="!p.preview" aria-hidden="true">
        <div class="aro"><div>
            <template x-if="p.foto"><img :src="p.foto" alt=""></template>
            <template x-if="!p.foto"><span x-text="(p.nombre || '?').charAt(0).toUpperCase()"></span></template>
        </div></div>
        <small x-text="t('bienvenida')"></small>
        <b x-text="p.nombre"></b>
    </div>

    <button type="button" class="pp-idioma" x-show="!p.preview" @click="cambiarIdioma()" :aria-label="idioma === 'es' ? 'Switch to English' : 'Cambiar a español'" x-text="t('otroIdioma')"></button>

    <template x-if="p.estructura === 'premium'">
        @include('partials.perfil.premium')
    </template>
    <template x-if="p.estructura === 'boutique'">
        @include('partials.perfil.boutique')
    </template>
    <template x-if="p.estructura === 'moderna'">
        <div>@include('partials.perfil.moderna')</div>
    </template>
    <template x-if="p.estructura === 'enlaces'">
        @include('partials.perfil.enlaces')
    </template>
    <template x-if="p.estructura === 'ejecutiva'">
        @include('partials.perfil.ejecutiva')
    </template>
    <template x-if="!['premium', 'boutique', 'moderna', 'enlaces', 'ejecutiva'].includes(p.estructura)">
        @include('partials.perfil.clasica')
    </template>

    {{-- Barra fija: "Guardar contacto" siempre a mano cuando el botón principal no está en pantalla --}}
    <div class="pp-barra" :class="barraVisible && !p.preview ? '' : 'oculta'" x-show="!p.preview && p.estructura !== 'enlaces'">
        <a class="pp-btn pri" :class="p.forma" :href="p.vcf" @click="accion('descarga_vcf', $event)" x-text="t('guardar')">Guardar contacto</a>
    </div>

    {{-- Nequi, Llave Bre-B y Zelle: el dato para copiar --}}
    <div class="pp-modal" x-show="pagoAbierto" x-transition.opacity x-cloak @click.self="pagoAbierto = null">
        <div class="pp-hoja">
            <h2 class="pp-h" style="margin:0" x-text="t('pagarCon') + ' ' + dtPago(pagoAbierto).nombre"></h2>
            <p style="margin:6px 0 0;font-size:13px;color:var(--muted)" x-text="(dtPago(pagoAbierto).ayuda || {})[idioma] || t('abreBanco')"></p>
            <div class="pp-copia" x-text="pagoAbierto && pagoAbierto.value"></div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
                <button type="button" class="pp-btn sec" @click="pagoAbierto = null" x-text="t('cerrar')"></button>
                <button type="button" class="pp-btn pri" @click="copiar(pagoAbierto.value)" x-text="copiado ? t('copiado') : t('copiar')"></button>
            </div>
        </div>
    </div>

    {{-- Galería a pantalla completa --}}
    <div class="pp-modal centro" x-show="fotoGrande" x-transition.opacity x-cloak @click="fotoGrande = null">
        <figure class="pp-foto-grande">
            <img :src="fotoGrande && fotoGrande.url" :alt="fotoGrande && fotoGrande.titulo || ''" style="max-width:100%;max-height:82vh;border-radius:14px">
            <figcaption x-show="fotoGrande && fotoGrande.titulo" x-text="fotoGrande && fotoGrande.titulo"></figcaption>
        </figure>
    </div>
</div>
