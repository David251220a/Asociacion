<?php

namespace Database\Seeders;

use App\Models\TipoIngreso;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TipoIngresoSeeder extends Seeder
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
            'INTERESES',
            'MULTA',
        ];

        foreach ($valores as $item) {
            TipoIngreso::firstOrCreate([
                'descripcion' => $item,
                'estado_id' => 1
            ]);
        }
    }
}
