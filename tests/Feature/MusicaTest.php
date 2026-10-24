<?php

namespace Tests\Feature;

use App\Models\MusicaAjuste;
use App\Models\Profile;
use App\Models\User;
use App\Services\Musica\Musica;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MusicaTest extends TestCase
{
    use RefreshDatabase;

    private const CANCION = 'https://open.spotify.com/intl-es/track/4uLU6hMCjMI75M1A2tKUQC?si=a1b2c3d4';
    private const LISTA = 'https://open.spotify.com/playlist/37i9dQZF1DXcBWIGoYBM5M?si=xyz';

    private User $dueno;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->dueno = User::factory()->create();
        Profile::create(['user_id' => $this->dueno->id, 'nickname' => 'djkeiner', 'nombre' => 'DJ Keiner', 'pais' => 'CO']);
    }

    private function datosPerfil(): ?array
    {
        return $this->get('/djkeiner')->assertOk()->viewData('datos')['musica'];
    }

    public function test_se_activa_desde_ver_apps_con_un_anio_gratis_y_sale_en_apps_activas(): void
    {
        $this->actingAs($this->dueno)->get('/dashboard/apps')->assertSee('Música');
        $this->post('/dashboard/musica/activar')->assertRedirect('/dashboard/musica');

        $app = Musica::app($this->dueno->id);
        $this->assertTrue($app->vigente());
        $this->assertSame(365, (int) round($app->activada_en->diffInDays($app->vence_en)));
        $this->get('/dashboard/musica')->assertOk()->assertSee('Canción destacada')->assertSee('href="/dashboard/musica"', false);
    }

    public function test_sin_activar_no_se_guarda_ni_sale_en_el_perfil(): void
    {
        $this->actingAs($this->dueno)->post('/dashboard/musica/ajustes', ['cancion' => self::CANCION])->assertForbidden();
        $this->assertNull($this->datosPerfil());
    }

    public function test_guarda_los_enlaces_limpios_y_el_perfil_muestra_el_reproductor_oficial(): void
    {
        Musica::activar($this->dueno->id);
        $this->actingAs($this->dueno)->post('/dashboard/musica/ajustes', [
            'titulo' => 'Mis sets', 'cancion' => self::CANCION, 'lista' => self::LISTA, 'mostrar' => '1',
        ])->assertRedirect('/dashboard/musica')->assertSessionHasNoErrors();

        $ajustes = MusicaAjuste::de($this->dueno->id);
        $this->assertSame('track/4uLU6hMCjMI75M1A2tKUQC', $ajustes->cancion);
        $this->assertSame('playlist/37i9dQZF1DXcBWIGoYBM5M', $ajustes->lista);

        $datos = $this->datosPerfil();
        $this->assertSame('Mis sets', $datos['titulo']);
        $this->assertSame('https://open.spotify.com/embed/track/4uLU6hMCjMI75M1A2tKUQC?utm_source=generator', $datos['cancion']['src']);
        $this->assertSame('https://open.spotify.com/embed/playlist/37i9dQZF1DXcBWIGoYBM5M?utm_source=generator', $datos['lista']['src']);
        $this->get('/djkeiner')->assertSee('pp-mu-player', false);
    }

    public function test_rechaza_enlaces_que_no_son_de_spotify_o_del_tipo_equivocado(): void
    {
        Musica::activar($this->dueno->id);
        $this->actingAs($this->dueno);

        $this->post('/dashboard/musica/ajustes', ['cancion' => 'https://evil.com/track/4uLU6hMCjMI75M1A2tKUQC'])->assertSessionHasErrors('cancion');
        $this->post('/dashboard/musica/ajustes', ['cancion' => self::LISTA])->assertSessionHasErrors('cancion');
        $this->post('/dashboard/musica/ajustes', ['lista' => 'https://open.spotify.com/user/abc'])->assertSessionHasErrors('lista');
        $this->post('/dashboard/musica/ajustes', ['lista' => 'spotify:album:1DFixLWuPkv3KT3TnV35m3'])->assertSessionHasNoErrors();
        $this->assertSame('album/1DFixLWuPkv3KT3TnV35m3', MusicaAjuste::de($this->dueno->id)->lista);
    }

    public function test_oculta_vacia_o_vencida_no_sale_en_el_perfil(): void
    {
        $app = Musica::activar($this->dueno->id);
        $this->assertNull($this->datosPerfil());

        MusicaAjuste::de($this->dueno->id)->update(['cancion' => 'track/4uLU6hMCjMI75M1A2tKUQC', 'mostrar' => false]);
        $this->assertNull($this->datosPerfil());

        MusicaAjuste::de($this->dueno->id)->update(['mostrar' => true]);
        $this->assertNotNull($this->datosPerfil());

        $app->update(['vence_en' => now()->subDay()]);
        $this->assertNull($this->datosPerfil());
    }
}
