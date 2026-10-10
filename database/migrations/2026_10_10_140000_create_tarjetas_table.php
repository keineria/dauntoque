<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tarjetas físicas y su servicio anual. El contador vive en la tarjeta (nfc_uid) y no en el perfil,
        // porque el chip se puede mudar de un perfil a otro (ProfileController::activar).
        Schema::create('tarjetas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('nfc_uid')->unique();
            $table->timestamp('activada_en');
            $table->timestamp('vence_en');
            $table->timestamps();
        });

        // Las tarjetas ya vinculadas no tienen fecha de activación guardada:
        // su año de servicio empieza el día de esta migración.
        $ahora = now();
        DB::table('profiles')->whereNotNull('nfc_uid')->where('nfc_uid', '!=', '')->orderBy('id')
            ->each(function ($perfil) use ($ahora) {
                DB::table('tarjetas')->insertOrIgnore([
                    'user_id' => $perfil->user_id,
                    'nfc_uid' => $perfil->nfc_uid,
                    'activada_en' => $ahora,
                    'vence_en' => $ahora->copy()->addDays(config('servicio.dias', 365)),
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarjetas');
    }
};
