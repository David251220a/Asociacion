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
        Schema::create('solicitud_ayuda_socials', function (Blueprint $table) {
            $table->id();
            $table->integer('anio');
            $table->integer('numero');
            $table->date('fecha_solicitud');
            $table->foreignId('persona_id')->constrained('personas');
            $table->string('beneficiario', 255)->nullable();
            $table->text('motivo');
            $table->decimal('monto_solicitado', 18, 0)->default(0);
            $table->decimal('monto_aprobado', 18, 0)->default(0);
            $table->string('documento_respaldo', 500)->nullable();
            $table->foreignId('estado_solicitud_id')->constrained();
            $table->dateTime('fecha_resolucion')->nullable();
            $table->text('motivo_rechazo')->nullable();
            $table->string('observacion', 500)->nullable();
            $table->foreignId('orden_pago_id')->nullable()->constrained('orden_pagos');
            $table->foreignId('estado_id')->constrained();
            $table->foreignId('user_id')->constrained('users');
            $table->unsignedBigInteger('usuario_modificacion');
            $table->timestamps();
            $table->unique(['anio', 'numero']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('solicitud_ayuda_socials');
    }
};
