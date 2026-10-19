<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            // Carrito abandonado: la última promoción se manda una sola vez por correo
            $table->char('promo_token', 40)->nullable()->unique()->after('correo_enviado_en');
            $table->timestamp('promo_enviada_en')->nullable()->after('promo_token');
            // Pedido nuevo hecho con esa promoción (precio con descuento)
            $table->foreignId('promo_de_pedido_id')->nullable()->after('promo_enviada_en')->constrained('pedidos')->nullOnDelete();
            $table->index(['estado', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('promo_de_pedido_id');
            $table->dropIndex(['estado', 'created_at']);
            $table->dropUnique(['promo_token']);
            $table->dropColumn(['promo_token', 'promo_enviada_en']);
        });
    }
};
