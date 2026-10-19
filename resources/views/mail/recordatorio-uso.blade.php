<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Tu tarjeta DaUnToque</title></head>
<body style="margin:0;background:#f5f4ef;font-family:Helvetica,Arial,sans-serif;color:#151816">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f5f4ef;padding:24px 12px">
<tr><td align="center">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#ffffff;border-radius:18px;overflow:hidden">
    <tr><td style="background:#0d0f10;padding:26px 28px;color:#eef1ee">
        <div style="font-size:20px;font-weight:800">dauntoque<span style="color:#2fae6e">.com</span></div>
        <div style="margin-top:18px;font-size:26px;font-weight:800;line-height:1.15">Hola, {{ $nombre }}. Tu tarjeta está lista para trabajar por ti.</div>
        <div style="margin-top:8px;color:#9aa39f;font-size:15px">Hace un tiempo que tu perfil no recibe visitas.</div>
    </td></tr>
    <tr><td style="padding:26px 28px">
        <p style="margin:0 0 14px;font-size:16px;line-height:1.5">Tres ideas rápidas para sacarle provecho:</p>
        <ol style="margin:0 0 18px;padding-left:20px;font-size:15px;line-height:1.6">
            <li>Acércala al celular de cada cliente nuevo: guarda tu contacto en un toque.</li>
            <li>Pon tu enlace en tu bio de Instagram y en tu estado de WhatsApp.</li>
            <li>Actualiza tu foto, tus servicios y tus redes desde tu panel.</li>
        </ol>
        <table role="presentation" cellpadding="0" cellspacing="0" style="margin:8px 0 20px"><tr><td style="background:#2fae6e;border-radius:999px">
            <a href="{{ $enlace_panel }}" style="display:inline-block;padding:15px 28px;color:#04140b;font-weight:700;font-size:16px;text-decoration:none">Actualizar mi perfil</a>
        </td></tr></table>
        <p style="margin:0;font-size:13px;color:#5d655f">Tu enlace: <a href="{{ $enlace_perfil }}" style="color:#14633b;word-break:break-all">{{ $enlace_perfil }}</a></p>
    </td></tr>
    <tr><td style="padding:18px 28px;background:#ebe9e1;font-size:12px;color:#5d655f">Sin mensualidades. Solo renuevas al año el servidor de tu perfil y listo. · DaUnToque</td></tr>
</table>
</td></tr>
</table>
</body>
</html>
