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
        Schema::table('solicitud_prestamos', function (Blueprint $table) {
            $table->string('resolucion', 50)->nullable()->after('tipo_prestamo_id');
        });

        Schema::table('prestamos',function (Blueprint $table) {
            $table->string('resolucion', 50)->nullable()->after('tipo_prestamo_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('solicitud_prestamos',function (Blueprint $table) {
            $table->dropColumn('resolucion');
        });

        Schema::table('prestamos', function (Blueprint $table) {
            $table->dropColumn('resolucion');
        }
        );
    }
};
