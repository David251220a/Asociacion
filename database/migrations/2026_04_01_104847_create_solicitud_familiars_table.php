<?php

use App\Models\Solicitud;
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
        Schema::create('solicitud_familiars', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Solicitud::class)->constrained();
            $table->foreignId('tipo_familiar')->constrained();
            $table->string('documento', 20);
            $table->string('nombre', 200)->nullable();
            $table->string('apellido', 200)->nullable();
            $table->string('celular', 20)->nullable();
            $table->foreignId('estado_id')->constrained();
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
        Schema::dropIfExists('solicitud_familiars');
    }
};
