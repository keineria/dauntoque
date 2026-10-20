<?php

namespace Tests\Feature;

use App\Mail\PromoCarrito;
use App\Models\Pedido;
use App\Models\Profile;
use App\Models\ProfileEvent;
use App\Models\Tarjeta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/** Administrador: acceso, métricas del negocio y carritos abandonados con la última promoción. */
class AdministradorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config(['services.bold.api_key' => 'llave-identidad', 'services.bold.secret' => 'secreta']);
    }

    private function admin(): User
    {
        return User::factory()->create()->forceFill(['es_admin' => true]);
    }

    private function pedido(array $datos): Pedido
    {
        $pedido = Pedido::create($datos + [
            'nombre' => 'Ana Gómez', 'correo' => 'ana@correo.com', 'celular' => '3001234567', 'departamento' => 'Antioquia',
            'ciudad' => 'Medellín', 'direccion' => 'Calle 1', 'acabado' => 'crystal', 'pais' => 'CO', 'moneda' => 'COP', 'monto' => 299900,
        ]);
        if (isset($datos['creado'])) {
            $pedido->forceFill(['created_at' => $datos['creado']])->save();
        }

        return $pedido;
    }

    public function test_solo_el_administrador_entra(): void
    {
        $this->actingAs(User::factory()->create())->get('/admin')->assertNotFound();
        $this->actingAs(User::factory()->create())->get('/admin/soporte')->assertNotFound();

        $admin = $this->admin();
        $admin->save();
        foreach (['/admin', '/admin/carritos', '/admin/correos', '/admin/soporte', '/admin/chips'] as $ruta) {
            $this->actingAs($admin)->get($ruta)->assertOk();
        }
    }

    public function test_metricas_de_compras_clientes_uso_y_carritos(): void
    {
        $admin = $this->admin();
        $admin->save();

        // Compras: una pagada; carritos: uno en proceso, uno abandonado, uno declinado y uno que luego pagó
        $this->pedido(['estado' => 'pagado', 'pagado_en' => now(), 'correo' => 'paga@correo.com']);
        $this->pedido(['correo' => 'pagando@correo.com', 'creado' => now()->subMinutes(20)]);
        $this->pedido(['correo' => 'olvido@correo.com', 'creado' => now()->subDay()]);
        $this->pedido(['correo' => 'rechazo@correo.com', 'estado' => 'rechazado', 'creado' => now()->subDay()]);
        $this->pedido(['correo' => 'paga@correo.com', 'creado' => now()->subDays(2)]);

        // Clientes: activo con visitas, inactivo (servicio en pausa) y uno que lleva meses sin visitas
        $activo = User::factory()->create(['created_at' => now()->subMonths(6)]);
        $perfil = Profile::create(['user_id' => $activo->id, 'nickname' => 'activo', 'nombre' => 'Activo', 'nfc_uid' => 'CHIP1']);
        Tarjeta::create(['user_id' => $activo->id, 'nfc_uid' => 'CHIP1', 'activada_en' => now()->subMonth(), 'vence_en' => now()->addMonths(11)]);
        ProfileEvent::create(['profile_id' => $perfil->id, 'accion' => 'vista_perfil']);

        $pausado = User::factory()->create(['created_at' => now()->subYears(2)]);
        Profile::create(['user_id' => $pausado->id, 'nickname' => 'pausado', 'nombre' => 'Pausado', 'nfc_uid' => 'CHIP2']);
        Tarjeta::create(['user_id' => $pausado->id, 'nfc_uid' => 'CHIP2', 'activada_en' => now()->subYears(2), 'vence_en' => now()->subMonths(6)]);

        $respuesta = $this->actingAs($admin)->get('/admin')->assertOk();
        $m = $respuesta->viewData('m');

        $this->assertSame(1, $m['compras']);
        $this->assertSame(1, $m['enProceso']);
        $this->assertSame(1, $m['abandonados']);
        $this->assertSame(1, $m['declinados']);
        $this->assertSame(1, $m['activos']);
        $this->assertSame(1, $m['inactivos']);
        $this->assertSame(1, $m['sinUso']);
        $correos = fn (string $lista) => collect($this->actingAs($admin)->get('/admin?lista='.$lista)->assertOk()->viewData('filas'))->pluck('correo')->all();
        $this->assertSame([$pausado->email], $correos('sin_uso'));
        $this->assertSame([$activo->email], $correos('activos'));
        $this->assertSame([$pausado->email], $correos('inactivos'));
        $this->assertSame([$admin->email], $correos('sin_activar'));
        $this->actingAs($admin)->get('/admin/sin-uso')->assertOk()->assertSee($pausado->email)->assertDontSee($activo->email);
    }

    public function test_la_promocion_se_envia_una_sola_vez_por_correo(): void
    {
        Mail::fake();
        $admin = $this->admin();
        $admin->save();
        $viejo = $this->pedido(['creado' => now()->subDays(3)]);
        $pedido = $this->pedido(['creado' => now()->subDay()]);

        $this->actingAs($admin)->post("/admin/carritos/{$pedido->id}/enviar")->assertSessionHas('status');
        $this->actingAs($admin)->post("/admin/carritos/{$pedido->id}/enviar")->assertSessionHas('error');
        $this->actingAs($admin)->post('/admin/carritos/enviar-todos')->assertSessionHas('status', 'Promociones enviadas: 0.');

        Mail::assertSent(PromoCarrito::class, 1);
        Mail::assertSent(PromoCarrito::class, fn ($m) => $m->hasTo('ana@correo.com'));
        $this->assertNotNull($pedido->fresh()->promo_token);
        $this->assertNull($viejo->fresh()->promo_token);
    }

    public function test_el_enlace_de_la_promocion_aplica_el_descuento_y_trae_los_datos(): void
    {
        Mail::fake();
        Http::fake(['integrations.api.bold.co/*' => Http::response(['payload' => ['payment_link' => 'LNK_9', 'url' => 'https://checkout.bold.co/LNK_9'], 'errors' => []])]);
        $admin = $this->admin();
        $admin->save();
        $pedido = $this->pedido(['creado' => now()->subDay(), 'ciudad' => 'Envigado']);
        $this->actingAs($admin)->post("/admin/carritos/{$pedido->id}/enviar");
        $token = $pedido->fresh()->promo_token;
        auth()->logout();

        // 10 % por defecto: $299.900 → $269.900
        $this->get('/comprar?promo='.$token)->assertOk()->assertSee('Pagar $269.900 COP')->assertSee('Envigado');
        $this->get('/comprar?promo='.str_repeat('x', 40))->assertOk()->assertSee('Pagar $299.900 COP');

        $this->post('/comprar', [
            'nombre' => 'Ana Gómez', 'correo' => 'ana@correo.com', 'celular' => '3001234567', 'departamento' => 'Antioquia',
            'ciudad' => 'Envigado', 'direccion' => 'Calle 1', 'acabado' => 'crystal', 'promo' => $token,
        ])->assertRedirect('https://checkout.bold.co/LNK_9');

        $nuevo = Pedido::latest('id')->first();
        $this->assertEquals(269900, $nuevo->monto);
        $this->assertSame($pedido->id, $nuevo->promo_de_pedido_id);
        Http::assertSent(fn ($r) => $r['amount']['total_amount'] === 269900);

        // Vencida (más de 7 días): precio normal
        $pedido->forceFill(['promo_enviada_en' => now()->subDays(8)])->save();
        $this->get('/comprar?promo='.$token)->assertOk()->assertSee('Pagar $299.900 COP');
    }
}
