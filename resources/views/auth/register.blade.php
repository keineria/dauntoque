<x-guest-layout>
    <x-slot name="titulo">Crear cuenta</x-slot>

    <h2>Crea tu cuenta</h2>
    <p class="dt-sub">En pocos minutos tendrás tu tarjeta digital lista para compartir.</p>

    {{-- Mensaje del flujo NFC ("¡Paso 1! Crea tu cuenta gratuita para vincular tu nueva tarjeta NFC.") --}}
    @if (session('status'))
        <p class="ac-aviso" role="status">{{ session('status') }}</p>
    @endif

    <form method="POST" action="{{ route('register') }}" x-data="{ ver: false, enviando: false }" @submit="enviando = true">
        @csrf

        <div class="ac-campo">
            <label class="dt-label" for="name">Nombre</label>
            <input id="name" class="dt-input" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="Tu nombre completo">
            @foreach ($errors->get('name') as $mensaje)<p class="ac-error">{{ $mensaje }}</p>@endforeach
        </div>

        <div class="ac-campo">
            <label class="dt-label" for="email">Correo electrónico</label>
            <input id="email" class="dt-input" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" placeholder="tu@correo.com">
            @foreach ($errors->get('email') as $mensaje)<p class="ac-error">{{ $mensaje }}</p>@endforeach
        </div>

        <div class="ac-campo">
            <label class="dt-label" for="password">Contraseña</label>
            <div class="dt-inline">
                <input id="password" :type="ver ? 'text' : 'password'" type="password" name="password" required autocomplete="new-password" placeholder="Mínimo 8 caracteres">
                <button type="button" class="ac-ojo" @click="ver = !ver" :aria-label="ver ? 'Ocultar contraseñas' : 'Mostrar contraseñas'">
                    <svg x-show="!ver" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                    <svg x-show="ver" x-cloak width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3l18 18M10.6 10.6a3 3 0 0 0 4.2 4.2M9.9 5.1A10.4 10.4 0 0 1 12 5c6 0 10 7 10 7a17.6 17.6 0 0 1-3.2 4.1M6.1 6.1C3.6 7.8 2 12 2 12s4 7 10 7a10 10 0 0 0 5-1.3"/></svg>
                </button>
            </div>
            @foreach ($errors->get('password') as $mensaje)<p class="ac-error">{{ $mensaje }}</p>@endforeach
        </div>

        <div class="ac-campo">
            <label class="dt-label" for="password_confirmation">Repite la contraseña</label>
            <input id="password_confirmation" class="dt-input" :type="ver ? 'text' : 'password'" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Escríbela de nuevo">
            @foreach ($errors->get('password_confirmation') as $mensaje)<p class="ac-error">{{ $mensaje }}</p>@endforeach
        </div>

        <button type="submit" class="dt-btn pri ac-enviar" :disabled="enviando">
            <span x-show="!enviando">Crear mi cuenta</span><span x-show="enviando" x-cloak>Creando cuenta…</span>
        </button>
    </form>

    <p class="ac-pie">¿Ya tienes cuenta? <a href="{{ route('login') }}">Entra aquí</a></p>
</x-guest-layout>
