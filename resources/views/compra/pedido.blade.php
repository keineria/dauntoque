<!doctype html>
<html lang="es-CO">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Pedido {{ $pedido->codigo }} | DaUnToque</title>
    @include('compra._estilos')
    <style>
        .caja{max-width:620px;margin:40px auto 60px;background:#fff;border:1px solid var(--line);border-radius:22px;padding:clamp(22px,5vw,36px);display:grid;gap:18px}
        .estado{justify-self:start;padding:6px 12px;border-radius:999px;font-weight:700;font-size:13px}
        .estado.pagado{background:#e3f5ea;color:#14633b}
        .estado.pendiente{background:#fff3c4;color:#6b5300}
        .estado.rechazado{background:#fbe4df;color:#9a2f1d}
        dl{display:grid;grid-template-columns:auto 1fr;gap:8px 16px;margin:0;font-size:15px}
        dt{color:var(--mut)}dd{margin:0;text-align:right}
        ol{margin:0;padding-left:20px;line-height:1.7}
    </style>
    @if ($pedido->estado === 'pendiente')<meta http-equiv="refresh" content="8">@endif
</head>
<body>
<header class="top"><div class="w"><a class="logo" href="https://dauntoque.com">dauntoque<span>.com</span></a><span class="seguro">Pedido {{ $pedido->codigo }}</span></div></header>
<main class="w">
    <div class="caja">
        @if ($pedido->estado === 'pagado')
            <span class="estado pagado">Pago confirmado</span>
            <h1 style="font-size:clamp(28px,5vw,38px)">¡Gracias, {{ \Illuminate\Support\Str::before($pedido->nombre, ' ') }}! Tu tarjeta está en camino.</h1>
            <p style="margin:0">Te enviamos este enlace a <b>{{ $pedido->correo }}</b>. Crea tu perfil ahora y el día que llegue la tarjeta solo la acercas a tu celular.</p>
            <a class="btn" href="{{ $pedido->enlaceRegistro() }}">{{ $pedido->tieneCuenta() ? 'Entrar a mi panel' : 'Crear mi perfil' }}</a>
        @elseif ($pedido->estado === 'rechazado')
            <span class="estado rechazado">Pago rechazado</span>
            <h1 style="font-size:32px">Tu pago no se completó</h1>
            <p style="margin:0">No se hizo ningún cobro. Puedes intentarlo de nuevo con otro medio de pago.</p>
            <a class="btn" href="/comprar?acabado={{ $pedido->acabado }}">Intentar de nuevo</a>
        @else
            <span class="estado pendiente">Confirmando tu pago…</span>
            <h1 style="font-size:32px">Estamos esperando la confirmación de Bold</h1>
            <p style="margin:0">Suele tardar unos segundos (con PSE puede tardar unos minutos). Esta página se actualiza sola, y al confirmarse te llega un correo a <b>{{ $pedido->correo }}</b>.</p>
        @endif
        <dl>
            <dt>Tarjeta</dt><dd>{{ $pedido->acabadoNombre() }}@if($pedido->nombre_tarjeta) · {{ $pedido->nombre_tarjeta }}@endif</dd>
            <dt>Total</dt><dd>{{ $pedido->totalTexto() }}</dd>
            <dt>Envío a</dt><dd>{{ $pedido->ciudad }}, {{ $pedido->departamento }}</dd>
        </dl>
        @if ($pedido->estado === 'pagado' && $pedido->chip)
            <div style="background:#e3f5ea;border-radius:14px;padding:14px 16px">
                <div style="font-size:13px;color:#14633b;font-weight:700">Tu código de activación</div>
                <div style="margin:4px 0;font-family:ui-monospace,Consolas,monospace;font-size:24px;font-weight:800;letter-spacing:.08em">{{ $pedido->chip->codigo }}</div>
                <div style="font-size:14px;color:#2d4a39">También llegó a tu correo. Sirve para activar la tarjeta desde tu panel si el celular no la lee.</div>
            </div>
        @endif
        @if ($pedido->estado === 'pagado')
            <ol>
                <li>Crea tu perfil con este mismo correo.</li>
                <li>Te escribimos al {{ $pedido->celular }} con la guía de envío.</li>
                <li>Cuando llegue, acércala a tu celular o escribe tu código en el panel y queda activa.</li>
            </ol>
        @endif
    </div>
</main>
</body>
</html>
