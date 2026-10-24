<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Pedido {{ $pedido->codigo }}</title></head>
<body style="margin:0;background:#f5f4ef;font-family:Helvetica,Arial,sans-serif;color:#151816">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f5f4ef;padding:24px 12px">
<tr><td align="center">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#ffffff;border-radius:18px;overflow:hidden">
    <tr><td style="background:#0d0f10;padding:26px 28px;color:#eef1ee">
        <div style="font-size:20px;font-weight:800">dauntoque<span style="color:#2fae6e">.com</span></div>
        <div style="margin-top:18px;font-size:26px;font-weight:800;line-height:1.15">¡Gracias, {{ \Illuminate\Support\Str::before($pedido->nombre, ' ') }}! Tu tarjeta está confirmada.</div>
        <div style="margin-top:8px;color:#9aa39f;font-size:15px">Pedido {{ $pedido->codigo }} · {{ $pedido->pagado_en?->format('d/m/Y') }}</div>
    </td></tr>
    <tr><td style="padding:26px 28px">
        <p style="margin:0 0 14px;font-size:16px;line-height:1.5"><b>Mientras te llega la tarjeta, crea tu perfil.</b> Así, el día que la recibas solo la acercas a tu celular y queda activa.</p>
        <table role="presentation" cellpadding="0" cellspacing="0" style="margin:20px 0"><tr><td style="background:#2fae6e;border-radius:999px">
            <a href="{{ $pedido->enlaceRegistro() }}" style="display:inline-block;padding:15px 28px;color:#04140b;font-weight:700;font-size:16px;text-decoration:none">{{ $pedido->tieneCuenta() ? 'Entrar a mi panel' : 'Crear mi perfil' }}</a>
        </td></tr></table>
        <p style="margin:0 0 22px;font-size:13px;color:#5d655f">Si el botón no abre, copia este enlace: <br><a href="{{ $pedido->enlaceRegistro() }}" style="color:#14633b;word-break:break-all">{{ $pedido->enlaceRegistro() }}</a></p>

        @if ($pedido->chip)
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 22px;background:#e3f5ea;border-radius:12px"><tr><td style="padding:16px 18px">
                <div style="font-size:13px;color:#14633b;font-weight:700">Tu código de activación</div>
                <div style="margin:6px 0;font-family:ui-monospace,Consolas,monospace;font-size:26px;font-weight:800;letter-spacing:.08em;color:#0d0f10">{{ $pedido->chip->codigo }}</div>
                <div style="font-size:13px;line-height:1.5;color:#2d4a39">Guárdalo. Si tu celular no lee la tarjeta o quieres activarla desde un computador, entra a tu panel y escríbelo en <b>Inicio → Activa tu tarjeta</b>.</div>
            </td></tr></table>
        @endif

        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #d9d6cb;border-radius:12px;font-size:14px">
            <tr><td style="padding:12px 14px;color:#5d655f">Tarjeta</td><td style="padding:12px 14px;text-align:right"><b>{{ $pedido->acabadoNombre() }}</b>@if($pedido->nombre_tarjeta) · {{ $pedido->nombre_tarjeta }}@endif</td></tr>
            <tr><td style="padding:12px 14px;color:#5d655f;border-top:1px solid #d9d6cb">Total pagado</td><td style="padding:12px 14px;text-align:right;border-top:1px solid #d9d6cb"><b>{{ $pedido->totalTexto() }}</b></td></tr>
            <tr><td style="padding:12px 14px;color:#5d655f;border-top:1px solid #d9d6cb">Envío a</td><td style="padding:12px 14px;text-align:right;border-top:1px solid #d9d6cb">{{ $pedido->direccion }}, {{ $pedido->ciudad }} ({{ $pedido->departamento }})</td></tr>
        </table>

        <p style="margin:22px 0 6px;font-size:15px;font-weight:700">Qué sigue</p>
        <ol style="margin:0;padding-left:20px;font-size:14px;line-height:1.6;color:#151816">
            <li>Crea tu perfil con el botón de arriba (usa este mismo correo).</li>
            <li>Por la oferta de lanzamiento puedes crear un segundo perfil (por ejemplo, uno ejecutivo y otro personal) y elegir desde tu panel cuál se abre con tu tarjeta.</li>
            <li>Preparamos tu tarjeta y te escribimos al {{ $pedido->celular }} con la guía de envío.</li>
            <li>Cuando llegue, acércala a tu celular: se enlaza a tu perfil y listo.@if ($pedido->chip) También puedes escribir tu código de activación en tu panel.@endif</li>
        </ol>
    </td></tr>
    <tr><td style="padding:18px 28px;background:#ebe9e1;font-size:12px;color:#5d655f">Sin mensualidades. Solo renuevas al año el servidor de tu perfil y listo. · DaUnToque</td></tr>
</table>
</td></tr>
</table>
</body>
</html>
