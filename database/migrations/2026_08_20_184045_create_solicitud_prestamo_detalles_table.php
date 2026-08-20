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
        Schema::create('solicitud_prestamo_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitud_prestamo_id')->constrained();
            $table->unsignedInteger('numero_cuota');
            $table->date('fecha_vencimiento');
            $table->decimal('monto_cuota', 18, 0);
            $table->decimal('monto_interes', 18, 0);
            $table->decimal('monto_capital', 18, 0);
            $table->decimal('iva', 18, 0);
            $table->decimal('monto_total', 18, 0);
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
        Schema::dropIfExists('solicitud_prestamo_detalles');
    }
};
