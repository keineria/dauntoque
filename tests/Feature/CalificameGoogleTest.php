<?php

namespace Tests\Feature;

use App\Models\CalificameAjuste;
use App\Models\Profile;
use App\Models\User;
use App\Services\Calificame\Calificame;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CalificameGoogleTest extends TestCase
{
    use RefreshDatabase;

    private User $dueno;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config(['services.google_places.key' => 'llave-places']);
        $this->dueno = User::factory()->create();
        Profile::create(['user_id' => $this->dueno->id, 'nickname' => 'barberia', 'nombre' => 'Palacio Barbershop', 'pais' => 'CO']);
        Calificame::activar($this->dueno->id);
    }

    private float $rating = 4.8;

    private function fakeGoogle(): void
    {
        Http::fake([
            'places.googleapis.com/v1/places:searchText' => Http::response(['places' => [
                ['id' => 'ChIJpalacio', 'displayName' => ['text' => 'Palacio Barbershop'], 'formattedAddress' => 'Cl. 10 #43, Medellín', 'rating' => 4.8, 'userRatingCount' => 120],
            ]]),
            'places.googleapis.com/v1/places/*' => fn () => Http::response([
                'id' => 'ChIJpalacio', 'displayName' => ['text' => 'Palacio Barbershop'], 'formattedAddress' => 'Cl. 10 #43, Medellín',
                'rating' => $this->rating, 'userRatingCount' => 120, 'googleMapsUri' => 'https://maps.google.com/?cid=123',
                'reviews' => [
                    ['rating' => 5, 'text' => ['text' => 'Los mejores cortes de Medellín'], 'authorAttribution' => ['displayName' => 'Laura M.'], 'relativePublishTimeDescription' => 'hace 2 semanas'],
                    ['rating' => 4, 'authorAttribution' => ['displayName' => 'Sin texto']],
                ],
            ]),
        ]);
    }

    private function datosPerfil(): ?array
    {
        return $this->get('/barberia')->assertOk()->viewData('datos')['calificame'];
    }

    public function test_el_duenio_busca_su_negocio_y_lo_conecta(): void
    {
        $this->fakeGoogle();

        $this->actingAs($this->dueno)->getJson('/dashboard/calificame/google/buscar?q=Palacio Barbershop Medellín')->assertOk()
            ->assertJson(['negocios' => [['id' => 'ChIJpalacio', 'nombre' => 'Palacio Barbershop', 'rating' => 4.8, 'total' => 120]]]);
        Http::assertSent(fn ($r) => str_ends_with($r->url(), 'places:searchText') && $r->hasHeader('X-Goog-Api-Key', 'llave-places')
            && $r['textQuery'] === 'Palacio Barbershop Medellín' && $r['regionCode'] === 'CO');

        $this->post('/dashboard/calificame/google', ['place_id' => 'ChIJpalacio'])->assertRedirect('/dashboard/calificame');

        $ajustes = CalificameAjuste::de($this->dueno->id);
        $this->assertSame('Palacio Barbershop', $ajustes->google_nombre);
        $this->assertSame(4.8, $ajustes->google_rating);
        $this->assertSame(120, $ajustes->google_total);
        $this->assertStringContainsString('writereview?placeid=ChIJpalacio', $ajustes->google_url);
        $this->get('/dashboard/calificame')->assertSee('Conectado')->assertSee('120 reseñas en Google');
    }

    public function test_el_perfil_muestra_la_calificacion_de_google_aparte_y_solo_resenas_con_texto(): void
    {
        $this->fakeGoogle();
        $this->actingAs($this->dueno)->post('/dashboard/calificame/google', ['place_id' => 'ChIJpalacio']);

        $google = $this->datosPerfil()['google'];
        $this->assertSame(4.8, $google['promedio']);
        $this->assertSame(120, $google['total']);
        $this->assertSame(['Laura M.'], array_column($google['resenas'], 'autor'));
        $this->assertSame('https://maps.google.com/?cid=123', $google['enlace']);
        $this->assertFalse($this->datosPerfil()['sello'], 'Google no se mezcla con el sello de Califícame');
    }

    public function test_el_duenio_puede_ocultarla_o_desconectarla(): void
    {
        $this->fakeGoogle();
        $this->actingAs($this->dueno)->post('/dashboard/calificame/google', ['place_id' => 'ChIJpalacio']);

        $this->post('/dashboard/calificame/ajustes', ['mostrar_boton' => 1]);
        $this->assertNull($this->datosPerfil()['google']);

        $this->post('/dashboard/calificame/ajustes', ['mostrar_boton' => 1, 'mostrar_google' => 1]);
        $this->assertNotNull($this->datosPerfil()['google']);

        $this->delete('/dashboard/calificame/google')->assertRedirect('/dashboard/calificame');
        $ajustes = CalificameAjuste::de($this->dueno->id);
        $this->assertNull($ajustes->google_place_id);
        $this->assertNull($ajustes->google_url, 'El enlace de reseñas que pusimos solos se quita con el negocio');
        $this->assertNull($this->datosPerfil()['google']);
    }

    public function test_se_actualiza_una_vez_al_dia(): void
    {
        $this->fakeGoogle();
        $this->actingAs($this->dueno)->post('/dashboard/calificame/google', ['place_id' => 'ChIJpalacio']);

        $this->rating = 4.9;
        $this->artisan('calificame:google')->assertSuccessful();
        $this->assertSame(4.8, CalificameAjuste::de($this->dueno->id)->google_rating, 'Recién actualizado: no vuelve a pedir');

        $this->travel(21)->hours();
        $this->artisan('calificame:google')->assertSuccessful();
        $this->assertSame(4.9, CalificameAjuste::de($this->dueno->id)->google_rating);
    }

    public function test_si_google_falla_avisa_sin_romper(): void
    {
        Http::fake(['places.googleapis.com/*' => Http::response(['error' => ['message' => 'API key not valid']], 400)]);

        $this->actingAs($this->dueno)->getJson('/dashboard/calificame/google/buscar?q=Palacio')->assertStatus(502);
        $this->post('/dashboard/calificame/google', ['place_id' => 'ChIJpalacio'])->assertSessionHas('status', fn ($s) => str_contains($s, 'No pudimos'));
        $this->assertNull(CalificameAjuste::de($this->dueno->id)->google_place_id);
    }

    public function test_sin_llave_de_google_la_opcion_sale_como_proximamente(): void
    {
        config(['services.google_places.key' => null]);

        $this->actingAs($this->dueno)->get('/dashboard/calificame')->assertSee('Muy pronto podrás conectar tu negocio de Google');
        $this->getJson('/dashboard/calificame/google/buscar?q=Palacio')->assertNotFound();
    }
}
