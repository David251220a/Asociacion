<?php

use App\Models\Asociado;
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
        Schema::create('ficha_medicas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asociado_id')->constrained();
            $table->tinyInteger('cancer')->default(0);
            $table->tinyInteger('diabetes')->default(0);
            $table->tinyInteger('presion_alta')->default(0);
            $table->string('otro', 250)->nullable();
            $table->text('medicamentos')->nullable();
            $table->tinyInteger('seguro_particular')->default(0);
            $table->tinyInteger('seguro_ips')->default(0);
            $table->tinyInteger('seguro_ninguno')->default(0);
            $table->text('observacion')->nullable();
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
        Schema::dropIfExists('ficha_medicas');
    }
};
