@extends('agenda._base')

@section('titulo', __('calificame.titulo', ['nombre' => $negocio['nombre']]))

@section('contenido')
    <style>
        .cf-estrellas { display:flex; justify-content:center; gap:6px; margin:18px 0 6px; }
        .cf-estrella { width:52px; height:52px; border:0; background:none; padding:0; cursor:pointer; color:rgba(255,255,255,.18); transition:transform .12s ease, color .12s ease; border-radius:12px; }
        .cf-estrella svg { width:100%; height:100%; }
        .cf-estrella.on { color:#fbbf24; }
        .cf-estrella:hover { transform:scale(1.08); }
        .cf-estrella:focus-visible { outline:none; box-shadow:0 0 0 3px color-mix(in srgb, var(--acc) 45%, transparent); }
        .cf-nivel { text-align:center; font-weight:700; min-height:22px; }
        .cf-gracias { text-align:center; padding:28px 18px; }
        .cf-gracias .ico { font-size:44px; }
        @media (prefers-reduced-motion: reduce) { .cf-estrella { transition:none; } }
    </style>

    @if($gracias)
        <div class="ag-card cf-gracias" role="status">
            <div class="ico" aria-hidden="true">{{ $gracias === 'baja' ? '🤝' : '⭐' }}</div>
            <h1 style="margin-top:8px">{{ $gracias === 'repetida' ? __('calificame.ya_calificaste') : __('calificame.gracias') }}</h1>
            @if($gracias !== 'repetida')
                <p class="ag-muted">{{ $gracias === 'buena' ? __('calificame.gracias_bueno', ['nombre' => $negocio['nombre']]) : __('calificame.gracias_malo', ['nombre' => $negocio['nombre']]) }}</p>
            @endif
            @if($google)
                <p style="margin:18px 0 0;font-weight:600">{{ __('calificame.google') }}</p>
                <a class="ag-btn" href="{{ $google }}" target="_blank" rel="noopener">{{ __('calificame.google_boton') }}</a>
            @endif
            <a class="ag-btn sec" href="{{ $urlPerfil }}">{{ __('calificame.volver') }}</a>
        </div>
    @elseif(!$activa)
        <h1>{{ __('calificame.titulo', ['nombre' => $negocio['nombre']]) }}</h1>
        <p class="ag-muted">{{ __('calificame.cerrada') }}</p>
        <a class="ag-btn sec" href="{{ $urlPerfil }}">{{ __('calificame.volver') }}</a>
    @else
        <h1>{{ __('calificame.titulo', ['nombre' => $negocio['nombre']]) }}</h1>
        <p class="ag-muted" style="margin:0">{{ __('calificame.subtitulo') }}</p>
        @if($cita)
            <p class="ag-muted" style="margin:6px 0 0">{{ __('calificame.cita', ['servicio' => $cita->servicio_nombre]) }}</p>
        @endif

        @if($errors->any())
            <div class="ag-aviso err" role="alert" style="margin-top:16px">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ $urlEnviar }}" x-data="{ estrellas: {{ (int) old('estrellas', 0) }}, sobre: 0, enviando: false, niveles: @js(__('calificame.niveles')) }" @submit="if (!estrellas) { $event.preventDefault(); return; } enviando = true">
            @csrf
            <input type="hidden" name="lang" value="{{ $idioma }}">
            <input type="hidden" name="estrellas" :value="estrellas">
            @if($cita)<input type="hidden" name="cita" value="{{ $cita->token }}">@endif
            <div class="ag-oculto" aria-hidden="true"><input type="text" name="sitio_web" tabindex="-1" autocomplete="off"></div>

            <div class="cf-estrellas" role="radiogroup" aria-label="{{ __('calificame.estrellas') }}" @mouseleave="sobre = 0">
                @foreach([1, 2, 3, 4, 5] as $n)
                    <button type="button" class="cf-estrella" role="radio" :aria-checked="estrellas === {{ $n }}" aria-label="{{ __('calificame.estrella', ['n' => $n]) }}"
                            :class="(sobre || estrellas) >= {{ $n }} ? 'on' : ''" @click="estrellas = {{ $n }}" @mouseenter="sobre = {{ $n }}">
                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2.5l2.9 6.1 6.6.8-4.9 4.6 1.3 6.6L12 17.3l-5.9 3.3 1.3-6.6L2.5 9.4l6.6-.8z"/></svg>
                    </button>
                @endforeach
            </div>
            <div class="cf-nivel" aria-live="polite" x-text="(sobre || estrellas) ? niveles[sobre || estrellas] : @js(__('calificame.estrellas'))"></div>

            <div x-show="estrellas" x-cloak>
                <label for="comentario" x-text="estrellas >= {{ config('calificame.estrellas_buenas') }} ? @js(__('calificame.comentario_bueno')) : @js(__('calificame.comentario_malo', ['nombre' => $negocio['nombre']]))">{{ __('calificame.comentario') }}</label>
                <textarea class="ag-in" id="comentario" name="comentario" rows="3" maxlength="600">{{ old('comentario') }}</textarea>
                @unless($cita)
                    <label for="nombre">{{ __('calificame.nombre') }}</label>
                    <input class="ag-in" id="nombre" name="nombre" value="{{ old('nombre') }}" maxlength="80" autocomplete="name">
                    <label for="contacto">{{ __('calificame.contacto') }}</label>
                    <input class="ag-in" id="contacto" name="contacto" value="{{ old('contacto') }}" maxlength="120" autocomplete="tel">
                    <p class="ag-muted" style="margin:6px 0 0">{{ __('calificame.contacto_ayuda', ['nombre' => $negocio['nombre']]) }}</p>
                @endunless
                <p class="ag-muted" style="margin:14px 0 0">{{ __('calificame.privado', ['nombre' => $negocio['nombre']]) }}</p>
            </div>

            <button class="ag-btn" type="submit" :disabled="!estrellas || enviando">{{ __('calificame.enviar') }}</button>
        </form>
    @endif
@endsection

@section('pie')
    <p class="ag-pie"><a href="{{ $urlPerfil }}">{{ __('calificame.volver') }}</a> · DaUnToque</p>
@endsection
