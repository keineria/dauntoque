@extends('layouts.app')

@section('contenido')
<div x-data="gestorPerfiles()" x-init="iniciar()">

    <div class="dt-head">
        <div>
            <h1 class="dt-h1">Mis perfiles</h1>
            <p class="dt-sub">Cada perfil tiene su propio estilo. El que lleva la etiqueta verde es el que se ve en público y abre tu tarjeta NFC.</p>
        </div>
        <div class="dt-panel" style="display:flex;align-items:center;gap:14px;padding:8px 8px 8px 14px">
            <div>
                <b style="font-size:13px"><span x-text="fichas.length"></span> de {{ $limite }} perfiles</b>
                <div style="width:90px;height:5px;border-radius:9px;background:var(--surface);overflow:hidden;margin-top:4px">
                    <div style="height:100%;background:var(--primary)" :style="`width:${Math.min(100, fichas.length * 100 / {{ $limite }})}%`"></div>
                </div>
            </div>
            <button class="dt-btn pri" @click="nuevo()" :disabled="fichas.length >= {{ $limite }}">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>Nuevo perfil
            </button>
        </div>
    </div>

    {{-- Fichas de perfiles --}}
    <section class="dt-panel" aria-labelledby="h-perfiles">
        <div class="dt-panel-h"><h2 id="h-perfiles">Perfiles</h2></div>

        <template x-if="fichas.length === 0">
            <button class="dt-new" style="max-width:320px;width:100%" @click="nuevo()">
                <div><b style="display:block;color:var(--text)">Crea tu primer perfil</b><small>La Forja lo armará contigo paso a paso.</small></div>
            </button>
        </template>

        <div class="dt-tiles" x-show="fichas.length > 0">
            <template x-for="p in fichas" :key="p.id">
                <div class="dt-tile" :class="p.id === sel ? 'sel' : ''">
                    <button class="dt-thumb" @click="elegir(p.id)" :aria-label="'Elegir ' + p.nombrePerfil">
                        <x-mini-perfil var="p" />
                        <span class="dt-badge" x-show="p.publico">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg>
                            <span x-text="p.tieneChip ? 'En tu tarjeta' : 'Público'"></span>
                        </span>
                        <span class="dt-check" x-show="p.id === sel"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg></span>
                    </button>
                    <div style="display:flex;justify-content:space-between;gap:8px;align-items:flex-start">
                        <div style="min-width:0">
                            <b style="display:block;font-size:14px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" x-text="p.nombrePerfil"></b>
                            <small style="color:var(--muted);font-size:12px" x-text="'@' + p.nickname + ' · ' + nombreEstilo(p.tema)"></small>
                        </div>
                        <div style="display:flex;gap:4px;flex:none">
                            <a class="dt-ico" :href="'/editor?id=' + p.id" title="Editar datos" aria-label="Editar datos">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 20h4L19 9l-4-4L4 16z"/></svg>
                            </a>
                            <a class="dt-ico" x-show="p.publico" :href="'/' + p.nickname" target="_blank" title="Ver perfil público" aria-label="Ver perfil público">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                            </a>
                            <button class="dt-ico" x-show="!p.publico" @click="activar(p)" title="Mostrar en público y en mi tarjeta" aria-label="Mostrar en público y en mi tarjeta">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12a7 7 0 0 1 14 0M8 12a4 4 0 0 1 8 0"/><circle cx="12" cy="12" r="1"/></svg>
                            </button>
                            <div style="position:relative" x-data="{ abierto: false }">
                                <button class="dt-ico" @click="abierto = !abierto" @click.away="abierto = false" title="Más opciones" aria-label="Más opciones">
                                    <svg viewBox="0 0 24 24" fill="currentColor"><circle cx="5" cy="12" r="1.8"/><circle cx="12" cy="12" r="1.8"/><circle cx="19" cy="12" r="1.8"/></svg>
                                </button>
                                <div x-show="abierto" x-cloak class="dt-panel" style="position:absolute;right:0;top:36px;padding:6px;min-width:170px;z-index:30;box-shadow:0 12px 32px rgba(0,0,0,.12)">
                                    <button class="dt-btn" style="width:100%;justify-content:flex-start;border:0" @click="abierto = false; duplicar(p)">Duplicar</button>
                                    <button class="dt-btn danger" style="width:100%;justify-content:flex-start;border:0" @click="abierto = false; pedirEliminar(p)">Eliminar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <button class="dt-new" x-show="fichas.length < {{ $limite }}" @click="nuevo()">
                <div style="display:flex;flex-direction:column;align-items:center;gap:6px;font-size:13px">
                    <span style="width:34px;height:34px;border-radius:50%;background:var(--card);border:1px solid var(--border);display:grid;place-items:center;font-size:18px;color:var(--text)">+</span>
                    Nuevo perfil
                    <small style="font-size:11.5px">Plan gratuito: {{ $limite }} perfiles</small>
                </div>
            </button>
        </div>
    </section>

    {{-- Estilo del perfil seleccionado --}}
    <template x-if="actual">
    <section class="dt-panel" aria-labelledby="h-estilo">
        <div class="dt-panel-h">
            <h2 id="h-estilo">Estilo de <span x-text="actual.nombrePerfil"></span></h2>
            <div class="dt-seg" role="group" aria-label="Estructura">
                <button :class="actual.estructura !== 'feria' ? 'on' : ''" @click="cambiar({ layout_estructura: 'clasica' }, 'estructura', 'clasica')">Clásica</button>
                <button :class="actual.estructura === 'feria' ? 'on' : ''" @click="cambiar({ layout_estructura: 'feria' }, 'estructura', 'feria')">Feria</button>
            </div>
        </div>

        <div class="dt-styles">
            <template x-for="e in estilos" :key="e.id">
                <div class="dt-tile" :class="actual.tema === e.id ? 'sel' : ''">
                    <button class="dt-thumb" @click="cambiar({ tema_base: e.id }, 'tema', e.id)" :aria-label="'Estilo ' + e.nombre">
                        <x-mini-perfil var="({ ...actual, tema: e.id })" :solo="true" />
                        <span class="dt-check" x-show="actual.tema === e.id"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg></span>
                    </button>
                    <div><b style="font-size:13px" x-text="e.nombre"></b><small style="display:block;color:var(--muted);font-size:11.5px" x-text="e.desc"></small></div>
                </div>
            </template>
        </div>

        <div style="display:flex;flex-wrap:wrap;gap:24px;margin-top:20px;align-items:flex-end">
            <div>
                <span class="dt-label">Color de acento</span>
                <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
                    <template x-for="c in colores" :key="c">
                        <button class="dt-sw" :class="(actual.color || '').toLowerCase() === c ? 'on' : ''" :style="'background:' + c" @click="cambiar({ color_primario: c }, 'color', c)" :aria-label="'Color ' + c"></button>
                    </template>
                    <label class="dt-sw" style="position:relative;overflow:hidden;background:conic-gradient(red,yellow,lime,cyan,blue,magenta,red)" title="Otro color">
                        <input type="color" :value="actual.color" @change="cambiar({ color_primario: $event.target.value }, 'color', $event.target.value)" style="position:absolute;inset:0;opacity:0;cursor:pointer" aria-label="Elegir otro color">
                    </label>
                </div>
            </div>
            <div>
                <span class="dt-label">Botones</span>
                <div class="dt-seg" role="group" aria-label="Forma de botones">
                    <button :class="['rounded-xl','rounded-2xl','rounded-md'].includes(actual.forma) ? 'on' : ''" @click="cambiar({ forma_botones: 'rounded-2xl' }, 'forma', 'rounded-2xl')">Suaves</button>
                    <button :class="actual.forma === 'rounded-full' ? 'on' : ''" @click="cambiar({ forma_botones: 'rounded-full' }, 'forma', 'rounded-full')">Píldora</button>
                    <button :class="['rounded-none','-skew-x-12'].includes(actual.forma) ? 'on' : ''" @click="cambiar({ forma_botones: 'rounded-none' }, 'forma', 'rounded-none')">Rectos</button>
                </div>
            </div>
            <a class="dt-btn" :href="'/editor?id=' + actual.id + '&step=8'">Más opciones de diseño</a>
        </div>
        <p class="dt-sub" style="margin-top:14px;font-size:12.5px">Los cambios se guardan solos y se aplican a la página pública de este perfil.</p>
    </section>
    </template>

    {{-- Apariencia del panel --}}
    <section class="dt-panel" aria-labelledby="h-ap">
        <div class="dt-panel-h">
            <h2 id="h-ap">Apariencia del panel</h2>
            <div class="dt-seg" role="group" aria-label="Modo">
                <button :class="modo === 'auto' ? 'on' : ''" @click="ponerModo('auto')">Automático</button>
                <button :class="modo === 'light' ? 'on' : ''" @click="ponerModo('light')">Claro</button>
                <button :class="modo === 'dark' ? 'on' : ''" @click="ponerModo('dark')">Oscuro</button>
            </div>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:12px">
            <template x-for="t in temas" :key="t.id">
                <button style="border:0;background:none;padding:0;text-align:left;display:flex;flex-direction:column;gap:6px;cursor:pointer;color:inherit" @click="ponerTema(t.id)">
                    <span style="aspect-ratio:16/10;border-radius:10px;display:flex;gap:6px;padding:9px;position:relative;border:2px solid transparent;width:100%" :style="`background:${t.bg};` + (tema === t.id ? 'border-color:var(--text)' : '')">
                        <span style="display:flex;flex-direction:column;gap:5px;padding-top:2px"><i style="width:7px;height:7px;border-radius:50%" :style="'background:' + t.dk"></i><i style="width:7px;height:7px;border-radius:50%;background:#fbc9a6"></i><i style="width:7px;height:7px;border-radius:50%;background:#d9d4fb"></i></span>
                        <span style="flex:1;background:#fff;border-radius:6px;padding:6px;display:flex;flex-direction:column;gap:5px">
                            <i style="height:3px;width:60%;border-radius:3px" :style="'background:' + t.dk"></i>
                            <span style="flex:1;display:flex;gap:5px"><span style="flex:1;border-radius:4px" :style="'background:' + t.dk"></span><span style="flex:1;border-radius:4px" :style="'background:' + t.bg"></span></span>
                        </span>
                        <span class="dt-check" style="width:20px;height:20px" x-show="tema === t.id"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg></span>
                    </span>
                    <span><b style="font-size:13px" x-text="t.nombre"></b><small style="display:block;color:var(--muted);font-size:11.5px" x-text="t.desc"></small></span>
                </button>
            </template>
        </div>
        <p class="dt-sub" style="margin-top:14px;font-size:12.5px">Solo cambia cómo ves tu panel en este dispositivo. Tus perfiles públicos mantienen su propio estilo.</p>
    </section>

    {{-- Confirmación de borrado --}}
    <div x-show="aEliminar" x-cloak style="position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:80;display:grid;place-items:center;padding:16px">
        <div class="dt-panel" style="max-width:400px;width:100%" @click.away="aEliminar = null">
            <h2 style="margin:0 0 6px;font-size:16px">Eliminar perfil</h2>
            <p class="dt-sub" style="margin:0 0 18px">Se eliminará «<span x-text="aEliminar?.nombrePerfil"></span>» de forma permanente. Esta acción no se puede deshacer.</p>
            <div style="display:flex;justify-content:flex-end;gap:8px">
                <button class="dt-btn" @click="aEliminar = null">Cancelar</button>
                <button class="dt-btn pri" style="background:var(--danger);border-color:var(--danger)" @click="eliminar()">Eliminar</button>
            </div>
        </div>
    </div>

    <div class="dt-toast" :class="toastError ? 'err' : ''" x-show="toast" x-transition x-cloak role="status" x-text="toast"></div>
