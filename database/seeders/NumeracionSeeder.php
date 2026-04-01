<?php

namespace Database\Seeders;

use App\Models\Numeracion;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NumeracionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Numeracion::create([
            'timbrado_id' => 1,
            'establecimiento_id' => 1,
            'tipo_documento_id' => 1,
            'numero_siguiente' => 1,
            'estado_id' => 1,
            'user_id' => 1,
        ]);
    }
}
