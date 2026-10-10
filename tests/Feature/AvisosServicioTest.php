<?php

namespace Tests\Feature;

use App\Mail\ServicioPorVencer;
use App\Models\Profile;
use App\Models\Tarjeta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AvisosServicioTest extends TestCase
{
    use RefreshDatabase;

    private function tarjeta(string $venceEn): Tarjeta
    {
        $usuario = User::factory()->create(['email' => 'cliente@correo.co']);
        Profile::create(['user_id' => $usuario->id, 'nickname' => 'cliente', 'nombre' => 'Cliente', 'pais' => 'CO']);

        return Tarjeta::create(['user_id' => $usuario->id, 'nfc_uid' => 'chip-123456', 'activada_en' => Carbon::parse($venceEn)->subDays(365), 'vence_en' => $venceEn]);
    }

    public function test_avisa_una_vez_por_etapa(): void
    {
        Mail::fake();
        Carbon::setTestNow('2026-10-10 09:00:00');
        $tarjeta = $this->tarjeta('2026-11-05 09:00:00'); // 26 días

        $this->artisan('servicio:avisar')->assertSuccessful();
        $this->artisan('servicio:avisar')->assertSuccessful();

        Mail::assertSent(ServicioPorVencer::class, 1);
        Mail::assertSent(ServicioPorVencer::class, fn ($m) => $m->etapa === 'mes' && $m->hasTo('cliente@correo.co') && $m->precio === '$120.000 COP');
        $this->assertSame('mes', $tarjeta->fresh()->aviso_etapa);

        Carbon::setTestNow('2026-10-31 09:00:00'); // 5 días
        $this->artisan('servicio:avisar');
        Carbon::setTestNow('2026-11-07 09:00:00'); // vencida, en gracia
        $this->artisan('servicio:avisar');

        Mail::assertSent(ServicioPorVencer::class, 3);
        $this->assertSame('vencido', $tarjeta->fresh()->aviso_etapa);
    }

    public function test_no_avisa_si_falta_mucho_y_renovar_reinicia_los_avisos(): void
    {
        Mail::fake();
        Carbon::setTestNow('2026-10-10 09:00:00');
        $tarjeta = $this->tarjeta('2027-03-01 09:00:00');

        $this->artisan('servicio:avisar');
        Mail::assertNothingSent();

        $tarjeta->update(['vence_en' => '2026-10-15 09:00:00', 'aviso_etapa' => 'semana']);
        $tarjeta->fresh()->renovar();
        $this->assertNull($tarjeta->fresh()->aviso_etapa);
        $this->assertNull($tarjeta->fresh()->avisoPendiente());
    }

    public function test_si_se_salto_etapas_solo_manda_la_mas_reciente(): void
    {
        Mail::fake();
        Carbon::setTestNow('2026-10-10 09:00:00');
        $this->tarjeta('2026-10-13 09:00:00'); // 3 días, nunca se avisó

        $this->artisan('servicio:avisar');

        Mail::assertSent(ServicioPorVencer::class, 1);
        Mail::assertSent(ServicioPorVencer::class, fn ($m) => $m->etapa === 'semana');
    }

    public function test_el_correo_se_arma_sin_errores(): void
    {
        Carbon::setTestNow('2026-10-10 09:00:00');
        $tarjeta = $this->tarjeta('2026-10-17 09:00:00');

        $html = (new ServicioPorVencer($tarjeta, 'semana', '$120.000 COP'))->render();

        $this->assertStringContainsString('Te quedan 7 días', $html);
        $this->assertStringContainsString('Renovar mi tarjeta · $120.000 COP', $html);
    }
}
