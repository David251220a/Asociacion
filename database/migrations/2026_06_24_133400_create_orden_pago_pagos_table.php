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
        Schema::create('orden_pago_pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_pago_id')->constrained();
            $table->date('fecha_pago');
            $table->foreignId('forma_cobro_id')->constrained();
            $table->foreignId('banco_id')->nullable()->constrained();
            $table->decimal('monto', 12, 0);
            $table->string('numero_comprobante', 100)->nullable();
            $table->string('observacion', 500)->nullable();
            $table->foreignId('estado_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->unsignedBigInteger('usuario_modificacion');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orden_pago_pagos');
    }
};
