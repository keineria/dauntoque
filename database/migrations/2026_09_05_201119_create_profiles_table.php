<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            // Vinculación estricta al dueño de la cuenta
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // 1. Identidad y Enrutamiento
            $table->string('nickname')->unique(); // El subdominio: keiner.dauntoque.com
            $table->string('tipo_perfil')->default('corporativo'); // empresa, corporativo, hobbies
            $table->string('nombre');
            $table->string('profesion')->nullable();
            $table->text('bio')->nullable();
            $table->string('foto_perfil')->nullable(); // Ruta en Hostinger
            
            // 2. Diseño (Lo que agregamos en el Paso 8)
            $table->string('plantilla')->default('crystal'); // crystal, matte, luxury
            $table->string('alineacion')->default('centro'); // centro, izquierda
            
            // 3. Contacto Básico
            $table->string('telefono')->nullable();
            $table->string('correo_contacto')->nullable();
            $table->string('direccion')->nullable();
            
            // 4. Billeteras Digitales
            $table->string('zelle')->nullable();
            $table->string('cashapp')->nullable();
            $table->string('venmo')->nullable();
            
            // 5. Arreglos (Guardados en formato JSON)
            $table->json('redes_sociales')->nullable(); // Máximo 4
            $table->json('galeria')->nullable(); // Rutas de hasta 4 fotos
            
            // Estado de vinculación física
            $table->string('nfc_uid')->nullable()->unique(); // Código del chip físico
            $table->boolean('activo')->default(false); // Cambia a true al escanear la tarjeta
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};