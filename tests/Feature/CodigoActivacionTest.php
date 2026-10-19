<?php

namespace Tests\Feature;

use App\Mail\PedidoConfirmado;
use App\Mail\PedidoNuevo;
use App\Models\Chip;
use App\Models\Pedido;
use App\Models\Profile;
use App\Models\Tarjeta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CodigoActivacionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.bold.api_key' => 'llave-identidad', 'services.bold.secret' => 'secreta', 'pedidos.aviso_correo' => 'ventas@dauntoque.com']);
    }

    private function usuario(bool $conPerfil = true, string $correo = 'cliente@correo.co'): User
    {
        $usuario = User::factory()->create(['email' => $correo]);
        if ($conPerfil) {
            Profile::create(['user_id' => $usuario->id, 'nickname' => 'cliente'.$usuario->id, 'nombre' => 'Cliente']);
        }

        return $usuario;
    }

    private function pagar(): Pedido
    {
        $pedido = Pedido::create([
            'nombre' => 'Carlos Pérez', 'correo' => 'carlos@correo.com', 'celular' => '3001234567', 'departamento' => 'Antioquia',
            'ciudad' => 'Medellín', 'direccion' => 'Calle 10', 'acabado' => 'crystal', 'pais' => 'CO', 'moneda' => 'COP',
            'monto' => 299900, 'link_id' => 'LNK_777',
        ]);
        $cuerpo = json_encode(['id' => 'evt-1', 'type' => 'SALE_APPROVED', 'data' => ['payment_id' => 'PAY_1', 'metadata' => ['reference' => 'LNK_777']]]);
        foreach ([1, 2] as $_) { // Bold puede repetir el aviso
            $this->call('POST', '/webhooks/bold', [], [], [], [
                'HTTP_X_BOLD_SIGNATURE' => hash_hmac('sha256', base64_encode($cuerpo), 'secreta'), 'CONTENT_TYPE' => 'application/json',
            ], $cuerpo)->assertOk();
        }

        return $pedido->fresh();
    }

    public function test_cada_compra_pagada_crea_un_codigo_y_lo_envia_por_correo(): void
    {
        Mail::fake();
        $pedido = $this->pagar();

        $this->assertSame(1, Chip::count());
        $chip = $pedido->chip;
        $this->assertMatchesRegularExpression('~^[a-hj-km-np-z2-9]{4}-[a-hj-km-np-z2-9]{4}$~', $chip->codigo);
        $this->assertStringContainsString('DT-00001', $chip->nota);

        Mail::assertSent(PedidoConfirmado::class, fn ($m) => str_contains($m->render(), $chip->codigo));
        Mail::assertSent(PedidoNuevo::class, fn ($m) => str_contains($m->render(), $chip->url()));
        $this->get(route('pedido.ver', $pedido))->assertSee($chip->codigo);
    }

    public function test_una_compra_rechazada_no_crea_codigo(): void
    {
        Mail::fake();
        Pedido::create(['nombre' => 'Ana', 'correo' => 'ana@correo.com', 'celular' => '300', 'departamento' => 'Antioquia', 'ciudad' => 'Medellín',
            'direccion' => 'Calle 1', 'acabado' => 'crystal', 'pais' => 'CO', 'moneda' => 'COP', 'monto' => 299900, 'link_id' => 'LNK_9']);
        $cuerpo = json_encode(['type' => 'SALE_REJECTED', 'data' => ['metadata' => ['reference' => 'LNK_9']]]);
        $this->call('POST', '/webhooks/bold', [], [], [], ['HTTP_X_BOLD_SIGNATURE' => hash_hmac('sha256', base64_encode($cuerpo), 'secreta')], $cuerpo);

        $this->assertSame(0, Chip::count());
    }

    public function test_el_cliente_activa_su_tarjeta_escribiendo_el_codigo(): void
    {
        $usuario = $this->usuario();
        Chip::create(['codigo' => 'k7mq-2xpa']);

        $this->actingAs($usuario)->get('/dashboard')->assertSee('Activa tu tarjeta');
        // Como lo escribiría a mano: mayúsculas, sin guion y con espacios
        $this->actingAs($usuario)->post('/tarjeta/activar', ['codigo' => ' K7MQ 2XPA '])
            ->assertRedirect('/dashboard')->assertSessionHas('nfc_success');

        $this->assertSame('k7mq-2xpa', Profile::where('user_id', $usuario->id)->value('nfc_uid'));
        $this->assertSame($usuario->id, Tarjeta::where('nfc_uid', 'k7mq-2xpa')->value('user_id'));
        $this->actingAs($usuario)->get('/dashboard')->assertDontSee('Activa tu tarjeta');
    }

    public function test_tambien_acepta_el_enlace_grabado_en_la_tarjeta(): void
    {
        $usuario = $this->usuario();
        Chip::create(['codigo' => 'abc123def456']);

        $this->actingAs($usuario)->post('/tarjeta/activar', ['codigo' => 'https://portal.dauntoque.com/activar/abc123def456'])->assertSessionHas('nfc_success');

        $this->assertSame('abc123def456', Profile::where('user_id', $usuario->id)->value('nfc_uid'));
    }

    public function test_un_codigo_que_no_existe_no_activa_nada(): void
    {
        $this->actingAs($this->usuario())->from('/dashboard')->post('/tarjeta/activar', ['codigo' => 'zzzz-zzzz'])
            ->assertRedirect('/dashboard')->assertSessionHasErrors('codigo');

        $this->assertDatabaseCount('tarjetas', 0);
    }

    public function test_no_se_puede_quitar_la_tarjeta_de_otra_cuenta(): void
    {
        $duena = $this->usuario(true, 'duena@correo.co');
        Chip::create(['codigo' => 'k7mq-2xpa']);
        Tarjeta::vincular(Profile::activoDe($duena->id), 'k7mq-2xpa');

        $this->actingAs($this->usuario())->from('/dashboard')->post('/tarjeta/activar', ['codigo' => 'k7mq-2xpa'])->assertSessionHasErrors('codigo');

        $this->assertSame($duena->id, Tarjeta::where('nfc_uid', 'k7mq-2xpa')->value('user_id'));
    }

    public function test_sin_perfil_el_codigo_queda_guardado_y_se_enlaza_al_crearlo(): void
    {
        $usuario = $this->usuario(false);
        Chip::create(['codigo' => 'k7mq-2xpa']);

        $this->actingAs($usuario)->post('/tarjeta/activar', ['codigo' => 'k7mq-2xpa'])->assertRedirect('/dashboard')->assertSessionHas('nfc_pendiente', 'k7mq-2xpa');
        $this->actingAs($usuario)->get('/dashboard')->assertSee('Crea tu perfil y queda activa');

        Profile::create(['user_id' => $usuario->id, 'nickname' => 'nuevo', 'nombre' => 'Nuevo']);
        $this->actingAs($usuario)->get('/dashboard')->assertOk()->assertSee('¡Tarjeta activada!');

        $this->assertSame('k7mq-2xpa', Profile::where('user_id', $usuario->id)->value('nfc_uid'));
        $this->assertNull(session('nfc_pendiente'));
    }

    public function test_tarjeta_acercada_antes_de_iniciar_sesion_se_enlaza_al_entrar(): void
    {
        $usuario = $this->usuario();
        Chip::create(['codigo' => 'abc123def456']);

        $this->get('/activar/abc123def456')->assertRedirect('/register');
        $this->post('/login', ['email' => $usuario->email, 'password' => 'password'])->assertRedirect('/dashboard');
        $this->get('/dashboard')->assertOk()->assertSee('¡Tarjeta activada!');

        $this->assertSame('abc123def456', Profile::where('user_id', $usuario->id)->value('nfc_uid'));
    }

    public function test_los_codigos_creados_a_mano_tambien_son_faciles_de_escribir(): void
    {
        config(['chips.admins' => ['dueno@dauntoque.com']]);

        $this->actingAs($this->usuario(true, 'dueno@dauntoque.com'))->post('/admin/chips/generar', ['cantidad' => 2, 'nota' => 'Feria'])->assertRedirect();

        $this->assertSame(2, Chip::where('codigo', 'like', '____-____')->where('nota', 'Feria')->count());
    }
}
