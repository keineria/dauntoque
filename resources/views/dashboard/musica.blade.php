@extends('layouts.app')

@section('contenido')
    <style>
        .mu-sw { display:inline-flex; align-items:center; gap:8px; font-size:13px; font-weight:600; cursor:pointer; }
        .mu-sw input { width:38px; height:22px; appearance:none; border-radius:99px; background:rgba(255,255,255,.12); position:relative; cursor:pointer; transition:background .15s; flex:none; margin:0; }
        .mu-sw input::after { content:""; position:absolute; top:3px; left:3px; width:16px; height:16px; border-radius:50%; background:#fff; transition:transform .15s; }
        .mu-sw input:checked { background:#2563eb; }
        .mu-sw input:checked::after { transform:translateX(16px); }
        .mu-sw input:focus-visible { outline:none; box-shadow:0 0 0 3px rgba(59,130,246,.35); }
        .mu-player { width:100%; border:0; border-radius:12px; display:block; }
        @media (min-width: 900px) { .mu-grid { grid-template-columns:minmax(0, 1fr) 380px; } }
    </style>

    @if(!$app)
        <div class="dt-head">
            <div>
                <h1 class="dt-h1">Música</h1>
                <p class="dt-sub">Tu música de Spotify en tu tarjeta. Ideal para DJs, artistas y productores.</p>
            </div>
        </div>
        <section class="dt-panel" style="max-width:720px">
            <div style="display:grid;gap:12px;font-size:14.5px">
                <div>🎵 <b>Tu canción destacada.</b> El que toca tu tarjeta la escucha ahí mismo con el reproductor de Spotify.</div>
                <div>🎧 <b>Tu lista, álbum o perfil de artista.</b> Muestra tu playlist o tus sets completos debajo de la canción.</div>
                <div>🔗 <b>Solo pegas el enlace.</b> En Spotify toca Compartir › Copiar enlace y lo pegas aquí. No necesitas cuenta de desarrollador.</div>
                <div>🔇 <b>Suena cuando el visitante quiere.</b> Los celulares no dejan que un perfil suene solo; tu canción queda lista con un toque en play.</div>
            </div>
            <form method="POST" action="/dashboard/musica/activar" style="margin-top:22px">
                @csrf
                <button class="dt-btn pri" type="submit" style="height:46px;padding:0 22px">Activar gratis por un año</button>
            </form>
            <p class="dt-sub" style="margin-top:10px">Después del primer año, {{ $precio }} al año.</p>
        </section>
    @else
        <div class="dt-head">
            <div>
                <h1 class="dt-h1">Música</h1>
                <p class="dt-sub">
                    Tu canción y tu lista de Spotify salen en tu perfil, antes del pie de página.
                    @unless($app->vigente()) <b style="color:#f87171">Venció el {{ $app->vence_en->format('d/m/Y') }}.</b>@endunless
                </p>
            </div>
            @if($perfil)
                <a class="dt-btn" href="{{ $perfil }}" target="_blank" rel="noopener">Ver mi perfil</a>
            @endif
        </div>

        <div class="mu-grid" style="display:grid;gap:18px;align-items:start">
            <section class="dt-panel" style="margin:0">
                <div class="dt-panel-h"><h2>En tu perfil</h2></div>
                <form method="POST" action="/dashboard/musica/ajustes" style="display:grid;gap:16px">
                    @csrf
                    <label class="mu-sw"><input type="checkbox" name="mostrar" value="1" @checked(old('mostrar', $ajustes->mostrar))> Mostrar mi música en el perfil</label>
                    <div>
                        <label class="dt-label" for="titulo">Título de la sección (opcional)</label>
                        <input class="dt-input" id="titulo" name="titulo" maxlength="60" placeholder="Mi música" value="{{ old('titulo', $ajustes->titulo) }}">
                        @error('titulo')<p class="dt-sub" style="color:#f87171" role="alert">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="dt-label" for="cancion">Canción destacada</label>
                        <input class="dt-input" id="cancion" name="cancion" type="url" inputmode="url" placeholder="https://open.spotify.com/track/..." value="{{ old('cancion', \App\Services\Musica\Musica::enlace($ajustes->cancion)) }}" aria-describedby="cancion-ayuda">
                        <p class="dt-sub" id="cancion-ayuda">En Spotify, en la canción toca ⋯ › Compartir › Copiar enlace de la canción.</p>
                        @error('cancion')<p class="dt-sub" style="color:#f87171" role="alert">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="dt-label" for="lista">Lista, álbum o artista</label>
                        <input class="dt-input" id="lista" name="lista" type="url" inputmode="url" placeholder="https://open.spotify.com/playlist/..." value="{{ old('lista', \App\Services\Musica\Musica::enlace($ajustes->lista)) }}" aria-describedby="lista-ayuda">
                        <p class="dt-sub" id="lista-ayuda">Sirve una playlist pública, un álbum, tu perfil de artista o un pódcast. Deja vacío lo que no quieras mostrar.</p>
                        @error('lista')<p class="dt-sub" style="color:#f87171" role="alert">{{ $message }}</p>@enderror
                    </div>
                    <button class="dt-btn pri" type="submit" style="justify-self:start">Guardar</button>
                </form>
            </section>

            <section class="dt-panel" style="margin:0;display:grid;gap:12px">
                <div class="dt-panel-h"><h2>Así se escucha</h2></div>
                @if($ajustes->cancion || $ajustes->lista)
                    @if($ajustes->cancion)
                        <iframe class="mu-player" title="Canción destacada en Spotify" src="{{ \App\Services\Musica\Musica::reproductor($ajustes->cancion) }}" height="{{ config('musica.alto_cancion') }}" allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture" loading="lazy"></iframe>
                    @endif
                    @if($ajustes->lista)
                        <iframe class="mu-player" title="Lista en Spotify" src="{{ \App\Services\Musica\Musica::reproductor($ajustes->lista) }}" height="{{ config('musica.alto_lista') }}" allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture" loading="lazy"></iframe>
                    @endif
                    <p class="dt-sub" style="margin:0">Quien no tenga sesión en Spotify escucha una muestra de 30 segundos; con sesión, la canción completa.</p>
                    @unless($ajustes->mostrar)<p class="dt-sub" style="margin:0;color:#fbbf24">Está oculta: enciende "Mostrar mi música en el perfil" para que se vea.</p>@endunless
                @else
                    <p class="dt-sub" style="margin:0">Pega el enlace de una canción o de una lista para verla aquí.</p>
                @endif
            </section>
        </div>
    @endif
@endsection
