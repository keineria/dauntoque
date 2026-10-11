<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * App adicional "Agenda de citas". Todo cuelga del usuario (no de un perfil): el perfil público que se ve
 * en dauntoque.com/usuario es el activo del usuario y puede cambiar, pero la agenda es una sola.
 * Las horas se guardan en UTC y se muestran en la zona horaria de la agenda (agenda_ajustes.zona_horaria).
 */
return new class extends Migration
{
    public function up(): void
    {
        // Apps adicionales del usuario (agenda; luego catálogo y reseñas) y hasta cuándo están pagas
        Schema::create('apps_adicionales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('app', 30);
            $table->timestamp('activada_en')->nullable();
            $table->timestamp('vence_en')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'app']);
        });

        Schema::create('agenda_ajustes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('zona_horaria', 40)->default('America/Bogota');
            $table->unsignedSmallInteger('intervalo_min')->default(30);
            $table->unsignedSmallInteger('antelacion_min')->default(120);
            $table->unsignedSmallInteger('dias_adelante')->default(30);
            $table->unsignedSmallInteger('limite_cancelar_horas')->default(4);
            $table->text('mensaje_whatsapp')->nullable();
            $table->text('politica_cancelacion')->nullable();
            // Google Calendar: el refresh token va cifrado (cast encrypted)
            $table->text('google_token')->nullable();
            $table->string('google_correo')->nullable();
            $table->timestamps();
        });

        Schema::create('agenda_servicios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('nombre', 80);
            $table->string('descripcion', 200)->nullable();
            $table->unsignedSmallInteger('duracion_min');
            $table->unsignedSmallInteger('colchon_min')->default(0);
            $table->decimal('precio', 12, 2)->nullable();
            $table->boolean('requiere_aprobacion')->default(false);
            $table->boolean('activo')->default(true);
            $table->unsignedSmallInteger('orden')->default(0);
            $table->timestamps();
        });

        // Horario semanal: una fila por franja (dia_semana 0 = domingo ... 6 = sábado)
        Schema::create('agenda_horarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('dia_semana');
            $table->time('desde');
            $table->time('hasta');
            $table->index(['user_id', 'dia_semana']);
        });

        // Días u horas cerradas (vacaciones, festivos, una tarde libre)
        Schema::create('agenda_bloqueos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('desde');
            $table->timestamp('hasta');
            $table->string('motivo', 120)->nullable();
            $table->timestamps();
            $table->index(['user_id', 'desde']);
        });

        Schema::create('citas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('servicio_id')->nullable()->constrained('agenda_servicios')->nullOnDelete();
            // Copia del servicio al reservar: si el dueño lo cambia o lo borra, la cita no cambia
            $table->string('servicio_nombre', 80);
            $table->unsignedSmallInteger('colchon_min')->default(0);
            $table->string('cliente_nombre', 80);
            $table->string('cliente_correo');
            $table->string('cliente_whatsapp', 30)->nullable();
            $table->string('nota', 500)->nullable();
            $table->timestamp('inicia_en');
            $table->timestamp('termina_en');
            // pendiente (espera aprobación), confirmada, cambio (nueva hora en espera), cancelada
            $table->string('estado', 15);
            $table->timestamp('propuesta_inicia_en')->nullable();
            $table->string('propuesta_por', 10)->nullable();
            $table->string('cancelada_por', 10)->nullable();
            $table->string('motivo', 200)->nullable();
            $table->string('token', 64)->unique();
            $table->string('idioma', 2)->default('es');
            $table->timestamp('recordado_24h_en')->nullable();
            $table->timestamp('recordado_2h_en')->nullable();
            $table->string('google_evento_id')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'inicia_en']);
            $table->index('cliente_correo');
        });

        Schema::create('cita_historial', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cita_id')->constrained('citas')->cascadeOnDelete();
            $table->string('accion', 30);
            $table->string('por', 10);
            $table->json('detalle')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cita_historial');
        Schema::dropIfExists('citas');
        Schema::dropIfExists('agenda_bloqueos');
        Schema::dropIfExists('agenda_horarios');
        Schema::dropIfExists('agenda_servicios');
        Schema::dropIfExists('agenda_ajustes');
        Schema::dropIfExists('apps_adicionales');
    }
};
