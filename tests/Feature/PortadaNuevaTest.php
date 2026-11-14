<?php

namespace Tests\Feature;

use App\Models\Ajuste;
use App\Models\Pedido;
use App\Models\User;
use App\Services\Marketing\ComprasRecientes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Portada nueva del sitio de ventas (prueba en /es-co/nueva/) y sus avisos de compras:
 * solo pedidos pagados reales, anónimos, recientes y con el interruptor del administrador encendido.
 */
class PortadaNuevaTest extends TestCase
{
    use RefreshDatabase;

    private function pedido(array $datos = []): Pedido
    {
        return Pedido::create($datos + [
            'nombre' => 'luis fernando PATIÑO gómez', 'correo' => 'luis@example.com', 'celular' => '3001234567',
            'departamento' => 'Antioquia', 'ciudad' => 'medellín', 'direccion' => 'Calle 1 # 2-3', 'acabado' => 'matte-black',
            'pais' => 'CO', 'moneda' => 'COP', 'monto' => 299900, 'estado' => 'pagado', 'pagado_en' => now()->subHours(2),
        ]);
    }

    public function test_apagados_no_muestran_nada_aunque_haya_compras(): void
    {
        $this->pedido();

        $this->getJson('/compras-recientes.json?pais=CO')->assertOk()->assertExactJson(['compras' => []]);
    }

    public function test_solo_salen_pagados_recientes_y_sin_datos_personales(): void
    {
        Ajuste::guardar(['avisos_compras' => true]);
        $this->pedido();
        $this->pedido(['nombre' => 'Ana Ruiz', 'estado' => 'pendiente', 'pagado_en' => null]);
        $this->pedido(['nombre' => 'Vieja Compra', 'pagado_en' => now()->subDays(40)]);
        $this->pedido(['nombre' => 'John Smith', 'pais' => 'US', 'moneda' => 'USD']);

        $respuesta = $this->getJson('/compras-recientes.json?pais=CO')->assertOk()->assertJsonCount(1, 'compras');
        $respuesta->assertJsonPath('compras.0.nombre', 'Luis P.')
            ->assertJsonPath('compras.0.ciudad', 'Medellín')
            ->assertJsonPath('compras.0.producto', 'Matte Black');
        $this->assertStringNotContainsString('example.com', $respuesta->getContent());
        $this->assertStringNotContainsStringIgnoringCase('patiño', $respuesta->getContent());
        $this->assertStringNotContainsString('300123', $respuesta->getContent());
        $respuesta->assertHeaderMissing('Set-Cookie');
    }

    public function test_nombres_anonimos(): void
    {
        $this->assertSame('Luis P.', ComprasRecientes::anonimo('Luis Patiño'));
        $this->assertSame('Luis P.', ComprasRecientes::anonimo('LUIS FERNANDO PATIÑO GÓMEZ'));
        $this->assertSame('María', ComprasRecientes::anonimo(' maría '));
        $this->assertSame('', ComprasRecientes::anonimo('123'));
    }

    public function test_el_administrador_enciende_y_apaga_los_avisos(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['es_admin' => true])->save();
        $this->pedido();

        $this->actingAs($admin)->post('/admin/pagos/avisos-compras', ['activos' => 1])->assertRedirect();
        $this->assertTrue(ComprasRecientes::activos());
        $this->getJson('/compras-recientes.json')->assertJsonCount(1, 'compras');
        $this->actingAs($admin)->get('/admin/pagos')->assertOk()->assertSee('Luis P.');

        $this->actingAs($admin)->post('/admin/pagos/avisos-compras', ['activos' => 0])->assertRedirect();
        Cache::flush();
        $this->getJson('/compras-recientes.json')->assertExactJson(['compras' => []]);
    }

    public function test_la_portada_de_prueba_se_genera_sin_indexar_y_fuera_del_mapa(): void
    {
        $carpeta = sys_get_temp_dir() . '/sitio-nueva-' . uniqid();
        $this->artisan('sitio:generar', ['--destino' => $carpeta, '--base' => '', '--indexar' => true, '--portada' => 'clasica'])->assertSuccessful();

        $nueva = File::get($carpeta . '/es-co/nueva/index.html');
        $this->assertStringContainsString('<meta name="robots" content="noindex, follow">', $nueva);
        $this->assertStringContainsString('<link rel="canonical" href="https://dauntoque.com/es-co/">', $nueva);
        $this->assertStringContainsString('Iniciar sesión', $nueva);
        $this->assertStringContainsString('https://portal.dauntoque.com/login', $nueva);
        $this->assertStringContainsString('/compras-recientes.json?pais=CO', $nueva);
        $this->assertStringContainsString('"@type":"FAQPage"', $nueva);
        $this->assertMatchesRegularExpression('#/nueva\.css\?v=[a-f0-9]{8}#', $nueva);
        $this->assertStringNotContainsString('/nueva/', File::get($carpeta . '/sitemap.xml'));
        // Los nombres de ejemplo solo existen en el modo demostración (?demo=avisos) y van rotulados
        $js = File::get($carpeta . '/nueva.js');
        $this->assertStringContainsString("demo=avisos", $js);
        $this->assertStringContainsString('es-demo', $js);
        $this->assertStringContainsString('Demostración', File::get($carpeta . '/nueva.css'));
        // La portada actual sigue igual
        $this->assertStringContainsString('sitio.css', File::get($carpeta . '/es-co/index.html'));
        $this->assertFileDoesNotExist($carpeta . '/en-us/nueva/index.html');
        File::deleteDirectory($carpeta);
    }

    public function test_con_portada_nueva_reemplaza_el_inicio_y_se_indexa(): void
    {
        $carpeta = sys_get_temp_dir() . '/sitio-nueva-' . uniqid();
        $this->artisan('sitio:generar', ['--destino' => $carpeta, '--base' => '', '--indexar' => true])->assertSuccessful();

        $inicio = File::get($carpeta . '/es-co/index.html');
        $this->assertStringContainsString('nueva.css', $inicio);
        $this->assertStringContainsString('content="index, follow', $inicio);
        $this->assertStringContainsString('hreflang="en-US"', $inicio);
        // La portada anterior queda de respaldo: sin indexar, con canónica al inicio, fuera del mapa y con sus enlaces sanos
        $respaldo = File::get($carpeta . '/es-co/clasica/index.html');
        $this->assertStringContainsString('sitio.css', $respaldo);
        $this->assertStringContainsString('<meta name="robots" content="noindex, nofollow">', $respaldo);
        $this->assertStringContainsString('<link rel="canonical" href="https://dauntoque.com/es-co/">', $respaldo);
        $this->assertStringNotContainsString('/en-us/clasica/', $respaldo);
        $this->assertStringNotContainsString('/clasica/', File::get($carpeta . '/sitemap.xml'));
        $this->assertFileDoesNotExist($carpeta . '/es-us/clasica/index.html');
        // El enlace de prueba ya compartido lleva al inicio
        $this->assertStringContainsString('url=/es-co/', File::get($carpeta . '/es-co/nueva/index.html'));
        File::deleteDirectory($carpeta);
    }
}
