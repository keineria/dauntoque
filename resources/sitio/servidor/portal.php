<?php
/*
 * dauntoque.com/carla muestra el perfil del portal sin cambiar la dirección del navegador.
 * El .htaccess manda aquí toda ruta que no sea un archivo del sitio de ventas; este archivo
 * pide esa misma ruta al portal y devuelve la respuesta tal cual. Lo genera `php artisan sitio:generar`.
 * Si algo falla (sin curl, portal caído), redirige al portal como antes.
 */
$portal = '__PORTAL__';
$host = parse_url($portal, PHP_URL_HOST);
$uri = '/' . ltrim($_SERVER['REQUEST_URI'] ?? '/', '/');
$metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$redirigir = function () use ($portal, $uri) {
    header('Location: ' . $portal . $uri, true, 302);
    exit;
};

if (!function_exists('curl_init')) {
    $redirigir();
}

// Cabeceras del visitante que el portal necesita (sesión, CSRF, idioma, caché)
$pasar = [
    'CONTENT_TYPE' => 'Content-Type', 'HTTP_ACCEPT' => 'Accept', 'HTTP_ACCEPT_LANGUAGE' => 'Accept-Language',
    'HTTP_USER_AGENT' => 'User-Agent', 'HTTP_COOKIE' => 'Cookie', 'HTTP_X_CSRF_TOKEN' => 'X-CSRF-TOKEN',
    'HTTP_X_XSRF_TOKEN' => 'X-XSRF-TOKEN', 'HTTP_X_REQUESTED_WITH' => 'X-Requested-With', 'HTTP_REFERER' => 'Referer',
    'HTTP_IF_NONE_MATCH' => 'If-None-Match', 'HTTP_IF_MODIFIED_SINCE' => 'If-Modified-Since',
];
$cabeceras = ['X-Forwarded-For: ' . ($_SERVER['REMOTE_ADDR'] ?? ''), 'X-Forwarded-Proto: https', 'Expect:'];
foreach ($pasar as $clave => $nombre) {
    if (isset($_SERVER[$clave]) && $_SERVER[$clave] !== '') {
        $cabeceras[] = $nombre . ': ' . $_SERVER[$clave];
    }
}

$pedir = function (bool $local) use ($portal, $host, $uri, $metodo, $cabeceras) {
    $ch = curl_init($portal . $uri);
    $opciones = [
        CURLOPT_CUSTOMREQUEST => $metodo,
        CURLOPT_HTTPHEADER => $cabeceras,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_ENCODING => '',
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 20,
    ];
    if ($metodo === 'HEAD') {
        $opciones[CURLOPT_NOBODY] = true;
    } elseif (!in_array($metodo, ['GET', 'OPTIONS'], true)) {
        $opciones[CURLOPT_POSTFIELDS] = file_get_contents('php://input');
    }
    // El portal vive en este mismo servidor: se le habla por 127.0.0.1 (y así ve la IP real del visitante)
    if ($local) {
        $opciones[CURLOPT_RESOLVE] = [$host . ':443:127.0.0.1'];
    }
    curl_setopt_array($ch, $opciones);
    $respuesta = curl_exec($ch);
    $info = $respuesta === false ? null : ['estado' => curl_getinfo($ch, CURLINFO_RESPONSE_CODE), 'largo' => curl_getinfo($ch, CURLINFO_HEADER_SIZE)];
    curl_close($ch);

    return $info ? $info + ['respuesta' => $respuesta] : null;
};

$r = $pedir(true) ?? $pedir(false);
if (!$r || $r['estado'] < 100) {
    $redirigir();
}

http_response_code($r['estado']);
$devolver = ['content-type', 'location', 'set-cookie', 'cache-control', 'etag', 'last-modified', 'expires', 'content-disposition', 'retry-after', 'vary'];
$bloques = preg_split("/\r\n\r\n/", rtrim(substr($r['respuesta'], 0, $r['largo'])));
foreach (explode("\r\n", end($bloques)) as $linea) {
    [$nombre, $valor] = array_pad(explode(':', $linea, 2), 2, null);
    $nombre = strtolower(trim((string) $nombre));
    if ($valor === null || !in_array($nombre, $devolver, true)) {
        continue;
    }
    $valor = trim($valor);
    // Las cookies del portal quedan para dauntoque.com
    if ($nombre === 'set-cookie') {
        $valor = preg_replace('/;\s*domain=[^;]*/i', '', $valor);
    }
    header($nombre . ': ' . $valor, $nombre !== 'set-cookie');
}
echo substr($r['respuesta'], $r['largo']);
