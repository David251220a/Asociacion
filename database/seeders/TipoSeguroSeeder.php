<?php

namespace Database\Seeders;

use App\Models\TipoSeguro;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TipoSeguroSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $valores = [
            'NINGUNO',
            'PARTICULAR',
            'IPS', 
        ];

        foreach ($valores as $item) {
            TipoSeguro::firstOrCreate([
                'descripcion' => $item,
            ]);
        }
    }
}
