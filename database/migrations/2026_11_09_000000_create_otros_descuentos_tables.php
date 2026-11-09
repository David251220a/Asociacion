<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!DB::table('tipo_ingresos')->where('descripcion', 'OTROS DESCUENTOS PLANILLA')->exists()) {
            DB::table('tipo_ingresos')->insert([
                'descripcion' => 'OTROS DESCUENTOS PLANILLA',
                'estado_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Schema::create('otro_descuentos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->text('descripcion')->nullable();
            $table->decimal('monto_por_socio', 18, 0);
            $table->unsignedSmallInteger('cantidad_cuotas');
            $table->unsignedTinyInteger('mes_inicio');
            $table->unsignedSmallInteger('anio_inicio');
            $table->foreignId('estado_id')->default(1)->constrained('estados');
            $table->foreignId('user_id')->constrained('users');
            $table->unsignedBigInteger('usuario_modificacion');
            $table->timestamps();
            $table->index(['anio_inicio', 'mes_inicio', 'estado_id']);
        });

        Schema::create('otro_descuento_asociados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('otro_descuento_id')->constrained('otro_descuentos');
            $table->foreignId('asociado_id')->constrained('asociados');
            $table->decimal('monto_total', 18, 0);
            $table->timestamps();
            $table->unique(['otro_descuento_id', 'asociado_id'], 'otro_descuento_asociado_unique');
        });

        Schema::create('otro_descuento_cuotas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('otro_descuento_asociado_id')->constrained('otro_descuento_asociados');
            $table->foreignId('planilla_detalle_id')->nullable()->constrained('planilla_detalles');
            $table->unsignedSmallInteger('numero_cuota');
            $table->unsignedTinyInteger('mes');
            $table->unsignedSmallInteger('anio');
            $table->decimal('monto_esperado', 18, 0);
            $table->decimal('monto_pagado', 18, 0)->default(0);
            $table->decimal('saldo', 18, 0);
            $table->foreignId('estado_pago_id')->default(1)->constrained('estado_pagos');
            $table->foreignId('estado_id')->default(1)->constrained('estados');
            $table->foreignId('user_id')->constrained('users');
            $table->unsignedBigInteger('usuario_modificacion');
            $table->timestamps();
            $table->unique(['otro_descuento_asociado_id', 'numero_cuota'], 'otro_descuento_cuota_unique');
            $table->index(['anio', 'mes', 'estado_id', 'estado_pago_id'], 'otro_descuento_periodo_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otro_descuento_cuotas');
        Schema::dropIfExists('otro_descuento_asociados');
        Schema::dropIfExists('otro_descuentos');
    }
};
