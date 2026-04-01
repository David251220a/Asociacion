<?php

use App\Models\Miembro;
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
        Schema::create('solicitud_aprobados', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Solicitud::class)->constrained();
            $table->foreignIdFor(Miembro::class)->constrained();
            $table->tinyInteger('presente')->default(0);
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
        Schema::dropIfExists('solicitud_aprobados');
    }
};
