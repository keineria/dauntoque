<?php

namespace Tests\Feature;

use App\Mail\AgendaCorreo;
use App\Models\AgendaAjuste;
use App\Models\AgendaBloqueo;
use App\Models\AgendaHorario;
use App\Models\AgendaServicio;
use App\Models\Cita;
use App\Models\Profile;
use App\Models\User;
use App\Services\Agenda\Agenda;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AgendaTest extends TestCase
{
    use RefreshDatabase;

    private User $dueno;
    private AgendaServicio $corte;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Mail::fake();
        $this->withHeader('Accept-Language', 'es-CO,es;q=0.9');
        // Lunes 12 de octubre de 2026, 7:00 a. m. en Bogotá
        Carbon::setTestNow(Carbon::parse('2026-10-12 07:00', 'America/Bogota'));

        $this->dueno = User::factory()->create(['email' => 'camilo@barberia.co']);
        Profile::create(['user_id' => $this->dueno->id, 'nickname' => 'barberia', 'nombre' => 'Palacio Barbershop', 'pais' => 'CO', 'telefono' => '3001234567']);
        Agenda::activar($this->dueno->id);
        $this->corte = AgendaServicio::create(['user_id' => $this->dueno->id, 'nombre' => 'Corte', 'duracion_min' => 40, 'precio' => 25000]);
        // Lunes y martes de 8:00 a 12:00
        foreach ([1, 2] as $dia) {
            AgendaHorario::create(['user_id' => $this->dueno->id, 'dia_semana' => $dia, 'desde' => '08:00', 'hasta' => '12:00']);
        }
    }

    private function horas(string $fecha): array
    {
        return $this->getJson('/barberia/agenda/horas?servicio=' . $this->corte->id)->assertOk()->json($fecha) ?? [];
    }

    private function reservar(string $fecha, string $hora, array $extra = [])
    {
        return $this->post('/barberia/agenda', $extra + [
            'servicio_id' => $this->corte->id, 'fecha' => $fecha, 'hora' => $hora,
            'nombre' => 'Ana Pérez', 'correo' => 'ana@correo.co', 'whatsapp' => '3109876543',
        ]);
    }

    public function test_muestra_solo_horas_libres_con_la_antelacion_minima(): void
    {
        // Antelación de 2 horas: desde las 9:00; un corte de 40 min a las 11:30 ya no cabe antes de las 12:00
        $this->assertSame(['09:00', '09:30', '10:00', '10:30', '11:00'], $this->horas('2026-10-12'));
        $this->assertCount(7, $this->horas('2026-10-13'));
        $this->assertSame([], $this->horas('2026-10-14'));
    }

    public function test_el_cliente_reserva_y_llegan_los_correos(): void
    {
        $respuesta = $this->reservar('2026-10-13', '09:00');

        $cita = Cita::firstOrFail();
        $respuesta->assertRedirect('/cita/' . $cita->token . '?listo=reservada');
        $this->assertSame('confirmada', $cita->estado);
        $this->assertTrue($cita->inicia_en->eq(Carbon::parse('2026-10-13 09:00', 'America/Bogota')));
        Mail::assertSent(AgendaCorreo::class, fn ($m) => $m->tipo === 'confirmada' && $m->hasTo('ana@correo.co'));
        Mail::assertSent(AgendaCorreo::class, fn ($m) => $m->tipo === 'nueva' && $m->hasTo('camilo@barberia.co'));

        // La hora tomada (y la que se cruza con ella) ya no aparecen
        $horas = $this->horas('2026-10-13');
        $this->assertNotContains('09:00', $horas);
        $this->assertNotContains('08:30', $horas);
        $this->assertContains('10:00', $horas);

        $this->get('/cita/' . $cita->token)->assertOk()->assertSee('Confirmada')->assertSee('Corte');
    }

    public function test_no_se_puede_tomar_una_hora_ocupada(): void
    {
        $this->reservar('2026-10-13', '09:00');
        $this->reservar('2026-10-13', '09:30', ['correo' => 'otro@correo.co'])->assertSessionHasErrors('hora');

        $this->assertSame(1, Cita::count());
    }

    public function test_un_servicio_con_aprobacion_espera_al_dueno(): void
    {
        $this->corte->update(['requiere_aprobacion' => true]);
        $this->reservar('2026-10-13', '10:00')->assertRedirectContains('listo=pendiente');
        $cita = Cita::firstOrFail();
        $this->assertSame('pendiente', $cita->estado);
        Mail::assertSent(AgendaCorreo::class, fn ($m) => $m->tipo === 'recibida');

        $this->actingAs($this->dueno)->get('/dashboard/agenda')->assertOk()->assertSee('Por resolver')->assertSee('Ana Pérez');
        $this->actingAs($this->dueno)->post("/dashboard/agenda/citas/{$cita->id}/aprobar")->assertRedirect();

        $this->assertSame('confirmada', $cita->fresh()->estado);
        Mail::assertSent(AgendaCorreo::class, fn ($m) => $m->tipo === 'confirmada' && $m->hasTo('ana@correo.co'));
    }

    public function test_el_cliente_pide_otra_hora_y_el_dueno_la_acepta(): void
    {
        $this->reservar('2026-10-13', '09:00');
        $cita = Cita::firstOrFail();

        $this->post("/cita/{$cita->token}/cambiar", ['fecha' => '2026-10-13', 'hora' => '11:00'])->assertRedirectContains('listo=cambio');
        $cita->refresh();
        $this->assertSame('cambio', $cita->estado);
        $this->assertTrue($cita->inicia_en->eq(Carbon::parse('2026-10-13 09:00', 'America/Bogota')), 'Mientras tanto sigue a la hora original');
        Mail::assertSent(AgendaCorreo::class, fn ($m) => $m->tipo === 'cambio_pedido' && $m->paraDuenio);
        // La hora pedida queda apartada
        $this->assertNotContains('11:00', $this->horas('2026-10-13'));

        $this->actingAs($this->dueno)->post("/dashboard/agenda/citas/{$cita->id}/responder", ['acepta' => 1]);
        $cita->refresh();
        $this->assertSame('confirmada', $cita->estado);
        $this->assertTrue($cita->inicia_en->eq(Carbon::parse('2026-10-13 11:00', 'America/Bogota')));
        Mail::assertSent(AgendaCorreo::class, fn ($m) => $m->tipo === 'cambio_aceptado' && $m->hasTo('ana@correo.co'));
    }

    public function test_el_dueno_propone_otra_hora_y_el_cliente_decide(): void
    {
        $this->reservar('2026-10-13', '09:00');
        $cita = Cita::firstOrFail();

        // El dueño puede mover fuera de su horario (las 15:00) si no se cruza con nada
        $this->actingAs($this->dueno)->post("/dashboard/agenda/citas/{$cita->id}/mover", ['fecha' => '2026-10-13', 'hora' => '15:00']);
        $this->assertSame('cambio', $cita->fresh()->estado);
        Mail::assertSent(AgendaCorreo::class, fn ($m) => $m->tipo === 'cambio_propuesto' && $m->hasTo('ana@correo.co'));

        auth()->logout();
        $this->post("/cita/{$cita->token}/responder", ['acepta' => 0])->assertRedirectContains('listo=rechazada');
        $cita->refresh();
        $this->assertSame('confirmada', $cita->estado);
        $this->assertTrue($cita->inicia_en->eq(Carbon::parse('2026-10-13 09:00', 'America/Bogota')));
        Mail::assertSent(AgendaCorreo::class, fn ($m) => $m->tipo === 'cambio_rechazado' && $m->paraDuenio);
    }

    public function test_el_cliente_cancela_solo_antes_del_limite(): void
    {
        $this->reservar('2026-10-12', '10:00');
        $hoy = Cita::firstOrFail();
        // Límite de 4 horas: a las 7:00 ya no puede cancelar la cita de las 10:00
        $this->post("/cita/{$hoy->token}/cancelar")->assertForbidden();

        $this->reservar('2026-10-13', '10:00', ['correo' => 'luis@correo.co']);
        $manana = Cita::where('cliente_correo', 'luis@correo.co')->firstOrFail();
        $this->post("/cita/{$manana->token}/cancelar")->assertRedirectContains('listo=cancelada');
        $this->assertSame('cancelada', $manana->fresh()->estado);
        Mail::assertSent(AgendaCorreo::class, fn ($m) => $m->tipo === 'cancelada' && $m->paraDuenio);
        $this->assertContains('10:00', $this->horas('2026-10-13'));
    }

    public function test_los_dias_cerrados_no_se_pueden_reservar(): void
    {
        $this->actingAs($this->dueno)->post('/dashboard/agenda/bloqueos', ['desde_fecha' => '2026-10-13', 'hasta_fecha' => '2026-10-13'])->assertRedirect();
        $this->assertSame(1, AgendaBloqueo::count());

        $this->assertSame([], $this->horas('2026-10-13'));
        $this->assertNotEmpty($this->horas('2026-10-12'));
    }

    public function test_mis_citas_con_codigo_al_correo(): void
    {
        $this->reservar('2026-10-13', '09:00');

        $this->post('/mis-citas/codigo', ['correo' => 'ANA@correo.co'])->assertRedirect('/mis-citas');
        $codigo = null;
        Mail::assertSent(AgendaCorreo::class, function ($m) use (&$codigo) {
            if ($m->tipo !== 'codigo') {
                return false;
            }
            $codigo = $m->extra['codigo'];

            return $m->hasTo('ana@correo.co');
        });

        $this->post('/mis-citas/entrar', ['codigo' => '000000' === $codigo ? '111111' : '000000'])->assertSessionHasErrors('codigo');
        $this->post('/mis-citas/entrar', ['codigo' => $codigo])->assertRedirect('/mis-citas');
        $this->get('/mis-citas')->assertOk()->assertSee('Corte')->assertSee('Palacio Barbershop');
    }

    public function test_un_correo_sin_citas_no_recibe_codigo(): void
    {
        $this->post('/mis-citas/codigo', ['correo' => 'nadie@correo.co'])->assertRedirect('/mis-citas');
        Mail::assertNotSent(AgendaCorreo::class);
    }

    public function test_recordatorios_24_y_2_horas_antes(): void
    {
        $this->reservar('2026-10-13', '10:00');

        Carbon::setTestNow(Carbon::parse('2026-10-12 10:05', 'America/Bogota'));
        $this->artisan('citas:recordar')->assertSuccessful();
        Mail::assertSent(AgendaCorreo::class, fn ($m) => $m->tipo === 'recordatorio');
        $this->assertNotNull(Cita::first()->recordado_24h_en);

        Carbon::setTestNow(Carbon::parse('2026-10-13 08:05', 'America/Bogota'));
        $this->artisan('citas:recordar')->assertSuccessful();
        $this->assertNotNull(Cita::first()->recordado_2h_en);
        Mail::assertSent(AgendaCorreo::class, fn ($m) => $m->tipo === 'recordatorio', 2);
    }

    public function test_una_solicitud_sin_aprobar_vence_a_su_hora(): void
    {
        $this->corte->update(['requiere_aprobacion' => true]);
        $this->reservar('2026-10-13', '09:00');

        Carbon::setTestNow(Carbon::parse('2026-10-13 09:01', 'America/Bogota'));
        $this->artisan('citas:recordar')->assertSuccessful();

        $this->assertSame('cancelada', Cita::first()->estado);
    }

    public function test_google_calendar_bloquea_lo_ocupado_y_recibe_la_cita(): void
    {
        config(['services.google.client_id' => 'cliente', 'services.google.client_secret' => 'secreto']);
        AgendaAjuste::de($this->dueno->id)->update(['google_token' => 'refresh-token']);
        Http::fake([
            'oauth2.googleapis.com/token' => Http::response(['access_token' => 'acceso', 'expires_in' => 3600]),
            'www.googleapis.com/calendar/v3/freeBusy' => Http::response(['calendars' => ['primary' => ['busy' => [
                ['start' => '2026-10-13T15:00:00Z', 'end' => '2026-10-13T16:00:00Z'], // 10:00 a 11:00 en Bogotá
            ]]]]),
            'www.googleapis.com/calendar/v3/calendars/primary/events' => Http::response(['id' => 'evento-1']),
        ]);

        $horas = $this->horas('2026-10-13');
        $this->assertNotContains('10:00', $horas);
        $this->assertNotContains('10:30', $horas);
        $this->assertContains('11:00', $horas);

        $this->reservar('2026-10-13', '08:00');
        $this->assertSame('evento-1', Cita::first()->google_evento_id);
        Http::assertSent(fn ($r) => str_ends_with($r->url(), '/calendars/primary/events') && $r['summary'] === 'Corte · Ana Pérez');
    }

    public function test_el_perfil_muestra_agendar_cita_solo_con_la_agenda_lista(): void
    {
        $this->get('/barberia')->assertOk()->assertDontSee('\u0022agenda\u0022:null', false)->assertSee('agenda', false);

        AgendaHorario::query()->delete();
        $this->get('/barberia')->assertOk()->assertSee('\u0022agenda\u0022:null', false);
        $this->get('/barberia/agenda')->assertNotFound();
    }

    public function test_el_dueno_publica_oculta_y_da_prioridad_al_boton(): void
    {
        $perfil = Profile::where('user_id', $this->dueno->id)->first();
        $this->actingAs($this->dueno)->get('/dashboard/agenda')->assertOk()->assertSee('Botón "Agendar cita" en tu perfil', false)->assertSee('Darle prioridad');

        $this->actingAs($this->dueno)->post('/dashboard/agenda/boton', ['perfil_id' => $perfil->id, 'visible' => 0])->assertRedirect();
        $this->get('/barberia')->assertOk()->assertSee('\u0022agenda\u0022:null', false);
        $this->actingAs($this->dueno)->get('/dashboard/agenda')->assertSee('Publicar botón en el perfil');

        $this->actingAs($this->dueno)->post('/dashboard/agenda/boton', ['perfil_id' => $perfil->id, 'visible' => 1, 'prioridad' => 1]);
        $this->get('/barberia')->assertOk()->assertDontSee('\u0022agenda\u0022:null', false)->assertSee('\u0022agenda_prioridad\u0022:true', false);
    }

    public function test_el_editor_ofrece_el_boton_de_la_agenda(): void
    {
        $perfil = Profile::where('user_id', $this->dueno->id)->first();
        $this->actingAs($this->dueno)->get('/editor?id=' . $perfil->id)->assertOk()->assertSee('Mostrar el botón en este perfil')->assertSee('Darle prioridad');

        $otro = User::factory()->create();
        $this->actingAs($otro)->get('/editor')->assertOk()->assertSee('Activar la agenda');
    }

    public function test_la_agenda_avisa_si_la_cuenta_no_tiene_perfil(): void
    {
        $sinPerfil = User::factory()->create();
        Agenda::activar($sinPerfil->id);
        $this->actingAs($sinPerfil)->get('/dashboard/agenda')->assertOk()->assertSee('Esta cuenta todavía no tiene perfil');
    }

    public function test_la_agenda_vencida_solo_muestra_whatsapp(): void
    {
        Agenda::app($this->dueno->id)->update(['vence_en' => now()->subDay()]);

        $this->get('/barberia/agenda')->assertOk()->assertSee('wa.me/3001234567', false)->assertDontSee('servicio_id');
        $this->reservar('2026-10-13', '09:00')->assertNotFound();
    }

    public function test_el_dueno_activa_la_agenda_gratis_por_un_ano(): void
    {
        $nuevo = User::factory()->create();
        Profile::create(['user_id' => $nuevo->id, 'nickname' => 'nuevo-negocio', 'nombre' => 'Nuevo', 'pais' => 'US']);

        $this->actingAs($nuevo)->get('/dashboard/agenda')->assertOk()->assertSee('Activar gratis por un año')->assertSee('$39 USD');
        $this->actingAs($nuevo)->post('/dashboard/agenda/activar')->assertRedirect('/dashboard/agenda/ajustes');

        $app = Agenda::app($nuevo->id);
        $this->assertTrue($app->vence_en->isSameDay(now()->addDays(365)));
        $this->assertSame('America/New_York', AgendaAjuste::de($nuevo->id)->zona_horaria);
        $this->actingAs($nuevo)->get('/dashboard/agenda/ajustes')->assertOk()->assertSee('Horario semanal');
    }

    public function test_ver_apps_lista_la_agenda_y_al_activarla_sale_en_apps_activas(): void
    {
        $nuevo = User::factory()->create();
        Profile::create(['user_id' => $nuevo->id, 'nickname' => 'otro-negocio', 'nombre' => 'Otro', 'pais' => 'CO']);

        $this->actingAs($nuevo)->get('/dashboard/apps')->assertOk()
            ->assertSee('Agenda de citas')->assertSee('$99.000 COP')->assertSee('Próximamente')
            ->assertSee('Ver apps')->assertDontSee('<div class="dt-small">Apps activas</div>', false);

        $this->actingAs($nuevo)->post('/dashboard/agenda/activar');
        $this->actingAs($nuevo)->get('/dashboard/apps')->assertOk()->assertSee('<div class="dt-small">Apps activas</div>', false)->assertSee('Abrir');
    }

    public function test_el_dueno_escribe_su_politica_de_cancelacion(): void
    {
        $this->actingAs($this->dueno)->post('/dashboard/agenda/reglas', [
            'zona_horaria' => 'America/Bogota', 'intervalo_min' => 30, 'antelacion_min' => 120, 'dias_adelante' => 30,
            'limite_cancelar_horas' => 24, 'politica_cancelacion' => 'Si no llegas, la próxima se paga antes.',
        ])->assertRedirect();

        $this->get('/barberia/agenda')->assertOk()->assertSee('hasta 24 horas antes')->assertSee('Si no llegas, la próxima se paga antes.');
        $this->reservar('2026-10-13', '09:00');
        $this->get('/cita/' . Cita::first()->token)->assertOk()->assertSee('Política de cancelación')->assertSee('Si no llegas, la próxima se paga antes.');
    }

    public function test_el_dueno_guarda_servicios_y_horario(): void
    {
        $this->actingAs($this->dueno)->post('/dashboard/agenda/servicios', ['nombre' => 'Barba', 'duracion_min' => 20, 'precio' => 15000, 'requiere_aprobacion' => '1']);
        $this->assertTrue(AgendaServicio::where('nombre', 'Barba')->firstOrFail()->requiere_aprobacion);

        $this->actingAs($this->dueno)->post('/dashboard/agenda/horario', [
            'abierto' => [3 => '1'],
            'dias' => [3 => [['desde' => '09:00', 'hasta' => '12:00'], ['desde' => '14:00', 'hasta' => '18:00']], 4 => [['desde' => '09:00', 'hasta' => '12:00']]],
        ])->assertRedirect();

        $this->assertSame(2, AgendaHorario::where('user_id', $this->dueno->id)->count());
        $this->assertSame(0, AgendaHorario::where('dia_semana', 4)->count(), 'El jueves quedó cerrado');
    }

    public function test_los_nombres_de_la_agenda_estan_reservados(): void
    {
        foreach (['mis-citas', 'cita', 'agenda'] as $nombre) {
            $this->assertContains($nombre, config('perfiles.reservados'));
        }
    }
}
