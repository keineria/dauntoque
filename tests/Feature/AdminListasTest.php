<?php

namespace Tests\Feature;

use App\Mail\CorreoLibre;
use App\Mail\PedidoConfirmado;
use App\Mail\RecordatorioUso;
use App\Models\Ajuste;
use App\Models\Chip;
use App\Models\Pedido;
use App\Models\Profile;
use App\Models\Tarjeta;
use App\Models\User;
use App\Support\Correos;
use App\Support\Plantillas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminListasTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $admin = User::factory()->create(['email' => 'dueno@dauntoque.com']);
        $admin->forceFill(['es_admin' => true])->save();

        return $admin;
    }

    private function cliente(string $correo, string $nickname, array $mas = []): User
    {
        $u = User::factory()->create(['email' => $correo, 'name' => ucfirst($nickname).' Cliente'] + $mas);
        Profile::create(['user_id' => $u->id, 'nickname' => $nickname, 'nombre' => ucfirst($nickname)]);

        return $u;
    }

    private function pedidoPagado(string $correo): Pedido
    {
        $pedido = Pedido::create(['nombre' => 'María Pérez', 'correo' => $correo, 'celular' => '3001234567', 'departamento' => 'Antioquia', 'ciudad' => 'Medellín',
            'direccion' => 'Calle 1', 'acabado' => 'crystal', 'pais' => 'CO', 'moneda' => 'COP', 'monto' => 299900, 'estado' => 'pagado', 'pagado_en' => now()]);
        Chip::deCompra($pedido);

        return $pedido->fresh();
    }

    public function test_la_activacion_guarda_medio_lugar_y_dispositivo(): void
    {
        $u = $this->cliente('ana@correo.co', 'ana');
        Chip::create(['codigo' => 'k7mq-2xpa']);

        $this->actingAs($u)->withHeaders([
            'CF-IPCountry' => 'CO', 'CF-IPCity' => 'Medellín', 'CF-Region' => 'Antioquia',
            'User-Agent' => 'Mozilla/5.0 (Linux; Android 14; SM-S918B Build/UP1A) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0 Mobile Safari/537.36',
        ])->post('/tarjeta/activar', ['codigo' => 'k7mq-2xpa'])->assertSessionHas('nfc_success');

        $t = Tarjeta::firstOrFail();
        $this->assertSame('codigo', $t->activacion_medio);
        $this->assertSame('Medellín, Antioquia, Colombia', $t->activacion_lugar);
        $this->assertSame('Android 14 · Samsung SM-S918B · Chrome', $t->activacion_dispositivo);

        $admin = $this->admin();
        $this->actingAs($admin)->get('/admin?lista=activos')->assertOk()->assertSee('k7mq-2xpa')->assertSee('Medellín, Antioquia, Colombia')->assertSee('Escribió el código');
        $this->actingAs($admin)->get('/admin/chips?estado=activos')->assertOk()->assertSee('ana@correo.co')->assertSee('Samsung SM-S918B');
    }

    public function test_lista_de_clientes_con_buscador_y_paginas(): void
    {
        $admin = $this->admin();
        foreach (range(1, 25) as $i) {
            $this->cliente("c$i@correo.co", "cliente$i");
        }

        $pagina = $this->actingAs($admin)->get('/admin?lista=todos')->assertOk();
        $this->assertCount(20, $pagina->viewData('filas'));
        $this->assertCount(26, $this->actingAs($admin)->get('/admin?lista=todos&por=50')->viewData('filas'));

        $buscado = $this->actingAs($admin)->get('/admin?lista=todos&buscar=c17@')->viewData('filas');
        $this->assertSame(['c17@correo.co'], array_column($buscado, 'correo'));
        $this->assertSame(['c3@correo.co'], array_column($this->actingAs($admin)->get('/admin?buscar=cliente3')->viewData('filas'), 'correo'));
    }

    public function test_sin_activar_muestra_y_reenvia_el_codigo_de_su_compra(): void
    {
        Mail::fake();
        $admin = $this->admin();
        $u = $this->cliente('maria@correo.co', 'maria');
        $chip = $this->pedidoPagado('maria@correo.co')->chip;

        $this->actingAs($admin)->get('/admin?lista=sin_activar')->assertSee($chip->codigo)->assertSee('Reenviar código');
        $this->actingAs($admin)->post("/admin/clientes/{$u->id}/reenviar-codigo")->assertSessionHas('status');

        Mail::assertSent(PedidoConfirmado::class, fn ($m) => $m->hasTo('maria@correo.co'));
    }

    public function test_recordatorio_de_uso_una_vez_cada_30_dias(): void
    {
        Mail::fake();
        $admin = $this->admin();
        $dormido = $this->cliente('dormido@correo.co', 'dormido', ['created_at' => now()->subMonths(5)]);
        $this->cliente('nuevo@correo.co', 'nuevo');

        $this->actingAs($admin)->get('/admin/sin-uso')->assertOk()->assertSee('dormido@correo.co')->assertDontSee('nuevo@correo.co');
        $this->actingAs($admin)->post("/admin/sin-uso/{$dormido->id}/enviar")->assertSessionHas('status');
        $this->actingAs($admin)->post("/admin/sin-uso/{$dormido->id}/enviar")->assertSessionHas('error');
        $this->actingAs($admin)->post('/admin/sin-uso/enviar-todos')->assertSessionHas('status', 'Recordatorios enviados: 0.');

        Mail::assertSent(RecordatorioUso::class, 1);
        $this->assertNotNull($dormido->fresh()->recordatorio_uso_en);
        $html = (new RecordatorioUso($dormido->fresh()))->render();
        $this->assertStringContainsString('dormido', $html);
    }

    public function test_el_servidor_smtp_se_guarda_cifrado_y_nunca_se_muestra(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post('/admin/correos/smtp', [
            'smtp_host' => 'smtp.hostinger.com', 'smtp_puerto' => 465, 'smtp_cifrado' => 'ssl', 'smtp_usuario' => 'pedidos@dauntoque.com', 'smtp_clave' => 'ClaveSecreta123',
        ])->assertSessionHas('status');

        $this->assertNotSame('ClaveSecreta123', Ajuste::valor('smtp_clave'));
        $this->assertSame('ClaveSecreta123', Crypt::decryptString(Ajuste::valor('smtp_clave')));
        $this->actingAs($admin)->get('/admin/correos')->assertOk()->assertDontSee('ClaveSecreta123')->assertSee('guardada')->assertSee('Configurado aquí');

        // Sin contraseña nueva se conserva la anterior
        $this->actingAs($admin)->post('/admin/correos/smtp', [
            'smtp_host' => 'smtp.hostinger.com', 'smtp_puerto' => 587, 'smtp_cifrado' => 'tls', 'smtp_usuario' => 'pedidos@dauntoque.com', 'smtp_clave' => '',
        ])->assertSessionHas('status');

        Correos::aplicarAjustes();
        $this->assertSame('smtp', config('mail.default'));
        $this->assertSame('smtp.hostinger.com', config('mail.mailers.smtp.host'));
        $this->assertSame(587, config('mail.mailers.smtp.port'));
        $this->assertSame('smtp', config('mail.mailers.smtp.scheme'));
        $this->assertSame('ClaveSecreta123', config('mail.mailers.smtp.password'));
    }

    public function test_plantilla_editable_con_campos_vista_previa_y_restaurar(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->get('/admin/correos/plantilla/pedido_confirmado')->assertOk()
            ->assertSee('codigo_activacion')->assertSee('María Pérez')->assertSee('Vista previa');
        $this->actingAs($admin)->get('/admin/correos/plantilla/no-existe')->assertNotFound();

        $this->actingAs($admin)->post('/admin/correos/plantilla/pedido_confirmado', [
            'asunto' => 'Hola {{nombre}}, pedido {{pedido}}',
            'html' => '<h1>Hola {{nombre|amigo}}</h1><p>Tu código: {{codigo_activacion}}</p><p>{{nombre_tarjeta|Sin nombre}}</p>',
        ])->assertSessionHas('status');

        $pedido = $this->pedidoPagado('ana@correo.co');
        $pedido->update(['nombre' => 'Ana <b>Gómez</b>', 'nombre_tarjeta' => null]);
        $correo = new PedidoConfirmado($pedido->fresh());
        $html = $correo->render();
        $this->assertStringContainsString('Hola Ana</h1>', $html);
        $this->assertStringContainsString('Tu código: '.$pedido->chip->codigo, $html);
        $this->assertStringContainsString('<p>Sin nombre</p>', $html);
        $this->assertSame('Hola Ana, pedido '.$pedido->codigo, $correo->envelope()->subject);

        $this->actingAs($admin)->post('/admin/correos/plantilla/pedido_confirmado/restaurar');
        $this->assertNull(Plantillas::personalizada('pedido_confirmado'));
        $this->assertStringContainsString('Crear mi perfil', (new PedidoConfirmado($pedido->fresh()))->render());
    }

    public function test_la_prueba_de_plantilla_no_guarda_y_usa_datos_de_ejemplo(): void
    {
        Mail::fake();
        $admin = $this->admin();
        $this->actingAs($admin)->post('/admin/correos/plantilla/recordatorio_uso', [
            'asunto' => 'Hola {{nombre}}', 'html' => '<p>{{enlace_perfil}}</p>', 'accion' => 'probar',
        ])->assertSessionHas('status');

        Mail::assertSent(CorreoLibre::class, fn ($m) => $m->hasTo('dueno@dauntoque.com') && $m->titulo === 'Hola María' && str_contains($m->cuerpo, 'mariaperez'));
        $this->assertNull(Plantillas::personalizada('recordatorio_uso'));
    }

    public function test_chips_filtros_por_origen_y_estado_y_registro_de_propios(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post('/admin/chips', ['codigos' => "tienda-001\nhttps://portal.dauntoque.com/activar/tienda-002"])->assertSessionHas('status');
        $this->actingAs($admin)->post('/admin/chips/generar', ['cantidad' => 2]);
        $usado = Chip::create(['codigo' => 'usado-123', 'origen' => 'propio']);
        Tarjeta::create(['user_id' => $admin->id, 'nfc_uid' => 'usado-123', 'activada_en' => now(), 'vence_en' => now()->addYear()]);

        $this->assertSame(2, Chip::where('origen', 'propio')->where('codigo', 'like', 'tienda-%')->count());
        $this->assertSame(2, Chip::where('origen', 'sistema')->count());

        $codigos = fn (string $q) => $this->actingAs($admin)->get('/admin/chips?'.$q)->viewData('chips')->pluck('codigo')->sort()->values()->all();
        $this->assertNotContains('usado-123', $codigos(''));                // por defecto: sin activar
        $this->assertSame(['usado-123'], $codigos('estado=activos'));
        $this->assertSame(['tienda-001', 'tienda-002'], $codigos('origen=propio'));
        $this->assertCount(2, $codigos('origen=sistema'));
        $this->assertSame(['tienda-002'], $codigos('buscar=002'));

        $csv = $this->actingAs($admin)->get('/admin/chips/exportar?origen=propio&estado=todos')->assertOk()->streamedContent();
        $this->assertStringContainsString('tienda-001', $csv);
        $this->assertStringContainsString('usado-123', $csv);
    }
}
