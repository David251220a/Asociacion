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
        Schema::create('planilla_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('planilla_id')->constrained();
            $table->foreignId('asociado_id')->constrained();
            $table->foreignId('tipo_asociado_id')->constrained();
            $table->decimal('monto_esperado', 12, 0);
            $table->decimal('pagado', 12, 0);
            $table->decimal('saldo', 12, 0);
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
        Schema::dropIfExists('planilla_detalles');
    }
};
