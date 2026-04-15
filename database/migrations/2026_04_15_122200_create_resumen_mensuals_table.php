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
        Schema::create('resumen_mensuals', function (Blueprint $table) {
            $table->id();
            $table->integer('anio');
            $table->tinyInteger('mes');
            $table->decimal('total_ingreso', 18, 0)->default(0);
            $table->decimal('total_egreso', 18, 0)->default(0);
            $table->decimal('saldo_final', 18, 0)->default(0);
            $table->dateTime('fecha_calculo')->nullable();
            $table->string('usuario_calculo', 100)->nullable();
            $table->string('observacion', 300)->nullable();
            $table->timestamps();

            $table->unique(['anio', 'mes']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('resumen_mensuals');
    }
};
