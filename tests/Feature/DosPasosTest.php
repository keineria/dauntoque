<?php

namespace Tests\Feature;

use App\Console\Commands\CrearAdmin;
use App\Models\User;
use App\Support\Totp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Verificación en dos pasos opcional y contraseña temporal del administrador. */
class DosPasosTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    /** Activa la verificación como lo hace la pantalla Mi cuenta y devuelve [secreto, códigos de respaldo]. */
    private function activar(User $usuario): array
    {
        $this->actingAs($usuario)->post('/dashboard/cuenta/dos-pasos')->assertRedirect();
        $secreto = session('dos_pasos_nuevo');

        $this->actingAs($usuario)->post('/dashboard/cuenta/dos-pasos/confirmar', ['codigo' => '000000'])->assertSessionHasErrors('codigo', null, 'dosPasos');
        $this->actingAs($usuario)->post('/dashboard/cuenta/dos-pasos/confirmar', ['codigo' => Totp::codigo($secreto)])->assertSessionHas('codigos_respaldo');

        return [$secreto, session('codigos_respaldo')];
    }

    public function test_con_dos_pasos_el_login_pide_el_codigo_de_la_app(): void
    {
        $usuario = User::factory()->create();
        [$secreto] = $this->activar($usuario);
        $this->assertTrue($usuario->fresh()->tieneDosPasos());
        auth()->logout();

        $this->post('/login', ['email' => $usuario->email, 'password' => 'password'])->assertRedirect('/dos-pasos');
        $this->assertGuest();
        $this->get('/dashboard')->assertRedirect('/login');

        $this->post('/dos-pasos', ['codigo' => '123456'])->assertSessionHasErrors('codigo');
        $this->assertGuest();
        $this->post('/dos-pasos', ['codigo' => Totp::codigo($secreto)])->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($usuario);
    }

    public function test_un_codigo_de_respaldo_sirve_una_sola_vez(): void
    {
        $usuario = User::factory()->create();
        [, $codigos] = $this->activar($usuario);
        $this->assertCount(8, $codigos);
        auth()->logout();

        $this->post('/login', ['email' => $usuario->email, 'password' => 'password']);
        $this->post('/dos-pasos', ['codigo' => strtoupper($codigos[0])])->assertRedirect('/dashboard');
        auth()->logout();

        $this->post('/login', ['email' => $usuario->email, 'password' => 'password']);
        $this->post('/dos-pasos', ['codigo' => $codigos[0]])->assertSessionHasErrors('codigo');
        $this->assertGuest();
    }

    public function test_sin_dos_pasos_el_login_sigue_igual_y_se_puede_desactivar_con_la_contrasena(): void
    {
        $usuario = User::factory()->create();
        $this->post('/login', ['email' => $usuario->email, 'password' => 'password'])->assertRedirect('/dashboard');

        $this->activar($usuario);
        $this->actingAs($usuario)->post('/dashboard/cuenta/dos-pasos/desactivar', ['clave' => 'mala'])->assertSessionHasErrors('clave', null, 'dosPasos');
        $this->assertTrue($usuario->fresh()->tieneDosPasos());
        $this->actingAs($usuario)->post('/dashboard/cuenta/dos-pasos/desactivar', ['clave' => 'password']);
        $this->assertFalse($usuario->fresh()->tieneDosPasos());
    }

    public function test_admin_crear_da_una_contrasena_temporal_que_obliga_a_cambiarla(): void
    {
        $this->artisan('admin:crear', ['correo' => 'Jefe@Dauntoque.com', '--nombre' => 'Jefe'])
            ->expectsOutputToContain('Contraseña temporal')->assertSuccessful();
        $jefe = User::where('email', 'jefe@dauntoque.com')->firstOrFail();
        $this->assertTrue($jefe->esAdmin());
        $this->assertTrue($jefe->debe_cambiar_clave);

        // Hasta cambiarla, el panel lleva a Mi cuenta
        $this->actingAs($jefe)->get('/admin')->assertRedirect('/dashboard/cuenta');
        $this->actingAs($jefe)->get('/dashboard/cuenta')->assertOk()->assertSee('contraseña temporal');

        $jefe->forceFill(['password' => 'Temporal-123'])->save();
        $this->actingAs($jefe)->post('/dashboard/cuenta/clave', ['actual' => 'Temporal-123', 'nueva' => 'corta', 'nueva_confirmation' => 'corta'])
            ->assertSessionHasErrors('nueva', null, 'clave');
        $this->actingAs($jefe)->post('/dashboard/cuenta/clave', ['actual' => 'Temporal-123', 'nueva' => 'MiClaveNueva2026', 'nueva_confirmation' => 'MiClaveNueva2026'])
            ->assertSessionHasNoErrors();
        $this->assertFalse($jefe->fresh()->debe_cambiar_clave);
        $this->actingAs($jefe->fresh())->get('/admin')->assertOk();

        // Si ya existe, conserva su contraseña salvo que se pida una nueva
        $this->artisan('admin:crear', ['correo' => 'jefe@dauntoque.com'])->expectsOutputToContain('conserva su contraseña')->assertSuccessful();
        $this->assertFalse($jefe->fresh()->debe_cambiar_clave);
        $this->assertSame(1, User::count());
    }

    public function test_la_clave_temporal_es_fuerte(): void
    {
        $clave = CrearAdmin::claveTemporal();
        $this->assertSame(16, strlen($clave));
        $this->assertMatchesRegularExpression('/[A-Z]/', $clave);
        $this->assertMatchesRegularExpression('/[a-z]/', $clave);
        $this->assertMatchesRegularExpression('/[0-9]/', $clave);
        $this->assertNotSame($clave, CrearAdmin::claveTemporal());
    }
}
