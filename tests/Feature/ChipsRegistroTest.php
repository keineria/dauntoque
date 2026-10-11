<?php

namespace Tests\Feature;

use App\Models\Chip;
use App\Models\Profile;
use App\Models\Tarjeta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChipsRegistroTest extends TestCase
{
    use RefreshDatabase;

    private function usuarioConPerfil(string $correo = 'cliente@correo.co'): User
    {
        $usuario = User::factory()->create(['email' => $correo]);
        Profile::create(['user_id' => $usuario->id, 'nickname' => 'cliente' . $usuario->id, 'nombre' => 'Cliente']);

        return $usuario;
    }

    public function test_un_codigo_inventado_no_se_puede_activar(): void
    {
        $usuario = $this->usuarioConPerfil();

        $this->actingAs($usuario)->get('/activar/inventado-123')->assertNotFound()->assertSee('aún no está habilitada');

        $this->assertDatabaseCount('tarjetas', 0);
        $this->assertNull(session('nfc_pendiente'));
    }

    public function test_un_chip_registrado_se_activa(): void
    {
        $usuario = $this->usuarioConPerfil();
        Chip::create(['codigo' => 'abc123def456']);

        $this->actingAs($usuario)->get('/activar/abc123def456')->assertRedirect('/dashboard');

        $this->assertSame($usuario->id, Tarjeta::where('nfc_uid', 'abc123def456')->value('user_id'));
    }

    public function test_solo_el_admin_entra_al_panel_de_chips(): void
    {
        config(['chips.admins' => ['dueno@dauntoque.com']]);

        $this->actingAs($this->usuarioConPerfil())->get('/admin/chips')->assertNotFound();
        $this->actingAs($this->usuarioConPerfil('dueno@dauntoque.com'))->get('/admin/chips')->assertOk()->assertSee('Chips');
    }

    public function test_el_panel_muestra_el_qr_del_perfil_y_de_cada_chip(): void
    {
        config(['chips.admins' => ['dueno@dauntoque.com'], 'perfiles.url_publica' => 'https://dauntoque.com']);
        $admin = $this->usuarioConPerfil('dueno@dauntoque.com');
        Chip::create(['codigo' => 'chip-qr-1']);

        $this->actingAs($admin)->get('/dashboard')->assertOk()
            ->assertSee('Tu código QR')->assertSee('https:\/\/dauntoque.com\/cliente' . $admin->id, false);
        $this->actingAs($admin)->get('/admin/chips')->assertOk()
            ->assertSee('QR del chip')->assertSee('\/activar\/chip-qr-1', false);
    }

    public function test_el_admin_registra_codigos_pegados_y_genera_nuevos(): void
    {
        config(['chips.admins' => ['dueno@dauntoque.com']]);
        $admin = $this->usuarioConPerfil('dueno@dauntoque.com');

        $this->actingAs($admin)->post('/admin/chips', [
            'codigos' => "https://portal.dauntoque.com/activar/chip-uno\nchip-dos\nchip-dos\n<mal>",
            'nota' => 'Lote 1',
        ])->assertSessionHas('status', 'Registrados: 2. Ya estaban: 1. No válidos: 1.');
        $this->assertTrue(Chip::where('codigo', 'chip-uno')->where('nota', 'Lote 1')->exists());

        $this->actingAs($admin)->post('/admin/chips/generar', ['cantidad' => 3])->assertSessionHas('generados');
        $this->assertSame(5, Chip::count());
        $this->assertSame(3, Chip::where('codigo', 'not like', 'chip-%')->whereRaw('length(codigo) = 12')->count());
    }

    public function test_no_se_borra_un_chip_activado(): void
    {
        config(['chips.admins' => ['dueno@dauntoque.com']]);
        $admin = $this->usuarioConPerfil('dueno@dauntoque.com');
        $libre = Chip::create(['codigo' => 'libre-1']);
        $usado = Chip::create(['codigo' => 'usado-1']);
        Tarjeta::create(['user_id' => $admin->id, 'nfc_uid' => 'usado-1', 'activada_en' => now(), 'vence_en' => now()->addYear()]);

        $this->actingAs($admin)->delete("/admin/chips/{$libre->id}");
        $this->actingAs($admin)->delete("/admin/chips/{$usado->id}");

        $this->assertModelMissing($libre);
        $this->assertModelExists($usado);
    }
}
