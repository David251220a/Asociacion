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
        Schema::create('sifens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('factura_id')->constrained();
            $table->string('cdc', 255)->unique();
            $table->integer('tipo_doc')->default(1);
            $table->string('documento_xml')->nullable();
            $table->text('documento_pdf')->nullable();
            $table->text('documento_zip')->nullable();
            $table->string('zipeado');
            $table->unsignedBigInteger('secuencia');
            $table->unsignedBigInteger('sifen_num_transaccion');
            $table->string('sifen_estado', 250);
            $table->text('sifen_mensaje')->nullable();
            $table->dateTime('fecha_firma');
            $table->text('link_qr');
            $table->string('evento')->nullable();
            $table->integer('sifen_evento_codrespuesta')->default(0);
            $table->text('sifen_evento_msjrespuesta')->nullable();
            $table->string('sifen_evento_estado', 20)->nullable();
            $table->text('sifen_evento_xml')->nullable();
            $table->unsignedBigInteger('secuencia_evento')->default(0);
            $table->unsignedBigInteger('sifen_cod');
            $table->integer('tipo_transaccion');
            $table->integer('condicion_pago')->default(1);
            $table->string('moneda')->default('PYG');
            $table->string('correo_enviado')->default('N');
            $table->dateTime('sifen_envio_fecha')->nullable();
            $table->integer('sifen_envio_codrespuesta')->nullable();
            $table->text('sifen_envio_msjrespuesta')->nullable();
            $table->text('sifen_envio_xml')->nullable();
            $table->text('sifen_respuesta_consulta_xml')->nullable();
            $table->string('enviado_sifen', 100)->nullable();
            $table->bigInteger('sifen_idprod');
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
        Schema::dropIfExists('sifens');
    }
};
