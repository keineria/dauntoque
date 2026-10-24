@extends('layouts.app')

@section('contenido')
    <div class="dt-head">
        <div>
            <h1 class="dt-h1">Mi cuenta</h1>
            <p class="dt-sub">{{ $usuario->email }} · apariencia, contraseña y verificación en dos pasos.</p>
        </div>
    </div>

    @if ($usuario->debe_cambiar_clave)
        <p class="dt-panel" style="color:var(--warn-t);margin:0 0 18px">Entraste con una contraseña temporal. Escribe una nueva para seguir usando el panel.</p>
    @endif

    {{-- Apariencia del panel: modo (automático, claro u oscuro) y paleta de colores. Cambia al instante y se guarda en la cuenta;
         sin JavaScript, cada botón envía su formulario. --}}
    <section class="dt-panel" id="apariencia"
             x-data="temaPanel({{ Js::from(['paleta' => $paleta, 'apariencia' => $apariencia]) }})">
        <div class="dt-panel-h">
            <div>
                <h2>Apariencia</h2>
                <p class="dt-sub" style="margin:2px 0 0">Colores de tu panel. Tu perfil público no cambia.</p>
            </div>
            <form method="POST" action="/dashboard/cuenta/apariencia" class="dt-seg tm-modos" role="group" aria-label="Modo de color">
                @csrf
                <input type="hidden" name="paleta" :value="paleta" value="{{ $paleta }}">
                @foreach (\App\Support\TemaPanel::APARIENCIAS as $id => $nombre)
                    <button type="submit" name="apariencia" value="{{ $id }}" class="{{ $apariencia === $id ? 'on' : '' }}"
                            :class="apariencia === '{{ $id }}' && 'on'" :aria-pressed="apariencia === '{{ $id }}'"
                            aria-pressed="{{ $apariencia === $id ? 'true' : 'false' }}"
                            @click.prevent="elegir(paleta, '{{ $id }}')">{{ $nombre }}</button>
                @endforeach
            </form>
        </div>

        <form method="POST" action="/dashboard/cuenta/apariencia" class="tm-grid" role="group" aria-label="Paleta de colores">
            @csrf
            <input type="hidden" name="apariencia" :value="apariencia" value="{{ $apariencia }}">
            @foreach (\App\Support\TemaPanel::PALETAS as $id => $p)
                <button type="submit" name="paleta" value="{{ $id }}" class="tm-op {{ $paleta === $id ? 'on' : '' }}"
                        :class="paleta === '{{ $id }}' && 'on'" :aria-pressed="paleta === '{{ $id }}'"
                        aria-pressed="{{ $paleta === $id ? 'true' : 'false' }}"
                        @click.prevent="elegir('{{ $id }}', apariencia)">
                    <span class="tm-prev" data-paleta="{{ $id }}" aria-hidden="true">
                        <span class="tm-puntos">@foreach ($p['puntos'] as $c)<i style="background:{{ $c }}"></i>@endforeach</span>
                        <span class="tm-hoja"><i class="tm-linea"></i><i class="tm-bloque"></i><i class="tm-suave"></i></span>
                        <span class="tm-ok"><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12l5 5L20 7"/></svg></span>
                    </span>
                    <b>{{ $p['nombre'] }}</b>
                    <small>{{ $p['desc'] }}</small>
                </button>
            @endforeach
        </form>
        <p class="dt-sub" x-show="error" x-text="error" x-cloak role="alert" style="color:var(--bad-t);margin:12px 0 0"></p>
        <p class="tm-estado" aria-live="polite" x-text="estado"></p>
    </section>
    <style>
        .tm-modos button { padding:7px 14px; }
        .tm-grid { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:18px 14px; }
        @media (min-width: 700px) { .tm-grid { grid-template-columns:repeat(auto-fill, minmax(190px, 1fr)); } }
        .tm-op { display:flex; flex-direction:column; align-items:flex-start; gap:2px; min-width:0; padding:0; border:0; background:none; color:var(--text); text-align:left; cursor:pointer; font:inherit; }
        .tm-op b { margin-top:8px; font-size:14px; font-weight:800; }
        .tm-op small { color:var(--muted); font-size:12.5px; }
        .tm-op:focus-visible { outline:none; }
        .tm-op:focus-visible .tm-prev { box-shadow:var(--ring); }
        /* Miniatura: muestra la paleta en el modo que está activo (claro u oscuro) */
        .tm-prev { --pv-bg:var(--p-paper); --pv-hoja:#fff; --pv-tinta:var(--p-ink); --pv-suave:var(--p-paper);
                   position:relative; display:flex; gap:10%; width:100%; aspect-ratio:16/9; padding:9% 7%; border-radius:18px; background:var(--pv-bg);
                   border:2px solid color-mix(in srgb, var(--pv-tinta) 8%, transparent); transition:border-color .15s, transform .15s; }
        [data-modo="oscuro"] .tm-prev { --pv-bg:color-mix(in srgb, var(--p-ink) 70%, #000); --pv-hoja:color-mix(in srgb, var(--p-ink) 94%, #fff); --pv-tinta:var(--p-paper); --pv-suave:color-mix(in srgb, var(--p-paper) 10%, transparent); }
        .tm-prev[data-paleta="dauntoque"] { --pv-tinta:#2563eb; --pv-suave:#eef2ff; --pv-bg:#f5f7fb; }
        [data-modo="oscuro"] .tm-prev[data-paleta="dauntoque"] { --pv-bg:#050507; --pv-hoja:#16161d; --pv-tinta:#3b82f6; --pv-suave:rgba(255,255,255,.06); }
        .tm-op:hover .tm-prev { transform:translateY(-2px); }
        .tm-op.on .tm-prev { border-color:var(--text); }
        .tm-puntos { display:flex; flex-direction:column; gap:8px; padding-top:2px; }
        .tm-puntos i { width:12px; height:12px; border-radius:50%; }
        .tm-hoja { flex:1; display:grid; grid-template-columns:1fr 1fr; grid-template-rows:auto 1fr; gap:8px; padding:9%; border-radius:12px; background:var(--pv-hoja); }
        .tm-linea { grid-column:1 / -1; height:6px; width:62%; border-radius:9px; background:var(--pv-tinta); }
        .tm-bloque { border-radius:7px; background:var(--pv-tinta); }
        .tm-suave { border-radius:7px; background:var(--pv-suave); }
        .tm-ok { position:absolute; right:8px; bottom:8px; width:26px; height:26px; border-radius:50%; display:none; place-items:center; background:var(--text); color:var(--bg); }
        .tm-op.on .tm-ok { display:grid; }
        .tm-estado { position:absolute; width:1px; height:1px; overflow:hidden; clip:rect(0 0 0 0); }
    </style>
    <script>
        function temaPanel(inicial) {
            return {
                paleta: inicial.paleta, apariencia: inicial.apariencia, error: '', estado: '', ultimo: inicial,
                async elegir(paleta, apariencia) {
                    if (paleta === this.paleta && apariencia === this.apariencia) return;
                    this.error = '';
                    this.paleta = paleta; this.apariencia = apariencia;
                    window.dtTema(paleta, apariencia);
                    try {
                        const r = await fetch('/dashboard/cuenta/apariencia', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                            body: JSON.stringify({ paleta, apariencia }),
                        });
                        if (!r.ok) throw new Error();
                        this.ultimo = { paleta, apariencia };
                        this.estado = 'Apariencia guardada.';
                    } catch (e) {
                        // Vuelve a lo último guardado para que lo que se ve coincida con la cuenta
                        this.paleta = this.ultimo.paleta; this.apariencia = this.ultimo.apariencia;
                        window.dtTema(this.paleta, this.apariencia);
                        this.error = 'No pudimos guardar la apariencia. Revisa tu conexión e inténtalo de nuevo.';
                    }
                },
            };
        }
    </script>

    <section class="dt-panel" id="clave">
        <div class="dt-panel-h"><h2>Cambiar contraseña</h2></div>
        <form method="POST" action="/dashboard/cuenta/clave" style="display:grid;gap:12px;max-width:420px">
            @csrf
            @if ($errors->clave->any())<p style="color:var(--bad-t);margin:0">{{ $errors->clave->first() }}</p>@endif
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
        @if ($errors->dosPasos->any())<p style="color:var(--bad-t);margin:0 0 12px">{{ $errors->dosPasos->first() }}</p>@endif

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
