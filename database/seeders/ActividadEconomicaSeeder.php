<?php

namespace Database\Seeders;

use App\Models\ActividadEconomica;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ActividadEconomicaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ActividadEconomica::create([
            'entidad_id' => 1,
            'codigo' => 84300,
            'descripcion' => 'Actividades de planes de seguro social obligatorio',
            'estado_id' => 1,
        ]);
    }
}
