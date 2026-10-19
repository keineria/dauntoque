<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Cada compra pagada trae su propio código de activación (un chip por pedido). */
    public function up(): void
    {
        Schema::table('chips', function (Blueprint $table) {
            $table->unsignedBigInteger('pedido_id')->nullable()->unique()->after('nota');
        });
    }

    public function down(): void
    {
        Schema::table('chips', function (Blueprint $table) {
            $table->dropUnique(['pedido_id']);
            $table->dropColumn('pedido_id');
        });
    }
};
