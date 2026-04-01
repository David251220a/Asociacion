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
        Schema::create('solicituds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('departamento_id')->constrained();
            $table->foreignId('distrito_id')->constrained();
            $table->foreignId('ciudad_id')->constrained();
            $table->foreignId('tipo_persona_id')->constrained();
            $table->foreignId('sexo_id')->constrained();
            $table->foreignId('estado_civil_id')->constrained();
            $table->foreignId('tipo_vivienda_id')->constrained();
            $table->integer('numero_solicitud');
            $table->integer('anio');
            $table->date('fecha_solicitud');
            $table->tinyInteger('aprobado')->default(0)->comment('0-PENDIENTE, 1-APROBADO, 2-RECHAZADO');
            $table->date('fecha_aprobacion_o_rechazo')->nullable();
            $table->text('motivo_rechazo')->nullable();
            $table->integer('numero_socio')->default(0);
            $table->integer('acta')->default(0);
            $table->string('documento', 20);
            $table->string('nombre', 200);
            $table->string('apellido', 200);
            $table->date('fecha_nacimiento')->nullable();
            $table->text('direccion');
            $table->string('barrio', 250);
            $table->string('celular')->nullable();
            $table->string('email',250);
            $table->text('vivienda')->nullable();
            $table->text('documento_frente')->nullable();
            $table->text('documento_reverso')->nullable();
            $table->text('selfi')->nullable();
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
        Schema::dropIfExists('solicituds');
    }
};
