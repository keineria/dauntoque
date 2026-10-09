<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDesignToProfilesTable extends Migration
{
    public function up()
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->string('nombre_perfil')->default('Mi Tarjeta Principal')->after('nickname');
            $table->json('design_config')->nullable()->after('bio');
        });
    }

    public function down()
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn(['nombre_perfil', 'design_config']);
        });
    }
}