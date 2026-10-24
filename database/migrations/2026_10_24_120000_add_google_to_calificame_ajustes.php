<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Califícame: negocio de Google que conecta el dueño y su calificación y reseñas (se actualizan una vez al día). */
    public function up(): void
    {
        Schema::table('calificame_ajustes', function (Blueprint $table) {
            $table->boolean('mostrar_google')->default(true);
            $table->string('google_place_id', 300)->nullable();
            $table->string('google_nombre', 200)->nullable();
            $table->string('google_direccion', 300)->nullable();
            $table->string('google_maps_url', 500)->nullable();
            $table->decimal('google_rating', 2, 1)->nullable();
            $table->unsignedInteger('google_total')->nullable();
            $table->json('google_resenas')->nullable();
            $table->timestamp('google_actualizado_en')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('calificame_ajustes', fn (Blueprint $table) => $table->dropColumn([
            'mostrar_google', 'google_place_id', 'google_nombre', 'google_direccion', 'google_maps_url',
            'google_rating', 'google_total', 'google_resenas', 'google_actualizado_en',
        ]));
    }
};
