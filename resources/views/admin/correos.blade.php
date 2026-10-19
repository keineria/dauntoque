@extends('layouts.app')

@section('contenido')
    <div class="dt-head">
        <div>
            <h1 class="dt-h1">Correos de notificación</h1>
            <p class="dt-sub">Quién envía los correos de la plataforma, a dónde llegan los avisos y cuáles se envían.</p>
        </div>
    </div>
    @include('admin._tabs')

    <form method="POST" action="/admin/correos">
        @csrf
        <section class="dt-panel">
            <div class="dt-panel-h"><h2>Remitente y avisos</h2></div>
            @if ($errors->any())<p style="color:#f87171;margin:0 0 12px">{{ $errors->first() }}</p>@endif
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:14px">
                <div><span class="dt-label">Correo remitente</span><input class="dt-input" type="email" name="correo_remitente" value="{{ old('correo_remitente', $remitente) }}" placeholder="{{ $actual['direccion'] }}"></div>
                <div><span class="dt-label">Nombre del remitente</span><input class="dt-input" name="correo_remitente_nombre" maxlength="80" value="{{ old('correo_remitente_nombre', $remitenteNombre) }}" placeholder="{{ $actual['nombre'] }}"></div>
                <div><span class="dt-label">Correo que recibe los pedidos pagados</span><input class="dt-input" type="email" name="correo_avisos_pedidos" value="{{ old('correo_avisos_pedidos', $avisos) }}" placeholder="{{ $actual['avisos'] ?: 'sin configurar' }}"></div>
            </div>
            <p class="dt-sub" style="font-size:12.5px;margin-top:10px">Si dejas un campo vacío se usa el del servidor (archivo .env), que es el que ves en gris. El correo remitente debe ser una cuenta de tu dominio que el servidor de correo permita usar.</p>
        </section>

        <section class="dt-panel">
            <div class="dt-panel-h"><h2>Plantillas</h2><span class="dt-chip">Apagada = no se envía</span></div>
            <div style="display:flex;flex-direction:column;gap:10px">
                @foreach ($plantillas as $id => $p)
                    <label style="display:flex;gap:12px;align-items:flex-start;padding:12px;border:1px solid var(--border);border-radius:14px;cursor:pointer">
                        <input type="checkbox" name="activas[]" value="{{ $id }}" @checked($p['activa']) style="margin-top:3px;width:18px;height:18px;accent-color:#3b82f6">
                        <span><b>{{ $p['nombre'] }}</b><small style="display:block;color:var(--muted);font-size:12.5px">{{ $p['desc'] }}</small></span>
                    </label>
                @endforeach
            </div>
            <button class="dt-btn pri" type="submit" style="margin-top:16px">Guardar configuración</button>
        </section>
    </form>

    <section class="dt-panel">
        <div class="dt-panel-h"><h2>Servidor de correo</h2><span class="dt-chip">Solo lectura</span></div>
        <div class="ad-tabla">
            <div class="ad-fila"><span>Envío</span><span><b>{{ $smtp['mailer'] }}</b></span><span></span></div>
            <div class="ad-fila"><span>Servidor</span><span><b>{{ $smtp['host'] ?: '—' }}</b></span><span>Puerto {{ $smtp['puerto'] ?: '—' }}</span></div>
            <div class="ad-fila"><span>Usuario</span><span><b>{{ $smtp['usuario'] ?: '—' }}</b></span><span>Contraseña: {{ $smtp['clave'] ? 'configurada ✓' : 'falta' }}</span></div>
        </div>
        <p class="dt-sub" style="font-size:12.5px;margin-top:10px">Por seguridad, la contraseña del correo solo se cambia en el servidor (archivo .env) y nunca se muestra aquí.</p>

        <form method="POST" action="/admin/correos/prueba" style="display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;margin-top:16px">
            @csrf
            <div style="flex:1;min-width:220px"><span class="dt-label">Enviar un correo de prueba a</span><input class="dt-input" type="email" name="para" required value="{{ auth()->user()->email }}"></div>
            <button class="dt-btn pri" type="submit" style="height:44px">Enviar prueba</button>
        </form>
    </section>
@endsection
