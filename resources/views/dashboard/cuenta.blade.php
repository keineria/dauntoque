@extends('layouts.app')

@section('contenido')
    <div class="dt-head">
        <div>
            <h1 class="dt-h1">Mi cuenta</h1>
            <p class="dt-sub">{{ $usuario->email }} · apariencia, contraseña y verificación en dos pasos.</p>
        </div>
    </div>

    @if ($usuario->debe_cambiar_clave)
        <p class="dt-panel" style="color:var(--warn-text);margin:0 0 18px">Entraste con una contraseña temporal. Escribe una nueva para seguir usando el panel.</p>
    @endif

    {{-- Apariencia: se aplica al tocar y se guarda en la cuenta (sirve en cualquier celular o computador) --}}
    <section class="dt-panel" id="apariencia" x-data="aparienciaPanel(@js($apariencia))">
        <div class="dt-panel-h">
            <h2>Apariencia</h2>
            <div class="dt-seg" role="radiogroup" aria-label="Modo claro u oscuro">
                @foreach (config('apariencia.modos') as $clave => $nombre)
                    <button type="button" role="radio" :aria-checked="modo === '{{ $clave }}'" :class="modo === '{{ $clave }}' ? 'on' : ''" @click="elegir(paleta, '{{ $clave }}')">{{ $nombre }}</button>
                @endforeach
            </div>
        </div>
        <div class="ap-grid" role="radiogroup" aria-label="Paleta de colores">
            @foreach (\App\Support\Apariencia::paletas() as $clave => $p)
                <button type="button" class="ap-op" role="radio" :aria-checked="paleta === '{{ $clave }}'" :class="paleta === '{{ $clave }}' ? 'on' : ''" @click="elegir('{{ $clave }}', modo)">
                    <span class="ap-mini" style="{{ \App\Support\Apariencia::variablesMiniatura($p) }}" aria-hidden="true">
                        <span class="ap-puntos"><i></i><i></i><i></i></span>
                        <span class="ap-ventana"><i class="ap-linea"></i><i class="ap-bloque"></i><i class="ap-caja"></i></span>
                        <span class="ap-check" x-show="paleta === '{{ $clave }}'">✓</span>
                    </span>
                    <b>{{ $p['nombre'] }}</b>
                    <span class="dt-sub" style="margin:0;font-size:12.5px">{{ $p['descripcion'] }}</span>
                </button>
            @endforeach
        </div>
        <p class="dt-sub" style="margin:14px 0 0;font-size:12.5px" x-text="estado || 'Automático sigue el modo claro u oscuro de tu celular o computador. Solo cambia tu panel: tu perfil público conserva su diseño.'" role="status"></p>
    </section>

    <section class="dt-panel" id="clave">
        <div class="dt-panel-h"><h2>Cambiar contraseña</h2></div>
        <form method="POST" action="/dashboard/cuenta/clave" style="display:grid;gap:12px;max-width:420px">
            @csrf
            @if ($errors->clave->any())<p style="color:var(--danger-text);margin:0">{{ $errors->clave->first() }}</p>@endif
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
        @if ($errors->dosPasos->any())<p style="color:var(--danger-text);margin:0 0 12px">{{ $errors->dosPasos->first() }}</p>@endif

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

    <style>
        .ap-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(190px, 1fr)); gap:18px; }
        .ap-op { display:flex; flex-direction:column; align-items:flex-start; gap:2px; padding:0; border:0; background:none; color:var(--text); text-align:left; cursor:pointer; font:inherit; }
        .ap-op b { margin-top:8px; font-size:15px; }
        /* Miniatura: toma los colores claros u oscuros de su paleta según el modo que se ve ahora */
        .ap-mini { --f:var(--m-fondo-claro); --t:var(--m-tarjeta-claro); --s:var(--m-superficie-claro); --i:var(--m-tinta-claro); --a:var(--m-acento-claro);
                   position:relative; display:flex; gap:12px; width:100%; aspect-ratio:16/9; padding:12px; border-radius:18px; background:var(--f); border:1px solid var(--border); transition:border-color .15s, box-shadow .15s, transform .15s; }
        html[data-esquema="oscuro"] .ap-mini { --f:var(--m-fondo-oscuro); --t:var(--m-tarjeta-oscuro); --s:var(--m-superficie-oscuro); --i:var(--m-tinta-oscuro); --a:var(--m-acento-oscuro); }
        .ap-op:hover .ap-mini { transform:translateY(-2px); border-color:var(--primary-line); }
        .ap-op.on .ap-mini { border-color:var(--text); box-shadow:0 0 0 2px var(--text); }
        .ap-op:focus-visible { outline:none; }
        .ap-op:focus-visible .ap-mini { box-shadow:0 0 0 3px var(--primary-line); }
        .ap-puntos { display:flex; flex-direction:column; gap:6px; padding-top:4px; }
        .ap-puntos i { width:12px; height:12px; border-radius:50%; background:var(--i); }
        .ap-puntos i:nth-child(2) { background:var(--a); opacity:.55; }
        .ap-puntos i:nth-child(3) { background:var(--a); opacity:.25; }
        .ap-ventana { flex:1; display:grid; grid-template-columns:1fr 1fr; grid-template-rows:8px 1fr; gap:8px; padding:10px; border-radius:12px; background:var(--t); }
        .ap-linea { grid-column:1 / -1; width:60%; border-radius:9px; background:var(--i); }
        .ap-bloque { border-radius:8px; background:var(--i); }
        .ap-caja { border-radius:8px; background:var(--s); }
        .ap-check { position:absolute; right:10px; bottom:10px; width:24px; height:24px; border-radius:50%; display:grid; place-items:center; font-size:12px; font-weight:800; background:var(--text); color:var(--bg); }
        @media (max-width: 520px) { .ap-grid { grid-template-columns:1fr 1fr; gap:12px; } .ap-mini { padding:8px; gap:8px; } }
    </style>
    <script>
        function aparienciaPanel(inicial) {
            return {
                paleta: inicial.paleta, modo: inicial.modo, estado: '',
                async elegir(paleta, modo) {
                    const antes = { paleta: this.paleta, modo: this.modo };
                    this.paleta = paleta; this.modo = modo;
                    window.dtApariencia(paleta, modo);
                    this.estado = 'Guardando…';
                    try {
                        const r = await fetch('/dashboard/cuenta/apariencia', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                            body: JSON.stringify({ paleta, modo }),
                        });
                        if (!r.ok) throw new Error();
                        this.estado = 'Listo: así se verá tu panel en todos tus dispositivos.';
                    } catch (e) {
                        this.paleta = antes.paleta; this.modo = antes.modo;
                        window.dtApariencia(antes.paleta, antes.modo);
                        this.estado = 'No se pudo guardar. Revisa tu conexión e inténtalo de nuevo.';
                    }
                },
            };
        }
    </script>
@endsection
