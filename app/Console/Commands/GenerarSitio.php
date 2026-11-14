<?php

namespace App\Console\Commands;

use App\Support\Precios;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

/**
 * Genera el sitio de ventas (dauntoque.com) como HTML estático: una versión por país e idioma
 * (es-co, es-us, en-us), con el precio de la tabla prices y los medios de pago de config/pagos.php.
 *
 * Prueba:     php artisan sitio:generar                      -> public/nuevo (noindex)
 * Publicación: php artisan sitio:generar --destino=/ruta --base= --indexar
 */
class GenerarSitio extends Command
{
    protected $signature = 'sitio:generar
        {--destino=public/nuevo : Carpeta de salida (se reemplaza completa)}
        {--base=/nuevo : Ruta donde vivirá el sitio ("" en la raíz del dominio)}
        {--dominio=https://dauntoque.com : Dominio de las URL canónicas}
        {--portal=https://portal.dauntoque.com : Portal donde se compra en línea (/comprar)}
        {--indexar : Permite que Google indexe (solo para la publicación final)}
        {--dentro-de-laravel : El destino es la carpeta public/ de Laravel: no la vacía entera ni crea .htaccess/portal.php}
        {--fuente= : Carpeta con textos, CSS, JS y media (por defecto resources/sitio)}
        {--portada=nueva : "nueva" pone la portada nueva (vista sitio.nueva) como inicio de /es-co/ y deja la anterior de respaldo en /es-co/clasica/; "clasica" vuelve a la anterior y deja la nueva de prueba en /es-co/nueva/}';

    protected $description = 'Genera el sitio estático de ventas por país e idioma';

    /** Versiones del sitio: país de venta, idioma y rutas traducidas. */
    public const VERSIONES = [
        'es-co' => ['mercado' => 'CO', 'idioma' => 'es', 'hreflang' => 'es-CO', 'tarjetas' => 'tarjetas', 'preguntas' => 'preguntas'],
        'es-us' => ['mercado' => 'US', 'idioma' => 'es', 'hreflang' => 'es-US', 'tarjetas' => 'tarjetas', 'preguntas' => 'preguntas'],
        'en-us' => ['mercado' => 'US', 'idioma' => 'en', 'hreflang' => 'en-US', 'tarjetas' => 'cards', 'preguntas' => 'faq'],
    ];

    /**
     * hreflang sin país y x-default: fuera de Colombia y Estados Unidos, quien busca en español ve la versión
     * en español con precio en dólares y el resto la versión en inglés (la raíz / no sirve: solo redirige y no se indexa).
     */
    public const GENERICOS = ['es' => 'es-us', 'en' => 'en-us', 'x-default' => 'en-us'];

    /** Redes oficiales de la marca (sameAs de la organización en schema.org). */
    public const REDES = ['https://www.instagram.com/dauntoqueof/', 'https://www.tiktok.com/@dauntoque.com', 'https://www.facebook.com/dauntoque.com'];

    /** Nombre del país de cada versión para títulos y textos (:pais). */
    public const PAISES = ['es' => ['CO' => 'Colombia', 'US' => 'EE. UU.'], 'en' => ['CO' => 'Colombia', 'US' => 'the US']];

    /** Portadas que viven junto al inicio sin indexarse: la de prueba y la de respaldo. Su canónica es el inicio. */
    public const SIN_INDEXAR = ['nueva', 'clasica'];

    public const ACABADOS = ['matte-black', 'crystal'];

    /** Versiones que tienen la portada nueva (vista sitio.nueva, por ahora solo en español de Colombia). */
    public const CON_PORTADA_NUEVA = ['es-co'];

    public function handle(): int
    {
        $destino = $this->ruta($this->option('destino'));
        $base = rtrim((string) $this->option('base'), '/');
        $dominio = rtrim((string) $this->option('dominio'), '/');
        $portal = rtrim((string) $this->option('portal'), '/');
        $indexar = (bool) $this->option('indexar');
        $fuente = rtrim($this->option('fuente') ?: resource_path('sitio'), '/');

        $mercados = $this->mercados();
        $textos = require $fuente . '/textos.php';
        $paginas = [];
        $redirigir = [];

        $dentroDeLaravel = (bool) $this->option('dentro-de-laravel');
        if ($dentroDeLaravel) {
            // En public/ conviven index.php, build, uploads…: solo se reemplaza lo que genera este comando
            $this->limpiarGenerados($destino);
        } else {
            File::deleteDirectory($destino);
        }
        File::ensureDirectoryExists($destino);

        foreach (self::VERSIONES as $codigo => $version) {
            $mercado = $mercados[$version['mercado']];
            $t = $textos[$version['idioma']];
            $o = $version['idioma'] === 'en' ? ' or ' : ' o ';
            // :pagos = cómo se paga la tarjeta en ese país (tabla payment_methods): "Nequi o Llave Bre-B"
            $pagos = implode($o, $mercado['pagos']);
            // Compra en línea con Bold en los países de config/pedidos.php; en los demás, por WhatsApp
            $enLinea = in_array($mercado['code'], config('pedidos.paises_en_linea', []), true);
            $modo = $enLinea ? 'linea' : 'whatsapp';
            $reemplazos = [':precio' => $mercado['precioTexto'] . ' ' . $mercado['moneda'], ':antes' => (string) $mercado['antesTexto'], ':ahorro' => $mercado['ahorroTexto'] . ' ' . $mercado['moneda'], ':pagos' => $pagos, ':pais' => self::PAISES[$version['idioma']][$mercado['code']] ?? $mercado['code'],
                ':comprarCon' => strtr($t['comprarCon'][$modo], [':pagos' => $pagos]), ':comoPago' => strtr($t['comoPago'][$modo], [':pagos' => $pagos])];
            $wa = fn (string $mensaje) => 'https://wa.me/' . $mercado['whatsapp'] . '?text=' . rawurlencode($mensaje);
            $comun = compact('codigo', 'version', 'mercado', 't', 'base', 'dominio', 'indexar') + [
                'versiones' => self::VERSIONES,
                'textos' => $textos,
                'medios' => $mercado['pagos'],
                // $x('texto con :precio') reemplaza los datos del país
                'x' => fn (string $texto, array $mas = []) => strtr($texto, $mas + $reemplazos),
                // Enlace de WhatsApp de ventas con el mensaje ya escrito
                'wa' => $wa,
                // Botones de compra: checkout del portal (misma pestaña) o WhatsApp (pestaña nueva)
                'compraExterna' => !$enLinea,
                'comprar' => fn (?string $acabado = null, ?string $mensaje = null) => $enLinea
                    ? $portal . '/comprar' . ($acabado ? '?acabado=' . $acabado : '')
                    : $wa($mensaje ?? $t['wa']),
            ];

            // Portada nueva: inicio de la versión (la anterior queda de respaldo en /clasica/, sin indexar)
            // o, con --portada=clasica, de prueba en /es-co/nueva/ (sin indexar)
            $conNueva = in_array($codigo, self::CON_PORTADA_NUEVA, true);
            $nuevaDeInicio = $conNueva && $this->option('portada') === 'nueva';
            $comun += ['portal' => $portal, 'prueba' => !$nuevaDeInicio];
            $paginas[] = $this->pagina('inicio', $comun, $nuevaDeInicio ? 'nueva' : null);
            if ($conNueva && !$nuevaDeInicio) {
                $paginas[] = $this->pagina('nueva', $comun);
            }
            if ($nuevaDeInicio) {
                $paginas[] = $this->pagina('clasica', array_merge($comun, ['indexar' => false]), 'inicio');
                // El enlace de prueba que ya se compartió (/es-co/nueva/) lleva al inicio
                $redirigir[$codigo . '/nueva'] = $base . '/' . $codigo . '/';
            }
            foreach (self::ACABADOS as $acabado) {
                $paginas[] = $this->pagina('tarjeta', $comun + ['acabado' => $acabado]);
            }
            $paginas[] = $this->pagina('preguntas', $comun);
        }

        foreach ($paginas as $p) {
            $archivo = $destino . '/' . $p['codigo'] . ($p['ruta'] === '' ? '' : '/' . $p['ruta']) . '/index.html';
            File::ensureDirectoryExists(dirname($archivo));
            File::put($archivo, $p['html']);
        }

        foreach ($redirigir as $ruta => $a) {
            File::ensureDirectoryExists($destino . '/' . $ruta);
            File::put($destino . '/' . $ruta . '/index.html', '<!doctype html><html lang="es"><head><meta charset="utf-8"><title>DaUnToque</title><meta name="robots" content="noindex, follow">'
                . '<link rel="canonical" href="' . $dominio . $a . '"><meta http-equiv="refresh" content="0;url=' . $a . '"><script>location.replace("' . $a . '" + location.search)</script></head>'
                . '<body><a href="' . $a . '">dauntoque.com</a></body></html>');
        }

        // Raíz: elige la versión por la elección guardada, la zona horaria o el idioma
        File::put($destino . '/index.html', view('sitio.raiz', ['base' => $base, 'dominio' => $dominio, 'indexar' => $indexar, 'versiones' => self::VERSIONES])->render());

        File::copyDirectory($fuente . '/media', $destino . '/media');
        File::put($destino . '/sitio.css', $this->minificarCss(File::get($fuente . '/sitio.css')));
        File::put($destino . '/sitio.js', File::get($fuente . '/sitio.js'));
        File::put($destino . '/nueva.css', $this->minificarCss(File::get($fuente . '/nueva.css')));
        File::put($destino . '/nueva.js', File::get($fuente . '/nueva.js'));
        // La portada de prueba y la de respaldo no van en el mapa del sitio (no se indexan)
        $enMapa = array_values(array_filter($paginas, fn ($p) => !in_array($p['vista'], self::SIN_INDEXAR, true)));
        File::put($destino . '/sitemap.xml', view('sitio.sitemap', ['paginas' => $enMapa, 'dominio' => $dominio, 'base' => $base, 'fecha' => now()->toDateString()])->render());
        File::put($destino . '/robots.txt', $indexar ? self::robots($dominio, $base) : "User-agent: *\nDisallow: /\n");

        // En la raíz del dominio, dauntoque.com/keiner muestra el perfil: lo que no es del sitio de ventas
        // lo sirve portal.php pidiéndolo al portal, sin cambiar la dirección
        // (dentro de Laravel no hace falta: su propio .htaccess ya sirve primero los archivos que existen)
        if ($base === '' && !$dentroDeLaravel) {
            File::put($destino . '/.htaccess', self::htaccess());
            File::put($destino . '/portal.php', strtr(File::get(resource_path('sitio/servidor/portal.php')), ['__PORTAL__' => $portal]));
        }

        // Versión de cada archivo en sus enlaces (sitio.css?v=…): así Cloudflare y el navegador no muestran los viejos
        $this->versionarEnlaces($destino, $base, $dentroDeLaravel ? $this->generados() : null);

        $this->info(count($paginas) . " páginas generadas en $destino");

        return self::SUCCESS;
    }

    /** Precio vigente y datos de cada país desde la base de datos. */
    private function mercados(): array
    {
        return DB::table('markets')->where('active', true)->get()->mapWithKeys(function ($m) {
            $precio = Precios::tarjeta($m->code);

            $whatsapp = $m->whatsapp ?: DB::table('markets')->where('code', 'CO')->value('whatsapp');

            return [$m->code => [
                'code' => $m->code,
                'moneda' => $m->currency,
                'precio' => (float) $precio->amount,
                'antes' => $precio->compare_at ? (float) $precio->compare_at : null,
                'precioTexto' => self::dinero($precio->amount, $m->currency),
                'antesTexto' => $precio->compare_at ? self::dinero($precio->compare_at, $m->currency) : null,
                'ahorroTexto' => $precio->compare_at ? self::dinero($precio->compare_at - $precio->amount, $m->currency) : null,
                'whatsapp' => preg_replace('/\D/', '', (string) $whatsapp),
                'pagos' => DB::table('payment_methods')->where('market_code', $m->code)->orderBy('sort')->pluck('label')->all(),
            ]];
        })->all();
    }

    /**
     * robots.txt de la publicación: deja fuera el panel, el administrador y las páginas privadas
     * (pedidos, citas, activación) y anuncia los dos mapas: el del sitio de ventas y el de los perfiles.
     */
    public static function robots(string $dominio, string $base = ''): string
    {
        // Las páginas con noindex propio (comprar, login, agenda) no se bloquean aquí: Google debe poder leer ese noindex
        $privadas = ['/dashboard', '/admin', '/editor', '/pedido/', '/cita/', '/activar/', '/api/', '/webhooks/', '/*/vcf$'];

        return "User-agent: *\nAllow: /\n"
            . implode('', array_map(fn ($ruta) => "Disallow: $ruta\n", $privadas))
            . "\nSitemap: $dominio$base/sitemap.xml\n"
            . ($base === '' ? "Sitemap: $dominio/sitemap-perfiles.xml\n" : '');
    }

    /** Reglas para dauntoque.com: toda ruta que no sea un archivo o carpeta del sitio la atiende portal.php. */
    public static function htaccess(): string
    {
        return "RewriteEngine On\n"
            . "RewriteCond %{REQUEST_URI} !^/\\.well-known/\n"
            . "RewriteCond %{REQUEST_FILENAME} !-f\n"
            . "RewriteCond %{REQUEST_FILENAME} !-d\n"
            . "RewriteRule ^ portal.php [L]\n";
    }

    /** Carpetas y archivos que genera este comando en la raíz del destino (nada más se toca en public/). */
    public function generados(): array
    {
        return [...array_keys(self::VERSIONES), 'media', 'sitio.css', 'sitio.js', 'nueva.css', 'nueva.js', 'index.html', 'sitemap.xml', 'robots.txt'];
    }

    private function limpiarGenerados(string $destino): void
    {
        foreach ($this->generados() as $nombre) {
            File::isDirectory("$destino/$nombre") ? File::deleteDirectory("$destino/$nombre") : File::delete("$destino/$nombre");
        }
    }

    /**
     * Agrega ?v=<huella del archivo> a sitio.css, sitio.js y media/… en todas las páginas generadas.
     * $solo = nombres dentro de $destino que se revisan (en public/ de Laravel no se recorre todo).
     */
    private function versionarEnlaces(string $destino, string $base, ?array $solo = null): void
    {
        $huellas = [];
        $patron = '#(?<=["\'(])' . preg_quote($base, '#') . '/((?:media/[^"\'()?\s]+)|sitio\.css|sitio\.js|nueva\.css|nueva\.js)(?=["\')])#';

        $archivos = $solo === null ? File::allFiles($destino) : collect($solo)->flatMap(function ($nombre) use ($destino) {
            if (File::isDirectory("$destino/$nombre")) {
                return File::allFiles("$destino/$nombre");
            }

            return File::isFile("$destino/$nombre") ? [new \SplFileInfo("$destino/$nombre")] : [];
        });

        foreach ($archivos as $archivo) {
            if ($archivo->getExtension() !== 'html') {
                continue;
            }
            $html = preg_replace_callback($patron, function ($m) use ($destino, $base, &$huellas) {
                $ruta = $destino . '/' . $m[1];
                if (!is_file($ruta)) {
                    return $m[0];
                }
                $huellas[$m[1]] ??= substr(md5_file($ruta), 0, 8);

                return $base . '/' . $m[1] . '?v=' . $huellas[$m[1]];
            }, File::get($archivo->getPathname()));
            File::put($archivo->getPathname(), $html);
        }
    }

    /** COP 299900 -> "$299.900"; USD 119 -> "$119". */
    public static function dinero($monto, string $moneda): string
    {
        return '$' . ($moneda === 'COP' ? number_format((float) $monto, 0, ',', '.') : rtrim(rtrim(number_format((float) $monto, 2, '.', ','), '0'), '.'));
    }

    /**
     * Oferta del producto para schema.org: precio del país, producto nuevo, vendido por DaUnToque
     * y la garantía de 30 días (devolución del dinero) que promete el sitio.
     */
    public static function oferta(array $mercado, string $url): array
    {
        return [
            '@type' => 'Offer', 'price' => $mercado['precio'], 'priceCurrency' => $mercado['moneda'],
            'availability' => 'https://schema.org/InStock', 'itemCondition' => 'https://schema.org/NewCondition', 'url' => $url,
            'eligibleRegion' => ['@type' => 'Country', 'name' => $mercado['code']],
            'seller' => ['@type' => 'Organization', 'name' => 'DaUnToque'],
            // Envío gratis al país de la versión (lo que promete el sitio)
            'shippingDetails' => [
                '@type' => 'OfferShippingDetails',
                'shippingRate' => ['@type' => 'MonetaryAmount', 'value' => 0, 'currency' => $mercado['moneda']],
                'shippingDestination' => ['@type' => 'DefinedRegion', 'addressCountry' => $mercado['code']],
            ],
            'hasMerchantReturnPolicy' => [
                '@type' => 'MerchantReturnPolicy', 'applicableCountry' => $mercado['code'],
                'returnPolicyCategory' => 'https://schema.org/MerchantReturnFiniteReturnWindow', 'merchantReturnDays' => 30,
                'returnMethod' => 'https://schema.org/ReturnByMail',
            ],
        ];
    }

    /** Ruta de una página dentro de una versión: '' (inicio), 'tarjetas/crystal', 'faq'… */
    public static function rutaDe(string $codigo, string $vista, ?string $acabado = null): string
    {
        $v = self::VERSIONES[$codigo];

        return match ($vista) {
            'tarjeta' => $v['tarjetas'] . '/' . ($acabado ?? self::ACABADOS[0]),
            'preguntas' => $v['preguntas'],
            'nueva' => 'nueva',
            'clasica' => 'clasica',
            default => '',
        };
    }

    /** $plantilla = vista Blade que dibuja la página cuando no es la de su mismo nombre (portada nueva como inicio). */
    private function pagina(string $vista, array $datos, ?string $plantilla = null): array
    {
        $acabado = $datos['acabado'] ?? null;
        $base = $datos['base'];
        $datos['vista'] = $vista;
        $datos['ruta'] = self::rutaDe($datos['codigo'], $vista, $acabado);
        // URL pública definitiva (canonical y hreflang), aunque la prueba viva en otra ruta
        $datos['canonica'] = function (string $codigo) use ($vista, $acabado, $datos) {
            $ruta = self::rutaDe($codigo, in_array($vista, self::SIN_INDEXAR, true) ? 'inicio' : $vista, $acabado);

            return $datos['dominio'] . '/' . $codigo . '/' . ($ruta === '' ? '' : $ruta . '/');
        };
        // $ir('en-us') = esta misma página en otra versión; $ir('es-co', 'preguntas') = otra página
        $datos['ir'] = function (string $codigo, ?string $otraVista = null, ?string $otroAcabado = null) use ($vista, $acabado, $base, $datos) {
            // La portada de prueba o de respaldo solo existe en su versión: en las demás, lleva al inicio
            $misma = in_array($vista, self::SIN_INDEXAR, true) && $codigo !== $datos['codigo'] ? 'inicio' : $vista;
            $ruta = self::rutaDe($codigo, $otraVista ?? $misma, $otroAcabado ?? $acabado);

            return $base . '/' . $codigo . '/' . ($ruta === '' ? '' : $ruta . '/');
        };

        return ['codigo' => $datos['codigo'], 'ruta' => $datos['ruta'], 'vista' => $vista, 'acabado' => $acabado, 'html' => view('sitio.' . ($plantilla ?? $vista), $datos)->render()];
    }

    private function ruta(string $ruta): string
    {
        return str_starts_with($ruta, '/') ? rtrim($ruta, '/') : base_path(trim($ruta, '/'));
    }

    private function minificarCss(string $css): string
    {
        $css = preg_replace('!/\*.*?\*/!s', '', $css);
        $css = preg_replace('/\s+/', ' ', $css);

        return trim(preg_replace('/\s*([{};:,>])\s*/', '$1', $css));
    }
}
