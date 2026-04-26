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
        Schema::create('numeracions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('timbrado_id')->constrained();
            $table->foreignId('establecimiento_id')->constrained();
            $table->foreignId('tipo_documento_id')->nullable()->constrained();
            $table->integer('numero_siguiente');
            $table->string('modulo', 20)->comment('SIFEN / RECIBO');
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
        Schema::dropIfExists('numeracions');
    }
};
