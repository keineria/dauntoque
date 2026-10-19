<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Tipografía elegida en el diseño del perfil (design_config.tipografia, config/tipografias.php). */
class TipografiasPerfilTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function perfilCon(array $diseno): Profile
    {
        $usuario = User::factory()->create();
        $this->actingAs($usuario)->postJson('/perfil/guardar', [
            'perfil_id' => 'null', 'nickname' => 'sofia', 'nombre' => 'Sofía Ríos', 'tipo_perfil' => 'corporativo',
            'design_config' => json_encode($diseno),
        ])->assertOk();

        return Profile::where('nickname', 'sofia')->first();
    }

    public function test_el_perfil_publico_carga_solo_la_tipografia_elegida(): void
    {
        $this->perfilCon(['tema_base' => 'onyx', 'tipografia' => 'playfair']);

        $this->get('/sofia')->assertOk()
            ->assertSee('family=Playfair+Display', false)
            ->assertDontSee('family=Poppins', false)
            ->assertSee('\u0022tipografia\u0022:\u0022playfair\u0022', false);
    }

    public function test_sin_tipografia_o_con_una_que_no_existe_queda_la_del_estilo(): void
    {
        $this->perfilCon(['tema_base' => 'onyx', 'tipografia' => 'comic-sans']);

        $respuesta = $this->get('/sofia')->assertOk()->assertSee('\u0022tipografia\u0022:\u0022\u0022', false);
        $this->assertStringNotContainsString('family=Playfair', $respuesta->getContent());
    }

    public function test_mis_perfiles_muestra_la_tipografia_guardada_y_la_lista(): void
    {
        $perfil = $this->perfilCon(['tipografia' => 'bebas']);

        $this->actingAs($perfil->user)->get('/dashboard/perfiles')->assertOk()
            ->assertSee('"tipografia":"bebas"', false)
            ->assertSee('Space Grotesk');
    }
}
