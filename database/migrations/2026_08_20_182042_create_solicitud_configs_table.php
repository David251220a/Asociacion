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
        Schema::create('solicitud_configs', function (Blueprint $table) {
            $table->id();
            $table->string('descripcion', 255);
            $table->tinyInteger('activo')->default(1);
            $table->decimal('tasa_cuota_unica', 12,2)->default(0);
            $table->decimal('tasa_cuota_mensual', 12,2)->default(0);
            $table->decimal('tasa_mora', 12,2)->default(0);
            $table->decimal('monto_minimo', 18,0)->default(0);
            $table->decimal('monto_maximo', 18,0)->default(0);
            $table->tinyInteger('plazo_minimo')->default(0);
            $table->tinyInteger('plazo_maximo')->default(0);
            $table->tinyInteger('limite_solicitud')->default(0);
            $table->tinyInteger('limite_solicitud_anual')->default(0);
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
        Schema::dropIfExists('solicitud_configs');
    }
};
