@extends('layouts.app')

@section('contenido')
    @php
        $estados = ['activo' => ['Activo', 'up'], 'pronto' => ['Renueva pronto', ''], 'gracia' => ['Vencido · en gracia', 'down'], 'pausa' => ['En pausa', 'down']];
        $f = fn ($fecha) => $fecha?->format('d/m/Y');
        $digitos = preg_replace('/\D/', '', (string) $c['celular']);
        $whatsapp = $digitos ? 'https://wa.me/'.(strlen($digitos) === 10 ? '57'.$digitos : $digitos) : null;
    @endphp

    <a href="{{ $volver }}" class="ad-volver">← Volver a la lista</a>
    <div class="dt-head" style="align-items:center">
        <div style="display:flex;gap:14px;align-items:center;min-width:0">
            <span class="ad-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($u->name, 0, 1)) }}</span>
            <div style="min-width:0">
                <h1 class="dt-h1" style="margin:0">{{ $u->name }}</h1>
                <p class="dt-sub" style="margin:2px 0 0;overflow-wrap:anywhere">{{ $u->email }} · cliente desde {{ $f($u->created_at) }}</p>
                <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:8px">
                    @if ($c['estado'])
                        <span class="dt-chip {{ $estados[$c['estado']][1] }}">{{ $estados[$c['estado']][0] }}</span>
                    @else
                        <span class="dt-chip">Tarjeta sin activar</span>
                    @endif
                    @if ($sinUso)<span class="dt-chip" style="color:var(--warn-text)">{{ $meses }}+ meses sin visitas</span>@endif
                    @if ($u->precio_renovacion !== null)<span class="dt-chip">Precio especial</span>@endif
                    @if ($c['admin'])<span class="dt-chip">Administrador</span>@endif
                </div>
            </div>
        </div>
        <div style="display:flex;flex-wrap:wrap;gap:8px">
            @if ($c['perfiles'])<a class="dt-btn" href="{{ $c['perfiles'][0]['url'] }}" target="_blank" rel="noopener">Ver perfil</a>@endif
            @if ($whatsapp)<a class="dt-btn" href="{{ $whatsapp }}" target="_blank" rel="noopener">WhatsApp</a>@endif
            @if ($chipPendiente)
                <form method="POST" action="/admin/clientes/{{ $u->id }}/reenviar-codigo">@csrf<button class="dt-btn" type="submit">Reenviar código</button></form>
            @endif
            @if ($sinUso)
                <form method="POST" action="/admin/sin-uso/{{ $u->id }}/enviar">@csrf<button class="dt-btn" type="submit">Enviar recordatorio</button></form>
            @endif
        </div>
    </div>
    @include('admin._tabs')

    @if ($errors->any())<p class="dt-panel" role="alert" style="color:var(--danger-text);margin:0 0 18px">{{ $errors->first() }}</p>@endif

    <div class="ad-ficha">
        <div class="ad-ficha-col">
            <section class="dt-panel" style="margin:0">
                <div class="dt-panel-h"><h2>Datos de la cuenta</h2></div>
                <form method="POST" action="/admin/clientes/{{ $u->id }}" style="display:grid;gap:14px">
                    @csrf @method('PUT')
                    <div><label class="dt-label" for="cl-nombre">Nombre</label><input id="cl-nombre" class="dt-input" name="name" value="{{ old('name', $u->name) }}" required maxlength="255"></div>
                    <div>
                        <label class="dt-label" for="cl-correo">Correo (con el que entra al portal)</label>
                        <input id="cl-correo" class="dt-input" type="email" name="email" value="{{ old('email', $u->email) }}" required maxlength="255">
                        <small class="dt-sub" style="display:block;font-size:12px;margin-top:4px">Si lo cambias, desde ese momento entra con el correo nuevo y su contraseña de siempre.</small>
                    </div>
                    <div>
                        <label class="dt-label" for="cl-precio">Precio de renovación ({{ $moneda }})</label>
                        <input id="cl-precio" class="dt-input" type="number" min="0" step="1" name="precio_renovacion" value="{{ old('precio_renovacion', $u->precio_renovacion) }}" placeholder="Vacío = precio general {{ $precioGeneral }}">
                        <small class="dt-sub" style="display:block;font-size:12px;margin-top:4px">Hoy paga <b>{{ $precioActual ?? '—' }}</b> al renovar. Se usa en su panel, en el pago con Bold y en los avisos por correo.</small>
                    </div>
                    <div><button class="dt-btn pri" type="submit">Guardar datos</button></div>
                </form>
            </section>

            <section class="dt-panel" style="margin:0">
                <div class="dt-panel-h"><h2>Tarjetas y servicio</h2></div>
                @forelse ($tarjetas as $t)
                    <div class="ad-tarjeta">
                        <div style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap">
                            <div>
                                <code style="font-size:15px">{{ $t['t']->nfc_uid }}</code>
                                <span class="dt-chip {{ $estados[$t['estado']][1] }}" style="margin-left:6px">{{ $estados[$t['estado']][0] }}</span>
                                @unless ($t['vinculada'])<span class="dt-chip" title="Ya no está en ninguno de sus perfiles">Reemplazada</span>@endunless
                            </div>
                            <b style="font-variant-numeric:tabular-nums">{{ $t['diasRestantes'] }} {{ $t['diasRestantes'] === 1 ? 'día' : 'días' }}</b>
                        </div>
                        <dl class="ad-datos">
                            <dt>Activada</dt><dd>{{ $t['t']->activada_en?->format('d/m/Y H:i') }}</dd>
                            <dt>Cómo</dt><dd>{{ $t['t']->activacion_medio === 'codigo' ? 'Escribió el código' : ($t['t']->activacion_medio === 'tarjeta' ? 'Acercó la tarjeta' : 'Sin dato') }}</dd>
                            <dt>Dónde</dt><dd>{{ $t['t']->activacion_lugar ?: 'Sin dato' }}</dd>
                            <dt>Dispositivo</dt><dd>{{ $t['t']->activacion_dispositivo ?: 'Sin dato' }}</dd>
                        </dl>
                        <form method="POST" action="/admin/clientes/{{ $u->id }}/tarjetas/{{ $t['t']->id }}" class="ad-vence">
                            @csrf @method('PUT')
                            <div style="flex:1;min-width:160px">
                                <label class="dt-label" for="v-{{ $t['t']->id }}">Vence (fecha de renovación)</label>
                                <input id="v-{{ $t['t']->id }}" class="dt-input" type="date" name="vence_en" value="{{ $t['t']->vence_en->toDateString() }}" min="{{ $t['t']->activada_en->copy()->addDay()->toDateString() }}" required>
                            </div>
                            <button class="dt-btn pri" type="submit">Guardar fecha</button>
                            <button class="dt-btn" type="submit" name="accion" value="anio" formnovalidate onclick="return confirm('¿Sumar un año de servicio a esta tarjeta?')">Sumar 1 año</button>
                        </form>
                    </div>
                @empty
                    <p class="dt-sub" style="margin:0">Aún no ha activado ninguna tarjeta.@if ($chipPendiente) El código de su compra es <code>{{ $chipPendiente->codigo }}</code>.@endif</p>
                @endforelse
            </section>
        </div>

        <div class="ad-ficha-col">
            <section class="dt-panel" style="margin:0">
                <div class="dt-panel-h"><h2>Uso</h2></div>
                <div class="ad-mini">
                    <div><b>{{ number_format($c['vistas'], 0, ',', '.') }}</b><span>Vistas del perfil</span></div>
                    <div><b>{{ number_format($c['interacciones'], 0, ',', '.') }}</b><span>Interacciones</span></div>
                    <div><b style="font-size:16px">{{ $c['ultimoUso']?->diffForHumans() ?? 'Nunca' }}</b><span>Último uso</span></div>
                    <div><b style="font-size:16px">{{ $f($c['recordatorioEn']) ?? 'Nunca' }}</b><span>Recordatorio</span></div>
                </div>
            </section>

            <section class="dt-panel" style="margin:0">
                <div class="dt-panel-h"><h2>Perfiles</h2></div>
                @forelse ($perfiles as $p)
                    <div class="ad-fila">
                        <span><b>{{ $p->nombre }}</b><small>{{ $p->nombre_perfil ?: 'Perfil' }}</small></span>
                        <span><a href="{{ \App\Models\Profile::urlPublica($p->nickname) }}" target="_blank" rel="noopener" style="color:var(--link)">/{{ $p->nickname }}</a></span>
                        <span style="text-align:right">@if ($p->nfc_uid)<span class="dt-chip up">Con tarjeta</span>@endif</span>
                    </div>
                @empty
                    <p class="dt-sub" style="margin:0">Aún no ha creado su perfil.</p>
                @endforelse
            </section>

            <section class="dt-panel" style="margin:0">
                <div class="dt-panel-h"><h2>Compras</h2></div>
                @forelse ($pedidos as $p)
                    <div class="ad-fila">
                        <span><b>{{ $p->codigo }}</b><small>{{ $p->acabadoNombre() }}@if($p->nombre_tarjeta) · {{ $p->nombre_tarjeta }}@endif · {{ $p->ciudad }}</small></span>
                        <span>{{ $p->totalTexto() }}<small>{{ ['pagado' => 'Pagado', 'pendiente' => 'Sin pagar', 'rechazado' => 'Rechazado'][$p->estado] ?? $p->estado }} · {{ $f($p->pagado_en ?? $p->created_at) }}</small></span>
                        <span style="text-align:right">@if ($p->chip)<code>{{ $p->chip->codigo }}</code>@endif</span>
                    </div>
                @empty
                    <p class="dt-sub" style="margin:0">Sin compras en línea (pudo comprar por WhatsApp).</p>
                @endforelse
            </section>

            <section class="dt-panel" style="margin:0">
                <div class="dt-panel-h"><h2>Ayuda</h2></div>
                @forelse ($conversaciones as $cv)
                    <a class="ad-fila ad-link" href="/admin/soporte/{{ $cv->id }}">
                        <span><b>{{ $cv->asunto }}</b><small>{{ $cv->tipo === 'chat' ? 'Chat' : 'Ticket' }} · {{ $f($cv->created_at) }}</small></span>
                        <span>{{ ['en_cola' => 'En la fila', 'abierta' => 'Abierta', 'cerrada' => 'Cerrada'][$cv->estado] ?? $cv->estado }}</span>
                        <span style="text-align:right"><small>{{ $cv->ultimo_mensaje_en?->diffForHumans() }}</small></span>
                    </a>
                @empty
                    <p class="dt-sub" style="margin:0">No ha escrito a soporte.</p>
                @endforelse
            </section>
        </div>
    </div>
@endsection
