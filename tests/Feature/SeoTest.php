<?php

namespace Tests\Feature;

use App\Console\Commands\GenerarSitio;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/** SEO: datos estructurados y mapa de los perfiles públicos, robots.txt y metadatos del sitio de ventas. */
class SeoTest extends TestCase
{
    use RefreshDatabase;

    private function perfil(array $datos = []): Profile
    {
        return Profile::create($datos + [
            'user_id' => User::factory()->create()->id, 'nickname' => 'sofia', 'tipo_perfil' => 'corporativo',
            'nombre' => 'Sofía Rojas', 'profesion' => 'Arquitecta', 'bio' => "Diseño casas\nque se viven.",
            'telefono' => '+57 300 111 2233', 'correo_contacto' => 'sofia@example.com',
            'redes_sociales' => [['id' => 'ig', 'usuario' => '@sofia.arq']],
        ]);
    }

    public function test_el_perfil_trae_datos_estructurados_y_contacto_sin_javascript(): void
    {
        $this->perfil();

        $html = $this->get('/sofia')->assertOk()->getContent();

        $this->assertStringContainsString('<title>Sofía Rojas · Arquitecta | DaUnToque</title>', $html);
        $this->assertStringContainsString('content="Arquitecta. Diseño casas que se viven."', $html);
        preg_match('#<script type="application/ld\+json">(.+?)</script>#s', $html, $m);
        $schema = json_decode($m[1], true);
        $this->assertSame('ProfilePage', $schema['@type']);
        $this->assertSame('Person', $schema['mainEntity']['@type']);
        $this->assertSame('Arquitecta', $schema['mainEntity']['jobTitle']);
        $this->assertSame(['https://instagram.com/sofia.arq'], $schema['mainEntity']['sameAs']);
        // Respaldo sin JavaScript con el nombre y los enlaces de contacto
        $this->assertMatchesRegularExpression('#<noscript>\s*<main class="pp-sinjs">.*<h1>Sofía Rojas</h1>.*https://wa\.me/573001112233.*</noscript>#s', $html);
    }

    public function test_el_perfil_de_empresa_se_marca_como_negocio(): void
    {
        $this->perfil(['tipo_perfil' => 'empresa']);

        preg_match('#<script type="application/ld\+json">(.+?)</script>#s', $this->get('/sofia')->getContent(), $m);

        $this->assertSame('LocalBusiness', json_decode($m[1], true)['mainEntity']['@type']);
    }

    public function test_el_mapa_de_perfiles_lista_solo_el_enlace_permanente_de_cada_cliente(): void
    {
        $perfil = $this->perfil();
        Profile::create(['user_id' => $perfil->user_id, 'nickname' => 'sofia-personal', 'tipo_perfil' => 'hobbies', 'nombre' => 'Sofi']);

        $xml = $this->get('/sitemap-perfiles.xml')->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8')->getContent();

        $this->assertStringContainsString('<loc>' . Profile::urlPublica('sofia') . '</loc>', $xml);
        $this->assertStringNotContainsString('sofia-personal', $xml);
        $this->assertNotFalse(simplexml_load_string($xml));
    }

    public function test_robots_protege_lo_privado_y_anuncia_los_dos_mapas(): void
    {
        $robots = GenerarSitio::robots('https://dauntoque.com');

        foreach (['Disallow: /dashboard', 'Disallow: /admin', 'Disallow: /pedido/', 'Disallow: /cita/', 'Disallow: /activar/',
            'Sitemap: https://dauntoque.com/sitemap.xml', 'Sitemap: https://dauntoque.com/sitemap-perfiles.xml'] as $linea) {
            $this->assertStringContainsString($linea, $robots);
        }
        $this->assertContains('sitemap-perfiles.xml', config('perfiles.reservados'));
    }

    public function test_el_sitio_generado_trae_metadatos_completos(): void
    {
        $carpeta = sys_get_temp_dir() . '/sitio-seo-' . uniqid();
        $this->artisan('sitio:generar', ['--destino' => $carpeta, '--base' => '', '--indexar' => true])->assertSuccessful();
        $inicio = File::get($carpeta . '/es-co/index.html');
        $tarjeta = File::get($carpeta . '/es-co/tarjetas/crystal/index.html');
        $mapa = File::get($carpeta . '/sitemap.xml');
        File::deleteDirectory($carpeta);

        $this->assertStringContainsString('<meta name="twitter:image"', $inicio);
        $this->assertStringContainsString('max-image-preview:large', $inicio);
        $this->assertStringContainsString('class="selector menu-movil"', $inicio);
        $this->assertStringContainsString('"merchantReturnDays":30', $tarjeta);
        $this->assertStringContainsString('"sku":"DT-NFC-CRYSTAL"', $tarjeta);
        $this->assertStringContainsString('<lastmod>' . now()->toDateString() . '</lastmod>', $mapa);
        // Todos los bloques de datos estructurados son JSON válido
        preg_match_all('#<script type="application/ld\+json">(.+?)</script>#s', $inicio . $tarjeta, $bloques);
        $this->assertGreaterThanOrEqual(5, count($bloques[1]));
        foreach ($bloques[1] as $json) {
            $this->assertIsArray(json_decode($json, true));
        }
    }
}
