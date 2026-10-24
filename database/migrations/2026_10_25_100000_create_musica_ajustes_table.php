<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** App Música: la canción destacada y la lista de Spotify que el dueño muestra en su perfil. */
    public function up(): void
    {
        Schema::create('musica_ajustes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('titulo', 60)->nullable();
            // Guardado ya limpio como "tipo/id" de Spotify (por ejemplo "track/4uLU6hMCjMI75M1A2tKUQC")
            $table->string('cancion', 80)->nullable();
            $table->string('lista', 80)->nullable();
            $table->boolean('mostrar')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('musica_ajustes');
    }
};
