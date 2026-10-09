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
        Schema::create('miembro_planilla_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('miembro_planilla_id')->constrained('miembro_planillas');
            $table->foreignId('miembro_id')->constrained('miembros');
            $table->string('nombre_completo');
            $table->unsignedTinyInteger('tipo')->nullable();
            $table->decimal('monto_remuneracion',18,0)->default(0);
            $table->decimal('adelanto',18,0)->default(0);
            $table->decimal('neto',18,0)->default(0);
            $table->unsignedTinyInteger('estado_pago')->default(1);
            $table->date('fecha_pago')->nullable();
            $table->foreignId('estado_id')->default(1)->constrained('estados');
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('usuario_modificacion')->nullable()->constrained('users');
            $table->timestamps();

            $table->unique(
                [
                    'miembro_planilla_id',
                    'miembro_id',
                ],
                'miembro_planilla_detalle_unique'
            );

            $table->index([
                'miembro_id',
                'estado_pago',
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
        Schema::dropIfExists('miembro_planilla_detalles');
    }
};
