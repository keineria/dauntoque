<!doctype html>
<html lang="es-CO">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Tarjeta sin habilitar | DaUnToque</title>
    @include('compra._estilos')
    <style>
        .caja{max-width:520px;margin:48px auto 60px;background:#fff;border:1px solid var(--line);border-radius:22px;padding:clamp(22px,5vw,36px);display:grid;gap:16px;text-align:center}
        .codigo{font-family:ui-monospace,Consolas,monospace;background:#f2f1ec;border-radius:10px;padding:8px 12px;justify-self:center;word-break:break-all}
    </style>
</head>
<body>
<main style="padding:0 16px">
    <div class="caja">
        <h1 style="font-size:24px;margin:0">Esta tarjeta aún no está habilitada</h1>
        <p style="margin:0;color:var(--mut)">Puede que la estemos terminando de preparar. Escríbenos y la habilitamos en un momento.</p>
        <span class="codigo">{{ $codigo }}</span>
        @if ($whatsapp)
            <a class="btn" style="justify-self:center;width:auto" href="https://wa.me/{{ $whatsapp }}?text={{ rawurlencode('Hola, mi tarjeta DaUnToque dice que no está habilitada. Código: ' . $codigo) }}" target="_blank" rel="noopener">Escribir por WhatsApp</a>
        @endif
    </div>
</main>
</body>
</html>
