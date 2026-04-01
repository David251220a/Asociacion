<?php

namespace Database\Seeders;

use App\Models\Establecimiento;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EstablecimientoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Establecimiento::create([
            'entidad_id' => 1,
            'departamento_id' => 1,
            'distrito_id' => 1,
            'ciudad_id' => 1,
            'punto' => '001',
            'numero_casa' => 1,
            'telefono' => '021497189',
            'descripcion' => 'Nuestra Señora',
            'direccion' => 'Benjamin Constant 955 c/ Colon y Montevideo',
            'sucursal' => '001',
            'general' => '003',
            'estado_id' => 1,
            'user_id' => 1,
        ]);
    }
}
