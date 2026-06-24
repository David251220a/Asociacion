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
        Schema::create('orden_pago_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_pago_id')->constrained();
            $table->string('descripcion', 500);
            $table->decimal('cantidad', 12, 2)->default(1);
            $table->decimal('precio', 12, 0)->default(0);
            $table->decimal('subtotal', 12, 0)->default(0);
            $table->foreignId('estado_id')->constrained();
            $table->foreignId('user_id')->constrained();
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
        Schema::dropIfExists('orden_pago_detalles');
    }
};
