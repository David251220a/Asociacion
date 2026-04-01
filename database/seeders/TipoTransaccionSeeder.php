<?php

namespace Database\Seeders;

use App\Models\TipoTransaccion;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TipoTransaccionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $valores = ['Venta de mercadería', 'Prestación de servicios', 'Mixto (Venta de mercadería y servicios)', 'Venta de activo fijo', 'Venta de divisas'
        , 'Compra de divisas', 'Promoción o entrega de muestras', 'Donación', 'Anticipo', 'Compra de productos', 'Compra de servicios', 'Venta de crédito fiscal', 'Muestras médicas (Art. 3 RG 24/2014)'];

        foreach ($valores as $item) {
            TipoTransaccion::firstOrCreate([
                'descripcion' => $item
            ]);
        }
    }
}
