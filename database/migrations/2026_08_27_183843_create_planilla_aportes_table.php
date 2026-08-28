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
        Schema::create('planilla_aportes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('planilla_detalle_id')->constrained('planilla_detalles');
            $table->decimal('monto_esperado', 18, 0)->default(0);
            $table->decimal('monto_pagado', 18, 0)->default(0);
            $table->decimal('saldo', 18, 0)->default(0);
            $table->foreignId('estado_pago_id')->default(1)->constrained('estado_pagos');
            $table->foreignId('estado_id')->default(1)->constrained('estados');
            $table->foreignId('user_id')->constrained('users');
            $table->unsignedBigInteger('usuario_modificacion');
            $table->timestamps();

            $table->unique('planilla_detalle_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('planilla_aportes');
    }
};
