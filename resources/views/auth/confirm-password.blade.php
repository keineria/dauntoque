<x-guest-layout>
    <x-slot name="titulo">Confirmar contraseña</x-slot>

    <h2>Confirma tu contraseña</h2>
    <p class="dt-sub">Esta es una zona protegida. Escribe tu contraseña para continuar.</p>

    <form method="POST" action="{{ route('password.confirm') }}" x-data="{ enviando: false }" @submit="enviando = true">
        @csrf
        <div class="ac-campo">
            <label class="dt-label" for="password">Contraseña</label>
            <input id="password" class="dt-input" type="password" name="password" required autocomplete="current-password">
            @foreach ($errors->get('password') as $mensaje)<p class="ac-error">{{ $mensaje }}</p>@endforeach
        </div>
        <button type="submit" class="dt-btn pri ac-enviar" :disabled="enviando">Confirmar</button>
    </form>
</x-guest-layout>
