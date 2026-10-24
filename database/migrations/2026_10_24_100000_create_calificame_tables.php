<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** App Califícame: calificaciones de los clientes, ajustes del dueño y aviso enviado tras cada cita. */
    public function up(): void
    {
        Schema::create('calificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cita_id')->nullable()->unique()->constrained('citas')->nullOnDelete();
            $table->unsignedTinyInteger('estrellas');
            $table->text('comentario')->nullable();
            $table->string('nombre', 80)->nullable();
            $table->string('contacto', 120)->nullable();
            // El dueño elige qué comentarios salen en su perfil (ninguno por defecto)
            $table->boolean('visible')->default(false);
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
        });

        Schema::create('calificame_ajustes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('mostrar_sello')->default(true);
            $table->boolean('mostrar_boton')->default(true);
            $table->boolean('pedir_tras_cita')->default(true);
            $table->string('google_url', 300)->nullable();
            $table->timestamps();
        });

        Schema::table('citas', function (Blueprint $table) {
            $table->timestamp('calificacion_pedida_en')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('citas', fn (Blueprint $table) => $table->dropColumn('calificacion_pedida_en'));
        Schema::dropIfExists('calificame_ajustes');
        Schema::dropIfExists('calificaciones');
    }
};
