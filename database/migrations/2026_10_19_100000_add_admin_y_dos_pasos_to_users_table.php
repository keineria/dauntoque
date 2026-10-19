<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Rol de administrador (además de ADMIN_CORREOS) y contraseña temporal que se cambia al entrar
            $table->boolean('es_admin')->default(false)->after('password');
            $table->boolean('debe_cambiar_clave')->default(false)->after('es_admin');
            // Verificación en dos pasos (app autenticadora). Secreto y códigos de respaldo van cifrados
            $table->text('dos_pasos_secreto')->nullable()->after('debe_cambiar_clave');
            $table->text('dos_pasos_respaldo')->nullable()->after('dos_pasos_secreto');
            $table->timestamp('dos_pasos_activo_en')->nullable()->after('dos_pasos_respaldo');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['es_admin', 'debe_cambiar_clave', 'dos_pasos_secreto', 'dos_pasos_respaldo', 'dos_pasos_activo_en']);
        });
    }
};
