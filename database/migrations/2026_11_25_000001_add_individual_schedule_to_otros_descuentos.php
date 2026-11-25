<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('otro_descuento_asociados', function (Blueprint $table) {
            $table->unsignedSmallInteger('cantidad_cuotas')->default(1)->after('monto_total');
            $table->unsignedTinyInteger('mes_inicio')->default(1)->after('cantidad_cuotas');
            $table->unsignedSmallInteger('anio_inicio')->default(2026)->after('mes_inicio');
        });

        $asignaciones = DB::table('otro_descuento_asociados as oda')
            ->join('otro_descuentos as od', 'od.id', '=', 'oda.otro_descuento_id')
            ->select('oda.id', 'od.cantidad_cuotas', 'od.mes_inicio', 'od.anio_inicio')
            ->get();

        foreach ($asignaciones as $asignacion) {
            $cuotas = DB::table('otro_descuento_cuotas')
                ->where('otro_descuento_asociado_id', $asignacion->id)
                ->orderBy('numero_cuota')
                ->get(['mes', 'anio']);

            $primeraCuota = $cuotas->first();

            DB::table('otro_descuento_asociados')
                ->where('id', $asignacion->id)
                ->update([
                    'cantidad_cuotas' => $cuotas->isNotEmpty() ? $cuotas->count() : $asignacion->cantidad_cuotas,
                    'mes_inicio' => $primeraCuota?->mes ?? $asignacion->mes_inicio,
                    'anio_inicio' => $primeraCuota?->anio ?? $asignacion->anio_inicio,
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('otro_descuento_asociados', function (Blueprint $table) {
            $table->dropColumn(['cantidad_cuotas', 'mes_inicio', 'anio_inicio']);
        });
    }
};
