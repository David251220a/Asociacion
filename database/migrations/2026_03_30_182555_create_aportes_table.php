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
        Schema::create('aportes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asociado_id')->constrained();
            $table->foreignId('tipo_asociado_id')->constrained();
            $table->tinyInteger('mes')->default(0);
            $table->integer('anio')->default(0);
            $table->date('fecha_aporte');
            $table->decimal('aporte', 12, 0)->default(0);
            $table->date('fecha_ingreso');
            $table->unsignedBigInteger('factura_id')->default(0);
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
        Schema::dropIfExists('aportes');
    }
};
