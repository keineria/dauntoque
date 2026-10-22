@extends('layouts.app')

@section('contenido')
    <div class="dt-head">
        <div>
            <h1 class="dt-h1">Mi cuenta</h1>
            <p class="dt-sub">{{ $usuario->email }} · contraseña y verificación en dos pasos.</p>
        </div>
    </div>

    @if ($usuario->debe_cambiar_clave)
        <p class="dt-panel" style="color:#fbbf24;margin:0 0 18px">Entraste con una contraseña temporal. Escribe una nueva para seguir usando el panel.</p>
    @endif

    <section class="dt-panel" id="clave">
        <div class="dt-panel-h"><h2>Cambiar contraseña</h2></div>
        <form method="POST" action="/dashboard/cuenta/clave" style="display:grid;gap:12px;max-width:420px">
            @csrf
            @if ($errors->clave->any())<p style="color:#f87171;margin:0">{{ $errors->clave->first() }}</p>@endif
            <div><span class="dt-label">Contraseña actual</span><input class="dt-input" type="password" name="actual" required autocomplete="current-password"></div>
            <div><span class="dt-label">Nueva contraseña</span><input class="dt-input" type="password" name="nueva" required minlength="10" autocomplete="new-password"></div>
            <div><span class="dt-label">Repite la nueva contraseña</span><input class="dt-input" type="password" name="nueva_confirmation" required minlength="10" autocomplete="new-password"></div>
            <p class="dt-sub" style="margin:0;font-size:12.5px">Mínimo 10 caracteres, con letras y números.</p>
            <div><button class="dt-btn pri" type="submit">Guardar contraseña</button></div>
        </form>
    </section>

    <section class="dt-panel" id="dos-pasos">
        <div class="dt-panel-h">
            <h2>Verificación en dos pasos</h2>
            <span class="dt-chip {{ $usuario->tieneDosPasos() ? 'up' : '' }}">{{ $usuario->tieneDosPasos() ? 'Activa' : 'Apagada' }}</span>
        </div>
        @if ($errors->dosPasos->any())<p style="color:#f87171;margin:0 0 12px">{{ $errors->dosPasos->first() }}</p>@endif

        @if ($codigos)
            <div class="sp-aviso" style="margin-bottom:16px">
                <b>Guarda estos códigos de respaldo</b>
                <span>Te sirven para entrar si pierdes el celular. Cada uno se usa una sola vez y no se vuelven a mostrar.</span>
                <div style="display:grid;grid-template-columns:repeat(2, max-content);gap:6px 24px;margin-top:10px;font:600 15px ui-monospace, SFMono-Regular, Menlo, monospace">
                    @foreach ($codigos as $codigo)<span>{{ $codigo }}</span>@endforeach
                </div>
            </div>
        @endif

        @if ($usuario->tieneDosPasos())
            <p class="dt-sub" style="margin:0 0 14px">Al entrar te pedimos el código de 6 dígitos de tu app autenticadora. Te quedan {{ $respaldoRestante }} códigos de respaldo.</p>
            <form method="POST" style="display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;max-width:560px">
                @csrf
                <div style="flex:1;min-width:200px"><span class="dt-label">Tu contraseña, para confirmar</span><input class="dt-input" type="password" name="clave" required autocomplete="current-password"></div>
                <button class="dt-btn" type="submit" formaction="/dashboard/cuenta/dos-pasos/codigos" style="height:44px">Nuevos códigos de respaldo</button>
                <button class="dt-btn danger" type="submit" formaction="/dashboard/cuenta/dos-pasos/desactivar" style="height:44px">Desactivar</button>
            </form>
        @elseif ($secreto)
            <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcode-generator/1.4.4/qrcode.min.js" integrity="sha512-ZDSPMa/JM1D+7kdg2x3BsruQ6T/JpJo3jWDWkCZsP+5yVyp1KfESqLI+7RqB5k24F7p2cV7i2YHh/890y6P6Sw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
            <div style="display:flex;flex-wrap:wrap;gap:22px;align-items:flex-start">
                <div x-data x-init="const qr = qrcode(0, 'M'); qr.addData(@js($enlaceApp)); qr.make(); $el.innerHTML = qr.createSvgTag({ cellSize: 5, margin: 16, scalable: true })"
                     style="width:200px;aspect-ratio:1;background:#fff;border-radius:14px;overflow:hidden" role="img" aria-label="Código QR para la app autenticadora"></div>
                <div style="flex:1;min-width:240px">
                    <ol style="margin:0 0 14px;padding-left:18px;line-height:1.7;font-size:14px">
                        <li>Abre Google Authenticator, Microsoft Authenticator o Authy en tu celular.</li>
                        <li>Toca <b>+</b> y escanea este código QR.</li>
                        <li>Escribe aquí el código de 6 dígitos que aparece.</li>
                    </ol>
                    <p class="dt-sub" style="font-size:12.5px">¿No puedes escanear? Escribe esta clave en la app: <b style="font-family:ui-monospace,monospace;color:var(--text);word-break:break-all">{{ trim(chunk_split($secreto, 4, ' ')) }}</b></p>
                    <form method="POST" action="/dashboard/cuenta/dos-pasos/confirmar" style="display:flex;gap:10px;align-items:flex-end;margin-top:12px">
                        @csrf
                        <div><span class="dt-label">Código de la app</span><input class="dt-input" name="codigo" required inputmode="numeric" autocomplete="one-time-code" maxlength="7" style="max-width:150px;letter-spacing:.2em"></div>
                        <button class="dt-btn pri" type="submit" style="height:44px">Activar</button>
                    </form>
                </div>
            </div>
        @else
            <p class="dt-sub" style="margin:0 0 14px">Opcional. Además de tu contraseña, al entrar te pediremos un código que cambia cada 30 segundos en tu celular. Así nadie entra a tu cuenta aunque sepa tu contraseña.</p>
            <form method="POST" action="/dashboard/cuenta/dos-pasos">@csrf<button class="dt-btn pri" type="submit">Configurar verificación en dos pasos</button></form>
        @endif
    </section>

    <a class="dt-panel ad-link" href="/dashboard/ayuda" style="display:flex;justify-content:space-between;align-items:center;gap:12px;text-decoration:none;color:inherit">
        <span><b>Ayuda y preguntas frecuentes</b><br><span class="dt-sub">Respuestas paso a paso y, si las necesitas, un asesor.</span></span>
        <span class="dt-btn">Abrir</span>
    </a>
@endsection
