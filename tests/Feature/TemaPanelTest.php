<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Temas de color del panel: se eligen en Mi cuenta, se guardan en la cuenta y pintan <html>. */
class TemaPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_por_defecto_el_panel_se_ve_como_siempre(): void
    {
        $usuario = User::factory()->create();

        $this->actingAs($usuario)->get('/dashboard/cuenta')
            ->assertOk()
            ->assertSee('data-paleta="dauntoque" data-apariencia="oscuro" data-modo="oscuro"', false)
            ->assertSee('Apariencia');
    }

    public function test_guarda_la_paleta_y_la_apariencia_con_fetch(): void
    {
        $usuario = User::factory()->create();

        $this->actingAs($usuario)->postJson('/dashboard/cuenta/apariencia', ['paleta' => 'papel', 'apariencia' => 'claro'])
            ->assertOk()->assertJson(['ok' => true]);

        $this->assertSame(['papel', 'claro'], [$usuario->fresh()->tema_panel, $usuario->fresh()->apariencia_panel]);
        $this->actingAs($usuario->fresh())->get('/dashboard/cuenta')
            ->assertSee('data-paleta="papel" data-apariencia="claro" data-modo="claro"', false);
    }

    public function test_sin_javascript_el_formulario_guarda_y_vuelve(): void
    {
        $usuario = User::factory()->create();

        $this->actingAs($usuario)->post('/dashboard/cuenta/apariencia', ['paleta' => 'salvia', 'apariencia' => 'auto'])
            ->assertRedirect('/dashboard/cuenta#apariencia');

        $this->assertSame('auto', $usuario->fresh()->apariencia_panel);
    }

    public function test_rechaza_paletas_que_no_existen(): void
    {
        $usuario = User::factory()->create();

        $this->actingAs($usuario)->postJson('/dashboard/cuenta/apariencia', ['paleta' => '"><script>', 'apariencia' => 'noche'])
            ->assertUnprocessable()->assertJsonValidationErrors(['paleta', 'apariencia']);

        $this->assertSame('dauntoque', $usuario->fresh()->tema_panel);
    }

    public function test_hace_falta_iniciar_sesion(): void
    {
        $this->post('/dashboard/cuenta/apariencia', ['paleta' => 'papel', 'apariencia' => 'claro'])->assertRedirect('/login');
    }
}
