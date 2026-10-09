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
        Schema::create('miembro_planillas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('numero');
            $table->unsignedSmallInteger('anio');
            $table->unsignedTinyInteger('mes');
            $table->date('fecha_generacion');
            $table->unsignedInteger('cantidad')->default(0);
            $table->decimal('total_remuneracion',18,0)->default(0);
            $table->decimal('total_adelanto',18,0)->default(0);
            $table->decimal('total_neto',18,0)->default(0);
            $table->unsignedTinyInteger('estado_planilla')->default(1);
            $table->date('fecha_pago')->nullable();
            $table->date('fecha_anulacion')->nullable();
            $table->string('motivo_anulacion', 500)->nullable();
            $table->foreignId('estado_id')->default(1)->constrained('estados');
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('usuario_modificacion')->nullable()->constrained('users');

            $table->timestamps();

            $table->unique([
                'anio',
                'numero',
            ]);

            $table->index([
                'anio',
                'mes',
                'estado_id',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('miembro_planillas');
    }
};
