<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // País de venta: moneda, idioma por defecto y WhatsApp del equipo de ventas
        Schema::create('markets', function (Blueprint $table) {
            $table->char('code', 2)->primary();
            $table->string('name', 60);
            $table->char('currency', 3);
            $table->string('locale_default', 5);
            $table->string('whatsapp', 20)->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Lo que vende dauntoque.com (la tarjeta y sus acabados)
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 60)->unique();
            $table->string('name', 120);
            $table->string('finish', 40)->nullable();
            $table->string('nfc_chip', 20)->nullable();
            $table->json('media')->nullable();
            $table->timestamps();
        });

        // Precio por país, con el "antes" para mostrar la oferta y vigencia opcional
        Schema::create('prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->char('market_code', 2);
            $table->decimal('amount', 12, 2);
            $table->decimal('compare_at', 12, 2)->nullable();
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_to')->nullable();
            $table->timestamps();

            $table->foreign('market_code')->references('code')->on('markets')->cascadeOnUpdate();
            $table->index(['product_id', 'market_code']);
        });

        // Cuentas donde dauntoque.com recibe los pagos de sus clientes (no son las de los perfiles)
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->char('market_code', 2);
            $table->string('type', 20);
            $table->string('label', 60);
            $table->string('account_value', 120)->nullable();
            $table->text('instructions')->nullable();
            $table->boolean('active')->default(false);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();

            $table->foreign('market_code')->references('code')->on('markets')->cascadeOnUpdate();
            $table->unique(['market_code', 'type']);
        });

        $ahora = now();
        DB::table('markets')->insert([
            ['code' => 'CO', 'name' => 'Colombia', 'currency' => 'COP', 'locale_default' => 'es', 'whatsapp' => '573026673868', 'active' => true, 'created_at' => $ahora, 'updated_at' => $ahora],
            ['code' => 'US', 'name' => 'Estados Unidos', 'currency' => 'USD', 'locale_default' => 'en', 'whatsapp' => null, 'active' => true, 'created_at' => $ahora, 'updated_at' => $ahora],
        ]);

        $tarjeta = DB::table('products')->insertGetId([
            'slug' => 'tarjeta-nfc', 'name' => 'Tarjeta digital NFC dauntoque', 'finish' => 'matte_black', 'nfc_chip' => 'NTAG215',
            'created_at' => $ahora, 'updated_at' => $ahora,
        ]);

        DB::table('prices')->insert([
            ['product_id' => $tarjeta, 'market_code' => 'CO', 'amount' => 299900, 'compare_at' => 499900, 'created_at' => $ahora, 'updated_at' => $ahora],
            ['product_id' => $tarjeta, 'market_code' => 'US', 'amount' => 119, 'compare_at' => 250, 'created_at' => $ahora, 'updated_at' => $ahora],
        ]);

        // Inactivas hasta que se carguen las cuentas reales de dauntoque
        DB::table('payment_methods')->insert([
            ['market_code' => 'CO', 'type' => 'nequi', 'label' => 'Nequi', 'sort' => 1, 'created_at' => $ahora, 'updated_at' => $ahora],
            ['market_code' => 'CO', 'type' => 'llave_breb', 'label' => 'Llave Bre-B', 'sort' => 2, 'created_at' => $ahora, 'updated_at' => $ahora],
            ['market_code' => 'US', 'type' => 'zelle', 'label' => 'Zelle', 'sort' => 1, 'created_at' => $ahora, 'updated_at' => $ahora],
            ['market_code' => 'US', 'type' => 'cashapp', 'label' => 'Cash App', 'sort' => 2, 'created_at' => $ahora, 'updated_at' => $ahora],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
        Schema::dropIfExists('prices');
        Schema::dropIfExists('products');
        Schema::dropIfExists('markets');
    }
};
