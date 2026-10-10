<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Compras de tarjetas desde el sitio de ventas (aún sin cuenta en el portal)
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique()->nullable();  // DT-00042, se asigna al crear
            $table->char('token', 40)->unique();                  // enlace privado de la página del pedido
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // se llena al registrarse con el mismo correo
            $table->string('nombre');
            $table->string('correo');
            $table->string('celular', 30);
            $table->string('documento', 30)->nullable();
            $table->string('departamento', 60);
            $table->string('ciudad', 80);
            $table->string('direccion');
            $table->string('acabado', 20);                        // matte-black, crystal
            $table->string('nombre_tarjeta', 40)->nullable();
            $table->char('pais', 2);
            $table->char('moneda', 3);
            $table->decimal('monto', 12, 2);
            $table->string('estado', 20)->default('pendiente');  // pendiente, pagado, rechazado
            $table->string('link_id')->nullable()->unique();      // LNK_... de Bold
            $table->string('pago_proveedor_id')->nullable();
            $table->timestamp('pagado_en')->nullable();
            $table->timestamp('correo_enviado_en')->nullable();
            $table->timestamps();

            $table->index('correo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
