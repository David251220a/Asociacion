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
        Schema::create('planilla_prestamos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('planilla_detalle_id')->constrained('planilla_detalles');
            $table->foreignId('prestamo_id')->constrained('prestamos');
            $table->foreignId('prestamo_detalle_id')->constrained('prestamo_detalles');
            $table->integer('numero_cuota');
            $table->date('fecha_vencimiento');
            /*
            |--------------------------------------------------------------------------
            | IMPORTES INCLUIDOS EN LA PLANILLA
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
            | IMPORTES EFECTIVAMENTE PAGADOS
            |--------------------------------------------------------------------------
            */
            $table->decimal('monto_capital_pagado', 18, 0)->default(0);
            $table->decimal('monto_interes_pagado', 18, 0)->default(0);
            $table->decimal('monto_iva_pagado', 18, 0)->default(0);
            $table->decimal('monto_mora_pagado', 18, 0)->default(0);
            $table->decimal('monto_mora_iva_pagado', 18, 0)->default(0);
            $table->decimal('monto_pagado', 18, 0)->default(0);
            $table->decimal('saldo', 18, 0)->default(0);
            $table->date('fecha_pago')->nullable();
            $table->foreignId('estado_pago_id')->default(1)->constrained('estado_pagos');
            $table->foreignId('estado_id')->default(1)->constrained('estados');
            $table->foreignId('user_id')->constrained('users');
            $table->unsignedBigInteger('usuario_modificacion');
            $table->timestamps();

            /*
            | La misma cuota no puede aparecer dos veces en el mismo
            | detalle de planilla.
            */

            $table->unique(['planilla_detalle_id','prestamo_detalle_id',],'uq_planilla_prestamo_cuota');
            $table->index(['prestamo_id','estado_pago_id']);});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('planilla_prestamos');
    }
};
