<?php

namespace Tests\Feature;

use App\Models\Chip;
use App\Models\Profile;
use App\Models\ProfileEvent;
use App\Models\Tarjeta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/** Reglas de los perfiles: perfil activo, link maestro, chip, nickname, datos y límites. */
class PerfilesReglasTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    /** Crea un perfil como lo hace el editor. */
    private function crear(User $usuario, string $nickname, string $nombre, array $extra = [])
    {
        return $this->actingAs($usuario)->postJson('/perfil/guardar', $extra + [
            'perfil_id' => 'null', 'nickname' => $nickname, 'nombre' => $nombre, 'tipo_perfil' => 'corporativo',
        ]);
    }

    private function usuarioConDosPerfiles(): array
    {
        $usuario = User::factory()->create();
        $this->crear($usuario, 'laura', 'Laura Trabajo')->assertOk();
        $this->crear($usuario, 'laura-pinta', 'Laura Pinta')->assertOk();

        return [$usuario, Profile::where('nickname', 'laura')->first(), Profile::where('nickname', 'laura-pinta')->first()];
    }

    // 1. El perfil activo solo cambia cuando el usuario lo elige

    public function test_crear_y_guardar_no_cambian_el_perfil_activo(): void
    {
        [$usuario, $uno, $dos] = $this->usuarioConDosPerfiles();

        $this->assertNotNull($uno->activo_en);
        $this->assertNull($dos->activo_en);
        $this->assertSame($uno->id, Profile::activoDe($usuario->id)->id);

        // Guardado automático del diseño del segundo perfil (pantalla Mis perfiles)
        Carbon::setTestNow(now()->addMinute());
        $this->actingAs($usuario)->postJson('/perfil/guardar', ['perfil_id' => $dos->id, 'design_config' => '{"tema_base":"onyx"}'])
            ->assertOk()->assertJson(['status' => 'success']);

        $this->assertSame($uno->id, Profile::activoDe($usuario->id)->id);
        $this->get('/laura')->assertOk()->assertSee('Laura Trabajo')->assertDontSee('Laura Pinta');
    }

    public function test_activar_fija_el_perfil_publico_hasta_que_se_elija_otro(): void
    {
        [$usuario, $uno, $dos] = $this->usuarioConDosPerfiles();

        $this->actingAs($usuario)->postJson("/perfil/{$dos->id}/activar")->assertOk()->assertJson(['status' => 'success']);
        $this->assertSame($dos->id, Profile::activoDe($usuario->id)->id);

        // Editar el primero después no lo vuelve público
        Carbon::setTestNow(now()->addMinute());
        $this->actingAs($usuario)->postJson('/perfil/guardar', ['perfil_id' => $uno->id, 'nombre' => 'Laura Trabajo 2'])->assertOk();

        $this->assertSame($dos->id, Profile::activoDe($usuario->id)->id);
        $this->get('/laura')->assertOk()->assertSee('Laura Pinta');
    }

    public function test_con_dominio_publico_la_tarjeta_abre_dauntoque_com_nickname(): void
    {
        config(['perfiles.url_publica' => 'https://dauntoque.com']);
        [$usuario] = $this->usuarioConDosPerfiles();
        Chip::firstOrCreate(['codigo' => 'chip-pub']);
        $this->actingAs($usuario)->get('/activar/chip-pub')->assertRedirect('/dashboard');

        $this->get('/activar/chip-pub')->assertRedirect('https://dauntoque.com/laura');
        $this->get('/laura')->assertOk()->assertSee('dauntoque.com/laura');
        $this->actingAs($usuario)->get('/dashboard')->assertSee('dauntoque.com/laura');
    }

    public function test_el_sitio_publicado_manda_al_portal_lo_que_no_es_suyo(): void
    {
        $htaccess = \App\Console\Commands\GenerarSitio::htaccess('https://portal.dauntoque.com');
        $this->assertStringContainsString('RewriteRule ^(.+)$ https://portal.dauntoque.com/$1 [R=302,L]', $htaccess);
        $this->assertStringContainsString('!^/\\.well-known/', $htaccess);

        $usuario = User::factory()->create();
        $this->actingAs($usuario)->getJson('/perfil/validar-nickname?nickname=es-co')->assertJson(['disponible' => false]);
    }

    // 2. El chip se muda entre cuentas

    public function test_el_chip_pasa_a_la_cuenta_que_lo_vincula_y_conserva_sus_fechas(): void
    {
        Carbon::setTestNow('2026-10-14 10:00:00');
        $beto = User::factory()->create();
        $this->crear($beto, 'beto', 'Beto')->assertOk();
        Chip::firstOrCreate(['codigo' => 'chip-uno']);
        $this->actingAs($beto)->get('/activar/chip-uno')->assertRedirect('/dashboard');

        // Beto cambia su tarjeta por otra: la vieja queda libre y no deja contador en su panel
        Carbon::setTestNow('2026-11-01 10:00:00');
        Chip::firstOrCreate(['codigo' => 'chip-dos']);
        $this->actingAs($beto)->get('/activar/chip-dos')->assertRedirect('/dashboard');
        $this->assertSame('chip-dos', Profile::where('user_id', $beto->id)->first()->nfc_uid);
        $this->assertSame(['chip-dos'], Tarjeta::where('user_id', $beto->id)->vinculadas()->pluck('nfc_uid')->all());

        // Carla vincula la tarjeta vieja: la tarjeta pasa a su cuenta con sus fechas
        Carbon::setTestNow('2027-01-01 10:00:00');
        $carla = User::factory()->create();
        $this->crear($carla, 'carla', 'Carla')->assertOk();
        $this->actingAs($carla)->get('/activar/chip-uno')->assertRedirect('/dashboard');

        $tarjeta = Tarjeta::where('nfc_uid', 'chip-uno')->first();
        $this->assertSame($carla->id, $tarjeta->user_id);
        $this->assertSame('2026-10-14', $tarjeta->activada_en->toDateString());
        $this->assertSame('2027-10-14', $tarjeta->vence_en->toDateString());
        $this->assertSame(2, Tarjeta::count());
        $this->actingAs($carla)->get('/dashboard')->assertOk()->assertSee('···ip-uno');
    }

    // 5. La tarjeta abre el link maestro

    public function test_la_tarjeta_abre_el_link_maestro_que_muestra_el_perfil_activo(): void
    {
        [$usuario, $uno, $dos] = $this->usuarioConDosPerfiles();
        Chip::firstOrCreate(['codigo' => 'chip-abc']);
        $this->actingAs($usuario)->get('/activar/chip-abc')->assertRedirect('/dashboard');
        $this->actingAs($usuario)->postJson("/perfil/{$dos->id}/activar")->assertOk();

        // El chip siguió al perfil activo, pero abre el nickname del primer perfil
        $this->assertSame('chip-abc', $dos->fresh()->nfc_uid);
        $this->get('/activar/chip-abc')->assertRedirect(url('/laura'));
        $this->get('/laura')->assertOk()->assertSee('Laura Pinta');
    }

    // 6. Nickname válido

    public function test_validar_nickname(): void
    {
        $usuario = User::factory()->create();
        $this->crear($usuario, 'laura', 'Laura')->assertOk();
        $otro = User::factory()->create();
        $validar = fn ($nick) => $this->actingAs($otro)->getJson('/perfil/validar-nickname?nickname=' . urlencode($nick))->json();

        $this->assertTrue($validar('Carla.Ruiz_1')['disponible']);
        foreach (['', 'ab', 'carla ruiz', 'carla/x', 'ñandú', str_repeat('a', 31), 'logout', 'forgot-password', 'robots.txt', 'laura'] as $nick) {
            $r = $validar($nick);
            $this->assertFalse($r['disponible'], "'$nick' no debería estar disponible");
            $this->assertNotEmpty($r['mensaje']);
        }
    }

    public function test_guardar_rechaza_nicknames_invalidos_y_los_pasa_a_minusculas(): void
    {
        $usuario = User::factory()->create();

        $this->crear($usuario, '', 'Laura')->assertStatus(422)->assertJson(['message' => 'Escribe un nombre de usuario.']);
        $this->crear($usuario, 'reset-password', 'Laura')->assertStatus(422)->assertJson(['message' => 'Ese nombre de usuario está reservado. Elige otro.']);
        $this->crear($usuario, 'la ura', 'Laura')->assertStatus(422);
        $this->assertSame(0, Profile::count());

        $this->crear($usuario, 'Laura.G', 'Laura')->assertOk();
        $this->assertSame('laura.g', Profile::first()->nickname);
        $this->crear(User::factory()->create(), 'laura.g', 'Otra')->assertStatus(422)->assertJson(['message' => 'Ese nombre de usuario ya está en uso. Elige otro.']);
    }

    // 7. Datos del perfil

    public function test_guardar_valida_los_campos_en_espanol(): void
    {
        $usuario = User::factory()->create();

        $this->crear($usuario, 'laura', 'Laura', ['correo_contacto' => 'no-es-correo'])
            ->assertStatus(422)->assertJson(['message' => 'Escribe un correo de contacto válido (ej. nombre@correo.com).']);
        $this->crear($usuario, 'laura', 'Laura', ['telefono' => 'abc'])
            ->assertStatus(422)->assertJson(['message' => 'El teléfono solo puede tener números, espacios y los signos + ( ) -.']);
        $this->crear($usuario, 'laura', str_repeat('a', 101))
            ->assertStatus(422)->assertJson(['message' => 'El nombre no puede tener más de 100 caracteres.']);
        $this->crear($usuario, 'laura', 'Laura', ['telefono' => '+57 (300) 123-4567', 'correo_contacto' => 'laura@correo.com'])->assertOk();
    }

    // 8. Configuración edita el perfil activo

    public function test_configuracion_edita_el_perfil_activo(): void
    {
        [$usuario] = $this->usuarioConDosPerfiles();

        $this->actingAs($usuario)->get('/dashboard/configuracion')->assertOk()
            ->assertSee('Laura Trabajo')->assertDontSee('Laura Pinta');
    }

    public function test_configuracion_sin_perfil_manda_a_crearlo(): void
    {
        $this->actingAs(User::factory()->create())->get('/dashboard/configuracion')->assertRedirect('/dashboard');
    }

    // 9. Tipo de perfil

    public function test_guarda_el_tipo_de_perfil(): void
    {
        $usuario = User::factory()->create();
        $this->crear($usuario, 'laura', 'Laura', ['tipo_perfil' => 'hobbies'])->assertOk();
        $this->assertSame('hobbies', Profile::first()->tipo_perfil);

        $this->actingAs($usuario)->postJson('/perfil/guardar', ['perfil_id' => Profile::first()->id, 'tipo_perfil' => 'pirata'])->assertStatus(422);
        $this->assertSame('hobbies', Profile::first()->tipo_perfil);
    }

    // 10. vCard

    public function test_vcard_con_n_texto_escapado_y_nombre_de_archivo_seguro(): void
    {
        $usuario = User::factory()->create();
        $this->crear($usuario, 'jose', 'José Pérez', ['profesion' => 'Diseño, web; apps', 'direccion' => "Calle 1\nBogotá"])->assertOk();

        $r = $this->get('/jose/vcf')->assertOk();
        $vcf = $r->getContent();

        $this->assertStringContainsString("N:Pérez;José;;;\r\n", $vcf);
        $this->assertStringContainsString("FN:José Pérez\r\n", $vcf);
        $this->assertStringContainsString("TITLE:Diseño\\, web\\; apps\r\n", $vcf);
        $this->assertStringContainsString("ADR;TYPE=WORK:;;Calle 1\\nBogotá;;;;\r\n", $vcf);
        $this->assertSame(
            "attachment; filename=\"Jose Perez.vcf\"; filename*=UTF-8''Jos%C3%A9%20P%C3%A9rez.vcf",
            $r->headers->get('Content-Disposition'),
        );
    }

    // 3. Fotos demasiado pesadas

    public function test_peticion_demasiado_grande_da_mensaje_en_espanol(): void
    {
        $this->actingAs(User::factory()->create())
            ->call('POST', '/perfil/guardar', [], [], [], ['CONTENT_LENGTH' => 10 * 1024 * 1024 * 1024, 'HTTP_ACCEPT' => 'application/json'])
            ->assertStatus(413)
            ->assertJson(['status' => 'error', 'message' => 'Las fotos pesan demasiado para guardarlas juntas. Usa imágenes más livianas o sube menos a la vez.']);
    }

    // 13. Límite de perfiles

    public function test_limite_de_perfiles_responde_422_sin_mencionar_pro(): void
    {
        [$usuario, $uno] = $this->usuarioConDosPerfiles();

        $this->crear($usuario, 'laura-tres', 'Laura 3')->assertStatus(422)
            ->assertJson(['message' => 'Tu cuenta incluye 2 perfiles y ya los tienes. Puedes editar o borrar uno.']);
        $this->actingAs($usuario)->postJson("/perfil/{$uno->id}/duplicar")->assertStatus(422);
        $this->assertSame(2, Profile::count());
    }

    // 15. Perfiles y tarjetas de otra cuenta

    public function test_ids_de_otra_cuenta_dan_404(): void
    {
        [$duena, $uno, $dos] = $this->usuarioConDosPerfiles();
        $tarjeta = Tarjeta::registrarActivacion($duena->id, 'chip-ajeno');
        $intruso = User::factory()->create();
        $this->crear($intruso, 'intruso', 'Intruso')->assertOk();

        $this->actingAs($intruso)->postJson("/perfil/{$dos->id}/activar")->assertNotFound();
        $this->actingAs($intruso)->deleteJson("/perfil/{$dos->id}/eliminar")->assertNotFound();
        $this->actingAs($intruso)->postJson("/perfil/{$uno->id}/duplicar")->assertNotFound();
        $this->actingAs($intruso)->postJson('/perfil/guardar', ['perfil_id' => $uno->id, 'nombre' => 'Hackeado'])->assertNotFound();
        $this->actingAs($intruso)->post("/renovar/{$tarjeta->id}")->assertNotFound();

        $this->assertSame('Laura Trabajo', $uno->fresh()->nombre);
        $this->assertSame(2, Profile::where('user_id', $duena->id)->count());
    }

    // 16. Clics repetidos

    public function test_clics_repetidos_cuentan_una_vez(): void
    {
        $usuario = User::factory()->create();
        $this->crear($usuario, 'laura', 'Laura')->assertOk();
        $perfil = Profile::first();
        auth()->logout();

        foreach (range(1, 5) as $i) {
            $this->post('/api/analiticas/registrar', ['perfil_id' => $perfil->id, 'accion' => 'whatsapp'])->assertNoContent();
        }
        $this->assertSame(1, ProfileEvent::where('accion', 'whatsapp')->count());

        Carbon::setTestNow(now()->addMinute());
        $this->post('/api/analiticas/registrar', ['perfil_id' => $perfil->id, 'accion' => 'whatsapp'])->assertNoContent();
        $this->assertSame(2, ProfileEvent::where('accion', 'whatsapp')->count());
    }
}
