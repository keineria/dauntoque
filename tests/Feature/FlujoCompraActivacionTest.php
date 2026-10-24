<?php

namespace Tests\Feature;

use App\Mail\PedidoConfirmado;
use App\Models\Pago;
use App\Models\Pedido;
use App\Models\Profile;
use App\Models\Tarjeta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/** Recorrido completo: compra con Bold → pago confirmado → código → cuenta → tarjeta activa 365 días → renovación. */
class FlujoCompraActivacionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.bold.api_key' => 'llave-identidad', 'services.bold.secret' => 'secreta', 'pedidos.aviso_correo' => 'ventas@dauntoque.com', 'chips.validar' => true]);
        Mail::fake();
    }

    public function test_de_la_compra_a_la_tarjeta_activa_y_renovada(): void
    {
        // 1. Compra en /comprar: se crea el pedido y se va al checkout de Bold
        Http::fake(['integrations.api.bold.co/*' => Http::sequence()
            ->push(['payload' => ['payment_link' => 'LNK_COMPRA', 'url' => 'https://checkout.bold.co/LNK_COMPRA'], 'errors' => []])
            ->push(['payload' => ['payment_link' => 'LNK_RENUEVA', 'url' => 'https://checkout.bold.co/LNK_RENUEVA'], 'errors' => []])]);
        $this->post('/comprar', [
            'nombre' => 'Ana Gómez', 'correo' => 'Ana@Correo.co', 'celular' => '3001234567', 'departamento' => 'Antioquia',
            'ciudad' => 'Medellín', 'direccion' => 'Calle 10 # 43-12', 'acabado' => 'matte-black',
        ])->assertRedirect('https://checkout.bold.co/LNK_COMPRA');
        $pedido = Pedido::firstOrFail();

        // 2. Bold confirma el pago (dos veces): un solo código y un solo correo
        $this->webhook('SALE_APPROVED', 'LNK_COMPRA')->assertOk();
        $this->webhook('SALE_APPROVED', 'LNK_COMPRA')->assertOk();
        $pedido->refresh();
        $this->assertSame('pagado', $pedido->estado);
        $codigo = $pedido->chip->codigo;
        Mail::assertSent(PedidoConfirmado::class, 1);
        $this->assertStringContainsString($codigo, (new PedidoConfirmado($pedido))->render());
        $this->get(route('pedido.ver', $pedido))->assertSee($codigo)->assertSee('Crear mi perfil');

        // 3. Crea su cuenta con el correo de la compra: el pedido queda a su nombre
        $this->post('/register', ['name' => 'Ana', 'email' => 'ana@correo.co', 'password' => 'clave-segura-123', 'password_confirmation' => 'clave-segura-123']);
        $usuario = User::where('email', 'ana@correo.co')->firstOrFail();
        $this->assertSame($usuario->id, $pedido->fresh()->user_id);

        // 4. Crea su perfil y escribe el código (en mayúsculas y sin guion): la tarjeta queda activa 365 días
        $perfil = Profile::create(['user_id' => $usuario->id, 'nickname' => 'ana', 'nombre' => 'Ana']);
        $this->actingAs($usuario)->post('/tarjeta/activar', ['codigo' => strtoupper(str_replace('-', '', $codigo))])
            ->assertRedirect('/dashboard')->assertSessionHas('nfc_success');
        $this->assertSame($codigo, $perfil->fresh()->nfc_uid);
        $tarjeta = Tarjeta::where('nfc_uid', $codigo)->firstOrFail();
        $this->assertSame($usuario->id, $tarjeta->user_id);
        $this->assertSame(365, (int) $tarjeta->activada_en->diffInDays($tarjeta->vence_en));

        // 5. Al acercar la tarjeta, abre su perfil público
        auth()->logout();
        $this->get('/activar/' . $codigo)->assertRedirect(Profile::urlPublica('ana'));

        // 6. Renueva con Bold: se suma un año al vencimiento
        $vence = $tarjeta->vence_en->copy();
        $this->actingAs($usuario)->post('/renovar/' . $tarjeta->id)->assertRedirect('https://checkout.bold.co/LNK_RENUEVA');
        $this->webhook('SALE_APPROVED', 'LNK_RENUEVA')->assertOk();
        $this->assertSame('pagado', Pago::firstOrFail()->estado);
        $this->assertTrue($tarjeta->fresh()->vence_en->equalTo($vence->addDays(365)));
    }

    public function test_si_el_correo_ya_tiene_cuenta_lleva_a_iniciar_sesion_y_no_a_registrarse(): void
    {
        $usuario = User::factory()->create(['email' => 'ana@correo.co']);
        Pedido::create([
            'nombre' => 'Ana Gómez', 'correo' => 'ana@correo.co', 'celular' => '3001234567', 'departamento' => 'Antioquia',
            'ciudad' => 'Medellín', 'direccion' => 'Calle 10', 'acabado' => 'crystal', 'pais' => 'CO', 'moneda' => 'COP',
            'monto' => 299900, 'link_id' => 'LNK_COMPRA',
        ]);

        $this->webhook('SALE_APPROVED', 'LNK_COMPRA')->assertOk();

        $pedido = Pedido::firstOrFail();
        $this->assertSame($usuario->id, $pedido->user_id);
        $this->assertSame(url('/login'), $pedido->enlaceRegistro());
        $this->get(route('pedido.ver', $pedido))->assertSee('Entrar a mi panel')->assertDontSee('Crear mi perfil');
        $this->assertStringContainsString('Entrar a mi panel', (new PedidoConfirmado($pedido))->render());
    }

    public function test_codigo_escrito_antes_de_tener_perfil_se_enlaza_al_guardar_el_perfil(): void
    {
        $usuario = User::factory()->create();
        $chip = \App\Models\Chip::create(['codigo' => 'abcd-efgh', 'origen' => 'sistema']);

        $this->actingAs($usuario)->post('/tarjeta/activar', ['codigo' => 'abcd-efgh'])->assertSessionHas('nfc_pendiente', 'abcd-efgh');
        Profile::create(['user_id' => $usuario->id, 'nickname' => 'nuevo', 'nombre' => 'Nuevo']);
        $this->get('/dashboard');

        $this->assertSame('abcd-efgh', Profile::where('user_id', $usuario->id)->value('nfc_uid'));
        $this->assertSame('codigo', Tarjeta::where('nfc_uid', $chip->codigo)->value('activacion_medio'));
    }

    private function webhook(string $tipo, string $link)
    {
        $cuerpo = json_encode(['id' => 'evt-' . $link, 'type' => $tipo, 'data' => ['payment_id' => 'PAY_' . $link, 'metadata' => ['reference' => $link]]]);

        return $this->call('POST', '/webhooks/bold', [], [], [], [
            'HTTP_X_BOLD_SIGNATURE' => hash_hmac('sha256', base64_encode($cuerpo), 'secreta'), 'CONTENT_TYPE' => 'application/json',
        ], $cuerpo);
    }
}
