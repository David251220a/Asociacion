<?php

namespace Database\Seeders;

use App\Models\TipoPersona;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TipoPersonaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        TipoPersona::create([
            'descripcion' => 'FISICA',
        ]);
        
        TipoPersona::create([
            'descripcion' => 'EMPRESA',
        ]);

        TipoPersona::create([
            'descripcion' => 'INSTITUCION',
        ]);
    }
}
