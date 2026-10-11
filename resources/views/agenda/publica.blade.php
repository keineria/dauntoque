@extends('agenda._base')

@section('titulo', __('agenda.agendar') . ' · ' . $negocio['nombre'])

@section('contenido')
    <h1>{{ __('agenda.titulo_reserva', ['nombre' => $negocio['nombre']]) }}</h1>

    @if($modo === 'basica')
        <p class="ag-muted">{{ __('agenda.agenda_cerrada') }}</p>
        @if($negocio['whatsapp'])
            <a class="ag-btn wa" href="https://wa.me/{{ $negocio['whatsapp'] }}?text={{ rawurlencode(__('agenda.whatsapp_basico_texto')) }}">{{ __('agenda.whatsapp_basico') }}</a>
        @endif
    @else
        @if($errors->any())
            <div class="ag-aviso err" role="alert">{{ $errors->first() }}</div>
        @endif

        <div x-data="reserva()" x-cloak>
            {{-- 1. Servicio --}}
            <h2>1 · {{ __('agenda.paso_servicio') }}</h2>
            <template x-if="!servicio">
                <div>
                    <template x-for="s in servicios" :key="s.id">
                        <button type="button" class="ag-op" @click="elegirServicio(s)">
                            <span>
                                <b x-text="s.nombre"></b>
                                <small x-text="s.descripcion" x-show="s.descripcion"></small>
                                <small><span x-text="s.duracion + ' {{ __('agenda.min') }}'"></span><span x-show="s.aprobacion"> · {{ __('agenda.requiere_aprobacion', ['nombre' => $negocio['nombre']]) }}</span></small>
                            </span>
                            <b x-text="s.precio || ''"></b>
                        </button>
                    </template>
                </div>
            </template>
            <template x-if="servicio">
                <div class="ag-card ag-resumen">
                    <span><b x-text="servicio.nombre"></b><br><span class="ag-muted" x-text="servicio.duracion + ' {{ __('agenda.min') }}' + (servicio.precio ? ' · ' + servicio.precio : '')"></span></span>
                    <button type="button" class="ag-link" @click="servicio = null; fecha = null; hora = null">{{ __('agenda.cambiar') }}</button>
                </div>
            </template>

            {{-- 2 y 3. Día y hora --}}
            <div x-show="servicio">
                <h2>2 · {{ __('agenda.paso_dia') }}</h2>
                <p class="ag-muted" x-show="cargando">{{ __('agenda.cargando') }}</p>
                <div x-show="!cargando && !Object.keys(dias).length">
                    <p class="ag-muted">{{ __('agenda.sin_horas') }}</p>
                    @if($negocio['whatsapp'])
                        <a class="ag-btn wa" href="https://wa.me/{{ $negocio['whatsapp'] }}?text={{ rawurlencode(__('agenda.whatsapp_basico_texto')) }}">{{ __('agenda.whatsapp_basico') }}</a>
                    @endif
                </div>
                <div class="ag-dias" x-show="!cargando">
                    <template x-for="d in Object.keys(dias)" :key="d">
                        <button type="button" class="ag-dia" :class="fecha === d ? 'on' : ''" @click="fecha = d; hora = null">
                            <small x-text="etiqueta(d, { weekday: 'short' })"></small>
                            <b x-text="Number(d.slice(8))"></b>
                            <small x-text="etiqueta(d, { month: 'short' })"></small>
                        </button>
                    </template>
                </div>

                <div x-show="fecha">
                    <h2>3 · {{ __('agenda.paso_hora') }}</h2>
                    <p class="ag-muted" style="margin:-4px 0 10px">{{ __('agenda.hora_zona', ['zona' => $zona]) }}</p>
                    <div class="ag-horas">
                        <template x-for="h in (dias[fecha] || [])" :key="h">
                            <button type="button" class="ag-hora" :class="hora === h ? 'on' : ''" @click="hora = h; $nextTick(() => $refs.datos.scrollIntoView({ behavior: 'smooth' }))" x-text="hora12(h)"></button>
                        </template>
                    </div>
                </div>
            </div>

            {{-- 4. Datos --}}
            <form method="POST" action="{{ $urlReservar }}" x-show="hora" x-ref="datos" @submit="enviando = true">
                @csrf
                <h2>4 · {{ __('agenda.paso_datos') }}</h2>
                <div class="ag-card" style="margin-bottom:6px">
                    <b x-text="servicio && servicio.nombre"></b><br>
                    <span class="ag-muted" x-text="fecha ? etiqueta(fecha, { weekday: 'long', day: 'numeric', month: 'long' }) + ' · ' + hora12(hora) : ''"></span>
                </div>
                <input type="hidden" name="servicio_id" :value="servicio && servicio.id">
                <input type="hidden" name="fecha" :value="fecha">
                <input type="hidden" name="hora" :value="hora">
                <input type="hidden" name="lang" value="{{ $idioma }}">
                <div class="ag-oculto" aria-hidden="true"><input type="text" name="sitio_web" tabindex="-1" autocomplete="off"></div>

                <label for="nombre">{{ __('agenda.nombre') }}</label>
                <input class="ag-in" id="nombre" name="nombre" value="{{ old('nombre') }}" required maxlength="80" autocomplete="name">
                <label for="correo">{{ __('agenda.correo') }}</label>
                <input class="ag-in" id="correo" type="email" name="correo" value="{{ old('correo') }}" required maxlength="120" autocomplete="email">
                <p class="ag-muted" style="margin:6px 0 0">{{ __('agenda.correo_ayuda') }}</p>
                <label for="whatsapp">{{ __('agenda.whatsapp') }}</label>
                <input class="ag-in" id="whatsapp" type="tel" name="whatsapp" value="{{ old('whatsapp') }}" required maxlength="30" autocomplete="tel" inputmode="tel">
                <label for="nota">{{ __('agenda.nota') }}</label>
                <textarea class="ag-in" id="nota" name="nota" rows="2" maxlength="500">{{ old('nota') }}</textarea>

                <p class="ag-muted" style="margin:14px 0 0">{{ trans_choice('agenda.limite', $limiteHoras, ['horas' => $limiteHoras, 'nombre' => $negocio['nombre']]) }}</p>
                @if($politica)
                    <p class="ag-muted" style="margin:6px 0 0;white-space:pre-line"><b>{{ __('agenda.politica') }}:</b> {{ $politica }}</p>
                @endif
                <button class="ag-btn" type="submit" :disabled="enviando" x-text="servicio && servicio.aprobacion ? @js(__('agenda.pedir')) : @js(__('agenda.confirmar'))"></button>
            </form>
        </div>

        <script>
            function reserva() {
                return {
                    servicios: @json($servicios),
                    servicio: null, dias: {}, fecha: null, hora: null, cargando: false, enviando: false,
                    init() {
                        // Con un solo servicio se salta el primer paso
                        if (this.servicios.length === 1) this.elegirServicio(this.servicios[0]);
                    },
                    async elegirServicio(s) {
                        this.servicio = s; this.fecha = null; this.hora = null; this.cargando = true; this.dias = {};
                        try {
                            const r = await fetch(@js($urlHoras) + '?servicio=' + s.id, { headers: { Accept: 'application/json' } });
                            this.dias = r.ok ? await r.json() : {};
                        } catch (e) { this.dias = {}; }
                        this.cargando = false;
                        const primero = Object.keys(this.dias)[0];
                        if (primero) this.fecha = primero;
                    },
                    etiqueta(d, opciones) {
                        return new Intl.DateTimeFormat(@js($idioma), { ...opciones, timeZone: 'UTC' }).format(new Date(d + 'T12:00:00Z'));
                    },
                    hora12(h) {
                        if (!h) return '';
                        const [hh, mm] = h.split(':').map(Number);
                        return ((hh % 12) || 12) + ':' + String(mm).padStart(2, '0') + (hh < 12 ? ' am' : ' pm');
                    },
                };
            }
        </script>
    @endif

    <p style="margin-top:22px"><a class="ag-link" href="/mis-citas">{{ __('agenda.ver_mis_citas') }}</a></p>
@endsection
