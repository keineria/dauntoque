<x-guest-layout>
    <x-slot name="titulo">Recuperar contraseña</x-slot>

    <h2>¿Olvidaste tu contraseña?</h2>
    <p class="dt-sub">Escribe tu correo y te enviaremos un enlace para crear una nueva.</p>

    @if (session('status'))
        <p class="ac-aviso" role="status">{{ session('status') }}</p>
    @endif

    <form method="POST" action="{{ route('password.email') }}" x-data="{ enviando: false }" @submit="enviando = true">
        @csrf
        <div class="ac-campo">
            <label class="dt-label" for="email">Correo electrónico</label>
            <input id="email" class="dt-input" type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="tu@correo.com">
            @foreach ($errors->get('email') as $mensaje)<p class="ac-error">{{ $mensaje }}</p>@endforeach
        </div>
        <button type="submit" class="dt-btn pri ac-enviar" :disabled="enviando">
            <span x-show="!enviando">Enviarme el enlace</span><span x-show="enviando" x-cloak>Enviando…</span>
        </button>
    </form>

    <p class="ac-pie"><a href="{{ route('login') }}">Volver a entrar</a></p>
</x-guest-layout>
