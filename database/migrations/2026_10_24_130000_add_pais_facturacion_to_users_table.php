<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * País de facturación: fija la moneda de las renovaciones (COP o USD). Se toma de la ubicación al crear
     * el primer perfil y solo lo cambia el administrador. Las cuentas existentes toman el de su primer perfil.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->char('pais_facturacion', 2)->nullable();
        });

        DB::table('profiles')->whereNotNull('pais')->orderBy('id')->get(['user_id', 'pais'])->unique('user_id')
            ->each(fn ($p) => DB::table('users')->where('id', $p->user_id)->update(['pais_facturacion' => $p->pais]));
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('pais_facturacion'));
    }
};
