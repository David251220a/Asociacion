<?php

namespace Database\Seeders;

use App\Models\TipoFactura;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TipoFacturaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $valores = [
            'PLANILLA',
            'COBRO APORTE INDIVIDUAL',
            'PRESTAMO', 
        ];

        foreach ($valores as $item) {
            TipoFactura::firstOrCreate([
                'descripcion' => $item,
            ]);
        }
    }
}
