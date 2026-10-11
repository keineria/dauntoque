{{-- Diseño "Moderno": secciones en tarjetas, acciones principales arriba y redes en filas. --}}
    <div class="pp-wrap">
        {{-- Encabezado: identidad y acciones principales --}}
        <section class="pp-card pp-hero" :class="p.alineacion === 'izquierda' ? 'izq' : ''">
            <div class="pp-id">
                <div class="pp-av" :class="p.tipo === 'empresa' ? 'cuadro' : ''">
                    <template x-if="p.foto"><img :src="p.foto" :alt="p.nombre"></template>
                    <template x-if="!p.foto"><span x-text="(p.nombre || '?').charAt(0).toUpperCase()"></span></template>
                </div>
                <div style="min-width:0">
                    <h1 class="pp-nombre" x-text="p.nombre || t('tuNombre')"></h1>
                    <p class="pp-prof" x-show="p.tipo !== 'empresa' && (p.profesion || p.preview)" x-text="p.profesion || t('tuProfesion')"></p>
                    <p class="pp-lugar" x-show="p.direccion">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 21s-7-6.2-7-11a7 7 0 0 1 14 0c0 4.8-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>
                        <span x-text="p.direccion"></span>
                    </p>
                </div>
            </div>

            <div x-ref="acciones">
                @include('partials.perfil._agenda')
                <div class="pp-acciones" :class="p.telefono ? '' : 'una'">
                    <a class="pp-btn pri" :class="p.forma" :href="p.vcf" @click="accion('descarga_vcf', $event)">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6M22 11h-6"/></svg><span class="ajusta" x-text="t('guardar')">Guardar contacto</span></a>
                    <a class="pp-btn wa" :class="p.forma" x-show="p.telefono" :href="'https://wa.me/' + dtSoloDigitos(p.telefono)" target="_blank" rel="noopener" @click="accion('whatsapp', $event)">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12.05 0C5.5 0 .16 5.34.16 11.89c0 2.1.55 4.14 1.59 5.95L.06 24l6.3-1.65a11.88 11.88 0 0 0 5.68 1.45h.01c6.55 0 11.89-5.34 11.89-11.89A11.82 11.82 0 0 0 12.05 0zm6.94 16.88c-.29.82-1.71 1.57-2.36 1.67-.6.09-1.37.13-2.21-.14-.51-.16-1.16-.38-2-.74-3.51-1.52-5.81-5.06-5.98-5.29-.17-.23-1.43-1.9-1.43-3.63s.9-2.57 1.23-2.93c.32-.35.7-.44.94-.44l.67.01c.21.01.5-.08.79.6.29.7.99 2.42 1.08 2.6.09.17.15.38.03.61-.12.23-.17.38-.35.58-.17.2-.36.45-.52.61-.17.17-.35.36-.15.7.2.35.9 1.48 1.93 2.4 1.33 1.18 2.45 1.55 2.8 1.72.35.17.55.15.75-.09.2-.23.87-1.01 1.1-1.36.23-.35.46-.29.78-.17.32.12 2.03.96 2.38 1.13.35.17.58.26.67.41.09.14.09.84-.2 1.66z"/></svg><span class="ajusta" x-text="t('whatsapp')">WhatsApp</span></a>
                </div>
                <div class="pp-mini">
                    <a x-show="p.telefono" :class="p.forma" :href="'tel:' + p.telefono" @click="accion('llamada', $event)">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/></svg><span class="ajusta" x-text="t('llamar')">Llamar</span></a>
                    <a x-show="p.correo" :class="p.forma" :href="'mailto:' + p.correo" @click="accion('correo', $event)">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M22 6l-10 7L2 6"/></svg><span class="ajusta" x-text="t('correo')">Correo</span></a>
                    <button type="button" :class="p.forma" @click="compartir($event)">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="M8.6 13.5l6.8 4M15.4 6.5l-6.8 4"/></svg><span x-text="copiado ? t('copiado') : t('compartir')"></span>
                    </button>
                </div>
            </div>
        </section>

        <section class="pp-card" x-show="p.bio || p.preview">
            <h2 class="pp-h"><span x-text="t('sobreMi')">Sobre mí</span></h2>
            <p class="pp-bio" x-text="p.bio || t('tuBio')"></p>
        </section>

        <section class="pp-card" x-show="p.redes && p.redes.length">
            <h2 class="pp-h"><span x-text="t('redes')">Redes sociales</span></h2>
            <template x-for="red in (p.redes || [])" :key="red.id">
                <a class="pp-fila" :class="p.forma" :href="dtUrlRed(red.id, red.usuario)" target="_blank" rel="noopener" @click="accion('red_social_' + red.id, $event)">
                    <span class="ic" x-html="red.svg"></span>
                    <span class="tx"><b x-text="red.nombre"></b><small x-text="red.usuario ? '@' + String(red.usuario).replace(/^@/, '').replace(/^https?:\/\/(www\.)?/, '') : t('verPerfil')"></small></span>
                    <svg class="fl" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
                </a>
            </template>
        </section>

        <section class="pp-card" x-show="p.galeria && p.galeria.length">
            <h2 class="pp-h"><span x-text="t('portafolio')">Portafolio</span></h2>
            <div class="pp-gal">
                <template x-for="(img, i) in (p.galeria || [])" :key="i">
                    <button type="button" style="border-radius:14px" @click="if (!p.preview) fotoGrande = img" :aria-label="t('verImagen') + ' ' + (i + 1)"><img :src="img" alt="" loading="lazy"></button>
                </template>
            </div>
        </section>

        <section class="pp-card" x-show="(p.pagos || []).length">
            <h2 class="pp-h"><span x-text="t('pagos')">Pagos</span></h2>
            <template x-for="m in (p.pagos || [])" :key="m.type">
                <a class="pp-fila" :class="p.forma" :href="dtPagoUrl(m) || '#'" :target="dtPagoUrl(m) ? '_blank' : null" rel="noopener" @click="pagar(m, $event)">
                    <span class="ic" :style="`background:${dtPago(m).acento}1f;color:${dtPago(m).acento}`"><b style="font-size:16px" x-text="dtPago(m).icono"></b></span>
                    <span class="tx"><b x-text="dtPago(m).nombre"></b><small x-text="m.value"></small></span>
                    <svg class="fl" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
                </a>
            </template>
        </section>

        <footer class="pp-pie">
            <a class="marca" href="https://dauntoque.com" target="_blank" rel="noopener" @click="if (p.preview) $event.preventDefault()"><span x-text="t('tarjetaPor')">Tarjeta digital por</span> <b>dauntoque</b></a>
            <a class="crea" href="https://dauntoque.com" target="_blank" rel="noopener" @click="if (p.preview) $event.preventDefault()" x-text="t('quieresUna')">¿Quieres una así? Crea la tuya</a>
        </footer>
    </div>
