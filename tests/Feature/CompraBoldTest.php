<?php

namespace Tests\Feature;

use App\Mail\PedidoConfirmado;
use App\Mail\PedidoNuevo;
use App\Models\Pedido;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CompraBoldTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.bold.api_key' => 'llave-identidad', 'services.bold.secret' => 'secreta', 'pedidos.aviso_correo' => 'ventas@dauntoque.com']);
    }

    private function datos(array $mas = []): array
    {
        return $mas + [
            'nombre' => 'Carlos Pérez', 'correo' => 'Carlos@Correo.com', 'celular' => '300 123 4567',
            'departamento' => 'Antioquia', 'ciudad' => 'Medellín', 'direccion' => 'Calle 10 # 43-12, El Poblado',
            'acabado' => 'crystal', 'nombre_tarjeta' => 'Carlos Constructor',
        ];
    }

    public function test_el_formulario_muestra_el_precio_de_colombia(): void
    {
        $this->get('/comprar?acabado=crystal')->assertOk()->assertSee('$299.900')->assertSee('Pagar $299.900 COP');
    }

    public function test_comprar_crea_el_pedido_y_lleva_al_checkout_de_bold(): void
    {
        Http::fake(['integrations.api.bold.co/*' => Http::response(['payload' => ['payment_link' => 'LNK_777', 'url' => 'https://checkout.bold.co/LNK_777'], 'errors' => []])]);

        $this->post('/comprar', $this->datos())->assertRedirect('https://checkout.bold.co/LNK_777');

        $pedido = Pedido::firstOrFail();
        $this->assertSame('DT-00001', $pedido->codigo);
        $this->assertSame('carlos@correo.com', $pedido->correo);
        $this->assertSame('LNK_777', $pedido->link_id);
        $this->assertSame('pendiente', $pedido->estado);
        Http::assertSent(fn ($r) => $r['amount']['total_amount'] === 299900 && $r['amount']['currency'] === 'COP'
            && $r['payer_email'] === 'carlos@correo.com' && $r['callback_url'] === route('pedido.ver', $pedido));
    }

    public function test_datos_invalidos_no_crean_pedido(): void
    {
        Http::fake();
        $this->post('/comprar', $this->datos(['departamento' => 'Narnia', 'correo' => 'no-es-correo']))->assertSessionHasErrors(['departamento', 'correo']);
        $this->assertSame(0, Pedido::count());
        Http::assertNothingSent();
    }

    public function test_pago_aprobado_envia_el_correo_con_el_enlace_una_sola_vez(): void
    {
        Mail::fake();
        $pedido = $this->pedido();
        $cuerpo = $this->evento('SALE_APPROVED');

        $this->webhook($cuerpo)->assertOk();
        $this->webhook($cuerpo)->assertOk(); // aviso repetido

        $pedido->refresh();
        $this->assertSame('pagado', $pedido->estado);
        $this->assertNotNull($pedido->correo_enviado_en);
        Mail::assertSent(PedidoConfirmado::class, 1);
        Mail::assertSent(PedidoConfirmado::class, fn ($m) => $m->hasTo('carlos@correo.com'));
        Mail::assertSent(PedidoNuevo::class, fn ($m) => $m->hasTo('ventas@dauntoque.com'));

        $html = (new PedidoConfirmado($pedido))->render();
        $this->assertStringContainsString(e($pedido->enlaceRegistro()), $html);
        $this->get(route('pedido.ver', $pedido))->assertOk()->assertSee('Crear mi perfil');
    }

    public function test_pago_rechazado_no_envia_correo(): void
    {
        Mail::fake();
        $pedido = $this->pedido();

        $this->webhook($this->evento('SALE_REJECTED'))->assertOk();

        $this->assertSame('rechazado', $pedido->fresh()->estado);
        Mail::assertNothingSent();
    }

    public function test_al_registrarse_con_el_correo_el_pedido_queda_en_su_cuenta(): void
    {
        $pedido = $this->pedido();

        $this->withoutVite()->get($pedido->enlaceRegistro())->assertOk()->assertSee('value="carlos@correo.com"', false);
        $this->post('/register', ['name' => 'Carlos', 'email' => 'carlos@correo.com', 'password' => 'clave-segura-123', 'password_confirmation' => 'clave-segura-123']);

        $this->assertNotNull($pedido->fresh()->user_id);
    }

    private function pedido(): Pedido
    {
        return Pedido::create($this->datos(['correo' => 'carlos@correo.com']) + ['pais' => 'CO', 'moneda' => 'COP', 'monto' => 299900, 'link_id' => 'LNK_777']);
    }

    private function evento(string $tipo): string
    {
        return json_encode(['id' => 'evt-1', 'type' => $tipo, 'data' => ['payment_id' => 'PAY_1', 'metadata' => ['reference' => 'LNK_777']]]);
    }

    private function webhook(string $cuerpo)
    {
        return $this->call('POST', '/webhooks/bold', [], [], [], [
            'HTTP_X_BOLD_SIGNATURE' => hash_hmac('sha256', base64_encode($cuerpo), 'secreta'), 'CONTENT_TYPE' => 'application/json',
        ], $cuerpo);
    }
}
