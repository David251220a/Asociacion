<?php

namespace Database\Seeders;

use App\Models\TipoAsociado;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TipoAsociadoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $valores = [
            'JUBILADO',
            'PENSIONADO',
            'APORTANTE',
        ];

        foreach ($valores as $item) {
            TipoAsociado::firstOrCreate([
                'descripcion' => $item,
            ]);
        }
    }
}
