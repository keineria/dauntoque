@php
    $fmt = fn ($n) => '$' . number_format((float) $n, 0, ',', '.');
@endphp
<!doctype html>
<html lang="es-CO">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Comprar tarjeta DaUnToque | Colombia</title>
    @include('compra._estilos')
    <style>
        .grid{display:grid;grid-template-columns:minmax(0,1fr) 380px;gap:32px;padding-block:34px 60px;align-items:start}
        .form{display:grid;gap:22px}
        fieldset{border:0;margin:0;padding:0;display:grid;gap:14px}
        legend{font:800 20px "Bricolage Grotesque",system-ui,sans-serif;margin-bottom:10px}
        .dos{display:grid;grid-template-columns:1fr 1fr;gap:14px}
        label{display:grid;gap:6px;font-size:13.5px;font-weight:600}
        label small{font-weight:400;color:var(--mut)}
        input,select{height:50px;border-radius:12px;border:1px solid var(--line);background:#fff;padding:0 14px;font:500 16px "Figtree",system-ui,sans-serif;color:var(--txt);width:100%}
        .err{color:var(--bad);font-size:13px;font-weight:500}
        .acabados{display:grid;grid-template-columns:1fr 1fr;gap:12px}
        .acabados > label{position:relative}
        .acabados input{position:absolute;inset:0 auto auto 0;width:1px;height:1px;opacity:0;pointer-events:none}
        .acab{position:relative;display:grid;gap:10px;border:2px solid var(--line);border-radius:16px;padding:12px;background:#fff;cursor:pointer}
        .acabados input:checked + .acab{border-color:var(--signal);box-shadow:0 0 0 4px rgba(47,174,110,.15)}
        .acabados input:focus-visible + .acab{outline:3px solid var(--signal2)}
        .mini{aspect-ratio:1.586;border-radius:10px;padding:10px 12px;display:flex;flex-direction:column;justify-content:space-between;font:700 11px "Bricolage Grotesque",system-ui,sans-serif}
        .mini.matte-black{background:linear-gradient(140deg,#2b3135,#0a0c0d 55%,#181b1d);color:#eef1ef}
        .mini.crystal{background:linear-gradient(140deg,#ffffff,#d3e4f3);color:#0f2233;border:1px solid #c5d6e6}
        .mini b{font-size:13px}
        .resumen{position:sticky;top:18px;background:var(--ink);color:#eef1ee;border-radius:22px;padding:24px;display:grid;gap:16px}
        .resumen .precio{display:flex;align-items:baseline;gap:10px;flex-wrap:wrap}
        .resumen .precio strong{font:800 42px/1 "Bricolage Grotesque",system-ui,sans-serif}
        .resumen .antes{text-decoration:line-through;color:#9aa39f}
        .resumen ul{list-style:none;margin:0;padding:0;display:grid;gap:9px;font-size:14.5px}
        .resumen li{display:flex;gap:9px}.resumen li::before{content:"✓";color:var(--signal2);font-weight:700}
        .resumen .chip{background:transparent;border-color:var(--line-d);color:#eef1ee}
        .mut{color:var(--mut)}
        .resumen .mut{color:#9aa39f}
        @media (max-width:880px){.grid{grid-template-columns:1fr}.resumen{position:static;order:-1}}
        @media (max-width:460px){.dos{grid-template-columns:1fr}}
    </style>
</head>
<body>
<header class="top"><div class="w">
    <a class="logo" href="https://dauntoque.com">dauntoque<span>.com</span></a>
    <span class="seguro">🔒 Pago seguro con Bold</span>
</div></header>

<main class="w grid">
    <form class="form" method="POST" action="/comprar" novalidate>
        @csrf
        <div style="display:grid;gap:6px">
            <h1 style="font-size:clamp(30px,4.5vw,42px)">Tu tarjeta, a un paso</h1>
            <p class="mut" style="margin:0">Llénalo en un minuto. Al pagar te llega un correo con el enlace para crear tu perfil.</p>
        </div>

        @if (session('status'))<div class="aviso" role="status">{{ session('status') }}</div>@endif
        @if (!$enLinea)
            <div class="aviso">El pago en línea se activa muy pronto. Mientras tanto puedes comprar por <a href="https://wa.me/{{ $whatsapp }}?text={{ rawurlencode('Hola, quiero mi tarjeta DaUnToque') }}" target="_blank" rel="noopener"><b>WhatsApp</b></a>.</div>
        @endif

        <fieldset>
            <legend>1. Elige tu acabado</legend>
            <div class="acabados">
                @foreach (\App\Models\Pedido::ACABADOS as $clave => $nombre)
                    <label style="display:block">
                        <input type="radio" name="acabado" value="{{ $clave }}" @checked(old('acabado', $acabado) === $clave)>
                        <span class="acab">
                            <span class="mini {{ $clave }}"><span>dauntoque</span><b data-nombre>{{ old('nombre_tarjeta') ?: 'Tu nombre' }}</b></span>
                            <span><b>{{ $nombre }}</b><br><small class="mut">{{ $clave === 'crystal' ? 'Transparente, el rompehielos.' : 'Negro mate, sobria y con autoridad.' }}</small></span>
                        </span>
                    </label>
                @endforeach
            </div>
            <label>Nombre en la tarjeta <small>(opcional, máx. 40)</small>
                <input id="nombre_tarjeta" name="nombre_tarjeta" maxlength="40" value="{{ old('nombre_tarjeta') }}" placeholder="Ej.: Carlos Constructor">
            </label>
            @error('acabado')<span class="err">{{ $message }}</span>@enderror
        </fieldset>

        <fieldset>
            <legend>2. Tus datos</legend>
            <label>Nombre completo
                <input id="nombre" name="nombre" required maxlength="120" autocomplete="name" value="{{ old('nombre') }}">
                @error('nombre')<span class="err">{{ $message }}</span>@enderror
            </label>
            <div class="dos">
                <label>Correo <small>(aquí te llega el enlace)</small>
                    <input id="correo" name="correo" type="email" required autocomplete="email" value="{{ old('correo') }}">
                    @error('correo')<span class="err">{{ $message }}</span>@enderror
                </label>
                <label>Celular / WhatsApp
                    <input id="celular" name="celular" type="tel" required autocomplete="tel" inputmode="tel" value="{{ old('celular') }}" placeholder="300 123 4567">
                    @error('celular')<span class="err">{{ $message }}</span>@enderror
                </label>
            </div>
            <label>Cédula <small>(opcional, para la guía de envío)</small>
                <input id="documento" name="documento" maxlength="30" inputmode="numeric" value="{{ old('documento') }}">
            </label>
        </fieldset>

        <fieldset>
            <legend>3. ¿A dónde te la enviamos?</legend>
            <div class="dos">
                <label>Departamento
                    <select id="departamento" name="departamento" required>
                        <option value="">Elige…</option>
                        @foreach ($departamentos as $d)<option @selected(old('departamento') === $d)>{{ $d }}</option>@endforeach
                    </select>
                    @error('departamento')<span class="err">{{ $message }}</span>@enderror
                </label>
                <label>Ciudad o municipio
                    <input id="ciudad" name="ciudad" required maxlength="80" autocomplete="address-level2" value="{{ old('ciudad') }}">
                    @error('ciudad')<span class="err">{{ $message }}</span>@enderror
                </label>
            </div>
            <label>Dirección <small>(con barrio, apto o referencia)</small>
                <input id="direccion" name="direccion" required maxlength="200" autocomplete="street-address" value="{{ old('direccion') }}">
                @error('direccion')<span class="err">{{ $message }}</span>@enderror
            </label>
        </fieldset>

        <button class="btn" type="submit" @disabled(!$enLinea)>Pagar {{ $fmt($precio->amount) }} {{ $moneda }}</button>
        <p class="mut" style="margin:-10px 0 0;font-size:13px;text-align:center">Te llevamos al pago seguro de Bold: tarjeta, PSE, Nequi o Botón Bancolombia.</p>
    </form>

    <aside class="resumen" aria-label="Resumen de tu compra">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:10px">
            <h2 style="font-size:19px">Tarjeta DaUnToque</h2>
            @if ($precio->compare_at)<span class="chip">Ahorras {{ $fmt($precio->compare_at - $precio->amount) }}</span>@endif
        </div>
        <div class="precio"><strong>{{ $fmt($precio->amount) }}</strong><span class="mut">{{ $moneda }}</span>@if ($precio->compare_at)<span class="antes">{{ $fmt($precio->compare_at) }}</span>@endif</div>
        <ul>
            <li>Tarjeta física con chip NFC</li>
            <li>Perfil web con WhatsApp, redes, portafolio y cobros</li>
            <li>Panel con visitas, contactos guardados y pagos</li>
            <li>Envío gratis a toda Colombia</li>
            <li>Garantía de 30 días</li>
        </ul>
        <div style="display:flex;gap:6px;flex-wrap:wrap"><span class="chip">Tarjeta</span><span class="chip">PSE</span><span class="chip">Nequi</span><span class="chip">Bancolombia</span></div>
        <p class="mut" style="margin:0;font-size:12.5px">Sin mensualidades. Solo renuevas al año el servidor de tu perfil y listo.</p>
    </aside>
</main>
<script>
    document.getElementById('nombre_tarjeta').addEventListener('input', e => {
        document.querySelectorAll('[data-nombre]').forEach(el => el.textContent = e.target.value.trim() || 'Tu nombre');
    });
</script>
</body>
</html>
