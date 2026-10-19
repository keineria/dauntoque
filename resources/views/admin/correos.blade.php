@extends('layouts.app')

@section('contenido')
    <div class="dt-head">
        <div>
            <h1 class="dt-h1">Correos</h1>
            <p class="dt-sub">El servidor que envía los correos, quién los firma, a dónde llegan los avisos y el diseño de cada plantilla.</p>
        </div>
    </div>
    @include('admin._tabs')

    @if ($errors->any())<p class="dt-panel" style="color:#f87171;margin:0 0 18px">{{ $errors->first() }}</p>@endif

    <section class="dt-panel" id="servidor">
        <div class="dt-panel-h">
            <h2>1. Servidor de correo (SMTP)</h2>
            <span class="dt-chip {{ $smtp['desdeAdmin'] ? 'up' : '' }}">{{ $smtp['desdeAdmin'] ? 'Configurado aquí' : 'Usando el del servidor (.env)' }}</span>
        </div>
        <p class="dt-sub" style="margin:0 0 14px">Son los datos de la cuenta de correo que envía (por ejemplo, la de tu dominio en Hostinger: servidor <code>smtp.hostinger.com</code>, puerto 465, seguridad SSL, usuario = el correo completo).</p>
        <form method="POST" action="/admin/correos/smtp" style="display:grid;gap:14px">
            @csrf
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px">
                <div><label class="dt-label" for="s-host">Servidor</label><input id="s-host" class="dt-input" name="smtp_host" value="{{ old('smtp_host', $smtp['host']) }}" placeholder="{{ $smtp['hostEnv'] ?: 'smtp.hostinger.com' }}" required autocomplete="off"></div>
                <div><label class="dt-label" for="s-puerto">Puerto</label><input id="s-puerto" class="dt-input" type="number" name="smtp_puerto" value="{{ old('smtp_puerto', $smtp['puerto']) }}" placeholder="465" required></div>
                <div><label class="dt-label" for="s-cifrado">Seguridad</label>
                    <select id="s-cifrado" class="dt-input" name="smtp_cifrado">
                        <option value="ssl" @selected(old('smtp_cifrado', $smtp['cifrado']) === 'ssl')>SSL (puerto 465)</option>
                        <option value="tls" @selected(old('smtp_cifrado', $smtp['cifrado']) === 'tls')>TLS / STARTTLS (puerto 587)</option>
                    </select>
                </div>
                <div><label class="dt-label" for="s-usuario">Usuario</label><input id="s-usuario" class="dt-input" name="smtp_usuario" value="{{ old('smtp_usuario', $smtp['usuario']) }}" placeholder="pedidos@dauntoque.com" required autocomplete="off"></div>
                <div><label class="dt-label" for="s-clave">Contraseña</label><input id="s-clave" class="dt-input" type="password" name="smtp_clave" autocomplete="new-password" placeholder="{{ $smtp['clave'] ? '•••••••• guardada (vacío = no cambiarla)' : 'Contraseña del correo' }}" @unless($smtp['clave']) required @endunless></div>
            </div>
            <p class="dt-sub" style="font-size:12.5px;margin:0">La contraseña se guarda cifrada y nunca vuelve a mostrarse. Después de guardar, envía un correo de prueba.</p>
            <div style="display:flex;flex-wrap:wrap;gap:10px">
                <button class="dt-btn pri" type="submit">Guardar servidor</button>
                @if ($smtp['desdeAdmin'])
                    <button class="dt-btn" type="submit" name="quitar" value="1" formnovalidate onclick="return confirm('¿Borrar estos datos y volver a usar los del archivo .env?')">Usar el del .env</button>
                @endif
            </div>
        </form>

        <form method="POST" action="/admin/correos/prueba" style="display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;margin-top:18px;padding-top:16px;border-top:1px solid var(--border)">
            @csrf
            <div style="flex:1;min-width:220px"><label class="dt-label" for="s-para">Enviar un correo de prueba a</label><input id="s-para" class="dt-input" type="email" name="para" required value="{{ auth()->user()->email }}"></div>
            <button class="dt-btn" type="submit" style="height:44px">Enviar prueba</button>
        </form>
    </section>

    <form method="POST" action="/admin/correos">
        @csrf
        <section class="dt-panel">
            <div class="dt-panel-h"><h2>2. Remitente y avisos</h2></div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:14px">
                <div><label class="dt-label" for="c-rem">Correo remitente</label><input id="c-rem" class="dt-input" type="email" name="correo_remitente" value="{{ old('correo_remitente', $remitente) }}" placeholder="{{ $actual['direccion'] }}"></div>
                <div><label class="dt-label" for="c-nom">Nombre del remitente</label><input id="c-nom" class="dt-input" name="correo_remitente_nombre" maxlength="80" value="{{ old('correo_remitente_nombre', $remitenteNombre) }}" placeholder="{{ $actual['nombre'] }}"></div>
                <div><label class="dt-label" for="c-av">Correo que recibe los pedidos pagados</label><input id="c-av" class="dt-input" type="email" name="correo_avisos_pedidos" value="{{ old('correo_avisos_pedidos', $avisos) }}" placeholder="{{ $actual['avisos'] ?: 'sin configurar' }}"></div>
            </div>
            <p class="dt-sub" style="font-size:12.5px;margin-top:10px">Si dejas un campo vacío se usa el que ves en gris. El remitente casi siempre debe ser el mismo correo del usuario del servidor.</p>
        </section>

        <section class="dt-panel">
            <div class="dt-panel-h"><h2>3. Plantillas</h2><span class="dt-chip">Apagada = no se envía</span></div>
            <div style="display:flex;flex-direction:column;gap:10px">
                @foreach ($plantillas as $id => $p)
                    <div style="display:flex;gap:12px;align-items:center;padding:12px;border:1px solid var(--border);border-radius:14px;flex-wrap:wrap">
                        <label style="display:flex;gap:12px;align-items:flex-start;cursor:pointer;flex:1;min-width:220px">
                            <input type="checkbox" name="activas[]" value="{{ $id }}" @checked($p['activa']) style="margin-top:3px;width:18px;height:18px;accent-color:#3b82f6">
                            <span><b>{{ $p['nombre'] }}</b>@if ($p['propia']) <span class="dt-chip up" style="font-size:10px">Diseño propio</span>@endif<small style="display:block;color:var(--muted);font-size:12.5px">{{ $p['desc'] }}</small></span>
                        </label>
                        @if ($p['editable'])
                            <a class="dt-btn" href="/admin/correos/plantilla/{{ $id }}">Editar diseño</a>
                        @else
                            <span class="dt-sub" style="font-size:12px">Varios correos en uno: sin editor por ahora</span>
                        @endif
                    </div>
                @endforeach
            </div>
            <button class="dt-btn pri" type="submit" style="margin-top:16px">Guardar remitente y plantillas</button>
        </section>
    </form>
@endsection
