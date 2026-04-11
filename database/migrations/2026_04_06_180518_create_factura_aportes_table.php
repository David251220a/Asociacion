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
        Schema::create('factura_aportes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('factura_id')->constrained();
            $table->foreignId('asociado_id')->constrained();
            $table->tinyInteger('planilla')->default(0)->comment('0-INDIVIDUAL;1-PLANILLA');
            $table->integer('planilla_numero')->default(0);
            $table->integer('planilla_anio')->default(0);
            $table->foreignId('planilla_id')->nullable()->constrained();
            $table->date('fecha_aporte');
            $table->tinyInteger('mes');
            $table->integer('anio');
            $table->decimal('aporte', 12, 0);
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
        Schema::dropIfExists('factura_aportes');
    }
};
