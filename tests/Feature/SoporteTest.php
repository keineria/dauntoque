<?php

namespace Tests\Feature;

use App\Models\SoporteConversacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Ayuda: tickets y chat en vivo con cola por orden de llegada. */
class SoporteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function pedirChat(User $cliente, string $asunto = 'No abre mi tarjeta'): SoporteConversacion
    {
        $this->actingAs($cliente)->post('/dashboard/ayuda', ['tipo' => 'chat', 'asunto' => $asunto, 'mensaje' => 'Hola, necesito ayuda'])->assertRedirect();

        return SoporteConversacion::where('user_id', $cliente->id)->latest('id')->first();
    }

    public function test_los_chats_esperan_en_cola_y_se_atienden_por_orden_de_llegada(): void
    {
        [$ana, $beto] = User::factory()->count(2)->create();
        $admin = User::factory()->create()->forceFill(['es_admin' => true]);
        $admin->save();

        $primero = $this->pedirChat($ana);
        $this->travel(1)->minutes();
        $segundo = $this->pedirChat($beto);

        $this->assertSame('en_cola', $primero->estado);
        $this->actingAs($beto)->getJson("/dashboard/ayuda/{$segundo->id}/mensajes")->assertJson(['estado' => 'en_cola', 'puesto' => 2]);

        // El agente toma al que más lleva esperando
        $this->actingAs($admin)->post('/admin/soporte/tomar')->assertRedirect("/admin/soporte/{$primero->id}");
        $this->assertSame('abierta', $primero->fresh()->estado);
        $this->assertSame($admin->id, $primero->fresh()->agente_id);
        $this->actingAs($beto)->getJson("/dashboard/ayuda/{$segundo->id}/mensajes")->assertJson(['puesto' => 1]);

        // Conversación: el agente responde y el cliente recibe solo lo nuevo
        $this->actingAs($admin)->postJson("/admin/soporte/{$primero->id}/mensajes", ['texto' => '¡Hola Ana! Ya te ayudo'])->assertOk();
        $ultimo = $primero->mensajes()->min('id');
        $this->actingAs($ana)->getJson("/dashboard/ayuda/{$primero->id}/mensajes?desde=$ultimo")
            ->assertJsonCount(1, 'mensajes')->assertJsonPath('mensajes.0.texto', '¡Hola Ana! Ya te ayudo')->assertJsonPath('mensajes.0.agente', true);

        // Cerrada: ya no se escribe
        $this->actingAs($admin)->post("/admin/soporte/{$primero->id}/cerrar");
        $this->actingAs($ana)->postJson("/dashboard/ayuda/{$primero->id}/mensajes", ['texto' => 'otra cosa'])->assertStatus(422);
    }

    public function test_un_cliente_solo_ve_sus_conversaciones_y_tiene_un_chat_a_la_vez(): void
    {
        [$ana, $beto] = User::factory()->count(2)->create();
        $chat = $this->pedirChat($ana);

        $this->actingAs($beto)->get("/dashboard/ayuda/{$chat->id}")->assertNotFound();
        $this->actingAs($beto)->getJson("/dashboard/ayuda/{$chat->id}/mensajes")->assertNotFound();
        $this->actingAs($beto)->postJson("/admin/soporte/{$chat->id}/mensajes", ['texto' => 'x'])->assertNotFound();

        // Un segundo chat mientras el primero sigue abierto se suma al mismo
        $this->pedirChat($ana, 'Otra duda');
        $this->assertSame(1, SoporteConversacion::where('user_id', $ana->id)->count());
        $this->assertSame(2, $chat->mensajes()->count());
    }

    public function test_un_ticket_queda_abierto_sin_pasar_por_la_cola(): void
    {
        $ana = User::factory()->create();
        $this->actingAs($ana)->post('/dashboard/ayuda', ['tipo' => 'ticket', 'asunto' => 'Factura', 'mensaje' => 'Necesito la factura'])->assertRedirect();

        $ticket = SoporteConversacion::firstOrFail();
        $this->assertSame('abierta', $ticket->estado);
        $this->assertNull($ticket->puestoEnCola());
        $this->actingAs($ana)->get('/dashboard/ayuda')->assertOk()->assertSee('Factura');
    }
}
