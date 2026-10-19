@extends('layouts.app')

@section('contenido')
    @php
        $h = fn ($c, $m = null) => $c->horaLocal($zona ?? 'America/Bogota', $m);
        $chip = ['pendiente' => ['Por aprobar', 'down'], 'confirmada' => ['Confirmada', 'up'], 'cambio' => ['Cambio en espera', 'down'], 'realizada' => ['Realizada', ''], 'cancelada' => ['Cancelada', 'down']];
    @endphp

    @if(!$app)
        <div class="dt-head">
            <div>
                <h1 class="dt-h1">Agenda de citas</h1>
                <p class="dt-sub">Tus clientes reservan desde tu perfil, sin llamarte ni escribirte.</p>
            </div>
        </div>
        <section class="dt-panel" style="max-width:720px">
            <div style="display:grid;gap:12px;font-size:14.5px">
                <div>📅 <b>Reservas desde tu tarjeta.</b> Al tocarla, tus clientes ven el botón "Agendar cita" y eligen servicio, día y hora libre.</div>
                <div>✉️ <b>Avisos automáticos.</b> Confirmación al reservar y recordatorios 24 horas y 2 horas antes, por correo.</div>
                <div>🔁 <b>Cambios con tu aprobación.</b> El cliente puede pedir otra hora o cancelar; tú apruebas, mueves o cierras días.</div>
                <div>🗓️ <b>Google Calendar.</b> Tus citas aparecen en tu calendario y lo que ya tienes ocupado no se puede reservar.</div>
            </div>
            <form method="POST" action="/dashboard/agenda/activar" style="margin-top:22px;display:flex;flex-wrap:wrap;gap:14px;align-items:center">
                @csrf
                <button class="dt-btn pri" type="submit" style="height:46px;padding:0 22px">Activar gratis por un año</button>
                <span class="dt-sub" style="margin:0">Después, {{ $precio }} al año, junto con la renovación de tu perfil.</span>
            </form>
        </section>
    @else
        <div class="dt-head">
            <div>
                <h1 class="dt-h1">Agenda</h1>
                <p class="dt-sub">
                    @if($enlace)Tus clientes reservan en <a href="{{ $enlace }}" target="_blank" rel="noopener" style="color:#60a5fa">{{ preg_replace('#^https?://#', '', $enlace) }}</a>.@endif
                    {{ $proximas }} {{ $proximas === 1 ? 'cita confirmada' : 'citas confirmadas' }} por venir.
                </p>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap" x-data>
                <button class="dt-btn pri" type="button" @click="$dispatch('nueva-cita')" @disabled($servicios->isEmpty())>+ Nueva cita</button>
                <a class="dt-btn" href="/dashboard/agenda/ajustes">Servicios, horario y ajustes</a>
            </div>
        </div>

        @if($servicios->isEmpty() || !$tieneHorario)
            <section class="dt-panel" style="border-color:rgba(245,158,11,.45);margin-bottom:18px">
                <b>Falta poco para recibir reservas.</b>
                <p class="dt-sub">{{ $servicios->isEmpty() ? 'Crea al menos un servicio' : '' }}{{ $servicios->isEmpty() && !$tieneHorario ? ' y ' : '' }}{{ !$tieneHorario ? 'define tu horario semanal' : '' }}. Mientras tanto, el botón "Agendar cita" no aparece en tu perfil.</p>
                <a class="dt-btn pri" href="/dashboard/agenda/ajustes" style="margin-top:12px">Completar ahora</a>
            </section>
        @endif

        @if($perfiles->isEmpty())
            <section class="dt-panel" style="border-color:rgba(245,158,11,.45);margin-bottom:18px">
                <b>Esta cuenta todavía no tiene perfil.</b>
                <p class="dt-sub">El botón "Agendar cita" aparece en el perfil de esta misma cuenta. Crea tu perfil para que tus clientes puedan reservar.</p>
                <a class="dt-btn pri" href="/editor" style="margin-top:12px">Crear mi perfil</a>
            </section>
        @elseif($lista)
            <section class="dt-panel" style="margin-bottom:18px">
                <div class="dt-panel-h"><h2>Botón "Agendar cita" en tu perfil</h2></div>
                @foreach($perfiles as $pf)
                    <div style="display:flex;flex-wrap:wrap;justify-content:space-between;gap:10px;align-items:center;padding:12px 0;border-top:1px solid var(--border)">
                        <div>
                            <b>{{ $pf['nombre'] }}</b>@if($pf['activo']) <span class="dt-chip">El que abre tu tarjeta</span>@endif<br>
                            <span class="dt-sub" style="margin:0">{{ $pf['visible'] ? ($pf['prioridad'] ? 'Publicado, justo después de la descripción' : 'Publicado') : 'Sin publicar' }}</span>
                        </div>
                        <div style="display:flex;gap:8px;flex-wrap:wrap">
                            @if($pf['visible'])
                                <form method="POST" action="/dashboard/agenda/boton">@csrf<input type="hidden" name="perfil_id" value="{{ $pf['id'] }}"><input type="hidden" name="visible" value="1"><input type="hidden" name="prioridad" value="{{ $pf['prioridad'] ? 0 : 1 }}"><button class="dt-btn" type="submit">{{ $pf['prioridad'] ? 'Quitar prioridad' : 'Darle prioridad' }}</button></form>
                                <form method="POST" action="/dashboard/agenda/boton">@csrf<input type="hidden" name="perfil_id" value="{{ $pf['id'] }}"><input type="hidden" name="visible" value="0"><button class="dt-btn danger" type="submit">Ocultar</button></form>
                            @else
                                <form method="POST" action="/dashboard/agenda/boton">@csrf<input type="hidden" name="perfil_id" value="{{ $pf['id'] }}"><input type="hidden" name="visible" value="1"><button class="dt-btn pri" type="submit">Publicar botón en el perfil</button></form>
                            @endif
                        </div>
                    </div>
                @endforeach
                <p class="dt-sub" style="font-size:12px">Con prioridad, el botón queda justo después de la descripción del perfil. También lo cambias al editar el perfil.</p>
            </section>
        @endif

        @if($app->diasRestantes() <= 30)
            <section class="dt-panel" style="border-color:{{ $app->vigente() ? 'rgba(245,158,11,.45)' : 'rgba(239,68,68,.5)' }};margin-bottom:18px">
                <b>{{ $app->vigente() ? 'Tu agenda vence en ' . $app->diasRestantes() . ' ' . ($app->diasRestantes() === 1 ? 'día' : 'días') . '.' : 'Tu agenda venció.' }}</b>
                <p class="dt-sub">{{ $app->vigente() ? 'Renueva por ' . $precio . ' al año y sigue recibiendo reservas.' : 'Tu perfil solo muestra un botón para pedir cita por WhatsApp. Renueva por ' . $precio . ' al año para volver a recibir reservas.' }} Las citas ya tomadas se respetan.</p>
                @if($whatsappVentas)
                    <a class="dt-btn pri" style="margin-top:12px" target="_blank" rel="noopener" href="https://wa.me/{{ preg_replace('/\D/', '', $whatsappVentas) }}?text={{ rawurlencode('Hola, quiero renovar la agenda de citas de mi perfil.') }}">Renovar por WhatsApp</a>
                @endif
            </section>
        @endif

        @if($porResolver->isNotEmpty())
            <section class="dt-panel" style="margin-bottom:18px">
                <div class="dt-panel-h"><h2>Por resolver ({{ $porResolver->count() }})</h2><span class="dt-sub" style="margin:0;font-size:12px">El cliente espera tu respuesta</span></div>
                @foreach($porResolver as $c)
                    <div style="display:flex;flex-wrap:wrap;justify-content:space-between;gap:10px;align-items:center;padding:12px 0;border-top:1px solid var(--border)">
                        <div>
                            <b>{{ $c->cliente_nombre }}</b> · {{ $c->servicio_nombre }}<br>
                            @if($c->estado === 'pendiente')
                                <span class="dt-sub" style="margin:0">Pide cita el {{ $c->fechaLocal($zona) }} a las {{ $h($c) }}</span>
                            @else
                                <span class="dt-sub" style="margin:0">Pide pasar su cita del {{ $c->fechaLocal($zona) }} {{ $h($c) }} al <b style="color:var(--text)">{{ $c->fechaLocal($zona, $c->propuesta_inicia_en) }} {{ $h($c, $c->propuesta_inicia_en) }}</b></span>
                            @endif
                        </div>
                        <div style="display:flex;gap:8px">
                            <form method="POST" action="/dashboard/agenda/citas/{{ $c->id }}/{{ $c->estado === 'pendiente' ? 'aprobar' : 'responder' }}">@csrf<input type="hidden" name="acepta" value="1"><button class="dt-btn pri" type="submit">{{ $c->estado === 'pendiente' ? 'Aprobar' : 'Aceptar cambio' }}</button></form>
                            <form method="POST" action="/dashboard/agenda/citas/{{ $c->id }}/{{ $c->estado === 'pendiente' ? 'rechazar' : 'responder' }}">@csrf<input type="hidden" name="acepta" value="0"><button class="dt-btn danger" type="submit">{{ $c->estado === 'pendiente' ? 'Rechazar' : 'Mantener hora' }}</button></form>
                        </div>
                    </div>
                @endforeach
            </section>
        @endif

        @php
            $paso = $vista === 'semana' ? 7 : 1;
            $url = fn ($f, $v = null) => '/dashboard/agenda?vista=' . ($v ?? $vista) . '&fecha=' . $f->format('Y-m-d');
            $dias = $vista === 'semana' ? collect(range(0, 6))->map(fn ($i) => $desde->copy()->addDays($i)) : collect([$desde->copy()]);
            $porDia = $citas->groupBy(fn ($c) => $c->inicia_en->copy()->setTimezone($zona)->format('Y-m-d'));
        @endphp
        <section class="dt-panel">
            <div class="dt-panel-h">
                <div style="display:flex;align-items:center;gap:8px">
                    <a class="dt-ico" href="{{ $url($fecha->copy()->subDays($paso)) }}" aria-label="Anterior"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg></a>
                    <a class="dt-ico" href="{{ $url($fecha->copy()->addDays($paso)) }}" aria-label="Siguiente"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6l6 6-6 6"/></svg></a>
                    <h2>{{ $vista === 'semana' ? 'Semana del ' . $desde->locale('es')->translatedFormat('j \d\e F') : \Illuminate\Support\Str::ucfirst($fecha->locale('es')->translatedFormat('l j \d\e F')) }}</h2>
                    <a class="dt-btn" style="height:30px;padding:0 10px" href="{{ $url(now($zona)) }}">Hoy</a>
                </div>
                <div class="dt-seg">
                    <button type="button" class="{{ $vista === 'dia' ? 'on' : '' }}" onclick="location.href='{{ $url($fecha, 'dia') }}'">Día</button>
                    <button type="button" class="{{ $vista === 'semana' ? 'on' : '' }}" onclick="location.href='{{ $url($fecha, 'semana') }}'">Semana</button>
                </div>
            </div>

            @foreach($dias as $dia)
                @php $lista = $porDia[$dia->format('Y-m-d')] ?? collect(); @endphp
                @if($vista === 'semana')
                    <div style="margin:16px 0 6px;font-weight:800;font-size:13px;text-transform:capitalize;{{ $dia->isSameDay(now($zona)) ? 'color:#60a5fa' : '' }}">{{ $dia->locale('es')->translatedFormat('l j') }}</div>
                @endif
                @forelse($lista as $c)
                    @php $estado = $c->estadoVisible(); $wa = \App\Services\Agenda\Citas::enlaceWhatsapp($c, $ajustes); @endphp
                    <div x-data="{ abierto: null }" style="border:1px solid var(--border);border-radius:16px;padding:12px 14px;margin-bottom:8px;background:rgba(0,0,0,.2)">
                        <div style="display:flex;flex-wrap:wrap;justify-content:space-between;gap:10px;align-items:center">
                            <div style="display:flex;gap:14px;align-items:center;min-width:0">
                                <div style="font-weight:800;font-variant-numeric:tabular-nums;min-width:74px">{{ $h($c) }}<br><span class="dt-sub" style="margin:0;font-size:12px;font-weight:500">{{ $c->duracionMin() }} min</span></div>
                                <div style="min-width:0">
                                    <b>{{ $c->cliente_nombre }}</b> <span class="dt-chip {{ $chip[$estado][1] }}">{{ $chip[$estado][0] }}</span><br>
                                    <span class="dt-sub" style="margin:0">{{ $c->servicio_nombre }} · {{ $c->cliente_correo }}@if($c->cliente_whatsapp) · {{ $c->cliente_whatsapp }}@endif</span>
                                    @if($c->nota)<br><span class="dt-sub" style="margin:0;font-style:italic">“{{ $c->nota }}”</span>@endif
                                    @if($c->estado === 'cambio')<br><span class="dt-sub" style="margin:0;color:#fbbf24">{{ $c->propuesta_por === 'duenio' ? 'Propusiste' : 'Pide' }} pasarla al {{ $c->fechaLocal($zona, $c->propuesta_inicia_en) }} {{ $h($c, $c->propuesta_inicia_en) }}</span>@endif
                                </div>
                            </div>
                            @if($c->activa() && !$c->realizada())
                                <div style="display:flex;gap:6px;flex-wrap:wrap">
                                    @if($wa)<a class="dt-btn" href="{{ $wa }}" target="_blank" rel="noopener" style="color:#4ade80">WhatsApp</a>@endif
                                    @if($c->estado === 'confirmada')<button class="dt-btn" type="button" @click="abierto = abierto === 'mover' ? null : 'mover'">Mover</button>@endif
                                    <button class="dt-btn danger" type="button" @click="abierto = abierto === 'cancelar' ? null : 'cancelar'">Cancelar</button>
                                </div>
                            @endif
                        </div>
                        <form x-show="abierto === 'mover'" x-cloak method="POST" action="/dashboard/agenda/citas/{{ $c->id }}/mover" style="display:flex;flex-wrap:wrap;gap:8px;align-items:flex-end;margin-top:12px">
                            @csrf
                            <div><span class="dt-label">Nuevo día</span><input class="dt-input" type="date" name="fecha" required min="{{ now($zona)->format('Y-m-d') }}" value="{{ $c->inicia_en->copy()->setTimezone($zona)->format('Y-m-d') }}"></div>
                            <div><span class="dt-label">Nueva hora</span><input class="dt-input" type="time" name="hora" required step="300" value="{{ $c->inicia_en->copy()->setTimezone($zona)->format('H:i') }}"></div>
                            <button class="dt-btn pri" type="submit" style="height:44px">Proponer al cliente</button>
                            <p class="dt-sub" style="flex-basis:100%;margin:0;font-size:12px">El cliente recibe un correo para aceptar la nueva hora. Hasta entonces, la cita sigue a la hora actual.</p>
                        </form>
                        <form x-show="abierto === 'cancelar'" x-cloak method="POST" action="/dashboard/agenda/citas/{{ $c->id }}/cancelar" style="display:flex;flex-wrap:wrap;gap:8px;align-items:flex-end;margin-top:12px">
                            @csrf
                            <div style="flex:1;min-width:200px"><span class="dt-label">Motivo (le llega al cliente, opcional)</span><input class="dt-input" name="motivo" maxlength="200"></div>
                            <button class="dt-btn danger" type="submit" style="height:44px">Sí, cancelar cita</button>
                        </form>
                    </div>
                @empty
                    @if($vista === 'dia')
                        <p class="dt-sub">No hay citas este día.</p>
                    @else
                        <p class="dt-sub" style="margin:0 0 4px;font-size:12.5px">Sin citas.</p>
                    @endif
                @endforelse
            @endforeach
        </section>

        {{-- Nueva cita a mano --}}
        <div x-data="{ abierta: {{ $errors->any() ? 'true' : 'false' }} }" @nueva-cita.window="abierta = true" x-show="abierta" x-cloak
             style="position:fixed;inset:0;z-index:80;background:rgba(0,0,0,.6);display:grid;place-items:center;padding:16px" @click.self="abierta = false">
            <form method="POST" action="/dashboard/agenda/citas" class="dt-panel" style="width:100%;max-width:520px;max-height:90vh;overflow:auto;background:var(--card-solid)">
                @csrf
                <div class="dt-panel-h"><h2>Nueva cita</h2><button type="button" class="dt-ico" @click="abierta = false" aria-label="Cerrar">✕</button></div>
                @if($errors->any())<p style="color:#f87171;margin:0 0 12px">{{ $errors->first() }}</p>@endif
                <div style="display:grid;gap:12px">
                    <div><span class="dt-label">Servicio</span>
                        <select class="dt-input" name="servicio_id" required>
                            @foreach($servicios as $s)<option value="{{ $s->id }}" @selected(old('servicio_id') == $s->id)>{{ $s->nombre }} · {{ $s->duracion_min }} min</option>@endforeach
                        </select>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                        <div><span class="dt-label">Día</span><input class="dt-input" type="date" name="fecha" required value="{{ old('fecha', $fecha->format('Y-m-d')) }}"></div>
                        <div><span class="dt-label">Hora</span><input class="dt-input" type="time" name="hora" required step="300" value="{{ old('hora') }}"></div>
                    </div>
                    <div><span class="dt-label">Nombre del cliente</span><input class="dt-input" name="nombre" required maxlength="80" value="{{ old('nombre') }}"></div>
                    <div><span class="dt-label">Correo del cliente</span><input class="dt-input" type="email" name="correo" required maxlength="120" value="{{ old('correo') }}"></div>
                    <div><span class="dt-label">WhatsApp (opcional)</span><input class="dt-input" type="tel" name="whatsapp" maxlength="30" value="{{ old('whatsapp') }}"></div>
                    <div><span class="dt-label">Nota (opcional)</span><input class="dt-input" name="nota" maxlength="500" value="{{ old('nota') }}"></div>
                </div>
                <p class="dt-sub" style="font-size:12px">Puedes agendar fuera de tu horario; solo se revisa que no se cruce con otra cita o un cierre.</p>
                <button class="dt-btn pri" type="submit" style="width:100%;height:46px;margin-top:12px">Agendar y avisar al cliente</button>
            </form>
        </div>
    @endif
@endsection
