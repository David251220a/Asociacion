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
        Schema::table('tipo_prestamos', function (Blueprint $table) {
            $table->foreignId('tipo_ingreso_id')->nullable()->constrained('tipo_ingresos');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tipo_prestamos', function (Blueprint $table) {
            $table->dropForeign(['tipo_ingreso_id']);
            $table->dropColumn('tipo_ingreso_id');
        });
    }
};
