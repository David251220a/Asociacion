<?php

namespace Database\Seeders;

use App\Models\Institucion;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InstitucionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Institucion::create([
            'muni_departamento_id' => 1,
            'departamento_id' => 100,
            'municipio_id' => 1,
            'descripcion' => 'SIN ESPECIFICAR',
            'estado_id' => 1,
        ]);

        Institucion::create([
            'muni_departamento_id' => 2,
            'departamento_id' => 0,
            'municipio_id' => 1,
            'descripcion' => 'ASUNCION',
            'estado_id' => 1,
        ]);

        Institucion::create([
            'muni_departamento_id' => 3,
            'departamento_id' => 99,
            'municipio_id' => 1,
            'descripcion' => 'CJPPM',
            'estado_id' => 1,
        ]);
    }
}
