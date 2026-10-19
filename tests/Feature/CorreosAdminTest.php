<?php

namespace Tests\Feature;

use App\Mail\CorreoPrueba;
use App\Mail\PedidoNuevo;
use App\Models\Pedido;
use App\Models\User;
use App\Support\Correos;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/** Administrador › Correos: remitente, correo de avisos, plantillas encendidas/apagadas y prueba de envío. */
class CorreosAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function admin(): User
    {
        $admin = User::factory()->create()->forceFill(['es_admin' => true]);
        $admin->save();

        return $admin;
    }

    /** Correos que de verdad salieron por el mailer "array". */
    private function enviados(): Collection
    {
        return app('mailer')->getSymfonyTransport()->messages();
    }

    public function test_guardar_cambia_el_remitente_y_apaga_plantillas(): void
    {
        $admin = $this->admin();
        $activas = array_values(array_diff(array_keys(Correos::PLANTILLAS), ['pedido_nuevo']));
        $this->actingAs($admin)->post('/admin/correos', [
            'correo_remitente' => 'hola@dauntoque.com', 'correo_remitente_nombre' => 'Equipo DaUnToque',
            'correo_avisos_pedidos' => 'pedidos@dauntoque.com', 'activas' => $activas,
        ])->assertSessionHas('status');

        $this->assertFalse(Correos::activa('pedido_nuevo'));
        $this->assertTrue(Correos::activa('pedido_confirmado'));

        Correos::aplicarAjustes();
        $pedido = Pedido::create(['nombre' => 'Ana', 'correo' => 'ana@correo.com', 'celular' => '300', 'departamento' => 'Antioquia', 'ciudad' => 'Medellín',
            'direccion' => 'Calle 1', 'acabado' => 'crystal', 'pais' => 'CO', 'moneda' => 'COP', 'monto' => 1]);
        Mail::to(config('pedidos.aviso_correo'))->send(new PedidoNuevo($pedido));
        Mail::to('ana@correo.com')->send(new CorreoPrueba);

        $this->assertCount(1, $this->enviados());
        $correo = $this->enviados()->first()->getOriginalMessage();
        $this->assertSame('hola@dauntoque.com', $correo->getFrom()[0]->getAddress());
        $this->assertSame('Equipo DaUnToque', $correo->getFrom()[0]->getName());
        $this->assertSame('pedidos@dauntoque.com', config('pedidos.aviso_correo'));
    }

    public function test_la_pantalla_no_muestra_la_contrasena_del_correo_y_la_prueba_se_envia(): void
    {
        config(['mail.mailers.smtp.username' => 'pedidos@dauntoque.com', 'mail.mailers.smtp.password' => 'SuperSecreta99']);
        $admin = $this->admin();

        $this->actingAs($admin)->get('/admin/correos')->assertOk()
            ->assertDontSee('SuperSecreta99')->assertSee('Usando el del servidor (.env)');

        Mail::fake();
        $this->actingAs($admin)->post('/admin/correos/prueba', ['para' => 'yo@correo.com'])->assertSessionHas('status');
        Mail::assertSent(CorreoPrueba::class, fn ($m) => $m->hasTo('yo@correo.com'));
    }
}
