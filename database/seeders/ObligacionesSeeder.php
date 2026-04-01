<?php

namespace Database\Seeders;

use App\Models\Obligaciones;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ObligacionesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Obligaciones::create([
            'entidad_id' => 1,
            'codigo' => 12345,
            'descripcion' => 'Sin especificar',
            'estado_id' => 1
        ]);
    }
}
