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
        Schema::create('establecimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entidad_id')->constrained();
            $table->foreignId('departamento_id')->constrained();
            $table->foreignId('distrito_id')->constrained();
            $table->foreignId('ciudad_id')->constrained();
            $table->string('punto', 3);
            $table->integer('numero_casa')->default(0);
            $table->string('telefono', 20);
            $table->string('descripcion');
            $table->text('direccion');
            $table->string('sucursal', 3);
            $table->string('general', 3);
            $table->foreignId('estado_id')->constrained();
            $table->foreignId('user_id')->constrained();
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
        Schema::dropIfExists('establecimientos');
    }
};
