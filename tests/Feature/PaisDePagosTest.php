<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * País de pagos de cada perfil (sale de la ubicación; el dueño lo cambia en Datos del perfil) y
 * país de facturación de la cuenta (moneda de las renovaciones; se fija una vez y solo lo cambia el administrador).
 */
class PaisDePagosTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function crear(User $usuario, string $nickname, array $extra = [], array $encabezados = [])
    {
        return $this->actingAs($usuario)->withHeaders($encabezados)->postJson('/perfil/guardar', $extra + [
            'perfil_id' => 'null', 'nickname' => $nickname, 'nombre' => 'Ana', 'tipo_perfil' => 'corporativo',
        ]);
    }

    public function test_el_editor_toma_el_pais_de_la_conexion_o_de_sus_otros_perfiles(): void
    {
        $usuario = User::factory()->create();

        $this->actingAs($usuario)->withHeaders(['CF-IPCountry' => 'US'])->get('/editor')->assertOk()->assertViewHas('paisSugerido', 'US');
        // Un país sin medios de pago no sirve: el editor usa la zona horaria del navegador
        $this->actingAs($usuario)->withHeaders(['CF-IPCountry' => 'MX'])->get('/editor')->assertViewHas('paisSugerido', null);

        $this->crear($usuario, 'ana', ['pais' => 'US'])->assertOk();
        $this->actingAs($usuario)->withHeaders(['CF-IPCountry' => 'CO'])->get('/editor')->assertViewHas('paisSugerido', 'US');
    }

    public function test_sin_pais_en_la_peticion_se_usa_la_conexion(): void
    {
        $usuario = User::factory()->create();
        $this->crear($usuario, 'ana', [], ['CF-IPCountry' => 'US'])->assertOk();

        $this->assertSame('US', Profile::where('nickname', 'ana')->value('pais'));
    }

    public function test_la_facturacion_se_fija_con_la_ubicacion_y_no_cambia_con_el_pais_de_pagos(): void
    {
        $usuario = User::factory()->create();
        // Se conecta desde EE. UU. aunque el navegador diga Colombia: factura en dólares
        $this->crear($usuario, 'ana', ['pais' => 'CO'], ['CF-IPCountry' => 'US'])->assertOk();
        $this->assertSame('US', $usuario->fresh()->pais_facturacion);

        // Cambiar los métodos de pago a Colombia no cambia la moneda de la renovación
        $perfil = Profile::where('nickname', 'ana')->first();
        $this->actingAs($usuario)->postJson('/perfil/guardar', [
            'perfil_id' => $perfil->id, 'pais' => 'CO',
            'payment_methods' => json_encode([['type' => 'zelle', 'value' => 'ana@correo.com'], ['type' => 'nequi', 'value' => '3001234567']]),
        ])->assertOk();

        $perfil->refresh();
        $this->assertSame('CO', $perfil->pais);
        $this->assertSame([['type' => 'nequi', 'value' => '3001234567']], $perfil->payment_methods);
        $this->assertSame('US', $usuario->fresh()->paisFacturacion());
    }

    public function test_solo_el_administrador_cambia_el_pais_de_facturacion(): void
    {
        $admin = User::factory()->create(['email' => 'admin@dauntoque.com']);
        $admin->forceFill(['es_admin' => true])->save();
        $cliente = User::factory()->create();
        $this->crear($cliente, 'ana', ['pais' => 'CO'])->assertOk();
        $this->assertSame('CO', $cliente->fresh()->pais_facturacion);

        $this->actingAs($admin)->put("/admin/clientes/{$cliente->id}", ['name' => 'Ana', 'email' => $cliente->email, 'pais_facturacion' => 'US'])
            ->assertSessionHasNoErrors();
        $this->assertSame('US', $cliente->fresh()->paisFacturacion());

        $this->actingAs($admin)->put("/admin/clientes/{$cliente->id}", ['name' => 'Ana', 'email' => $cliente->email, 'pais_facturacion' => 'MX'])
            ->assertSessionHasErrors('pais_facturacion');
    }
}
