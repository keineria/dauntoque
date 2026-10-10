<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Último aviso de renovación enviado (mes, semana, vencido, pausa) para no repetirlo. */
    public function up(): void
    {
        Schema::table('tarjetas', function (Blueprint $table) {
            $table->string('aviso_etapa', 20)->nullable()->after('vence_en');
        });
    }

    public function down(): void
    {
        Schema::table('tarjetas', function (Blueprint $table) {
            $table->dropColumn('aviso_etapa');
        });
    }
};
