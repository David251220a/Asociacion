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
        Schema::table('miembros', function (Blueprint $table) {
            $table->foreignId('estado_id')->default(1)->constrained('estados');
            $table->unsignedTinyInteger('pago')->default(2);
            $table->decimal('monto_remuneracion', 18, 0)->default(0);
            $table->decimal('adelanto', 18, 0)->default(0);
            $table->decimal('neto', 18, 0)->default(0);
            $table->string('documento', 20)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('miembros', function (Blueprint $table) {
            $table->dropForeign([
                'estado_id',
            ]);

            $table->dropColumn([
                'estado_id',
                'pago',
                'monto_remuneracion',
                'adelanto',
                'neto',
                'documento',
            ]);
        });
    }
};
