<?php

namespace Database\Seeders;

use App\Models\TipoFamiliar;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TipoFamiliarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $valores = [
            'CONYUGE',
            'HIJO',
            'HIJA',
        ];

        foreach ($valores as $item) {
            TipoFamiliar::firstOrCreate([
                'descripcion' => $item,
            ]);
        }
    }
}
