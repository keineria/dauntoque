@extends('layouts.app')

@section('contenido')
    @php
        $nombresDias = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 0 => 'Domingo'];
        $local = fn ($d) => $d->copy()->setTimezone($zona);
    @endphp

    <div class="dt-head">
        <div>
            <h1 class="dt-h1">Ajustes de la agenda</h1>
            <p class="dt-sub">
                @if($app->vigente())
                    Activa hasta el {{ $app->vence_en->copy()->setTimezone($zona)->format('d/m/Y') }}.
                @else
                    Tu agenda venció: renueva para volver a recibir reservas.
                @endif
            </p>
        </div>
        <a class="dt-btn" href="/dashboard/agenda">← Volver a la agenda</a>
    </div>

    @if($errors->any())
        <div class="dt-panel" style="border-color:rgba(239,68,68,.5);margin-bottom:18px;color:#f87171">{{ $errors->first() }}</div>
    @endif

    {{-- Servicios --}}
    <section class="dt-panel" id="servicios" x-data="{ editar: null, nuevo: {{ $servicios->isEmpty() ? 'true' : 'false' }} }">
        <div class="dt-panel-h">
            <h2>1 · Servicios</h2>
            <button class="dt-btn pri" type="button" @click="nuevo = true; editar = null">+ Agregar servicio</button>
        </div>
        @forelse($servicios as $s)
            <div style="border-top:1px solid var(--border);padding:12px 0">
                <div style="display:flex;justify-content:space-between;gap:10px;align-items:center;flex-wrap:wrap">
                    <div>
                        <b>{{ $s->nombre }}</b>
                        @if($s->requiere_aprobacion)<span class="dt-chip">Requiere tu aprobación</span>@endif<br>
                        <span class="dt-sub" style="margin:0">{{ $s->duracion_min }} min{{ $s->colchon_min ? ' + ' . $s->colchon_min . ' min de descanso' : '' }}{{ \App\Services\Agenda\Agenda::precio($s->precio, $s->user_id) ? ' · ' . \App\Services\Agenda\Agenda::precio($s->precio, $s->user_id) . ' ' . $moneda : '' }}</span>
                    </div>
                    <div style="display:flex;gap:6px">
                        <button class="dt-btn" type="button" @click="editar = editar === {{ $s->id }} ? null : {{ $s->id }}; nuevo = false">Editar</button>
                        <form method="POST" action="/dashboard/agenda/servicios/{{ $s->id }}" onsubmit="return confirm('¿Quitar este servicio de tu agenda? Las citas ya tomadas no cambian.')">@csrf @method('DELETE')<button class="dt-btn danger" type="submit">Quitar</button></form>
                    </div>
                </div>
                <div x-show="editar === {{ $s->id }}" x-cloak>@include('dashboard.partials.agenda-servicio', ['s' => $s])</div>
            </div>
        @empty
            <p class="dt-sub">Crea lo que tus clientes pueden reservar, por ejemplo "Corte de cabello · 40 min".</p>
        @endforelse
        <div x-show="nuevo" x-cloak style="border-top:1px solid var(--border);padding-top:12px">@include('dashboard.partials.agenda-servicio', ['s' => null])</div>
    </section>

    {{-- Horario semanal --}}
    <section class="dt-panel" id="horario">
        <div class="dt-panel-h"><h2>2 · Horario semanal</h2><span class="dt-sub" style="margin:0;font-size:12px">Hasta dos franjas por día, por ejemplo mañana y tarde</span></div>
        <form method="POST" action="/dashboard/agenda/horario">
            @csrf
            @foreach($nombresDias as $n => $nombre)
                @php $f = $horario[$n] ?? []; @endphp
                <div x-data="{ abierto: {{ $f ? 'true' : 'false' }}, dos: {{ count($f) > 1 ? 'true' : 'false' }} }" style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;padding:10px 0;border-top:1px solid var(--border)">
                    <label style="display:flex;gap:8px;align-items:center;width:130px;font-weight:700;cursor:pointer">
                        <input type="checkbox" name="abierto[{{ $n }}]" value="1" x-model="abierto"> {{ $nombre }}
                    </label>
                    <span class="dt-sub" style="margin:0" x-show="!abierto">Cerrado</span>
                    <div x-show="abierto" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
                        <input class="dt-input" style="width:140px" type="time" name="dias[{{ $n }}][0][desde]" value="{{ $f[0]['desde'] ?? '08:00' }}">
                        <span class="dt-sub" style="margin:0">a</span>
                        <input class="dt-input" style="width:140px" type="time" name="dias[{{ $n }}][0][hasta]" value="{{ $f[0]['hasta'] ?? '12:00' }}">
                        <template x-if="dos">
                            <span style="display:flex;gap:8px;align-items:center">
                                <span class="dt-sub" style="margin:0">y</span>
                                <input class="dt-input" style="width:140px" type="time" name="dias[{{ $n }}][1][desde]" value="{{ $f[1]['desde'] ?? '14:00' }}">
                                <span class="dt-sub" style="margin:0">a</span>
                                <input class="dt-input" style="width:140px" type="time" name="dias[{{ $n }}][1][hasta]" value="{{ $f[1]['hasta'] ?? '18:00' }}">
                                <button type="button" class="dt-ico" @click="dos = false" aria-label="Quitar franja">✕</button>
                            </span>
                        </template>
                        <button type="button" class="dt-btn" x-show="!dos" @click="dos = true">+ Otra franja</button>
                    </div>
                </div>
            @endforeach
            <button class="dt-btn pri" type="submit" style="margin-top:12px">Guardar horario</button>
        </form>
    </section>

    {{-- Cierres --}}
    <section class="dt-panel" id="bloqueos">
        <div class="dt-panel-h"><h2>3 · Cerrar días u horas</h2><span class="dt-sub" style="margin:0;font-size:12px">Vacaciones, festivos o una tarde libre</span></div>
        <form method="POST" action="/dashboard/agenda/bloqueos" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:10px;align-items:end">
            @csrf
            <div><span class="dt-label">Desde el día</span><input class="dt-input" type="date" name="desde_fecha" required min="{{ now($zona)->format('Y-m-d') }}"></div>
            <div><span class="dt-label">Hora (opcional)</span><input class="dt-input" type="time" name="desde_hora"></div>
            <div><span class="dt-label">Hasta el día</span><input class="dt-input" type="date" name="hasta_fecha" required min="{{ now($zona)->format('Y-m-d') }}"></div>
            <div><span class="dt-label">Hora (opcional)</span><input class="dt-input" type="time" name="hasta_hora"></div>
            <div><span class="dt-label">Motivo (solo tú lo ves)</span><input class="dt-input" name="motivo" maxlength="120" placeholder="Vacaciones"></div>
            <button class="dt-btn pri" type="submit" style="height:44px">Cerrar</button>
        </form>
        <p class="dt-sub" style="font-size:12px">Sin horas se cierran los días completos.</p>
        @foreach($bloqueos as $b)
            <div style="display:flex;justify-content:space-between;gap:10px;align-items:center;padding:10px 0;border-top:1px solid var(--border)">
                <span>{{ $local($b->desde)->locale('es')->translatedFormat('D j M, g:i a') }} → {{ $local($b->hasta)->locale('es')->translatedFormat('D j M, g:i a') }}@if($b->motivo) · <span class="dt-sub">{{ $b->motivo }}</span>@endif</span>
                <form method="POST" action="/dashboard/agenda/bloqueos/{{ $b->id }}">@csrf @method('DELETE')<button class="dt-btn" type="submit">Abrir otra vez</button></form>
            </div>
        @endforeach
    </section>

    {{-- Reglas --}}
    <section class="dt-panel" id="reglas">
        <div class="dt-panel-h"><h2>4 · Reglas</h2></div>
        <form method="POST" action="/dashboard/agenda/reglas" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px">
            @csrf
            <div><span class="dt-label">Zona horaria</span>
                <select class="dt-input" name="zona_horaria">@foreach(config('agenda.zonas') as $z => $nombre)<option value="{{ $z }}" @selected($ajustes->zona_horaria === $z)>{{ $nombre }}</option>@endforeach</select>
            </div>
            <div><span class="dt-label">Mostrar horas cada</span>
                <select class="dt-input" name="intervalo_min">@foreach(config('agenda.intervalos') as $i)<option value="{{ $i }}" @selected($ajustes->intervalo_min == $i)>{{ $i }} minutos</option>@endforeach</select>
            </div>
            <div><span class="dt-label">Reservar con al menos</span>
                <select class="dt-input" name="antelacion_min">@foreach([0 => 'Sin mínimo', 30 => '30 minutos', 60 => '1 hora', 120 => '2 horas', 240 => '4 horas', 720 => '12 horas', 1440 => '1 día', 2880 => '2 días'] as $v => $t)<option value="{{ $v }}" @selected($ajustes->antelacion_min == $v)>{{ $t }} de anticipación</option>@endforeach</select>
            </div>
            <div><span class="dt-label">Abrir la agenda</span>
                <select class="dt-input" name="dias_adelante">@foreach([7, 14, 30, 60, 90] as $v)<option value="{{ $v }}" @selected($ajustes->dias_adelante == $v)>{{ $v }} días hacia adelante</option>@endforeach</select>
            </div>
            <div><span class="dt-label">El cliente puede mover o cancelar hasta</span>
                <select class="dt-input" name="limite_cancelar_horas">@foreach([0 => 'La hora de la cita', 1 => '1 hora antes', 2 => '2 horas antes', 4 => '4 horas antes', 12 => '12 horas antes', 24 => '24 horas antes', 48 => '48 horas antes'] as $v => $t)<option value="{{ $v }}" @selected($ajustes->limite_cancelar_horas == $v)>{{ $t }}</option>@endforeach</select>
            </div>
            <div style="grid-column:1/-1"><span class="dt-label">Tu política de cancelación (opcional)</span>
                <textarea class="dt-input" name="politica_cancelacion" rows="3" maxlength="600" placeholder="Ejemplo: si no llegas ni avisas, la próxima cita se paga por adelantado.">{{ $ajustes->politica_cancelacion }}</textarea>
                <p class="dt-sub" style="font-size:12px">Tus clientes la ven antes de reservar y en su cita, junto al límite para mover o cancelar que elegiste arriba.</p>
            </div>
            <div style="grid-column:1/-1"><span class="dt-label">Mensaje del botón de WhatsApp</span>
                <textarea class="dt-input" name="mensaje_whatsapp" rows="3" maxlength="500">{{ $ajustes->mensaje_whatsapp ?: config('agenda.mensaje_whatsapp') }}</textarea>
                <p class="dt-sub" style="font-size:12px">Se usa en el botón WhatsApp de cada cita. Escribe {nombre}, {servicio}, {fecha}, {hora} y {enlace} donde quieras esos datos.</p>
            </div>
            <div><button class="dt-btn pri" type="submit">Guardar reglas</button></div>
        </form>
    </section>

    {{-- Google Calendar --}}
    <section class="dt-panel" id="google">
        <div class="dt-panel-h"><h2>5 · Google Calendar</h2></div>
        @if(!$google)
            <p class="dt-sub">La conexión con Google Calendar aún no está habilitada en el portal. Pronto podrás activarla aquí.</p>
        @elseif($ajustes->conGoogle())
            <p style="margin:0 0 12px">Conectado{{ $ajustes->google_correo ? ' con ' . $ajustes->google_correo : '' }}. Tus citas aparecen en tu calendario y tus eventos ocupados bloquean la agenda.</p>
            <form method="POST" action="/dashboard/agenda/google/desconectar">@csrf<button class="dt-btn danger" type="submit">Desconectar</button></form>
        @else
            <p class="dt-sub" style="margin:0 0 12px">Tus citas confirmadas se guardan en tu Google Calendar y lo que ya tienes ocupado ahí no se puede reservar.</p>
            <a class="dt-btn pri" href="/dashboard/agenda/google/conectar">Conectar Google Calendar</a>
        @endif
    </section>
@endsection
