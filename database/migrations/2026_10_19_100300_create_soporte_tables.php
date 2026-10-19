<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ayuda a clientes: tickets (respuesta por escrito) y chats en vivo con cola de atención
        Schema::create('soporte_conversaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('tipo', 10);                          // ticket, chat
            $table->string('asunto', 150);
            $table->string('estado', 15)->default('abierta');    // en_cola (chat esperando), abierta, cerrada
            $table->foreignId('agente_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('tomada_en')->nullable();
            $table->timestamp('cerrada_en')->nullable();
            $table->timestamp('ultimo_mensaje_en')->nullable();
            $table->timestamps();
            $table->index(['tipo', 'estado', 'created_at']);
        });

        Schema::create('soporte_mensajes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversacion_id')->constrained('soporte_conversaciones')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('es_agente')->default(false);
            $table->text('texto');
            $table->timestamp('created_at')->useCurrent();
            $table->index(['conversacion_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soporte_mensajes');
        Schema::dropIfExists('soporte_conversaciones');
    }
};
