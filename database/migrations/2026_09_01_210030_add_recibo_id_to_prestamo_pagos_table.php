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
        Schema::table('prestamo_pagos', function (Blueprint $table) {
            $table->foreignId('recibo_id')->nullable()->after('planilla_prestamo_id')->constrained('recibos');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('prestamo_pagos', function (Blueprint $table) {
            $table->dropForeign(['recibo_id']);
            $table->dropColumn('recibo_id');
        });
    }
};
