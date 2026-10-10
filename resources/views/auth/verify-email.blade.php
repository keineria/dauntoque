<x-guest-layout>
    <x-slot name="titulo">Verifica tu correo</x-slot>

    <h2>Verifica tu correo</h2>
    <p class="dt-sub">¡Gracias por registrarte! Abre el enlace que te enviamos por correo para activar tu cuenta. Si no te llegó, te enviamos otro.</p>

    @if (session('status') == 'verification-link-sent')
        <p class="ac-aviso" role="status">Te enviamos un nuevo enlace de verificación al correo con el que te registraste.</p>
    @endif

    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit" class="dt-btn pri ac-enviar">Reenviar el correo de verificación</button>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="ac-pie">
        @csrf
        <button type="submit" class="ac-link" style="border:0;background:none;cursor:pointer;font-size:13.5px">Cerrar sesión</button>
    </form>
</x-guest-layout>
