{{-- Botón "Agendar cita" (app Agenda): aparece en todos los diseños cuando la agenda recibe reservas --}}
<template x-if="p.agenda">
    <a class="pp-agenda" :class="p.forma" :href="p.agenda + '?lang=' + idioma" @click="accion('agenda', $event)">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="flex:none"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18M9 16l2 2 4-4"/></svg>
        <span class="ajusta" x-text="t('agendar')">Agendar cita</span>
    </a>
</template>
