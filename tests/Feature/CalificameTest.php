<?php

namespace Tests\Feature;

use App\Mail\CalificameCorreo;
use App\Models\AgendaServicio;
use App\Models\Calificacion;
use App\Models\CalificameAjuste;
use App\Models\Cita;
use App\Models\Profile;
use App\Models\User;
use App\Services\Agenda\Agenda;
use App\Services\Calificame\Calificame;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CalificameTest extends TestCase
{
    use RefreshDatabase;

    private User $dueno;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Mail::fake();
        $this->withHeader('Accept-Language', 'es-CO,es;q=0.9');
        $this->dueno = User::factory()->create(['email' => 'camilo@barberia.co']);
        Profile::create(['user_id' => $this->dueno->id, 'nickname' => 'barberia', 'nombre' => 'Palacio Barbershop', 'pais' => 'CO']);
    }

    private function calificar(array $datos)
    {
        return $this->post('/barberia/califica', $datos + ['lang' => 'es']);
    }

    private function datosPerfil(): ?array
    {
        return $this->get('/barberia')->assertOk()->viewData('datos')['calificame'];
    }

    public function test_se_activa_desde_ver_apps_con_un_anio_gratis(): void
    {
        $this->actingAs($this->dueno)->get('/dashboard/apps')->assertSee('Califícame');
        $this->post('/dashboard/calificame/activar')->assertRedirect('/dashboard/calificame');

        $app = Calificame::app($this->dueno->id);
        $this->assertTrue($app->vigente());
        $this->assertSame(365, (int) round($app->activada_en->diffInDays($app->vence_en)));
        $this->get('/dashboard/calificame')->assertOk()->assertSee('barberia/califica');
    }

    public function test_sin_activar_la_pagina_esta_cerrada_y_el_perfil_no_muestra_nada(): void
    {
        $this->get('/barberia/califica')->assertOk()->assertSee('no está activa');
        $this->calificar(['estrellas' => 5])->assertNotFound();
        $this->assertNull($this->datosPerfil());
    }

    public function test_el_cliente_califica_y_el_sello_sale_desde_tres_calificaciones(): void
    {
        Calificame::activar($this->dueno->id);
        $this->get('/barberia/califica')->assertOk()->assertSee('¿Cómo te atendió Palacio Barbershop?');

        $this->calificar(['estrellas' => 5, 'comentario' => 'Excelente corte', 'nombre' => 'Ana'])
            ->assertRedirect('/barberia/califica?lang=es')->assertSessionHas('calificame_gracias', 'buena');
        $this->assertFalse($this->datosPerfil()['sello']);

        // Otras dos personas (cada navegador califica una vez cada 12 horas)
        foreach ([4, 5] as $estrellas) {
            $this->flushSession();
            $this->calificar(['estrellas' => $estrellas]);
        }

        $datos = $this->datosPerfil();
        $this->assertTrue($datos['sello']);
        $this->assertSame(4.7, $datos['promedio']);
        $this->assertSame(3, $datos['total']);
        $this->assertSame([], $datos['comentarios'], 'Ningún comentario sale hasta que el dueño lo elija');
        $this->assertStringEndsWith('/barberia/califica', $datos['enlace']);
    }

    public function test_el_mismo_navegador_no_puede_calificar_dos_veces_seguidas(): void
    {
        Calificame::activar($this->dueno->id);
        $this->calificar(['estrellas' => 1]);
        $this->calificar(['estrellas' => 1])->assertSessionHas('calificame_gracias', 'repetida');

        $this->assertSame(1, Calificacion::count());
    }

    public function test_sin_estrellas_vuelve_con_el_error_a_la_direccion_relativa(): void
    {
        Calificame::activar($this->dueno->id);
        $respuesta = $this->calificar(['comentario' => 'Hola']);

        $this->assertSame('/barberia/califica?lang=es', $respuesta->headers->get('Location'));
        $respuesta->assertSessionHasErrors('estrellas');
        $this->assertSame(0, Calificacion::count());
    }

    public function test_la_calificacion_baja_le_llega_en_privado_al_duenio_y_nunca_se_puede_mostrar(): void
    {
        Calificame::activar($this->dueno->id);
        $this->calificar(['estrellas' => 2, 'comentario' => 'Me hicieron esperar', 'contacto' => '3109876543'])
            ->assertSessionHas('calificame_gracias', 'baja');

        $baja = Calificacion::firstOrFail();
        Mail::assertSent(CalificameCorreo::class, fn ($m) => $m->tipo === 'baja' && $m->hasTo('camilo@barberia.co'));
        $this->assertStringContainsString('Me hicieron esperar', (new CalificameCorreo('baja', $baja))->render());

        $this->actingAs($this->dueno)->postJson("/dashboard/calificame/{$baja->id}/visible", ['visible' => true])->assertStatus(422);
        $this->get('/dashboard/calificame')->assertSee('Privada')->assertSee('Responder por WhatsApp');
        $this->assertFalse($baja->fresh()->visible);
    }

    public function test_el_duenio_elige_que_comentarios_se_ven_o_ninguno(): void
    {
        Calificame::activar($this->dueno->id);
        $buena = Calificacion::create(['user_id' => $this->dueno->id, 'estrellas' => 5, 'comentario' => 'Muy puntual', 'nombre' => 'Ana']);
        $otra = Calificacion::create(['user_id' => $this->dueno->id, 'estrellas' => 4, 'comentario' => 'Buen servicio']);

        $this->actingAs($this->dueno)->postJson("/dashboard/calificame/{$buena->id}/visible", ['visible' => true])->assertOk()->assertJson(['visible' => true]);
        $comentarios = $this->datosPerfil()['comentarios'];
        $this->assertSame([['nombre' => 'Ana', 'estrellas' => 5, 'texto' => 'Muy puntual']], $comentarios);

        // Lo vuelve a ocultar: no se ve ninguno
        $this->postJson("/dashboard/calificame/{$buena->id}/visible", ['visible' => false])->assertOk();
        $this->assertSame([], $this->datosPerfil()['comentarios']);
        $this->assertFalse($otra->fresh()->visible);
    }

    public function test_no_puede_cambiar_las_calificaciones_de_otro(): void
    {
        $ajena = Calificacion::create(['user_id' => $this->dueno->id, 'estrellas' => 5, 'comentario' => 'Top']);
        $otro = User::factory()->create();

        $this->actingAs($otro)->postJson("/dashboard/calificame/{$ajena->id}/visible", ['visible' => true])->assertNotFound();
    }

    public function test_los_ajustes_apagan_el_sello_y_el_boton(): void
    {
        Calificame::activar($this->dueno->id);
        foreach ([5, 5, 5] as $e) {
            Calificacion::create(['user_id' => $this->dueno->id, 'estrellas' => $e]);
        }

        $this->actingAs($this->dueno)->post('/dashboard/calificame/ajustes', ['google_url' => 'https://g.page/r/palacio/review'])->assertRedirect();
        $this->assertNull($this->datosPerfil(), 'Sin sello, sin botón y sin comentarios elegidos no sale el bloque');
        $this->assertSame('https://g.page/r/palacio/review', CalificameAjuste::de($this->dueno->id)->google_url);

        $this->post('/dashboard/calificame/ajustes', ['google_url' => 'no-es-enlace'])->assertSessionHasErrors('google_url');
    }

    public function test_a_quien_califica_bien_lo_invita_a_resenar_en_google(): void
    {
        Calificame::activar($this->dueno->id);
        CalificameAjuste::de($this->dueno->id)->update(['google_url' => 'https://g.page/r/palacio/review']);

        $this->calificar(['estrellas' => 5]);
        $this->get('/barberia/califica?lang=es')->assertSee('Dejar reseña en Google')->assertSee('https://g.page/r/palacio/review', false);
    }

    public function test_despues_de_la_cita_le_pide_la_calificacion_al_cliente_una_sola_vez(): void
    {
        Agenda::activar($this->dueno->id);
        Calificame::activar($this->dueno->id);
        $servicio = AgendaServicio::create(['user_id' => $this->dueno->id, 'nombre' => 'Corte', 'duracion_min' => 40]);
        $cita = Cita::create([
            'user_id' => $this->dueno->id, 'servicio_id' => $servicio->id, 'servicio_nombre' => 'Corte', 'cliente_nombre' => 'Ana Pérez',
            'cliente_correo' => 'ana@correo.co', 'inicia_en' => now()->subHours(3), 'termina_en' => now()->subHours(2), 'estado' => 'confirmada', 'idioma' => 'es',
        ]);
        $futura = Cita::create([
            'user_id' => $this->dueno->id, 'servicio_id' => $servicio->id, 'servicio_nombre' => 'Corte', 'cliente_nombre' => 'Luis',
            'cliente_correo' => 'luis@correo.co', 'inicia_en' => now()->addHour(), 'termina_en' => now()->addHours(2), 'estado' => 'confirmada', 'idioma' => 'es',
        ]);

        $this->artisan('calificame:pedir')->assertSuccessful();
        $this->artisan('calificame:pedir')->assertSuccessful();

        Mail::assertSent(CalificameCorreo::class, 1);
        Mail::assertSent(CalificameCorreo::class, fn ($m) => $m->tipo === 'pedir' && $m->hasTo('ana@correo.co'));
        $this->assertNull($futura->fresh()->calificacion_pedida_en);

        // El enlace del correo abre la página con la cita y la calificación queda unida a ella, con el nombre del cliente
        $enlace = (new CalificameCorreo('pedir', cita: $cita))->enlace();
        $this->assertStringContainsString('/barberia/califica?cita=' . $cita->token, $enlace);
        $this->get('/barberia/califica?cita=' . $cita->token)->assertSee('Tu cita: Corte');
        $this->calificar(['estrellas' => 5, 'cita' => $cita->token]);
        $this->assertSame($cita->id, Calificacion::firstOrFail()->cita_id);
        $this->assertSame('Ana Pérez', Calificacion::firstOrFail()->nombre);
    }

    public function test_si_el_duenio_apaga_pedir_tras_cita_no_se_manda(): void
    {
        Calificame::activar($this->dueno->id);
        CalificameAjuste::de($this->dueno->id)->update(['pedir_tras_cita' => false]);
        Cita::create([
            'user_id' => $this->dueno->id, 'servicio_nombre' => 'Corte', 'cliente_nombre' => 'Ana', 'cliente_correo' => 'ana@correo.co',
            'inicia_en' => now()->subHours(3), 'termina_en' => now()->subHours(2), 'estado' => 'confirmada', 'idioma' => 'es',
        ]);

        $this->artisan('calificame:pedir')->assertSuccessful();

        Mail::assertNothingSent();
    }

    public function test_el_perfil_muestra_el_sello_en_todos_los_disenos(): void
    {
        Calificame::activar($this->dueno->id);
        foreach (['clasica', 'premium', 'boutique', 'moderna', 'enlaces', 'ejecutiva'] as $estructura) {
            Profile::where('nickname', 'barberia')->update(['design_config' => json_encode(['layout_estructura' => $estructura])]);
            $this->get('/barberia')->assertOk()->assertSee('pp-cf-sello', false);
        }
    }
}
