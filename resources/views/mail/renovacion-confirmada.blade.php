@php
    $tarjeta = $pago->tarjeta;
    $nombre = \Illuminate\Support\Str::before($tarjeta->user->name ?? '', ' ');
    $chip = '···' . \Illuminate\Support\Str::substr($tarjeta->nfc_uid, -6);
    $vence = $tarjeta->vence_en->format('d/m/Y');
@endphp
<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Renovación confirmada</title></head>
<body style="margin:0;background:#f5f4ef;font-family:Helvetica,Arial,sans-serif;color:#151816">
@if($interno)
    <h2 style="margin:16px 16px 12px">Renovación pagada</h2>
    <table cellpadding="6" cellspacing="0" style="font-size:14px;border-collapse:collapse;margin:0 16px">
        <tr><td><b>Cliente</b></td><td>{{ $tarjeta->user->name ?? '' }}</td></tr>
        <tr><td><b>Correo</b></td><td>{{ $tarjeta->user->email ?? '' }}</td></tr>
        <tr><td><b>Tarjeta</b></td><td>{{ $chip }}</td></tr>
        <tr><td><b>Vence ahora</b></td><td>{{ $vence }}</td></tr>
        <tr><td><b>Total</b></td><td>{{ $total }} · Bold {{ $pago->pago_proveedor_id }}</td></tr>
    </table>
@else
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f5f4ef;padding:24px 12px">
<tr><td align="center">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#ffffff;border-radius:18px;overflow:hidden">
    <tr><td style="background:#0d0f10;padding:26px 28px;color:#eef1ee">
        <div style="font-size:20px;font-weight:800">dauntoque<span style="color:#2fae6e">.com</span></div>
        <div style="margin-top:18px;font-size:26px;font-weight:800;line-height:1.15">@if($nombre)Gracias, {{ $nombre }}. @endif Tu renovación quedó lista.</div>
        <div style="margin-top:8px;color:#9aa39f;font-size:15px">Tarjeta {{ $chip }} · activa hasta el {{ $vence }}</div>
    </td></tr>
    <tr><td style="padding:26px 28px">
        <p style="margin:0 0 14px;font-size:16px;line-height:1.5">Recibimos tu pago de <b>{{ $total }}</b>. Tu perfil, tus contactos y tus estadísticas siguen igual, y tu tarjeta sigue abriendo tu perfil sin pausas.</p>
        <table role="presentation" cellpadding="0" cellspacing="0" style="margin:20px 0"><tr><td style="background:#2fae6e;border-radius:999px">
            <a href="{{ url('/dashboard') }}" style="display:inline-block;padding:15px 28px;color:#04140b;font-weight:700;font-size:16px;text-decoration:none">Ir a mi panel</a>
        </td></tr></table>
        <p style="margin:0;font-size:13px;color:#5d655f">Referencia del pago: {{ $pago->pago_proveedor_id ?: $pago->link_id }}. Guarda este correo como comprobante.</p>
    </td></tr>
    <tr><td style="padding:18px 28px;background:#ebe9e1;font-size:12px;color:#5d655f">Sin mensualidades: renuevas una vez al año el servidor de tu perfil y listo. · DaUnToque</td></tr>
</table>
</td></tr>
</table>
@endif
</body>
</html>
