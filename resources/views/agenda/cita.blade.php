@extends('agenda._base')

@section('titulo', __('agenda.tu_cita'))

@section('contenido')
    @php
        $estado = $cita->estadoVisible();
        $hora12 = fn ($m) => $cita->horaLocal($zona, $m);
        $fecha = fn ($m) => \Illuminate\Support\Str::ucfirst($cita->fechaLocal($zona, $m));
        $mensajes = ['reservada' => __('agenda.listo_reservada'), 'pendiente' => __('agenda.listo_pendiente', ['nombre' => $negocio['nombre']]),
            'cambio' => __('agenda.listo_cambio', ['nombre' => $negocio['nombre']]), 'cancelada' => __('agenda.listo_cancelada'),
            'aceptada' => __('agenda.listo_aceptada'), 'rechazada' => __('agenda.listo_rechazada')];
    @endphp

    @if($listo && isset($mensajes[$listo]))
        <div class="ag-aviso" role="status">{{ $mensajes[$listo] }}</div>
    @endif
    @if($errors->any())
        <div class="ag-aviso err" role="alert">{{ $errors->first() }}</div>
    @endif

    <h1>{{ __('agenda.tu_cita') }}</h1>
    <div class="ag-card" style="margin-top:12px">
        <span class="ag-chip {{ $estado }}">{{ __('agenda.estado_' . $estado) }}</span>
        <p style="margin:12px 0 4px;font-size:18px"><b>{{ $cita->servicio_nombre }}</b></p>
        <p style="margin:0;{{ $estado === 'cancelada' ? 'text-decoration:line-through;color:var(--muted)' : '' }}">{{ $fecha($cita->inicia_en) }} · {{ $hora12($cita->inicia_en) }} – {{ $hora12($cita->termina_en) }}</p>
        <p class="ag-muted" style="margin:4px 0 0">{{ $zonaNombre }}</p>
        @if($negocio['direccion'])
            <p style="margin:10px 0 0"><span class="ag-muted">{{ __('agenda.donde') }}:</span> {{ $negocio['direccion'] }}</p>
        @endif
        @if($cita->motivo && $estado === 'cancelada')
            <p class="ag-muted" style="margin:10px 0 0">{{ __('agenda.motivo', ['motivo' => $cita->motivo]) }}</p>
        @endif
    </div>

    @if($cita->estado === 'cambio' && $cita->propuesta_inicia_en)
        <div class="ag-card" style="margin-top:12px;border-color:rgba(245,158,11,.45)">
            @if($cita->propuesta_por === 'cliente')
                <p style="margin:0">{{ __('agenda.propuesta_tuya', ['fecha' => $fecha($cita->propuesta_inicia_en), 'hora' => $hora12($cita->propuesta_inicia_en), 'nombre' => $negocio['nombre']]) }}</p>
            @else
                <p style="margin:0">{{ __('agenda.propuesta_duenio', ['fecha' => $fecha($cita->propuesta_inicia_en), 'hora' => $hora12($cita->propuesta_inicia_en), 'nombre' => $negocio['nombre']]) }}</p>
                <div class="ag-row">
                    <form method="POST" action="/cita/{{ $cita->token }}/responder">@csrf<input type="hidden" name="acepta" value="1"><button class="ag-btn" type="submit">{{ __('agenda.aceptar') }}</button></form>
                    <form method="POST" action="/cita/{{ $cita->token }}/responder">@csrf<input type="hidden" name="acepta" value="0"><button class="ag-btn sec" type="submit">{{ __('agenda.rechazar') }}</button></form>
                </div>
            @endif
        </div>
    @endif

    @if($cita->activa() && !$cita->realizada())
        {{-- Guardar en el calendario: la opción del dispositivo va primero (iPhone/Mac → Apple; los demás → Google) --}}
        <div x-data="{ abierto: false, apple: /iPhone|iPad|iPod|Macintosh/.test(navigator.userAgent) }">
            <button type="button" class="ag-btn sec" @click="abierto = !abierto" :aria-expanded="abierto">{{ __('agenda.anadir_calendario') }}</button>
            <div x-show="abierto" x-cloak style="display:flex;flex-direction:column">
                <a class="ag-btn sec" :style="apple ? 'order:2' : 'order:1'" href="{{ $calendarios['google'] }}" target="_blank" rel="noopener">Google Calendar</a>
                <a class="ag-btn sec" :style="apple ? 'order:1' : 'order:2'" href="{{ $calendarios['ics'] }}">{{ __('agenda.cal_apple') }}</a>
                <a class="ag-btn sec" style="order:3" href="{{ $calendarios['outlook'] }}" target="_blank" rel="noopener">Outlook</a>
            </div>
        </div>

        @if($puedeCambiar)
            <div x-data="mover()" x-cloak>
                @if($cita->estado === 'confirmada' && $cita->servicio_id)
                    <button type="button" class="ag-btn sec" x-show="!abierto" @click="abrir()">{{ __('agenda.mover') }}</button>
                    <div x-show="abierto">
                        <h2>{{ __('agenda.mover') }}</h2>
                        <p class="ag-muted" style="margin-top:-4px">{{ __('agenda.mover_ayuda', ['nombre' => $negocio['nombre']]) }}</p>
                        <p class="ag-muted" x-show="cargando">{{ __('agenda.cargando') }}</p>
                        <p class="ag-muted" x-show="!cargando && !Object.keys(dias).length">{{ __('agenda.sin_horas') }}</p>
                        <div class="ag-dias">
                            <template x-for="d in Object.keys(dias)" :key="d">
                                <button type="button" class="ag-dia" :class="fecha === d ? 'on' : ''" @click="fecha = d; hora = null">
                                    <small x-text="etiqueta(d, { weekday: 'short' })"></small><b x-text="Number(d.slice(8))"></b><small x-text="etiqueta(d, { month: 'short' })"></small>
                                </button>
                            </template>
                        </div>
                        <div class="ag-horas" style="margin-top:12px" x-show="fecha">
                            <template x-for="h in (dias[fecha] || [])" :key="h">
                                <button type="button" class="ag-hora" :class="hora === h ? 'on' : ''" @click="hora = h" x-text="hora12(h)"></button>
                            </template>
                        </div>
                        <form method="POST" action="/cita/{{ $cita->token }}/cambiar" x-show="hora">
                            @csrf
                            <input type="hidden" name="fecha" :value="fecha"><input type="hidden" name="hora" :value="hora">
                            <button class="ag-btn" type="submit">{{ __('agenda.enviar_cambio') }}</button>
                        </form>
                    </div>
                @endif

                <button type="button" class="ag-btn bad" x-show="!confirmar" @click="confirmar = true">{{ __('agenda.cancelar') }}</button>
                <div class="ag-card" x-show="confirmar" style="margin-top:16px">
                    <p style="margin:0">{{ __('agenda.cancelar_seguro') }}</p>
                    <div class="ag-row">
                        <button type="button" class="ag-btn sec" @click="confirmar = false">{{ __('agenda.no') }}</button>
                        <form method="POST" action="/cita/{{ $cita->token }}/cancelar">@csrf<button class="ag-btn bad" type="submit">{{ __('agenda.cancelar_si') }}</button></form>
                    </div>
                </div>
            </div>
        @endif
        <p class="ag-muted" style="margin-top:16px">{{ trans_choice('agenda.limite', $limiteHoras, ['horas' => $limiteHoras, 'nombre' => $negocio['nombre']]) }}</p>
        @if($politica)
            <p class="ag-muted" style="white-space:pre-line"><b>{{ __('agenda.politica') }}:</b> {{ $politica }}</p>
        @endif
        @if(!$puedeCambiar && $negocio['whatsapp'])
            <a class="ag-btn wa" href="https://wa.me/{{ $negocio['whatsapp'] }}">WhatsApp</a>
        @endif
    @elseif($cita->estado === 'cancelada' && $urlReservar)
        <a class="ag-btn" href="{{ $urlReservar }}">{{ __('agenda.boton_reservar') }}</a>
    @endif

    <script>
        function mover() {
            return {
                abierto: false, confirmar: false, cargando: false, dias: {}, fecha: null, hora: null,
                async abrir() {
                    this.abierto = true; this.cargando = true;
                    try { const r = await fetch('/cita/{{ $cita->token }}/horas', { headers: { Accept: 'application/json' } }); this.dias = r.ok ? await r.json() : {}; } catch (e) { this.dias = {}; }
                    this.cargando = false;
                },
                etiqueta(d, o) { return new Intl.DateTimeFormat(@js($cita->idioma), { ...o, timeZone: 'UTC' }).format(new Date(d + 'T12:00:00Z')); },
                hora12(h) { const [hh, mm] = h.split(':').map(Number); return ((hh % 12) || 12) + ':' + String(mm).padStart(2, '0') + (hh < 12 ? ' am' : ' pm'); },
            };
        }
    </script>
@endsection
