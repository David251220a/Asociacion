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
        Schema::create('entidads', function (Blueprint $table) {
            $table->id();
            $table->string('razon_social', 250);
            $table->string('nombra_fantasia', 250);
            $table->string('ruc');
            $table->string('ruc_sin_digito');
            $table->string('digito_verificador');
            $table->integer('tipo_contribuyente');
            $table->integer('tipo_regimen')->nullable();
            $table->string('email');
            $table->foreignId('tipo_transaccion_id')->constrained();
            $table->tinyInteger('ambiente')->default(0);
            $table->text('direccion');
            $table->bigInteger('departamento_id');
            $table->integer('numero_casa')->default(0);
            $table->string('telefono')->nullable();
            $table->bigInteger('distrito_id');
            $table->bigInteger('ciudad_id');
            $table->string('codigo_set_id' , 100);
            $table->string('codigo_cliente_set', 250);
            $table->string('firma', 250);
            $table->string('pass_firma', 250);
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
        Schema::dropIfExists('entidads');
    }
};
