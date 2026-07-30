<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('entidads', function (Blueprint $table) {
            $table->tinyInteger('activo_ayuda_social')->default(0)->after('mision')->comment('0: Inactivo, 1: Activo');
            $table->tinyInteger('limite_ayuda_social')->default(1)->after('activo_ayuda_social');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('entidads', function (Blueprint $table) {
            $table->dropColumn('activo_ayuda_social');
            $table->dropColumn('limite_ayuda_social');
        });
    }
};
