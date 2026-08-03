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
        Schema::table('solicitud_actualizacion_datos', function (Blueprint $table) {
            $table->json('campos_aprobados')->nullable()->after('observacion');
            $table->json('campos_no_aprobados')->nullable()->after('campos_aprobados');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('solicitud_actualizacion_datos', function (Blueprint $table) {
            $table->dropColumn([
                'campos_aprobados',
                'campos_no_aprobados',
            ]);
        });
    }
};
