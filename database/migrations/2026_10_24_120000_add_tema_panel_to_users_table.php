<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Tema de color del panel que elige cada dueño en Mi cuenta (App\Support\TemaPanel). */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('tema_panel', 20)->default('dauntoque');
            $table->string('apariencia_panel', 10)->default('oscuro');
        });
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn(['tema_panel', 'apariencia_panel']));
    }
};
