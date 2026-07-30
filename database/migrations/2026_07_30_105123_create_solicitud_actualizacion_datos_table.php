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
        Schema::create('solicitud_actualizacion_datos', function (Blueprint $table) {
            $table->id();
            $table->integer('anio');
            $table->integer('numero');
            $table->date('fecha_solicitud');

            $table->foreignId('persona_id')->constrained('personas');
            /*
            |--------------------------------------------------------------------------
            | Documento
            |--------------------------------------------------------------------------
            */
            $table->string('documento_actual', 20);
            $table->string('documento_nuevo', 20)->nullable();
            /*
            |--------------------------------------------------------------------------
            | Nombre y apellido
            |--------------------------------------------------------------------------
            */
            $table->string('nombre_actual', 200);
            $table->string('nombre_nuevo', 200)->nullable();
            $table->string('apellido_actual', 200);
            $table->string('apellido_nuevo', 200)->nullable();
            /*
            |--------------------------------------------------------------------------
            | Fecha de nacimiento
            |--------------------------------------------------------------------------
            */
            $table->date('fecha_nacimiento_actual')->nullable();
            $table->date('fecha_nacimiento_nueva')->nullable();
            /*
            |--------------------------------------------------------------------------
            | Institución municipal
            |--------------------------------------------------------------------------
            */
            $table->unsignedBigInteger('institucion_municipal_id_actual')->nullable();

            $table->unsignedBigInteger('institucion_municipal_id_nueva')->nullable();
            /*
            |--------------------------------------------------------------------------
            | Contacto
            |--------------------------------------------------------------------------
            */
            $table->string('email_actual', 250)->nullable();
            $table->string('email_nuevo', 250)->nullable();
            $table->string('celular_actual', 255)->nullable();
            $table->string('celular_nuevo', 255)->nullable();
            /*
            |--------------------------------------------------------------------------
            | Documentos
            |--------------------------------------------------------------------------
            */
            $table->text('documento_frente_actual')->nullable();
            $table->text('documento_frente_nuevo')->nullable();
            $table->text('documento_reverso_actual')->nullable();
            $table->text('documento_reverso_nuevo')->nullable();
            /*
            |--------------------------------------------------------------------------
            | Motivo y resolución
            |--------------------------------------------------------------------------
            */
            $table->text('motivo');

            $table->foreignId('estado_solicitud_id')->constrained('estado_solicituds');
            $table->dateTime('fecha_resolucion')->nullable();
            $table->foreignId('usuario_resolucion')->nullable()->constrained('users');
            $table->text('motivo_rechazo')->nullable();
            $table->string('observacion', 500)->nullable();
            /*
            |--------------------------------------------------------------------------
            | Auditoría
            |--------------------------------------------------------------------------
            */
            $table->foreignId('estado_id')->constrained('estados');
            $table->foreignId('user_id')->constrained('users');
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
        Schema::dropIfExists('solicitud_actualizacion_datos');
    }
};
