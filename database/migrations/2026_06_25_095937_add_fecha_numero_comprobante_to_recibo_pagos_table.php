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
        Schema::table('recibo_pagos', function (Blueprint $table) {
            $table->date('fecha')->after('recibo_id')->default(now());
            $table->string('numero_comprobante', 100)->nullable()->after('monto');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('recibo_pagos', function (Blueprint $table) {
            $table->dropColumn([
                'fecha',
                'numero_comprobante',
            ]);
        });
    }
};
