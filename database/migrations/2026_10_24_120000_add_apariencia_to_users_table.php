<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Apariencia del panel que elige cada usuario (config/apariencia.php); vacío = DaUnToque oscuro. */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('panel_paleta', 20)->nullable();
            $table->string('panel_modo', 10)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn(['panel_paleta', 'panel_modo']));
    }
};
