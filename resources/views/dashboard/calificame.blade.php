@extends('layouts.app')

@section('contenido')
    <style>
        .cf-est { color:#fbbf24; letter-spacing:1px; white-space:nowrap; }
        .cf-est .off { color:rgba(255,255,255,.16); }
        .cf-barra { display:grid; grid-template-columns:34px 1fr 34px; gap:10px; align-items:center; font-size:12.5px; color:var(--muted); }
        .cf-barra div { height:8px; border-radius:99px; background:rgba(255,255,255,.07); overflow:hidden; }
        .cf-barra i { display:block; height:100%; background:#fbbf24; border-radius:99px; }
        .dt-seg a { border-radius:9px; padding:6px 14px; font-size:12.5px; font-weight:600; color:var(--muted); text-decoration:none; }
        .dt-seg a.on { background:rgba(255,255,255,.1); color:#fff; }
        .cf-item { display:grid; gap:6px; padding:16px 0; border-top:1px solid var(--border); }
        .cf-item:first-child { border-top:0; padding-top:0; }
        .cf-sw { display:inline-flex; align-items:center; gap:8px; font-size:13px; font-weight:600; cursor:pointer; }
        .cf-sw input { width:38px; height:22px; appearance:none; border-radius:99px; background:rgba(255,255,255,.12); position:relative; cursor:pointer; transition:background .15s; flex:none; margin:0; }
        .cf-sw input::after { content:""; position:absolute; top:3px; left:3px; width:16px; height:16px; border-radius:50%; background:#fff; transition:transform .15s; }
        .cf-sw input:checked { background:#2563eb; }
        .cf-sw input:checked::after { transform:translateX(16px); }
        .cf-sw input:focus-visible { outline:none; box-shadow:0 0 0 3px rgba(59,130,246,.35); }
        .cf-sw input:disabled { opacity:.4; cursor:not-allowed; }
        @media (min-width: 900px) { .cf-grid { grid-template-columns:minmax(0, 1fr) 360px; } }
    </style>
    @php
        $estrellas = fn (int $n) => str_repeat('★', $n) . '<span class="off">' . str_repeat('★', 5 - $n) . '</span>';
    @endphp

    @if(!$app)
        <div class="dt-head">
            <div>
                <h1 class="dt-h1">Califícame</h1>
                <p class="dt-sub">Tus clientes califican tu atención en un toque y tu perfil lo muestra.</p>
            </div>
        </div>
        <section class="dt-panel" style="max-width:720px">
            <div style="display:grid;gap:12px;font-size:14.5px">
                <div>⭐ <b>Calificación en 10 segundos.</b> Desde tu perfil, un enlace o un QR, el cliente toca de 1 a 5 estrellas y deja un comentario si quiere.</div>
                <div>🏅 <b>Sello de confianza en tu perfil.</b> "4,9 ★ · 37 calificaciones" se ve apenas alguien toca tu tarjeta.</div>
                <div>💬 <b>Tú eliges qué comentarios se ven.</b> Muestra uno, varios o ninguno. Solo se pueden mostrar los de 4 y 5 estrellas.</div>
                <div>🤝 <b>Las bajas te llegan en privado.</b> Con 3 estrellas o menos te avisamos por correo para que lo arregles antes de que se vuelva una mala reseña.</div>
                <div>📅 <b>Después de cada cita.</b> Si usas la Agenda, le pedimos la calificación al cliente por correo cuando termina su cita.</div>
            </div>
            <form method="POST" action="/dashboard/calificame/activar" style="margin-top:22px;display:flex;flex-wrap:wrap;gap:14px;align-items:center">
                @csrf
                <button class="dt-btn pri" type="submit" style="height:46px;padding:0 22px">Activar gratis por un año</button>
            </form>
        </section>
    @else
        <div class="dt-head">
            <div>
                <h1 class="dt-h1">Califícame</h1>
                <p class="dt-sub">
                    @if($enlace)Tus clientes califican en <a href="{{ $enlace }}" target="_blank" rel="noopener" style="color:#60a5fa">{{ preg_replace('#^https?://#', '', $enlace) }}</a>.@else Crea tu perfil para tener tu enlace de calificaciones.@endif
                    @unless($app->vigente()) <b style="color:#f87171">Venció el {{ $app->vence_en->format('d/m/Y') }}.</b>@endunless
                </p>
            </div>
            @if($enlace)
                <div style="display:flex;gap:8px;flex-wrap:wrap" x-data="{ copiado: false }">
                    <button class="dt-btn" type="button" @click="navigator.clipboard.writeText(@js($enlace)).then(() => { copiado = true; setTimeout(() => copiado = false, 2000) })" x-text="copiado ? 'Enlace copiado ✓' : 'Copiar enlace'">Copiar enlace</button>
                    <a class="dt-btn pri" target="_blank" rel="noopener" href="https://wa.me/?text={{ rawurlencode('¿Me ayudas calificando mi atención? Solo toma 10 segundos: ' . $enlace) }}">Pedir por WhatsApp</a>
                </div>
            @endif
        </div>

        <div class="cf-grid" style="display:grid;gap:18px;align-items:start">
            <div style="display:grid;gap:18px;min-width:0">
                <section class="dt-panel" style="margin:0">
                    <div style="display:flex;flex-wrap:wrap;gap:26px;align-items:center">
                        <div style="text-align:center;min-width:130px">
                            <div style="font-size:46px;font-weight:900;line-height:1">{{ $resumen['total'] ? \App\Services\Calificame\Calificame::promedioTexto($resumen['promedio']) : '–' }}</div>
                            <div class="cf-est" style="font-size:18px;margin-top:6px" aria-hidden="true">{!! $estrellas((int) round($resumen['promedio'])) !!}</div>
                            <div class="dt-sub">{{ $resumen['total'] }} {{ $resumen['total'] === 1 ? 'calificación' : 'calificaciones' }}</div>
                        </div>
                        <div style="flex:1;min-width:200px;display:grid;gap:6px">
                            @foreach($resumen['por'] as $n => $cuantas)
                                <div class="cf-barra"><span>{{ $n }} ★</span><div><i style="width:{{ $resumen['total'] ? round($cuantas / $resumen['total'] * 100) : 0 }}%"></i></div><span>{{ $cuantas }}</span></div>
                            @endforeach
                        </div>
                    </div>
                    @if($resumen['total'] < config('calificame.minimo_sello'))
                        <p class="dt-sub" style="margin-top:16px">El sello sale en tu perfil desde {{ config('calificame.minimo_sello') }} calificaciones. Comparte tu enlace con tus últimos clientes para empezar.</p>
                    @endif
                </section>

                <section class="dt-panel" style="margin:0" x-data="comentarios({{ $visibles }})">
                    <div class="dt-panel-h">
                        <h2>Calificaciones</h2>
                        <div class="dt-seg">
                            @foreach(['todas' => 'Todas', 'buenas' => '4 y 5 ★', 'bajas' => 'Privadas'] as $clave => $texto)
                                <a href="?ver={{ $clave }}" class="{{ $filtro === $clave ? 'on' : '' }}" @if($filtro === $clave) aria-current="page" @endif>{{ $texto }}</a>
                            @endforeach
                        </div>
                    </div>
                    <p class="dt-sub" style="margin:-8px 0 14px">Enciende "Mostrar en mi perfil" en los comentarios que quieras que se vean, o en ninguno. Van <b x-text="visibles"></b> de {{ config('calificame.maximo_comentarios') }}.</p>
                    <p class="dt-sub" x-show="error" x-text="error" style="color:#f87171" role="alert"></p>

                    @forelse($calificaciones as $c)
                        <article class="cf-item">
                            <div style="display:flex;flex-wrap:wrap;justify-content:space-between;gap:8px;align-items:center">
                                <span><span class="cf-est" aria-label="{{ $c->estrellas }} de 5 estrellas">{!! $estrellas($c->estrellas) !!}</span>
                                    <b style="margin-left:6px">{{ $c->nombre ?: 'Sin nombre' }}</b>
                                    <span class="dt-sub" style="margin:0 0 0 6px">{{ $c->created_at->diffForHumans() }}@if($c->cita) · {{ $c->cita->servicio_nombre }}@endif</span>
                                </span>
                                @if($c->buena())
                                    <label class="cf-sw" title="{{ $c->comentario ? '' : 'Sin comentario: suma al promedio, pero no hay texto para mostrar' }}">
                                        <input type="checkbox" @checked($c->visible) @disabled(!$c->publicable()) @change="cambiar({{ $c->id }}, $event)">
                                        Mostrar en mi perfil
                                    </label>
                                @else
                                    <span class="dt-chip down">Privada · solo tú la ves</span>
                                @endif
                            </div>
                            @if($c->comentario)<p style="margin:0;white-space:pre-line">{{ $c->comentario }}</p>@endif
                            @if($c->contacto && !$c->buena())
                                @php $digitos = preg_replace('/\D/', '', $c->contacto); @endphp
                                <div style="display:flex;flex-wrap:wrap;gap:8px;align-items:center">
                                    <span class="dt-sub" style="margin:0">Contacto: {{ $c->contacto }}</span>
                                    @if(str_contains($c->contacto, '@'))
                                        <a class="dt-btn" href="mailto:{{ $c->contacto }}">Responder por correo</a>
                                    @elseif(strlen($digitos) >= 7)
                                        <a class="dt-btn" target="_blank" rel="noopener" href="https://wa.me/{{ $digitos }}?text={{ rawurlencode('Hola' . ($c->nombre ? ' ' . \Illuminate\Support\Str::before($c->nombre, ' ') : '') . ', gracias por tu calificación. Quiero saber qué pasó para mejorarlo.') }}">Responder por WhatsApp</a>
                                    @endif
                                </div>
                            @endif
                        </article>
                    @empty
                        <p class="dt-sub">Todavía no hay calificaciones{{ $filtro !== 'todas' ? ' en este filtro' : '' }}.</p>
                    @endforelse
                    <div style="margin-top:14px">{{ $calificaciones->links() }}</div>
                </section>
            </div>

            <section class="dt-panel" style="margin:0">
                <div class="dt-panel-h"><h2>En tu perfil</h2></div>
                <form method="POST" action="/dashboard/calificame/ajustes" style="display:grid;gap:16px">
                    @csrf
                    <label class="cf-sw"><input type="checkbox" name="mostrar_sello" value="1" @checked($ajustes->mostrar_sello)> Mostrar el sello de estrellas</label>
                    <label class="cf-sw"><input type="checkbox" name="mostrar_boton" value="1" @checked($ajustes->mostrar_boton)> Botón "Califica mi atención"</label>
                    @if($tieneAgenda)
                        <label class="cf-sw"><input type="checkbox" name="pedir_tras_cita" value="1" @checked($ajustes->pedir_tras_cita)> Pedir calificación por correo después de cada cita</label>
                    @else
                        <input type="hidden" name="pedir_tras_cita" value="{{ $ajustes->pedir_tras_cita ? 1 : 0 }}">
                    @endif
                    <div>
                        <label class="dt-label" for="google_url">Tu enlace de reseñas de Google (opcional)</label>
                        <input class="dt-input" id="google_url" name="google_url" type="url" inputmode="url" placeholder="https://g.page/r/..." value="{{ old('google_url', $ajustes->google_url) }}">
                        <p class="dt-sub">A quien te dé 4 o 5 estrellas lo invitamos a dejarte también la reseña en Google.</p>
                        @error('google_url')<p class="dt-sub" style="color:#f87171" role="alert">{{ $message }}</p>@enderror
                    </div>
                    <button class="dt-btn pri" type="submit" style="justify-self:start">Guardar</button>
                </form>
            </section>
        </div>

        <script>
            function comentarios(visibles) {
                return {
                    visibles, error: '',
                    async cambiar(id, e) {
                        const caja = e.target, visible = caja.checked;
                        caja.disabled = true; this.error = '';
                        try {
                            const r = await fetch('/dashboard/calificame/' + id + '/visible', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                                body: JSON.stringify({ visible }),
                            });
                            const datos = await r.json();
                            if (!r.ok) throw new Error(datos.message || 'No se pudo guardar.');
                            this.visibles += visible ? 1 : -1;
                        } catch (err) {
                            caja.checked = !visible;
                            this.error = err.message || 'No se pudo guardar. Revisa tu conexión.';
                        }
                        caja.disabled = false;
                    },
                };
            }
        </script>
    @endif
@endsection
