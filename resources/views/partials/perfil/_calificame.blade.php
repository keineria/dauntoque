{{-- App Califícame: sello de estrellas, los comentarios que eligió el dueño (uno, varios o ninguno), su negocio de Google y el botón para calificar --}}
<template x-if="p.calificame">
    <section class="pp-cf" aria-labelledby="pp-cf-t">
        <template x-if="p.calificame.sello">
            <div class="pp-cf-sello">
                <b id="pp-cf-t" x-text="idioma === 'es' ? String(p.calificame.promedio.toFixed(1)).replace('.', ',') : p.calificame.promedio.toFixed(1)"></b>
                <span class="pp-cf-est" aria-hidden="true" x-text="'★'.repeat(Math.round(p.calificame.promedio)) + '☆'.repeat(5 - Math.round(p.calificame.promedio))"></span>
                <small x-text="p.calificame.total + ' ' + t(p.calificame.total === 1 ? 'calificacion' : 'calificaciones')"></small>
            </div>
        </template>
        <template x-if="!p.calificame.sello && p.calificame.comentarios.length">
            <h2 id="pp-cf-t" class="pp-cf-h" x-text="t('opiniones')"></h2>
        </template>
        <template x-for="(c, i) in p.calificame.comentarios" :key="i">
            <figure class="pp-cf-com">
                <span class="pp-cf-est" :aria-label="c.estrellas + ' / 5'" x-text="'★'.repeat(c.estrellas)"></span>
                <blockquote x-text="'“' + c.texto + '”'"></blockquote>
                <figcaption x-show="c.nombre" x-text="'— ' + c.nombre"></figcaption>
            </figure>
        </template>
        {{-- Negocio de Google que conectó el dueño: aparte, tal como lo entrega Google (sin mezclarlo con Califícame) --}}
        <template x-if="p.calificame.google">
            <div class="pp-cf-google">
                <a class="pp-cf-sello" :href="p.calificame.google.enlace" target="_blank" rel="noopener" @click="accion('google_resenas', $event)">
                    <svg width="18" height="18" viewBox="0 0 48 48" aria-hidden="true"><path fill="#4285F4" d="M45.1 24.5c0-1.6-.1-3.1-.4-4.5H24v8.5h11.8c-.5 2.8-2.1 5.1-4.4 6.7v5.6h7.1c4.2-3.8 6.6-9.5 6.6-16.3z"/><path fill="#34A853" d="M24 46c5.9 0 10.9-2 14.5-5.3l-7.1-5.6c-2 1.3-4.5 2.1-7.4 2.1-5.7 0-10.5-3.8-12.2-9H4.5v5.7C8.1 41.1 15.5 46 24 46z"/><path fill="#FBBC05" d="M11.8 28.2c-.4-1.3-.7-2.7-.7-4.2s.3-2.9.7-4.2v-5.7H4.5C3 17.1 2 20.4 2 24s1 6.9 2.5 9.9l7.3-5.7z"/><path fill="#EA4335" d="M24 10.8c3.2 0 6.1 1.1 8.4 3.3l6.3-6.3C34.9 4.2 29.9 2 24 2 15.5 2 8.1 6.9 4.5 14.1l7.3 5.7c1.7-5.2 6.5-9 12.2-9z"/></svg>
                    <b x-text="idioma === 'es' ? Number(p.calificame.google.promedio).toFixed(1).replace('.', ',') : Number(p.calificame.google.promedio).toFixed(1)"></b>
                    <span class="pp-cf-est" aria-hidden="true" x-text="'★'.repeat(Math.round(p.calificame.google.promedio)) + '☆'.repeat(5 - Math.round(p.calificame.google.promedio))"></span>
                    <small x-text="p.calificame.google.total + ' ' + t('resenasGoogle')"></small>
                </a>
                <template x-for="(r, i) in p.calificame.google.resenas" :key="'g' + i">
                    <figure class="pp-cf-com">
                        <span class="pp-cf-est" :aria-label="r.estrellas + ' / 5'" x-text="'★'.repeat(r.estrellas)"></span>
                        <blockquote x-text="'“' + r.texto + '”'"></blockquote>
                        <figcaption><span x-text="'— ' + r.autor"></span><span x-show="r.cuando" x-text="' · ' + r.cuando"></span> · Google</figcaption>
                    </figure>
                </template>
                <a class="pp-cf-btn" :class="p.forma" :href="p.calificame.google.escribir" target="_blank" rel="noopener" @click="accion('google_resena', $event)" x-text="t('escribirGoogle')"></a>
            </div>
        </template>
        <template x-if="p.calificame.enlace">
            <a class="pp-cf-btn" :class="p.forma" :href="p.calificame.enlace + '?lang=' + idioma" @click="accion('calificame', $event)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2.5l2.9 6.1 6.6.8-4.9 4.6 1.3 6.6L12 17.3l-5.9 3.3 1.3-6.6L2.5 9.4l6.6-.8z"/></svg>
                <span x-text="t('calificar')">Califica mi atención</span>
            </a>
        </template>
    </section>
</template>
