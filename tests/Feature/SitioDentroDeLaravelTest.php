<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * El sitio de ventas puede generarse dentro de public/ de Laravel (sitio:generar --dentro-de-laravel):
 * solo reemplaza lo suyo y no crea el .htaccess ni el portal.php del puente de antes.
 */
class SitioDentroDeLaravelTest extends TestCase
{
    use RefreshDatabase;

    private string $carpeta;

    protected function setUp(): void
    {
        parent::setUp();
        $this->carpeta = sys_get_temp_dir() . '/sitio-laravel-' . uniqid();
        File::ensureDirectoryExists($this->carpeta . '/build');
        File::put($this->carpeta . '/index.php', '<?php // front controller');
        File::put($this->carpeta . '/.htaccess', '# de Laravel');
        File::put($this->carpeta . '/build/app.js', 'js');
        File::ensureDirectoryExists($this->carpeta . '/es-co');
        File::put($this->carpeta . '/es-co/viejo.html', 'viejo');
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->carpeta);
        parent::tearDown();
    }

    public function test_genera_el_sitio_sin_borrar_ni_pisar_lo_de_laravel(): void
    {
        $this->artisan('sitio:generar', ['--destino' => $this->carpeta, '--base' => '', '--indexar' => true, '--dentro-de-laravel' => true])
            ->assertSuccessful();

        // Lo de Laravel sigue intacto
        $this->assertSame('<?php // front controller', File::get($this->carpeta . '/index.php'));
        $this->assertSame('# de Laravel', File::get($this->carpeta . '/.htaccess'));
        $this->assertSame('js', File::get($this->carpeta . '/build/app.js'));
        $this->assertFileDoesNotExist($this->carpeta . '/portal.php');

        // Lo generado reemplazó lo viejo y está completo
        $this->assertFileDoesNotExist($this->carpeta . '/es-co/viejo.html');
        foreach (['index.html', 'es-co/index.html', 'es-us/index.html', 'en-us/index.html', 'sitemap.xml', 'robots.txt', 'sitio.css', 'sitio.js'] as $archivo) {
            $this->assertFileExists($this->carpeta . '/' . $archivo, $archivo);
        }
        $this->assertStringContainsString('Sitemap: https://dauntoque.com/sitemap.xml', File::get($this->carpeta . '/robots.txt'));
        $this->assertMatchesRegularExpression('#/sitio\.css\?v=[0-9a-f]{8}#', File::get($this->carpeta . '/es-co/index.html'));
    }

    public function test_sin_la_opcion_sigue_reemplazando_la_carpeta_y_creando_el_puente(): void
    {
        $this->artisan('sitio:generar', ['--destino' => $this->carpeta, '--base' => ''])->assertSuccessful();

        $this->assertFileDoesNotExist($this->carpeta . '/index.php');
        $this->assertFileExists($this->carpeta . '/portal.php');
        $this->assertStringContainsString('portal.php', File::get($this->carpeta . '/.htaccess'));
    }

    public function test_la_raiz_muestra_el_sitio_si_existe_y_si_no_manda_al_login(): void
    {
        $inicio = public_path('index.html');
        $this->assertFileDoesNotExist($inicio);

        $this->get('/')->assertRedirect('/login');

        File::put($inicio, '<!doctype html><title>Inicio de ventas</title>');
        try {
            $respuesta = $this->get('/')->assertOk()->assertHeader('Content-Type', 'text/html; charset=UTF-8');
            $this->assertSame('<!doctype html><title>Inicio de ventas</title>', file_get_contents($respuesta->baseResponse->getFile()->getPathname()));
        } finally {
            File::delete($inicio);
        }
    }
}
