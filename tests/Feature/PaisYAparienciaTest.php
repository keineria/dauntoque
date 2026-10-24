<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** País de pagos al crear el perfil (por la conexión o por sus otros perfiles) y apariencia del panel. */
class PaisYAparienciaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function crear(User $usuario, string $nickname, array $extra = [], array $encabezados = [])
    {
        return $this->actingAs($usuario)->withHeaders($encabezados)->postJson('/perfil/guardar', $extra + [
            'perfil_id' => 'null', 'nickname' => $nickname, 'nombre' => 'Ana', 'tipo_perfil' => 'corporativo',
        ]);
    }

    public function test_el_editor_sugiere_el_pais_de_la_conexion(): void
    {
        $usuario = User::factory()->create();

        $this->actingAs($usuario)->withHeaders(['CF-IPCountry' => 'US'])->get('/editor')
            ->assertOk()->assertViewHas('paisSugerido', 'US');
        // Un país sin medios de pago no se sugiere: el editor usa la zona horaria del navegador
        $this->actingAs($usuario)->withHeaders(['CF-IPCountry' => 'MX'])->get('/editor')
            ->assertOk()->assertViewHas('paisSugerido', null);
    }

    public function test_el_segundo_perfil_toma_el_pais_del_primero(): void
    {
        $usuario = User::factory()->create();
        $this->crear($usuario, 'ana', ['pais' => 'US'])->assertOk();

        $this->actingAs($usuario)->withHeaders(['CF-IPCountry' => 'CO'])->get('/editor')
            ->assertViewHas('paisSugerido', 'US');
    }

    public function test_sin_pais_en_la_peticion_se_usa_la_conexion(): void
    {
        $usuario = User::factory()->create();
        $this->crear($usuario, 'ana', [], ['CF-IPCountry' => 'US'])->assertOk();

        $this->assertSame('US', Profile::where('nickname', 'ana')->value('pais'));
    }

    public function test_cambiar_el_pais_deja_solo_los_medios_de_ese_pais(): void
    {
        $usuario = User::factory()->create();
        $this->crear($usuario, 'ana', ['pais' => 'CO', 'payment_methods' => json_encode([['type' => 'nequi', 'value' => '3001234567']])])->assertOk();
        $perfil = Profile::where('nickname', 'ana')->first();

        $this->actingAs($usuario)->postJson('/perfil/guardar', [
            'perfil_id' => $perfil->id, 'pais' => 'US',
            'payment_methods' => json_encode([['type' => 'nequi', 'value' => '3001234567'], ['type' => 'zelle', 'value' => 'ana@correo.com']]),
        ])->assertOk();

        $perfil->refresh();
        $this->assertSame('US', $perfil->pais);
        $this->assertSame([['type' => 'zelle', 'value' => 'ana@correo.com']], $perfil->payment_methods);
    }

    public function test_guardar_la_apariencia_del_panel(): void
    {
        $usuario = User::factory()->create();

        // Sin elegir: DaUnToque oscuro, como siempre
        $this->actingAs($usuario)->get('/dashboard/cuenta')->assertOk()
            ->assertSee('data-paleta', false)->assertSee('"dauntoque", "oscuro"', false);

        $this->actingAs($usuario)->postJson('/dashboard/cuenta/apariencia', ['paleta' => 'papel', 'modo' => 'auto'])
            ->assertOk()->assertJson(['status' => 'success']);
        $this->assertSame(['papel', 'auto'], [$usuario->fresh()->panel_paleta, $usuario->fresh()->panel_modo]);

        $this->actingAs($usuario)->get('/dashboard')->assertSee('"papel", "auto"', false);

        $this->actingAs($usuario)->postJson('/dashboard/cuenta/apariencia', ['paleta' => 'neon', 'modo' => 'claro'])
            ->assertStatus(422);
        $this->assertSame('papel', $usuario->fresh()->panel_paleta);
    }
}
