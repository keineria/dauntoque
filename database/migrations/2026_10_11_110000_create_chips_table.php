<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Chips NFC vendidos. Solo los registrados aquí se pueden activar (config/chips.php). */
    public function up(): void
    {
        Schema::create('chips', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->string('nota')->nullable(); // pedido, cliente o lote
            $table->timestamps();
        });

        // Los chips que ya están en uso quedan registrados para que sigan funcionando
        $ahora = now();
        DB::table('profiles')->whereNotNull('nfc_uid')->where('nfc_uid', '<>', '')->pluck('nfc_uid')
            ->merge(DB::table('tarjetas')->pluck('nfc_uid'))
            ->unique()
            ->each(fn ($codigo) => DB::table('chips')->insertOrIgnore([
                'codigo' => $codigo, 'nota' => 'En uso antes del registro de chips', 'created_at' => $ahora, 'updated_at' => $ahora,
            ]));
    }

    public function down(): void
    {
        Schema::dropIfExists('chips');
    }
};
