<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pagos en línea (por ahora, renovaciones del servicio de la tarjeta con Bold)
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tarjeta_id')->nullable()->constrained()->nullOnDelete();
            $table->string('concepto', 30);              // renovacion
            $table->string('proveedor', 20);             // bold
            $table->char('pais', 2);
            $table->char('moneda', 3);
            $table->decimal('monto', 12, 2);
            $table->string('estado', 20)->default('pendiente'); // pendiente, pagado, rechazado
            $table->string('link_id')->nullable()->unique();     // LNK_... de Bold
            $table->string('url')->nullable();
            $table->string('pago_proveedor_id')->nullable();     // payment_id que confirma el webhook
            $table->timestamp('pagado_en')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
