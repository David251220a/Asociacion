<?php

namespace Database\Seeders;

use App\Models\EstadoCivil;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EstadoCivilSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $valores = [
            'SIN ESPECIFICAR',
            'SOLTERO/A',
            'CASADO/A',
            'VIUDO/A',
            'DIVORCIADO/A'
        ];

        foreach ($valores as $item) {
            EstadoCivil::firstOrCreate([
                'descripcion' => $item,
            ]);
        }
    }
}
