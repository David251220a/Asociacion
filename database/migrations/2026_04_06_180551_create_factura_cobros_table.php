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
        Schema::create('factura_cobros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('factura_id')->constrained();
            $table->foreignId('forma_cobro_id')->constrained();
            $table->foreignId('banco_id')->constrained();
            $table->decimal('monto', 12 ,0)->default(0);
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
        Schema::dropIfExists('factura_cobros');
    }
};
