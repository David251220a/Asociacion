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
        Schema::create('prestamo_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prestamo_id')->constrained('prestamos');
            $table->integer('numero_cuota');
            $table->date('fecha_vencimiento');
            $table->decimal('monto_capital', 18, 0)->default(0);
            $table->decimal('monto_interes', 18, 0)->default(0);
            $table->decimal('monto_cuota', 18, 0)->default(0);
            $table->decimal('monto_iva', 18, 0)->default(0);
            $table->decimal('monto_total', 18, 0)->default(0);
            $table->decimal('monto_capital_pagado', 18, 0)->default(0);
            $table->decimal('monto_interes_pagado', 18, 0)->default(0);
            $table->decimal('monto_iva_pagado', 18, 0)->default(0);
            $table->decimal('monto_pagado', 18, 0)->default(0);
            $table->decimal('saldo_capital', 18, 0)->default(0);
            $table->decimal('saldo_interes', 18, 0)->default(0);
            $table->decimal('saldo_iva', 18, 0)->default(0);
            $table->decimal('saldo_total', 18, 0)->default(0);
            $table->decimal('monto_mora', 18, 0)->default(0);
            $table->decimal('monto_mora_iva', 18, 0)->default(0);
            $table->date('fecha_pago')->nullable();
            $table->date('fecha_cancelacion')->nullable();
            $table->date('fecha_ultimo_calculo_mora')->nullable();
            $table->foreignId('estado_pago_id')->default(1)->constrained('estado_pagos');
            $table->foreignId('estado_id')->default(1)->constrained('estados');
            $table->foreignId('usuario_id')->constrained('users');
            $table->foreignId('usuario_modificacion')->nullable()->constrained('users');
            $table->timestamps();

            $table->unique(['prestamo_id','numero_cuota',]);
            $table->index(['fecha_vencimiento','estado_pago_id',]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('prestamo_detalles');
    }
};
