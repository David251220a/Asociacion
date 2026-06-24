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
        Schema::create('orden_pagos', function (Blueprint $table) {
            $table->id();
            $table->integer('anio');
            $table->integer('numero');
            $table->date('fecha');
            $table->foreignId('tipo_egreso_id')->constrained();
            $table->unsignedBigInteger('origen_id')->default(0);
            $table->foreignId('persona_id')->constrained();
            $table->string('beneficiario', 255);
            $table->string('concepto', 500)->nullable();
            $table->string('observacion', 500)->nullable();
            $table->decimal('total', 12, 0)->default(0);
            $table->foreignId('estado_id')->constrained();
            $table->tinyInteger('estado_pago')->default(0);
            $table->text('motivo_anulado')->nullable();
            $table->date('fecha_anulado')->nullable();
            $table->date('fecha_pago')->nullable();
            $table->foreignId('user_id')->constrained();
            $table->unsignedBigInteger('usuario_modificacion');
            $table->timestamps();
            $table->unique(['anio','numero']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orden_pagos');
    }
};
