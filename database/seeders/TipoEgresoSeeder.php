<?php

namespace Database\Seeders;

use App\Models\TipoEgreso;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TipoEgresoSeeder extends Seeder
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
            TipoEgreso::firstOrCreate([
                'descripcion' => $item,
                'estado_id' => 1
            ]);
        }
    }
}
