{{-- App Califícame: sello de estrellas, los comentarios que eligió el dueño (uno, varios o ninguno) y el botón para calificar --}}
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
        <template x-if="p.calificame.enlace">
            <a class="pp-cf-btn" :class="p.forma" :href="p.calificame.enlace + '?lang=' + idioma" @click="accion('calificame', $event)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2.5l2.9 6.1 6.6.8-4.9 4.6 1.3 6.6L12 17.3l-5.9 3.3 1.3-6.6L2.5 9.4l6.6-.8z"/></svg>
                <span x-text="t('calificar')">Califica mi atención</span>
            </a>
        </template>
    </section>
</template>
