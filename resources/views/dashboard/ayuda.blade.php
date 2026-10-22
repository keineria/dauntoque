@extends('layouts.app')

@php
    $temas = config('ayuda.temas', []);
    // Texto plano de cada pregunta para el buscador
    $indice = [];
    foreach ($temas as $clave => $tema) {
        foreach ($tema['preguntas'] as $i => $q) {
            $indice[$clave.'-'.$i] = mb_strtolower($q['p'].' '.strip_tags($q['r']));
        }
    }
@endphp

@section('contenido')
    <div class="dt-head">
        <div>
            <h1 class="dt-h1">Ayuda y preguntas frecuentes</h1>
            <p class="dt-sub">Busca tu duda o elige un tema. Si no encuentras la respuesta, abajo puedes hablar con un asesor.</p>
        </div>
    </div>

    <div x-data="{
            q: '',
            tema: 'todos',
            abierta: null,
            indice: @js($indice),
            norm(t) { return t.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '') },
            ve(id, clave) {
                if (this.q.trim() !== '') return this.norm(this.indice[id]).includes(this.norm(this.q.trim()));
                return this.tema === 'todos' || this.tema === clave;
            },
            hayResultados() { return Object.keys(this.indice).some(id => this.ve(id, id.split('-')[0])) },
        }">
        <section class="dt-panel ay-buscar">
            <label class="ay-caja">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
                <input type="search" x-model="q" placeholder="Escribe tu duda, por ejemplo: activar tarjeta, renovar, contraseña" aria-label="Buscar en las preguntas frecuentes">
            </label>
            <div class="ay-temas" role="group" aria-label="Temas">
                <button type="button" :class="tema === 'todos' && !q ? 'on' : ''" @click="tema = 'todos'; q = ''">Todos</button>
                @foreach ($temas as $clave => $tema)
                    <button type="button" :class="tema === @js($clave) && !q ? 'on' : ''" @click="tema = @js($clave); q = ''">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="{{ $tema['icono'] }}"/></svg>{{ $tema['titulo'] }}
                    </button>
                @endforeach
            </div>
        </section>

        @foreach ($temas as $clave => $tema)
            <section class="dt-panel ay-tema" x-show="{{ collect(array_keys($tema['preguntas']))->map(fn ($i) => "ve('{$clave}-{$i}', '{$clave}')")->implode(' || ') }}" id="tema-{{ $clave }}">
                <div class="dt-panel-h">
                    <h2 class="ay-titulo"><span class="ay-icono"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="{{ $tema['icono'] }}"/></svg></span>{{ $tema['titulo'] }}</h2>
                </div>
                @foreach ($tema['preguntas'] as $i => $pregunta)
                    @php $id = $clave.'-'.$i; @endphp
                    <div class="ay-preg" x-show="ve('{{ $id }}', '{{ $clave }}')" :class="abierta === '{{ $id }}' ? 'abierta' : ''">
                        <button type="button" class="ay-p" @click="abierta = abierta === '{{ $id }}' ? null : '{{ $id }}'" :aria-expanded="abierta === '{{ $id }}'">
                            <span>{{ $pregunta['p'] }}</span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
                        </button>
                        <div class="ay-r" x-show="abierta === '{{ $id }}'" x-cloak>
                            {!! $pregunta['r'] !!}
                            @if (! empty($pregunta['imagen']))
                                <figure class="ay-img">@include('ayuda.ilustraciones.'.$pregunta['imagen'])</figure>
                            @endif
                        </div>
                    </div>
                @endforeach
            </section>
        @endforeach

        <section class="dt-panel" x-show="!hayResultados()" x-cloak>
            <p class="dt-sub" style="margin:0">No encontramos preguntas con esa búsqueda. Prueba con otras palabras o escríbenos aquí abajo.</p>
        </section>
    </div>

    <section class="dt-panel ay-asesor" id="asesor" x-data="{ tipo: 'chat' }">
        <div class="dt-panel-h">
            <div>
                <h2>¿Necesitas ayuda? Habla con un asesor</h2>
                <p class="dt-sub" style="margin:4px 0 0">Si tu duda no está arriba, escríbenos. Te atiende una persona de nuestro equipo.</p>
            </div>
        </div>
        <form method="POST" action="/dashboard/ayuda" style="display:grid;gap:12px">
            @csrf
            @if ($errors->any())<p style="color:#f87171;margin:0">{{ $errors->first() }}</p>@endif
            <div class="dt-seg" role="group" aria-label="Tipo de ayuda" style="justify-self:start">
                <button type="button" :class="tipo === 'chat' ? 'on' : ''" @click="tipo = 'chat'">Chat con un asesor</button>
                <button type="button" :class="tipo === 'ticket' ? 'on' : ''" @click="tipo = 'ticket'">Dejar un mensaje</button>
            </div>
            <input type="hidden" name="tipo" :value="tipo">
            <p class="dt-sub" style="margin:0" x-text="tipo === 'chat' ? 'Entras a una fila y un asesor te atiende en orden de llegada.' : 'Te respondemos por escrito; verás la respuesta aquí mismo, en Ayuda.'"></p>
            <div><span class="dt-label">Asunto</span><input class="dt-input" name="asunto" required maxlength="150" value="{{ old('asunto') }}" placeholder="Ej.: Mi tarjeta no abre el perfil"></div>
            <div><span class="dt-label">Cuéntanos</span><textarea class="dt-input" name="mensaje" rows="4" required maxlength="3000">{{ old('mensaje') }}</textarea></div>
            <div><button class="dt-btn pri" type="submit" x-text="tipo === 'chat' ? 'Hablar con un asesor' : 'Enviar mensaje'">Hablar con un asesor</button></div>
        </form>
    </section>

    @if ($conversaciones->isNotEmpty())
        <section class="dt-panel">
            <div class="dt-panel-h"><h2>Tus conversaciones</h2></div>
            @foreach ($conversaciones as $c)
                <a class="ad-fila ad-link" href="/dashboard/ayuda/{{ $c->id }}">
                    <span><b>{{ $c->asunto }}</b><small>{{ $c->tipo === 'chat' ? 'Chat' : 'Mensaje' }} · {{ $c->created_at->format('d/m/Y') }}</small></span>
                    <span>{{ ['en_cola' => 'En la fila', 'abierta' => 'Abierta', 'cerrada' => 'Cerrada'][$c->estado] ?? $c->estado }}</span>
                    <span style="text-align:right"><small>{{ $c->ultimo_mensaje_en?->diffForHumans() }}</small></span>
                </a>
            @endforeach
        </section>
    @endif

    <style>
        .ay-buscar { display:grid; gap:14px }
        .ay-caja { display:flex; align-items:center; gap:10px; border:1px solid var(--border); border-radius:14px; padding:0 14px; background:var(--surface) }
        .ay-caja svg { width:20px; height:20px; opacity:.6; flex:none }
        .ay-caja input { flex:1; border:0; outline:0; background:transparent; color:inherit; font:inherit; font-size:15px; padding:13px 0 }
        .ay-temas { display:flex; flex-wrap:wrap; gap:8px }
        .ay-temas button { display:inline-flex; align-items:center; gap:6px; border:1px solid var(--border); background:transparent; color:inherit; border-radius:999px; padding:7px 12px; font:inherit; font-size:13px; font-weight:600; cursor:pointer }
        .ay-temas button svg { width:15px; height:15px }
        .ay-temas button.on { background:var(--primary); border-color:var(--primary); color:#fff }
        .ay-titulo { display:flex; align-items:center; gap:10px }
        .ay-icono { width:32px; height:32px; border-radius:10px; display:grid; place-items:center; background:var(--primary-soft); color:var(--primary) }
        .ay-icono svg { width:18px; height:18px }
        .ay-preg { border-top:1px solid var(--border) }
        .ay-p { width:100%; display:flex; justify-content:space-between; align-items:center; gap:12px; padding:14px 2px; background:none; border:0; color:inherit; font:inherit; font-size:15px; font-weight:600; text-align:left; cursor:pointer }
        .ay-p svg { width:18px; height:18px; flex:none; transition:transform .2s; opacity:.6 }
        .ay-preg.abierta .ay-p svg { transform:rotate(180deg) }
        .ay-r { padding:0 2px 16px; line-height:1.6; font-size:14.5px }
        .ay-r p { margin:0 0 10px } .ay-r ol, .ay-r ul { margin:0 0 10px; padding-left:22px } .ay-r ol { list-style:decimal } .ay-r ul { list-style:disc } .ay-r li { margin:4px 0 }
        .ay-img { margin:12px 0 0; max-width:420px; border-radius:14px; overflow:hidden; border:1px solid var(--border) }
        .ay-img svg { display:block; width:100%; height:auto }
        .ay-asesor { border:1px solid rgba(59,130,246,.35); margin-top:18px }
    </style>
@endsection
