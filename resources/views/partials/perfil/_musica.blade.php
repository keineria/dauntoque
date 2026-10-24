{{-- App Música: canción destacada y lista de Spotify con el reproductor oficial. Nunca suena sola: el visitante toca play --}}
<template x-if="p.musica">
    <section class="pp-mu" aria-labelledby="pp-mu-t">
        <h2 id="pp-mu-t" class="pp-mu-h" x-text="p.musica.titulo || t('musica')">Mi música</h2>
        <template x-if="p.musica.cancion">
            <iframe class="pp-mu-player" :title="t('cancionDestacada')" :src="p.musica.cancion.src" :height="p.musica.cancion.alto" allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture" loading="lazy"></iframe>
        </template>
        <template x-if="p.musica.lista">
            <iframe class="pp-mu-player" :title="t('listaMusica')" :src="p.musica.lista.src" :height="p.musica.lista.alto" allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture" loading="lazy"></iframe>
        </template>
    </section>
</template>
