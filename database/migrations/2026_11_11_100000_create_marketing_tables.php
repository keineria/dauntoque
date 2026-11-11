<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Centro de mando: campañas con UTM, medición propia de visitas, origen de cada pedido y registro de avisos de pago. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campanas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 120);
            $table->string('canal', 20);                      // meta, google, tiktok, correo, whatsapp, qr, aliado, otro
            $table->string('utm_source', 100);
            $table->string('utm_medium', 100);
            $table->string('utm_campaign', 100)->unique();
            $table->string('destino', 500);                   // página a la que lleva el enlace
            $table->decimal('presupuesto', 14, 2)->nullable(); // lo que se invierte (lo escribe el administrador)
            $table->char('moneda', 3)->default('COP');
            $table->date('inicia_en')->nullable();
            $table->date('termina_en')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();
        });

        // Cada página vista del sitio de ventas y la compra (sin datos personales; visitante = número al azar del navegador)
        Schema::create('visitas_marketing', function (Blueprint $table) {
            $table->id();
            $table->char('visitante', 32);
            $table->string('pagina', 190);
            $table->string('utm_source', 100)->nullable();
            $table->string('utm_medium', 100)->nullable();
            $table->string('utm_campaign', 100)->nullable();
            $table->string('utm_content', 100)->nullable();
            $table->string('utm_term', 100)->nullable();
            $table->string('clic', 10)->nullable();          // gclid, fbclid, ttclid: vino de un anuncio
            $table->string('referente', 120)->nullable();     // dominio de donde llegó (google.com, instagram.com…)
            $table->char('pais', 2)->nullable();
            $table->string('dispositivo', 10)->nullable();    // celular, tableta, computador
            $table->timestamp('created_at')->useCurrent();

            $table->index('created_at');
            $table->index(['utm_campaign', 'created_at']);
        });

        Schema::table('pedidos', function (Blueprint $table) {
            $table->string('utm_source', 100)->nullable();
            $table->string('utm_medium', 100)->nullable();
            $table->string('utm_campaign', 100)->nullable();
            $table->string('utm_content', 100)->nullable();
            $table->string('utm_term', 100)->nullable();
            $table->string('clic', 10)->nullable();
            $table->char('visitante', 32)->nullable();

            $table->index('utm_campaign');
        });

        // Avisos que llegan de las pasarelas (Bold hoy, Stripe después): para ver que la conexión funciona
        Schema::create('avisos_pago', function (Blueprint $table) {
            $table->id();
            $table->string('proveedor', 20);
            $table->string('tipo', 40)->nullable();          // SALE_APPROVED, SALE_REJECTED…
            $table->string('resultado', 40);                  // pedido, renovacion, sin coincidencia, firma inválida
            $table->string('referencia', 80)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['proveedor', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('avisos_pago');
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropIndex(['utm_campaign']);
            $table->dropColumn(['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'clic', 'visitante']);
        });
        Schema::dropIfExists('visitas_marketing');
        Schema::dropIfExists('campanas');
    }
};
