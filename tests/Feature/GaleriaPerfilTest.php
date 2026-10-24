<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\User;
use App\Support\Galeria;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Galería del perfil: cada foto con el nombre del producto o servicio ([{url, titulo}]). */
class GaleriaPerfilTest extends TestCase
{
    use RefreshDatabase;

    private array $creadas = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    protected function tearDown(): void
    {
        foreach ($this->creadas as $ruta) {
            @unlink(public_path(ltrim($ruta, '/')));
        }
        parent::tearDown();
    }

    private function png(): string
    {
        $img = imagecreatetruecolor(4, 4);
        ob_start();
        imagepng($img);

        return 'data:image/png;base64,' . base64_encode(ob_get_clean());
    }

    private function guardar(User $usuario, array $galeria, $perfilId = 'null'): Profile
    {
        $this->actingAs($usuario)->postJson('/perfil/guardar', [
            'perfil_id' => $perfilId, 'nickname' => 'sofia', 'nombre' => 'Sofía Ríos', 'tipo_perfil' => 'corporativo',
            'galeria' => json_encode($galeria),
        ])->assertOk();

        $perfil = Profile::where('nickname', 'sofia')->first();
        $this->creadas = array_merge($this->creadas, Galeria::rutas($perfil->galeria));

        return $perfil;
    }

    public function test_guarda_cada_foto_con_su_titulo_y_lo_muestra_en_la_tarjeta(): void
    {
        $perfil = $this->guardar(User::factory()->create(), [
            ['url' => $this->png(), 'titulo' => '  Torta de <b>chocolate</b>  '],
            ['url' => $this->png(), 'titulo' => ''],
        ]);

        $this->assertCount(2, $perfil->galeria);
        $this->assertStringStartsWith('/uploads/galeria_', $perfil->galeria[0]['url']);
        $this->assertSame('Torta de chocolate', $perfil->galeria[0]['titulo']);
        $this->assertSame('', $perfil->galeria[1]['titulo']);

        $this->get('/sofia')->assertOk()->assertSee('Torta de chocolate');
    }

    public function test_al_editar_conserva_las_fotos_y_cambia_el_titulo(): void
    {
        $usuario = User::factory()->create();
        $perfil = $this->guardar($usuario, [['url' => $this->png(), 'titulo' => 'Uno']]);
        $ruta = $perfil->galeria[0]['url'];

        $perfil = $this->guardar($usuario, [['url' => $ruta, 'titulo' => str_repeat('a', 80)]], $perfil->id);

        $this->assertSame($ruta, $perfil->galeria[0]['url']);
        $this->assertSame(Galeria::MAX_TITULO, mb_strlen($perfil->galeria[0]['titulo']));
    }

    public function test_las_galerias_viejas_solo_con_rutas_se_siguen_leyendo(): void
    {
        $this->assertSame(
            [['url' => '/uploads/a.jpg', 'titulo' => '']],
            Galeria::normalizar(['/uploads/a.jpg', null, ['titulo' => 'sin foto']])
        );
    }

    public function test_no_acepta_rutas_ajenas(): void
    {
        $perfil = $this->guardar(User::factory()->create(), [['url' => 'https://otro.sitio/foto.jpg', 'titulo' => 'x']]);

        $this->assertSame([], $perfil->galeria);
    }
}
