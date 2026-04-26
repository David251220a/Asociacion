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
        Schema::create('recibos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('persona_id')->constrained();
            $table->foreignId('tipo_recibo_id')->constrained();
            $table->string('sucursal');
            $table->string('general');
            $table->integer('numero')->default(0);
            $table->date('fecha');
            $table->string('concepto');
            $table->decimal('monto_total', 12, 0)->default(0);
            $table->decimal('monto_abonado', 12, 0)->default(0);
            $table->decimal('monto_devuelto', 12, 0)->default(0);
            $table->foreignId('estado_id')->constrained();
            $table->tinyInteger('anulado')->default(0);
            $table->date('fecha_anulado')->nullable();
            $table->foreignId('user_id')->constrained();
            $table->unsignedBigInteger('usuario_anulacion')->nullable();
            $table->string('motivo_anulacion', 250)->nullable();
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
        Schema::dropIfExists('recibos');
    }
};
