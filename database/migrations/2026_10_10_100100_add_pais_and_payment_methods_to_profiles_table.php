<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Los medios de pago dejan de ser columnas fijas: lista [{type, value}] según el país del perfil
        Schema::table('profiles', function (Blueprint $table) {
            $table->char('pais', 2)->nullable()->after('direccion');
            $table->json('payment_methods')->nullable()->after('venmo');
        });

        // Se copian zelle, cashapp y venmo a la lista sin tocar las columnas viejas (quedan de respaldo).
        // Quien ya tenía alguno de esos medios queda en EE. UU.; el resto, en Colombia.
        DB::table('profiles')->orderBy('id')->each(function ($perfil) {
            $medios = [];
            foreach (['zelle', 'cashapp', 'venmo'] as $tipo) {
                $valor = trim((string) $perfil->{$tipo});
                if ($valor !== '') {
                    $medios[] = ['type' => $tipo, 'value' => $valor];
                }
            }
            DB::table('profiles')->where('id', $perfil->id)->update([
                'pais' => $medios ? 'US' : 'CO',
                'payment_methods' => $medios ? json_encode($medios) : null,
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn(['pais', 'payment_methods']);
        });
    }
};
