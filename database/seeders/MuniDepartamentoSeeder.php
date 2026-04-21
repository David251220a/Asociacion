<?php

namespace Database\Seeders;

use App\Models\MuniDepartamento;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MuniDepartamentoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        MuniDepartamento::create([
            'departamento_id' => 100,
            'descripcion' => 'SIN ESPECIFICAR',
            'estado_id' => 1,
        ]);

        MuniDepartamento::create([
            'departamento_id' => 0,
            'descripcion' => 'CAPITAL',
            'estado_id' => 1,
        ]);

        MuniDepartamento::create([
            'departamento_id' => 99,
            'descripcion' => 'CAPITAL',
            'estado_id' => 1,
        ]);

    }
}
