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
        Schema::create('asociados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('persona_id')->constrained();
            $table->foreignId('tipo_asociado_id')->constrained();
            $table->date('fecha_admision');
            $table->unsignedBigInteger('solicitud_id')->default(0);
            $table->integer('anio_aporte')->default(0);
            $table->tinyInteger('mes_aporte')->default(0);
            $table->integer('numero_socio')->default(0);
            $table->date('fecha_baja')->nullable();
            $table->tinyInteger('motivo')->default(0);
            $table->string('motivo_baja_otro')->nullable();
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
        Schema::dropIfExists('asociados');
    }
};
