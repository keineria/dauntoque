<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Perfil activo explícito: solo cambia cuando el usuario lo elige (ProfileController::activar).
     * Antes se deducía del chip y del último perfil editado, así que cualquier guardado lo cambiaba.
     */
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->timestamp('activo_en')->nullable()->after('nfc_uid');
        });

        // Cada usuario conserva el perfil que hoy se ve en público (la regla anterior: chip primero, luego el más reciente)
        $ahora = now();
        DB::table('profiles')->select('user_id')->distinct()->orderBy('user_id')->pluck('user_id')
            ->each(function ($userId) use ($ahora) {
                $id = DB::table('profiles')->where('user_id', $userId)
                    ->orderByRaw("CASE WHEN nfc_uid IS NOT NULL AND nfc_uid <> '' THEN 1 ELSE 0 END DESC")
                    ->orderBy('updated_at', 'desc')
                    ->value('id');
                DB::table('profiles')->where('id', $id)->update(['activo_en' => $ahora]);
            });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn('activo_en');
        });
    }
};
