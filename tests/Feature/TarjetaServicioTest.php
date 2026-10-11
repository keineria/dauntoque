<?php

namespace Tests\Feature;

use App\Models\Chip;
use App\Models\Profile;
use App\Models\Tarjeta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TarjetaServicioTest extends TestCase
{
    use RefreshDatabase;

    private function usuarioConPerfil(): array
    {
        $usuario = User::factory()->create();
        $perfil = Profile::create(['user_id' => $usuario->id, 'nickname' => 'kathe', 'nombre' => 'Kathe']);

        return [$usuario, $perfil];
    }

    public function test_activar_la_tarjeta_arranca_365_dias_de_servicio(): void
    {
        Carbon::setTestNow('2026-10-14 10:00:00');
        [$usuario, $perfil] = $this->usuarioConPerfil();

        Chip::firstOrCreate(['codigo' => 'chip-abc123']);
        $this->actingAs($usuario)->get('/activar/chip-abc123')->assertRedirect('/dashboard');

        $tarjeta = Tarjeta::where('nfc_uid', 'chip-abc123')->firstOrFail();
        $this->assertSame($usuario->id, $tarjeta->user_id);
        $this->assertSame('2026-10-14', $tarjeta->activada_en->toDateString());
        $this->assertSame('2027-10-14', $tarjeta->vence_en->toDateString());
        $this->assertSame('chip-abc123', $perfil->fresh()->nfc_uid);
    }

    public function test_mudar_el_chip_de_perfil_conserva_las_fechas(): void
    {
        Carbon::setTestNow('2026-10-14 10:00:00');
        [$usuario] = $this->usuarioConPerfil();
        Tarjeta::registrarActivacion($usuario->id, 'chip-abc123');

        Carbon::setTestNow('2027-01-01 10:00:00');
        $otra = Tarjeta::registrarActivacion($usuario->id, 'chip-abc123');

        $this->assertSame(1, Tarjeta::count());
        $this->assertSame('2027-10-14', $otra->vence_en->toDateString());
    }

    public function test_estados_del_servicio(): void
    {
        $tarjeta = new Tarjeta([
            'activada_en' => Carbon::parse('2026-10-14'),
            'vence_en' => Carbon::parse('2027-10-14'),
        ]);

        $this->assertSame(['activo', 365], $this->estado($tarjeta, '2026-10-14'));
        $this->assertSame(['activo', 31], $this->estado($tarjeta, '2027-09-13'));
        $this->assertSame(['pronto', 30], $this->estado($tarjeta, '2027-09-14'));
        $this->assertSame(['pronto', 1], $this->estado($tarjeta, '2027-10-13'));
        $this->assertSame(['gracia', 0], $this->estado($tarjeta, '2027-10-14'));
        $this->assertSame(15, $tarjeta->servicio(Carbon::parse('2027-10-14'))['diasGracia']);
        $this->assertSame(['pausa', 0], $this->estado($tarjeta, '2027-10-29'));
    }

    public function test_el_panel_muestra_el_contador(): void
    {
        Carbon::setTestNow('2026-10-14 10:00:00');
        [$usuario, $perfil] = $this->usuarioConPerfil();
        $perfil->update(['nfc_uid' => 'chip-abc123']);
        Tarjeta::registrarActivacion($usuario->id, 'chip-abc123');

        Carbon::setTestNow('2026-12-06 10:00:00');
        $this->withoutVite()->actingAs($usuario)->get('/dashboard')
            ->assertOk()
            ->assertSee('Servicio de tu tarjeta')
            ->assertSee('312')
            ->assertSee('···abc123')
            ->assertSee('$120.000 COP / año');
    }

    private function estado(Tarjeta $tarjeta, string $hoy): array
    {
        $s = $tarjeta->servicio(Carbon::parse($hoy));

        return [$s['estado'], $s['diasRestantes']];
    }
}
