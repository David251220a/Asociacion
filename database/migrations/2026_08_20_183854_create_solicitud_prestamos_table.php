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
        Schema::create('solicitud_prestamos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('persona_id')->constrained();
            $table->foreignId('estado_solicitud_id')->constrained();
            $table->foreignId('tipo_prestamo_id')->constrained();
            $table->date('fecha_solicitud');
            $table->integer('anio');
            $table->integer('numero_solicitud');
            $table->decimal('monto_solicitado', 18, 0);
            $table->decimal('monto_aprobado', 18, 0)->nullable();
            $table->decimal('tasa_aplicada', 10, 2)->default(10);
            $table->integer('cantidad_cuotas')->default(1);
            $table->foreignId('orden_pago_id')->nullable()->constrained();
            $table->unsignedBigInteger('prestamo_id')->nullable();
            $table->string('observaciones')->nullable();
            $table->date('fecha_aprobacion_rechazo')->nullable();
            $table->foreignId('usuario_aprobacion_rechazo_id')->nullable()->constrained('users');
            $table->text('motivo_rechazo')->nullable();
            $table->foreignId('estado_id')->default(1)->constrained();
            $table->foreignId('usuario_id')->constrained('users');
            $table->timestamps();


            $table->unique([
                'anio',
                'numero_solicitud',
            ]);

            $table->index([
                'persona_id',
                'estado_solicitud_id',
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
        Schema::dropIfExists('solicitud_prestamos');
    }
};
