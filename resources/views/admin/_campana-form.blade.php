{{--
    Formulario de campaña con el generador de UTM en vivo. Variables: $c (Campana o null), $canales, $destinos.
    Al elegir el canal se llenan la fuente y el medio recomendados; el nombre arma el utm_campaign.
--}}
@php
    $nueva = $c === null;
    $id = $nueva ? 'nueva' : $c->id;
    // Lo escrito antes de un error vuelve solo al formulario de campaña nueva (no a los de editar)
    $o = fn (string $campo, mixed $valor = null) => $nueva ? old($campo, $valor) : $valor;
    $inicial = [
        'nombre' => $o('nombre', $c?->nombre ?? ''),
        'canal' => $o('canal', $c?->canal ?? 'meta'),
        'source' => $o('utm_source', $c?->utm_source ?? $canales['meta']['source']),
        'medium' => $o('utm_medium', $c?->utm_medium ?? $canales['meta']['medium']),
        'campana' => $o('utm_campaign', $c?->utm_campaign ?? ''),
        'destino' => $o('destino', $c?->destino ?? array_key_first($destinos)),
        'contenido' => '',
        'campanaTocada' => ! $nueva,
    ];
@endphp
<form method="POST" action="{{ $nueva ? '/admin/campanas' : '/admin/campanas/' . $c->id }}"
      x-data="dtCampana({{ Js::from($inicial) }}, {{ Js::from(collect($canales)->map(fn ($x) => ['source' => $x['source'], 'medium' => $x['medium']])) }})"
      @submit="guardando = true" style="display:grid;gap:14px">
    @csrf
    @unless ($nueva) @method('PUT') @endunless
    <div class="ad-form">
        <label class="ad-campo" for="c-{{ $id }}-nombre">Nombre de la campaña
            <input id="c-{{ $id }}-nombre" name="nombre" class="dt-input" x-model="nombre" @input="if (!campanaTocada) campana = slug(nombre)" required maxlength="120" placeholder="Lanzamiento diciembre">
        </label>
        <label class="ad-campo" for="c-{{ $id }}-canal">Canal
            <select id="c-{{ $id }}-canal" name="canal" class="dt-input" x-model="canal" @change="elegirCanal()">
                @foreach ($canales as $clave => $canal)<option value="{{ $clave }}">{{ $canal['nombre'] }}</option>@endforeach
            </select>
        </label>
        <label class="ad-campo" for="c-{{ $id }}-destino">Página a la que lleva
            <select id="c-{{ $id }}-destino" name="destino" class="dt-input" x-model="destino">
                @foreach ($destinos as $url => $nombre)<option value="{{ $url }}">{{ $nombre }}</option>@endforeach
                @if (! $nueva && ! array_key_exists($c->destino, $destinos))<option value="{{ $c->destino }}">{{ $c->destino }}</option>@endif
            </select>
        </label>
    </div>
    <div class="ad-form">
        <label class="ad-campo" for="c-{{ $id }}-source">Fuente <small>utm_source</small>
            <input id="c-{{ $id }}-source" name="utm_source" class="dt-input" x-model="source" @input="source = slug(source)" required maxlength="100">
        </label>
        <label class="ad-campo" for="c-{{ $id }}-medium">Medio <small>utm_medium</small>
            <input id="c-{{ $id }}-medium" name="utm_medium" class="dt-input" x-model="medium" @input="medium = slug(medium)" required maxlength="100">
        </label>
        <label class="ad-campo" for="c-{{ $id }}-campana">Campaña <small>utm_campaign (único)</small>
            <input id="c-{{ $id }}-campana" name="utm_campaign" class="dt-input" x-model="campana" @input="campanaTocada = true; campana = slug(campana)" required maxlength="100">
        </label>
        <label class="ad-campo" for="c-{{ $id }}-contenido">Variante del anuncio <small>utm_content, opcional, no se guarda</small>
            <input id="c-{{ $id }}-contenido" class="dt-input" x-model="contenido" @input="contenido = slug(contenido)" maxlength="100" placeholder="video_a">
        </label>
    </div>
    <div class="ad-form">
        <label class="ad-campo" for="c-{{ $id }}-presupuesto">Presupuesto <small>lo que vas a invertir, para el costo por compra</small>
            <input id="c-{{ $id }}-presupuesto" name="presupuesto" class="dt-input" inputmode="numeric" value="{{ $o('presupuesto', $c?->presupuesto !== null ? (int) $c->presupuesto : '') }}" placeholder="500000">
        </label>
        <label class="ad-campo" for="c-{{ $id }}-moneda">Moneda
            <select id="c-{{ $id }}-moneda" name="moneda" class="dt-input">
                @foreach (['COP', 'USD'] as $m)<option value="{{ $m }}" @selected($o('moneda', $c?->moneda ?? 'COP') === $m)>{{ $m }}</option>@endforeach
            </select>
        </label>
        <label class="ad-campo" for="c-{{ $id }}-inicia">Inicia
            <input id="c-{{ $id }}-inicia" type="date" name="inicia_en" class="dt-input" value="{{ $o('inicia_en', $c?->inicia_en?->toDateString()) }}">
        </label>
        <label class="ad-campo" for="c-{{ $id }}-termina">Termina <small>opcional</small>
            <input id="c-{{ $id }}-termina" type="date" name="termina_en" class="dt-input" value="{{ $o('termina_en', $c?->termina_en?->toDateString()) }}">
        </label>
    </div>
    <label class="ad-campo" for="c-{{ $id }}-notas">Notas <small>público, creativos, qué se probó</small>
        <textarea id="c-{{ $id }}-notas" name="notas" class="dt-input" rows="2" maxlength="2000">{{ $o('notas', $c?->notas) }}</textarea>
    </label>

    <div>
        <span class="dt-label">Enlace para el anuncio</span>
        <div class="ad-url" x-text="enlace()" aria-live="polite"></div>
    </div>
    <div class="ad-acciones">
        <button type="submit" class="dt-btn pri" :disabled="guardando" x-text="guardando ? 'Guardando…' : '{{ $nueva ? 'Crear campaña' : 'Guardar cambios' }}'">{{ $nueva ? 'Crear campaña' : 'Guardar cambios' }}</button>
        <button type="button" class="dt-btn" @click="copiar()" x-text="copiado ? '¡Copiado!' : 'Copiar enlace'">Copiar enlace</button>
    </div>
