<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Dónde y con qué se activó cada tarjeta, origen de cada código (del sistema o propio)
     * y cuándo se le mandó a cada cliente el último recordatorio de uso.
     */
    public function up(): void
    {
        Schema::table('tarjetas', function (Blueprint $table) {
            $table->string('activacion_medio', 20)->nullable(); // tarjeta (NFC) o codigo (escrito en el panel)
            $table->string('activacion_lugar', 120)->nullable();
            $table->string('activacion_dispositivo', 120)->nullable();
        });

        Schema::table('chips', function (Blueprint $table) {
            $table->string('origen', 10)->default('sistema')->index(); // sistema: los crea el portal · propio: registrados a mano
        });
        // Los anteriores: los de compras y los generados (12 letras y números) son del sistema; el resto, propios
        DB::table('chips')->whereNull('pedido_id')->get(['id', 'codigo'])->each(function ($chip) {
            if (! preg_match('~^[a-z0-9]{12}$~', $chip->codigo)) {
                DB::table('chips')->where('id', $chip->id)->update(['origen' => 'propio']);
            }
        });

        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('recordatorio_uso_en')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('tarjetas', fn (Blueprint $table) => $table->dropColumn(['activacion_medio', 'activacion_lugar', 'activacion_dispositivo']));
        Schema::table('chips', function (Blueprint $table) {
            $table->dropIndex(['origen']);
            $table->dropColumn('origen');
        });
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('recordatorio_uso_en'));
    }
};
