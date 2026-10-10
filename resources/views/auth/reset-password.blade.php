<x-guest-layout>
    <x-slot name="titulo">Nueva contraseña</x-slot>

    <h2>Crea una nueva contraseña</h2>
    <p class="dt-sub">Elige una contraseña segura para tu cuenta.</p>

    <form method="POST" action="{{ route('password.store') }}" x-data="{ ver: false, enviando: false }" @submit="enviando = true">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="ac-campo">
            <label class="dt-label" for="email">Correo electrónico</label>
            <input id="email" class="dt-input" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username">
            @foreach ($errors->get('email') as $mensaje)<p class="ac-error">{{ $mensaje }}</p>@endforeach
        </div>

        <div class="ac-campo">
            <label class="dt-label" for="password">Nueva contraseña</label>
            <div class="dt-inline">
                <input id="password" :type="ver ? 'text' : 'password'" type="password" name="password" required autocomplete="new-password" placeholder="Mínimo 8 caracteres">
                <button type="button" class="ac-ojo" @click="ver = !ver" :aria-label="ver ? 'Ocultar contraseñas' : 'Mostrar contraseñas'">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
            </div>
            @foreach ($errors->get('password') as $mensaje)<p class="ac-error">{{ $mensaje }}</p>@endforeach
        </div>

        <div class="ac-campo">
            <label class="dt-label" for="password_confirmation">Repite la contraseña</label>
            <input id="password_confirmation" class="dt-input" :type="ver ? 'text' : 'password'" type="password" name="password_confirmation" required autocomplete="new-password">
            @foreach ($errors->get('password_confirmation') as $mensaje)<p class="ac-error">{{ $mensaje }}</p>@endforeach
        </div>

        <button type="submit" class="dt-btn pri ac-enviar" :disabled="enviando">
            <span x-show="!enviando">Guardar contraseña</span><span x-show="enviando" x-cloak>Guardando…</span>
        </button>
    </form>
</x-guest-layout>
