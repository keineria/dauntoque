<x-guest-layout>
    <x-slot name="titulo">Verificación en dos pasos</x-slot>

    <h2>Verificación en dos pasos</h2>
    <p class="dt-sub">Escribe el código de 6 dígitos que muestra tu app autenticadora. Si no tienes el celular, usa uno de tus códigos de respaldo.</p>

    <form method="POST" action="/dos-pasos" x-data="{ enviando: false }" @submit="enviando = true" style="margin-top:18px">
        @csrf
        <div class="ac-campo">
            <label class="dt-label" for="codigo">Código</label>
            <input id="codigo" class="dt-input" name="codigo" required autofocus autocomplete="one-time-code" maxlength="20" placeholder="123456" style="letter-spacing:.2em">
            @foreach ($errors->get('codigo') as $mensaje)<p class="ac-error">{{ $mensaje }}</p>@endforeach
        </div>
        <button type="submit" class="dt-btn pri ac-enviar" :disabled="enviando">
            <span x-show="!enviando">Entrar</span><span x-show="enviando" x-cloak>Verificando…</span>
        </button>
    </form>

    <p class="ac-pie"><a href="{{ route('login') }}">Volver al inicio de sesión</a></p>
</x-guest-layout>
