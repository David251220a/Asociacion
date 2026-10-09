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
        Schema::table('miembro_planillas', function (Blueprint $table) {
            $table->foreignId('orden_pago_id')->nullable()->constrained('orden_pagos');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('miembro_planillas', function (Blueprint $table) {
            $table->dropForeign(['orden_pago_id']);
            $table->dropColumn('orden_pago_id');
        });
    }
};
