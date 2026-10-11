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
        {--fuente= : Carpeta con textos, CSS, JS y media (por defecto resources/sitio)}';

    protected $description = 'Genera el sitio estático de ventas por país e idioma';

    /** Versiones del sitio: país de venta, idioma y rutas traducidas. */
    public const VERSIONES = [
        'es-co' => ['mercado' => 'CO', 'idioma' => 'es', 'hreflang' => 'es-CO', 'tarjetas' => 'tarjetas', 'preguntas' => 'preguntas'],
        'es-us' => ['mercado' => 'US', 'idioma' => 'es', 'hreflang' => 'es-US', 'tarjetas' => 'tarjetas', 'preguntas' => 'preguntas'],
        'en-us' => ['mercado' => 'US', 'idioma' => 'en', 'hreflang' => 'en-US', 'tarjetas' => 'cards', 'preguntas' => 'faq'],
    ];

    public const ACABADOS = ['matte-black', 'crystal'];

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

        File::deleteDirectory($destino);
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
            $reemplazos = [':precio' => $mercado['precioTexto'] . ' ' . $mercado['moneda'], ':antes' => (string) $mercado['antesTexto'], ':ahorro' => $mercado['ahorroTexto'] . ' ' . $mercado['moneda'], ':pagos' => $pagos,
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

            $paginas[] = $this->pagina('inicio', $comun);
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

        // Raíz: elige la versión por la elección guardada, la zona horaria o el idioma
        File::put($destino . '/index.html', view('sitio.raiz', ['base' => $base, 'dominio' => $dominio, 'indexar' => $indexar, 'versiones' => self::VERSIONES])->render());

        File::copyDirectory($fuente . '/media', $destino . '/media');
        File::put($destino . '/sitio.css', $this->minificarCss(File::get($fuente . '/sitio.css')));
        File::put($destino . '/sitio.js', File::get($fuente . '/sitio.js'));
        File::put($destino . '/sitemap.xml', view('sitio.sitemap', ['paginas' => $paginas, 'dominio' => $dominio, 'base' => $base])->render());
        File::put($destino . '/robots.txt', $indexar
            ? "User-agent: *\nDisallow: /pedido\nSitemap: $dominio$base/sitemap.xml\n"
            : "User-agent: *\nDisallow: /\n");

        // En la raíz del dominio, dauntoque.com/keiner abre el perfil: lo que no es del sitio de ventas va al portal
        if ($base === '') {
            File::put($destino . '/.htaccess', self::htaccess($portal));
        }

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

    /** Reglas para dauntoque.com: toda ruta que no sea un archivo o carpeta del sitio se abre en el portal. */
    public static function htaccess(string $portal): string
    {
        return "RewriteEngine On\n"
            . "RewriteCond %{REQUEST_URI} !^/\\.well-known/\n"
            . "RewriteCond %{REQUEST_FILENAME} !-f\n"
            . "RewriteCond %{REQUEST_FILENAME} !-d\n"
            . "RewriteRule ^(.+)$ $portal/$1 [R=302,L]\n";
    }

    /** COP 299900 -> "$299.900"; USD 119 -> "$119". */
    public static function dinero($monto, string $moneda): string
    {
        return '$' . ($moneda === 'COP' ? number_format((float) $monto, 0, ',', '.') : rtrim(rtrim(number_format((float) $monto, 2, '.', ','), '0'), '.'));
    }

    /** Ruta de una página dentro de una versión: '' (inicio), 'tarjetas/crystal', 'faq'… */
    public static function rutaDe(string $codigo, string $vista, ?string $acabado = null): string
    {
        $v = self::VERSIONES[$codigo];

        return match ($vista) {
            'tarjeta' => $v['tarjetas'] . '/' . ($acabado ?? self::ACABADOS[0]),
            'preguntas' => $v['preguntas'],
            default => '',
        };
    }

    private function pagina(string $vista, array $datos): array
    {
        $acabado = $datos['acabado'] ?? null;
        $base = $datos['base'];
        $datos['vista'] = $vista;
        $datos['ruta'] = self::rutaDe($datos['codigo'], $vista, $acabado);
        // URL pública definitiva (canonical y hreflang), aunque la prueba viva en otra ruta
        $datos['canonica'] = function (string $codigo) use ($vista, $acabado, $datos) {
            $ruta = self::rutaDe($codigo, $vista, $acabado);

            return $datos['dominio'] . '/' . $codigo . '/' . ($ruta === '' ? '' : $ruta . '/');
        };
        // $ir('en-us') = esta misma página en otra versión; $ir('es-co', 'preguntas') = otra página
        $datos['ir'] = function (string $codigo, ?string $otraVista = null, ?string $otroAcabado = null) use ($vista, $acabado, $base) {
            $ruta = self::rutaDe($codigo, $otraVista ?? $vista, $otroAcabado ?? $acabado);

            return $base . '/' . $codigo . '/' . ($ruta === '' ? '' : $ruta . '/');
        };

        return ['codigo' => $datos['codigo'], 'ruta' => $datos['ruta'], 'vista' => $vista, 'acabado' => $acabado, 'html' => view("sitio.$vista", $datos)->render()];
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