</div>

<script>
    function gestorPerfiles() {
        return {
            fichas: @json($fichas),
            sel: null,
            aEliminar: null,
            toast: '', toastError: false, toastT: null, guardarT: null,
            modo: 'auto', tema: '',
            estilos: [
                { id: 'crystal', nombre: 'Crystal', desc: 'Vidrio sobre noche' },
                { id: 'matte', nombre: 'Mate', desc: 'Negro sin brillo' },
                { id: 'luxury', nombre: 'Luxury', desc: 'Dorado nocturno' },
                { id: 'corporativo', nombre: 'Corporativo', desc: 'Claro ejecutivo' },
                { id: 'sport', nombre: 'Sport', desc: 'Energía y trama' },
                { id: 'minimalista', nombre: 'Minimal', desc: 'Blanco limpio' },
            ],
            colores: ['#3b82f6', '#38bdf8', '#2eaa6e', '#d4af37', '#e4572e', '#d6336c', '#7a3fd6', '#111111'],
            temas: [
                { id: '', nombre: 'Grafito', desc: 'Gris neutro', bg: '#f4f5f7', dk: '#1e1f21' },
                { id: 'paper', nombre: 'Papel', desc: 'Crema cálido', bg: '#f6f1e7', dk: '#2b241b' },
                { id: 'sage', nombre: 'Salvia', desc: 'Verde gris', bg: '#eef2ec', dk: '#1f2a22' },
                { id: 'tide', nombre: 'Marea', desc: 'Azul de agua', bg: '#eaf2f8', dk: '#102230' },
                { id: 'plum', nombre: 'Ciruela', desc: 'Malva suave', bg: '#f5edf2', dk: '#2a1b25' },
                { id: 'clay', nombre: 'Arcilla', desc: 'Terracota tostada', bg: '#f7ece4', dk: '#2d1d14' },
                { id: 'wisteria', nombre: 'Glicina', desc: 'Violeta azulado', bg: '#efeff9', dk: '#1d1c2e' },
            ],

            get actual() { return this.fichas.find(p => p.id === this.sel) || null; },

            iniciar() {
                this.sel = this.fichas[0]?.id ?? null;
                try { this.modo = localStorage.getItem('dt-mode') || 'auto'; this.tema = localStorage.getItem('dt-theme') || ''; } catch (e) {}
            },
            nombreEstilo(id) { return (this.estilos.find(e => e.id === id) || { nombre: id }).nombre; },
            avisar(m, error = false) {
                this.toast = m; this.toastError = error;
                clearTimeout(this.toastT); this.toastT = setTimeout(() => this.toast = '', 2600);
            },
            elegir(id) { this.sel = id; },
            nuevo() { window.location.href = '/editor?modo=nuevo_secundario'; },

            async llamar(url, metodo, cuerpo = null) {
                const r = await fetch(url, {
                    method: metodo,
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                    body: cuerpo,
                });
                return r.json();
            },

            // Cambia el estilo en la miniatura al instante y lo guarda un momento después
            cambiar(parcial, campo, valor) {
                const p = this.actual; if (!p) return;
                p[campo] = valor;
                p.diseno = { ...p.diseno, ...parcial };
                clearTimeout(this.guardarT);
                this.guardarT = setTimeout(() => this.guardarDiseno(p), 500);
            },
            async guardarDiseno(p) {
                const datos = new FormData();
                datos.append('perfil_id', p.id);
                datos.append('design_config', JSON.stringify(p.diseno));
                try {
                    const r = await this.llamar('/perfil/guardar', 'POST', datos);
                    r.status === 'success' ? this.avisar('Estilo guardado') : this.avisar(r.message || 'No se pudo guardar el estilo.', true);
                } catch (e) { this.avisar('Sin conexión con el servidor.', true); }
            },

            async activar(p) {
                try {
                    const r = await this.llamar(`/perfil/${p.id}/activar`, 'POST');
                    if (r.status !== 'success') return this.avisar(r.message, true);
                    window.location.reload();
                } catch (e) { this.avisar('Sin conexión con el servidor.', true); }
            },
            async duplicar(p) {
                try {
                    const r = await this.llamar(`/perfil/${p.id}/duplicar`, 'POST');
                    if (r.status !== 'success') return this.avisar(r.message, true);
                    window.location.reload();
                } catch (e) { this.avisar('Sin conexión con el servidor.', true); }
            },
            pedirEliminar(p) {
                if (p.publico) return this.avisar('No puedes eliminar el perfil que se ve en público. Muestra otro primero.', true);
                this.aEliminar = p;
            },
            async eliminar() {
                const p = this.aEliminar; this.aEliminar = null;
                try {
                    const r = await this.llamar(`/perfil/${p.id}/eliminar`, 'DELETE');
                    if (r.status !== 'success') return this.avisar(r.message, true);
                    this.fichas = this.fichas.filter(x => x.id !== p.id);
                    this.sel = this.fichas[0]?.id ?? null;
                    this.avisar('Perfil eliminado');
                } catch (e) { this.avisar('Sin conexión con el servidor.', true); }
            },

            ponerModo(m) {
                this.modo = m;
                try { localStorage.setItem('dt-mode', m); } catch (e) {}
                const oscuro = m === 'dark' || (m === 'auto' && matchMedia('(prefers-color-scheme: dark)').matches);
                oscuro ? document.documentElement.dataset.mode = 'dark' : delete document.documentElement.dataset.mode;
                this.avisar('Apariencia guardada');
            },
            ponerTema(t) {
                this.tema = t;
                try { localStorage.setItem('dt-theme', t); } catch (e) {}
                t ? document.documentElement.dataset.theme = t : delete document.documentElement.dataset.theme;
                this.avisar('Apariencia guardada');
            },
        };
    }
</script>
@endsection
