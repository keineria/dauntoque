<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AvisoPago;
use App\Models\Campana;
use App\Models\Pedido;
use App\Models\User;
use App\Models\VisitaMarketing;
use App\Services\Marketing\Etiquetas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/** Centro de mando: menú propio, píxeles, medición de visitas, UTM en los pedidos, campañas, rendimiento y pagos. */
class CentroDeMandoTest extends TestCase
{
    use RefreshDatabase;

    private const VISITANTE = '0123456789abcdef0123456789abcdef';

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config(['services.bold.api_key' => 'llave-identidad', 'services.bold.secret' => 'secreta']);
    }

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->forceFill(['es_admin' => true])->save();

        return $admin;
    }

    private function pedido(array $datos = []): Pedido
    {
        return Pedido::create($datos + [
            'nombre' => 'Ana Gómez', 'correo' => 'ana@correo.com', 'celular' => '3001234567', 'departamento' => 'Antioquia',
            'ciudad' => 'Medellín', 'direccion' => 'Calle 1', 'acabado' => 'crystal', 'pais' => 'CO', 'moneda' => 'COP', 'monto' => 299900,
        ]);
    }

    public function test_todas_las_paginas_del_modulo_abren_con_su_menu_y_solo_para_el_administrador(): void
    {
        $admin = $this->admin();
        foreach (['/admin', '/admin/pagos', '/admin/pixeles', '/admin/campanas', '/admin/rendimiento', '/admin/carritos', '/admin/soporte', '/admin/correos', '/admin/chips'] as $ruta) {
            $this->actingAs($admin)->get($ruta)->assertOk()->assertSee('Centro de mando')->assertSee('Volver a mi panel');
            $this->actingAs(User::factory()->create())->get($ruta)->assertNotFound();
        }
        $this->actingAs($admin)->get('/admin')->assertSee('Listo para vender')->assertSee('Embudo de los últimos 30 días');
    }

    public function test_guardar_pixeles_valida_el_formato_y_los_carga_en_etiquetas_js(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post('/admin/pixeles', ['meta' => 'abc'])->assertSessionHasErrors('meta');
        $this->actingAs($admin)->post('/admin/pixeles', ['gads' => 'AW-123456789'])->assertSessionHasErrors('gads_compra');

        $this->actingAs($admin)->post('/admin/pixeles', [
            'meta' => '123456789012345', 'ga4' => ' g-ab12cd34ef ', 'gads' => 'AW-123456789', 'gads_compra' => 'AbC-dEfGh', 'tiktok' => 'C1A2B3C4D5E6F7G8H9I0',
        ])->assertSessionHasNoErrors();

        $this->assertSame('G-AB12CD34EF', Etiquetas::ids()['ga4']);
        $js = $this->get('/etiquetas.js')->assertOk()->assertHeader('Content-Type', 'application/javascript; charset=UTF-8');
        $this->assertStringContainsString('"meta":"123456789012345"', $js->getContent());
        $this->assertStringContainsString('"gadsCompra":"AW-123456789/AbC-dEfGh"', $js->getContent());
        $this->assertStringContainsString('"clarity":null', $js->getContent());
        // Sin sesión: Cloudflare lo puede guardar en caché
        $this->assertEmpty($js->headers->getCookies());
    }

    public function test_la_visita_se_guarda_sin_datos_personales_y_se_ignoran_robots_y_administrador(): void
    {
        $cuerpo = json_encode(['v' => self::VISITANTE, 'p' => '/es-co/', 'r' => 'instagram.com', 'u' => ['utm_source' => 'Facebook', 'utm_medium' => 'paid_social', 'utm_campaign' => 'navidad', 'clic' => 'fbclid']]);

        $this->call('POST', '/etiquetas/visita', [], [], [], ['CONTENT_TYPE' => 'text/plain', 'HTTP_USER_AGENT' => 'Mozilla/5.0 (iPhone)', 'HTTP_CF_IPCOUNTRY' => 'CO'], $cuerpo)->assertNoContent();
        $visita = VisitaMarketing::sole();
        $this->assertSame(['facebook', 'navidad', 'fbclid', 'CO', 'celular'], [$visita->utm_source, $visita->utm_campaign, $visita->clic, $visita->pais, $visita->dispositivo]);

        $this->call('POST', '/etiquetas/visita', [], [], [], ['CONTENT_TYPE' => 'text/plain', 'HTTP_USER_AGENT' => 'Googlebot/2.1'], $cuerpo)->assertNoContent();
        $this->call('POST', '/etiquetas/visita', [], ['dt_yo' => '1'], [], ['CONTENT_TYPE' => 'text/plain', 'HTTP_USER_AGENT' => 'Mozilla/5.0'], $cuerpo)->assertNoContent();
        $this->call('POST', '/etiquetas/visita', [], [], [], ['CONTENT_TYPE' => 'text/plain'], '{"v":"malo"}')->assertNoContent();
        $this->assertSame(1, VisitaMarketing::count());
    }

    public function test_el_pedido_guarda_el_origen_de_la_galleta_utm(): void
    {
        Http::fake(['integrations.api.bold.co/*' => Http::response(['payload' => ['payment_link' => 'LNK_9', 'url' => 'https://checkout.bold.co/LNK_9'], 'errors' => []])]);

        $this->withUnencryptedCookies([
            'dt_utm' => json_encode(['utm_source' => 'tiktok', 'utm_medium' => 'paid_social', 'utm_campaign' => 'lanzamiento', 'clic' => 'ttclid']),
            'dt_v' => self::VISITANTE,
        ])->post('/comprar', [
            'nombre' => 'Carlos Pérez', 'correo' => 'carlos@correo.com', 'celular' => '300 123 4567', 'departamento' => 'Antioquia',
            'ciudad' => 'Medellín', 'direccion' => 'Calle 10', 'acabado' => 'crystal',
        ])->assertRedirect('https://checkout.bold.co/LNK_9');

        $pedido = Pedido::sole();
        $this->assertSame(['tiktok', 'lanzamiento', 'ttclid', self::VISITANTE], [$pedido->utm_source, $pedido->utm_campaign, $pedido->clic, $pedido->visitante]);
    }

    public function test_formulario_y_pedido_pagado_mandan_los_eventos_de_compra(): void
    {
        $this->get('/comprar')->assertOk()->assertSee("dtq.push(['inicio_compra'", false)->assertSee('/etiquetas.js');

        $pedido = $this->pedido(['estado' => 'pagado', 'pagado_en' => now()]);
        $this->get('/pedido/' . $pedido->token)->assertOk()->assertSee("dtq.push(['compra'", false)->assertSee($pedido->codigo);
        $pendiente = $this->pedido();
        $this->get('/pedido/' . $pendiente->token)->assertOk()->assertDontSee("dtq.push(['compra'", false);
    }

    public function test_campanas_con_utm_limpio_unico_y_rendimiento_con_costo_y_retorno(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post('/admin/campanas', [
            'nombre' => 'Lanzamiento Navidad', 'canal' => 'meta_ads', 'utm_source' => 'Facebook', 'utm_medium' => 'paid_social',
            'destino' => 'https://dauntoque.com/es-co/', 'presupuesto' => '$600.000', 'moneda' => 'COP',
        ])->assertSessionHasNoErrors();

        $campana = Campana::sole();
        $this->assertSame(['lanzamiento_navidad', 'facebook', '600000.00'], [$campana->utm_campaign, $campana->utm_source, $campana->presupuesto]);
        $this->assertSame('https://dauntoque.com/es-co/?utm_source=facebook&utm_medium=paid_social&utm_campaign=lanzamiento_navidad', $campana->enlace());

        // Mismo UTM o destino fuera de dauntoque.com: no
        $this->actingAs($admin)->post('/admin/campanas', ['nombre' => 'Lanzamiento navidad', 'canal' => 'meta_ads', 'utm_source' => 'facebook', 'utm_medium' => 'cpc', 'destino' => 'https://dauntoque.com/', 'moneda' => 'COP'])->assertSessionHasErrors('utm_campaign');
        $this->actingAs($admin)->post('/admin/campanas', ['nombre' => 'Otra', 'canal' => 'meta_ads', 'utm_source' => 'facebook', 'utm_medium' => 'cpc', 'destino' => 'https://otro.com/', 'moneda' => 'COP'])->assertSessionHasErrors('destino');

        // 3 personas de la campaña, 2 compras pagadas de $299.900 → costo $300.000 por compra y retorno 1 x ($599.800 / $600.000)
        foreach (['a', 'b', 'c'] as $i => $v) {
            VisitaMarketing::create(['visitante' => str_repeat($v, 32), 'pagina' => '/es-co/', 'utm_source' => 'facebook', 'utm_medium' => 'paid_social', 'utm_campaign' => 'lanzamiento_navidad']);
        }
        VisitaMarketing::create(['visitante' => str_repeat('d', 32), 'pagina' => '/es-co/']);
        $this->pedido(['estado' => 'pagado', 'pagado_en' => now(), 'utm_source' => 'facebook', 'utm_medium' => 'paid_social', 'utm_campaign' => 'lanzamiento_navidad']);
        $this->pedido(['estado' => 'pagado', 'pagado_en' => now(), 'utm_source' => 'facebook', 'utm_medium' => 'paid_social', 'utm_campaign' => 'lanzamiento_navidad']);
        $this->pedido(['utm_campaign' => 'lanzamiento_navidad']);

        $this->actingAs($admin)->get('/admin/rendimiento?dias=30')->assertOk()
            ->assertSee('Lanzamiento Navidad')->assertSee('$300.000 COP')->assertSee('1 x')->assertSee('Directo');
        $this->actingAs($admin)->get('/admin/campanas')->assertOk()->assertSee('$599.800 COP')->assertSee('Ver quién llegó al pago (3)')->assertSee('Compró');

        // Un UTM armado por fuera (sin crearlo aquí) también se cuenta
        VisitaMarketing::create(['visitante' => str_repeat('e', 32), 'pagina' => '/es-co/', 'utm_source' => 'instagram', 'utm_medium' => 'social', 'utm_campaign' => 'bio_ig']);
        $this->actingAs($admin)->get('/admin/campanas')->assertSee('bio_ig')->assertSee('UTM creado por fuera del Centro de mando');

        $this->actingAs($admin)->put('/admin/campanas/' . $campana->id, ['nombre' => 'Navidad', 'canal' => 'meta_ads', 'utm_source' => 'facebook', 'utm_medium' => 'paid_social', 'utm_campaign' => 'lanzamiento_navidad', 'destino' => 'https://dauntoque.com/comprar', 'moneda' => 'COP'])->assertSessionHasNoErrors();
        $this->assertSame('Navidad', $campana->fresh()->nombre);
        $this->actingAs($admin)->delete('/admin/campanas/' . $campana->id)->assertRedirect('/admin/campanas');
        $this->assertSame(0, Campana::count());
    }

    public function test_pagos_muestra_los_avisos_de_bold_y_prueba_la_conexion(): void
    {
        $admin = $this->admin();
        $pedido = $this->pedido(['link_id' => 'LNK_55']);
        $cuerpo = json_encode(['type' => 'SALE_APPROVED', 'data' => ['payment_id' => 'P1', 'metadata' => ['reference' => 'LNK_55']]]);
        $firma = hash_hmac('sha256', base64_encode($cuerpo), 'secreta');
        $this->call('POST', '/webhooks/bold', [], [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_X_BOLD_SIGNATURE' => $firma], $cuerpo)->assertOk();
        $this->call('POST', '/webhooks/bold', [], [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_X_BOLD_SIGNATURE' => 'mala'], $cuerpo)->assertStatus(401);

        $this->assertSame(['pedido ' . $pedido->codigo, 'firma inválida'], AvisoPago::orderBy('id')->pluck('resultado')->all());
        $this->actingAs($admin)->get('/admin/pagos')->assertOk()->assertSee('Funcionando')->assertSee('SALE_APPROVED')->assertSee('firma inválida');

        Http::fake(['integrations.api.bold.co/*' => Http::sequence()->push(['payload' => []], 200)->push([], 401)]);
        $this->actingAs($admin)->post('/admin/pagos/probar-bold')->assertSessionHas('status');
        $this->actingAs($admin)->post('/admin/pagos/probar-bold')->assertSessionHas('error');
    }
}
