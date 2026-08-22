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
        Schema::create('prestamo_pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prestamo_id')->constrained('prestamos');
            $table->foreignId('prestamo_detalle_id')->constrained('prestamo_detalles');
            $table->unsignedBigInteger('planilla_prestamo_id')->nullable();
            $table->unsignedTinyInteger('canal_cobro')->default(1);
            $table->date('fecha_generacion');
            $table->date('fecha_pago')->nullable();
            /*
            |--------------------------------------------------------------------------
            | IMPORTES ORIGINALES DE LA CUOTA
            |--------------------------------------------------------------------------
            */
            $table->decimal('monto_capital', 18, 0)->default(0);
            $table->decimal('monto_interes', 18, 0)->default(0);
            $table->decimal('monto_iva', 18, 0)->default(0);
            $table->decimal('monto_mora', 18, 0)->default(0);
            $table->decimal('monto_mora_iva', 18, 0)->default(0);
            $table->decimal('monto_total', 18, 0)->default(0);
            /*
            |--------------------------------------------------------------------------
            | SALDOS AL MOMENTO DE GENERAR EL COBRO
            |--------------------------------------------------------------------------
            */
            $table->decimal('saldo_capital', 18, 0)->default(0);
            $table->decimal('saldo_interes', 18, 0)->default(0);
            $table->decimal('saldo_iva', 18, 0)->default(0);
            $table->decimal('saldo_total', 18, 0)->default(0);
            /*
            |--------------------------------------------------------------------------
            | IMPORTES EFECTIVAMENTE PAGADOS
            |--------------------------------------------------------------------------
            */
            $table->decimal('monto_capital_pagado', 18, 0)->default(0);
            $table->decimal('monto_interes_pagado', 18, 0)->default(0);
            $table->decimal('monto_iva_pagado', 18, 0)->default(0);
            $table->decimal('monto_mora_pagado', 18, 0)->default(0);
            $table->decimal('monto_mora_iva_pagado', 18, 0)->default(0);
            $table->decimal('monto_pagado', 18, 0)->default(0);
            /*
            |--------------------------------------------------------------------------
            | ESTADO Y AUDITORÍA
            |--------------------------------------------------------------------------
            */
            $table->foreignId('estado_pago_id')->default(1)->constrained('estado_pagos');
            $table->string('observaciones', 500)->nullable();
            $table->foreignId('estado_id')->default(1)->constrained('estados');
            $table->foreignId('usuario_id')->constrained('users');
            $table->foreignId('usuario_modificacion')->nullable()->constrained('users');
            $table->timestamps();

            $table->index(['prestamo_detalle_id','estado_pago_id',]);
            $table->index(['planilla_prestamo_id','estado_pago_id',]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('prestamo_pagos');
    }
};
