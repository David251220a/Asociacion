<?php

namespace Database\Seeders;

use App\Models\TipoVivienda;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TipoViviendaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $valores = [
            'PROPIA',
            'ALQUILADA',
        ];

        foreach ($valores as $item) {
            TipoVivienda::firstOrCreate([
                'descripcion' => $item,
            ]);
        }
    }
}
