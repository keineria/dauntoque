<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Eventos del perfil público: visitas y clics (contacto, WhatsApp, pagos, redes)
        Schema::create('profile_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->string('accion', 40);
            // Huella anónima del visitante por día (sin guardar la IP) para contar visitantes únicos
            $table->char('visitante', 32)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['profile_id', 'accion', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_events');
    }
};
