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

            $table->foreignId('tipo_ingreso_id')->nullable()->constrained();
            $table->foreignId('tipo_egreso_id')->nullable()->constrained();

            $table->string('tipo_movimiento', 1); // I / E

            $table->decimal('total_egreso', 18, 0)->default(0);
            $table->decimal('total_ingreso', 18, 0)->default(0);

            $table->integer('anio');
            $table->tinyInteger('mes');

            $table->dateTime('fecha_calculo')->nullable();
            $table->string('usuario_calculo', 100)->nullable();
            $table->string('observacion', 300)->nullable();

            $table->timestamps();

            $table->unique([
                'tipo_ingreso_id',
                'tipo_egreso_id',
                'tipo_movimiento',
                'anio',
                'mes'
            ], 'uk_resumen_mensual');

            $table->index(['anio', 'mes']);
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
