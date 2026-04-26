<?php

namespace Database\Seeders;

use App\Models\TipoRecibo;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TipoReciboSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $valores = [
            'COMSION',
            'PRESTAMOS',
            'PRESTAMO-SIN ESPECIFICAR',
            'PRESTAMO-SIN ESPECIFICAR',
            'PRESTAMO-SIN ESPECIFICAR',
            'APORTE PLANILLA',
            'APORTE INDIVIDUAL',
            'DONACIONES',
        ];

        foreach ($valores as $item) {
            TipoRecibo::firstOrCreate([
                'descripcion' => $item,
                'estado_id' => 1
            ]);
        }
    }
}
