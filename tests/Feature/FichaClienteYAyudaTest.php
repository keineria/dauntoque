<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\Tarjeta;
use App\Models\User;
use App\Support\Precios;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Ficha del cliente en el administrador y la página de Ayuda con preguntas frecuentes. */
class FichaClienteYAyudaTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $admin = User::factory()->create(['email' => 'dueno@dauntoque.com']);
        $admin->forceFill(['es_admin' => true])->save();

        return $admin;
    }

    private function clienteConTarjeta(): array
    {
        $u = User::factory()->create(['email' => 'ana@correo.co', 'name' => 'Ana Cliente']);
        Profile::create(['user_id' => $u->id, 'nickname' => 'ana', 'nombre' => 'Ana', 'pais' => 'CO', 'nfc_uid' => 'abc123']);
        $t = Tarjeta::create(['user_id' => $u->id, 'nfc_uid' => 'abc123', 'activada_en' => now()->subMonths(11), 'vence_en' => now()->addMonth(), 'aviso_etapa' => 'mes']);

        return [$u, $t];
    }

    public function test_la_ficha_muestra_al_cliente_y_solo_la_ve_el_admin(): void
    {
        [$u] = $this->clienteConTarjeta();

        $this->actingAs($u)->get("/admin/clientes/{$u->id}")->assertNotFound();
        $this->actingAs($this->admin())->get("/admin/clientes/{$u->id}?desde=".urlencode('/admin?lista=todos'))
            ->assertOk()->assertSee('Ana Cliente')->assertSee('ana@correo.co')->assertSee('Datos de la cuenta')
            ->assertSee('/admin?lista=todos', false);
    }

    public function test_el_admin_cambia_nombre_correo_y_precio_de_renovacion(): void
    {
        [$u] = $this->clienteConTarjeta();
        User::factory()->create(['email' => 'otro@correo.co']);
        $admin = $this->admin();

        $this->actingAs($admin)->put("/admin/clientes/{$u->id}", ['name' => 'Ana', 'email' => 'otro@correo.co'])
            ->assertSessionHasErrors(['email' => 'Ese correo ya es de otra cuenta.']);

        $this->actingAs($admin)->put("/admin/clientes/{$u->id}", ['name' => 'Ana María', 'email' => 'Ana.Nueva@Correo.co', 'precio_renovacion' => 90000])
            ->assertSessionHasNoErrors();

        $u->refresh();
        $this->assertSame('ana.nueva@correo.co', $u->email);
        $this->assertSame('Ana María', $u->name);
        $this->assertSame(90000, Precios::montoRenovacion('CO', $u));
        $this->assertSame(120000, Precios::montoRenovacion('CO', User::factory()->create()));

        // Vacío vuelve al precio general
        $this->actingAs($admin)->put("/admin/clientes/{$u->id}", ['name' => 'Ana María', 'email' => 'ana.nueva@correo.co', 'precio_renovacion' => '']);
        $this->assertSame(120000, Precios::montoRenovacion('CO', $u->fresh()));
    }

    public function test_el_admin_cambia_el_vencimiento_y_los_avisos_vuelven_a_empezar(): void
    {
        [$u, $t] = $this->clienteConTarjeta();
        $admin = $this->admin();
        $fecha = now()->addMonths(5)->toDateString();

        $this->actingAs($admin)->put("/admin/clientes/{$u->id}/tarjetas/{$t->id}", ['vence_en' => now()->subYears(2)->toDateString()])
            ->assertSessionHasErrors('vence_en');

        $this->actingAs($admin)->put("/admin/clientes/{$u->id}/tarjetas/{$t->id}", ['vence_en' => $fecha])->assertSessionHasNoErrors();
        $t->refresh();
        $this->assertSame($fecha, $t->vence_en->toDateString());
        $this->assertNull($t->aviso_etapa);

        $this->actingAs($admin)->put("/admin/clientes/{$u->id}/tarjetas/{$t->id}", ['accion' => 'anio']);
        $this->assertSame(now()->addMonths(5)->addDays(365)->toDateString(), $t->fresh()->vence_en->toDateString());
    }

    public function test_no_se_edita_la_tarjeta_de_otro_cliente(): void
    {
        [, $t] = $this->clienteConTarjeta();
        $otro = User::factory()->create();

        $this->actingAs($this->admin())->put("/admin/clientes/{$otro->id}/tarjetas/{$t->id}", ['accion' => 'anio'])->assertNotFound();
    }

    public function test_ayuda_muestra_preguntas_frecuentes_y_debajo_el_asesor(): void
    {
        $u = User::factory()->create();

        $html = $this->actingAs($u)->get('/dashboard/ayuda')->assertOk()
            ->assertSee('preguntas frecuentes')->assertSee('¿Cómo activo mi tarjeta?')
            ->assertSee('Habla con un asesor')->getContent();

        foreach (config('ayuda.temas') as $tema) {
            $this->assertStringContainsString(e($tema['titulo']), $html);
        }
        $this->assertLessThan(strpos($html, 'Habla con un asesor'), strpos($html, '¿Cómo activo mi tarjeta?'));
    }

    public function test_ayuda_ya_no_esta_en_el_menu_izquierdo_sino_en_configuracion(): void
    {
        $html = $this->actingAs(User::factory()->create())->get('/dashboard')->getContent();
        $menu = substr($html, strpos($html, 'dt-brand'), strpos($html, 'dt-main') - strpos($html, 'dt-brand'));
        $cabecera = substr($html, strpos($html, 'dt-main'));

        $this->assertStringNotContainsString('/dashboard/ayuda', $menu);
        $this->assertStringContainsString('Configuración', $cabecera);
        $this->assertStringContainsString('Ayuda y preguntas frecuentes', $cabecera);
    }
}