</form>

@once
    <script>
        // Generador de UTM del Centro de mando (mismo formato que guarda CampanaRequest)
        window.dtCampana = function (inicial, canales) {
            return Object.assign({ guardando: false, copiado: false }, inicial, {
                slug(texto) {
                    return (texto || '').normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase().replace(/[^a-z0-9._-]+/g, '_').replace(/^_+/, '').slice(0, 100);
                },
                elegirCanal() {
                    const c = canales[this.canal];
                    if (c && c.source) { this.source = c.source; this.medium = c.medium; }
                },
                enlace() {
                    const p = new URLSearchParams();
                    const limpio = (v) => (v || '').replace(/_+$/, '');
                    if (this.source) p.set('utm_source', limpio(this.source));
                    if (this.medium) p.set('utm_medium', limpio(this.medium));
                    if (this.campana) p.set('utm_campaign', limpio(this.campana));
                    if (this.contenido) p.set('utm_content', limpio(this.contenido));
                    const [base, ancla] = (this.destino || '').split('#');
                    return base + (base.includes('?') ? '&' : '?') + p.toString() + (ancla ? '#' + ancla : '');
                },
                async copiar() {
                    try {
                        await navigator.clipboard.writeText(this.enlace());
                        this.copiado = true;
                        setTimeout(() => this.copiado = false, 2000);
                    } catch (e) {
                        window.prompt('Copia el enlace:', this.enlace());
                    }
                },
            });
        };
    </script>
@endonce
