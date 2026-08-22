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
        Schema::create('prestamos', function (Blueprint $table) {
            $table->id();
            $table->integer('anio');
            $table->integer('numero_prestamo');
            $table->date('fecha_prestamo');
            $table->foreignId('persona_id')->constrained('personas');
            $table->foreignId('tipo_prestamo_id')->constrained('tipo_prestamos');
            $table->decimal('monto_capital', 18, 0)->default(0);
            $table->decimal('monto_interes', 18, 0)->default(0);
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
            $table->decimal('tasa_aplicada', 10, 2)->default(0);
            $table->decimal('tasa_mora', 10, 2)->default(0);
            $table->integer('cantidad_cuotas')->default(1);
            $table->foreignId('orden_pago_id')->nullable()->constrained('orden_pagos');
            $table->date('fecha_desembolso')->nullable();
            $table->date('fecha_cancelacion')->nullable();
            $table->foreignId('estado_prestamo_id')->constrained();
            $table->string('observaciones', 500)->nullable();
            $table->foreignId('estado_id')->default(1)->constrained('estados');
            $table->foreignId('usuario_id')->constrained('users');
            $table->foreignId('usuario_modificacion')->nullable()->constrained('users');
            $table->timestamps();

            $table->unique(['anio','numero_prestamo',]);
            $table->index(['persona_id','estado_prestamo_id',]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('prestamos');
    }
};
