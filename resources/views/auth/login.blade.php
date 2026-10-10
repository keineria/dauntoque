<x-guest-layout>
    <x-slot name="titulo">Entrar</x-slot>

    <h2>Bienvenido de nuevo</h2>
    <p class="dt-sub">Entra para gestionar tus perfiles y tu tarjeta NFC.</p>

    @if (session('status'))
        <p class="ac-aviso" role="status">{{ session('status') }}</p>
    @endif

    <form method="POST" action="{{ route('login') }}" x-data="{ ver: false, enviando: false }" @submit="enviando = true">
        @csrf

        <div class="ac-campo">
            <label class="dt-label" for="email">Correo electrónico</label>
            <input id="email" class="dt-input" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="tu@correo.com">
            @foreach ($errors->get('email') as $mensaje)<p class="ac-error">{{ $mensaje }}</p>@endforeach
        </div>

        <div class="ac-campo">
            <div style="display:flex;justify-content:space-between;align-items:baseline">
                <label class="dt-label" for="password">Contraseña</label>
                @if (Route::has('password.request'))
                    <a class="ac-link" style="font-size:12.5px" href="{{ route('password.request') }}">¿La olvidaste?</a>
                @endif
            </div>
            <div class="dt-inline">
                <input id="password" :type="ver ? 'text' : 'password'" type="password" name="password" required autocomplete="current-password" placeholder="••••••••">
                <button type="button" class="ac-ojo" @click="ver = !ver" :aria-label="ver ? 'Ocultar contraseña' : 'Mostrar contraseña'">
                    <svg x-show="!ver" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                    <svg x-show="ver" x-cloak width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3l18 18M10.6 10.6a3 3 0 0 0 4.2 4.2M9.9 5.1A10.4 10.4 0 0 1 12 5c6 0 10 7 10 7a17.6 17.6 0 0 1-3.2 4.1M6.1 6.1C3.6 7.8 2 12 2 12s4 7 10 7a10 10 0 0 0 5-1.3"/></svg>
                </button>
            </div>
            @foreach ($errors->get('password') as $mensaje)<p class="ac-error">{{ $mensaje }}</p>@endforeach
        </div>

        <label class="ac-check" for="remember_me" style="margin:4px 0 18px">
            <input id="remember_me" type="checkbox" name="remember"> Mantener la sesión iniciada
        </label>

        <button type="submit" class="dt-btn pri ac-enviar" :disabled="enviando">
            <span x-show="!enviando">Entrar</span><span x-show="enviando" x-cloak>Entrando…</span>
        </button>
    </form>

    <p class="ac-pie">¿Aún no tienes cuenta? <a href="{{ route('register') }}">Crea la tuya gratis</a></p>
</x-guest-layout>
