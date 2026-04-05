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
        Schema::create('planillas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_asociado_id')->constrained();
            $table->tinyInteger('mes');
            $table->integer('anio');
            $table->date('fecha');
            $table->integer('cantidad');
            $table->decimal('total', 12, 0);
            $table->tinyInteger('pagado')->default(0);
            $table->decimal('monto_pagado', 12, 0)->default(0);
            $table->date('fecha_pagado')->nullable();
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
        Schema::dropIfExists('planillas');
    }
};
