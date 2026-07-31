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
        Schema::table('solicitud_ayuda_socials', function (Blueprint $table) {
            $table->date('fecha_anulacion')->nullable()->after('fecha_resolucion');
            $table->text('motivo_anulacion')->nullable()->after('fecha_anulacion');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('solicitud_ayuda_socials', function (Blueprint $table) {
            $table->dropColumn([
                'fecha_anulacion',
                'motivo_anulacion',
            ]);
        });
    }
};
