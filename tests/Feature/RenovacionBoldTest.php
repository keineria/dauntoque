<?php

namespace Tests\Feature;

use App\Models\Pago;
use App\Models\Profile;
use App\Models\Tarjeta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RenovacionBoldTest extends TestCase
{
    use RefreshDatabase;

    private User $usuario;
    private Tarjeta $tarjeta;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.bold.api_key' => 'llave-identidad', 'services.bold.secret' => 'secreta']);
        Carbon::setTestNow('2027-10-01 10:00:00');

        $this->usuario = User::factory()->create();
        Profile::create(['user_id' => $this->usuario->id, 'nickname' => 'kathe', 'nombre' => 'Kathe', 'pais' => 'CO', 'nfc_uid' => 'chip-abc123']);
        $this->tarjeta = Tarjeta::create([
            'user_id' => $this->usuario->id, 'nfc_uid' => 'chip-abc123',
            'activada_en' => '2026-10-14', 'vence_en' => '2027-10-14',
        ]);
    }

    public function test_renovar_crea_el_link_de_bold_con_el_precio_del_pais(): void
    {
        Http::fake(['integrations.api.bold.co/*' => Http::response(['payload' => ['payment_link' => 'LNK_123', 'url' => 'https://checkout.bold.co/LNK_123'], 'errors' => []])]);

        $this->actingAs($this->usuario)->post('/renovar/' . $this->tarjeta->id)
            ->assertRedirect('https://checkout.bold.co/LNK_123');

        Http::assertSent(fn ($r) => $r->hasHeader('Authorization', 'x-api-key llave-identidad')
            && $r['amount']['total_amount'] == 120000 && $r['amount']['currency'] === 'COP' && $r['amount_type'] === 'CLOSE');
        $this->assertDatabaseHas('pagos', ['link_id' => 'LNK_123', 'estado' => 'pendiente', 'moneda' => 'COP']);
    }

    public function test_no_se_puede_renovar_la_tarjeta_de_otro(): void
    {
        Http::fake();
        $this->actingAs(User::factory()->create())->post('/renovar/' . $this->tarjeta->id)->assertNotFound();
        Http::assertNothingSent();
    }

    public function test_el_webhook_aprobado_suma_un_anio_una_sola_vez(): void
    {
        $this->pagoPendiente();
        $cuerpo = $this->evento('SALE_APPROVED');

        $this->webhook($cuerpo)->assertOk();
        $this->webhook($cuerpo)->assertOk(); // Bold puede repetir el aviso

        $this->assertSame('2028-10-13', $this->tarjeta->fresh()->vence_en->toDateString());
        $this->assertDatabaseHas('pagos', ['link_id' => 'LNK_123', 'estado' => 'pagado', 'pago_proveedor_id' => 'PAY_9']);
    }

    public function test_renovar_vencida_cuenta_desde_hoy(): void
    {
        $this->tarjeta->update(['vence_en' => '2027-09-01']);
        $this->pagoPendiente();

        $this->webhook($this->evento('SALE_APPROVED'))->assertOk();

        $this->assertSame('2028-09-30', $this->tarjeta->fresh()->vence_en->toDateString());
    }

    public function test_el_webhook_con_firma_falsa_no_hace_nada(): void
    {
        $this->pagoPendiente();

        $this->call('POST', '/webhooks/bold', [], [], [], ['HTTP_X_BOLD_SIGNATURE' => 'falsa', 'CONTENT_TYPE' => 'application/json'], $this->evento('SALE_APPROVED'))
            ->assertStatus(401);

        $this->assertSame('2027-10-14', $this->tarjeta->fresh()->vence_en->toDateString());
    }

    private function pagoPendiente(): void
    {
        Pago::create(['user_id' => $this->usuario->id, 'tarjeta_id' => $this->tarjeta->id, 'concepto' => 'renovacion', 'proveedor' => 'bold',
            'pais' => 'CO', 'moneda' => 'COP', 'monto' => 120000, 'link_id' => 'LNK_123']);
    }

    private function evento(string $tipo): string
    {
        return json_encode(['id' => 'evt-1', 'type' => $tipo, 'data' => ['payment_id' => 'PAY_9', 'metadata' => ['reference' => 'LNK_123'], 'amount' => ['currency' => 'COP', 'total' => 120000]]]);
    }

    private function webhook(string $cuerpo)
    {
        $firma = hash_hmac('sha256', base64_encode($cuerpo), 'secreta');

        return $this->call('POST', '/webhooks/bold', [], [], [], ['HTTP_X_BOLD_SIGNATURE' => $firma, 'CONTENT_TYPE' => 'application/json'], $cuerpo);
    }
}
