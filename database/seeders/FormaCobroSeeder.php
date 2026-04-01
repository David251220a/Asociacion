<?php

namespace Database\Seeders;

use App\Models\FormaCobro;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FormaCobroSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $formasCobro = [
            ['id' => 1, 'descripcion' => 'Efectivo', 'banco_ver' => 0],
            ['id' => 2, 'descripcion' => 'Cheque', 'banco_ver' => 1],
            ['id' => 3, 'descripcion' => 'Tarjeta de crédito', 'banco_ver' => 1],
            ['id' => 4, 'descripcion' => 'Tarjeta de débito', 'banco_ver' => 1],
            ['id' => 5, 'descripcion' => 'Transferencia', 'banco_ver' => 1],
            ['id' => 6, 'descripcion' => 'Giro', 'banco_ver' => 1],
            ['id' => 7, 'descripcion' => 'Billetera electrónica', 'banco_ver' => 1],
            ['id' => 8, 'descripcion' => 'Tarjeta empresarial', 'banco_ver' => 1],
            ['id' => 9, 'descripcion' => 'Vale', 'banco_ver' => 0],
            ['id' => 10, 'descripcion' => 'Retención', 'banco_ver' => 0],
            ['id' => 11, 'descripcion' => 'Pago por anticipo', 'banco_ver' => 0],
            ['id' => 12, 'descripcion' => 'Valor fiscal', 'banco_ver' => 0],
            ['id' => 13, 'descripcion' => 'Valor comercial', 'banco_ver' => 0],
            ['id' => 14, 'descripcion' => 'Compensación', 'banco_ver' => 0],
            ['id' => 15, 'descripcion' => 'Permuta', 'banco_ver' => 0],
            ['id' => 16, 'descripcion' => 'Pago bancario', 'banco_ver' => 1],
            ['id' => 17, 'descripcion' => 'Pago Móvil', 'banco_ver' => 1],
            ['id' => 18, 'descripcion' => 'Donación', 'banco_ver' => 0],
            ['id' => 19, 'descripcion' => 'Promoción', 'banco_ver' => 0],
            ['id' => 20, 'descripcion' => 'Consumo Interno', 'banco_ver' => 0],
            ['id' => 21, 'descripcion' => 'Pago Electrónico', 'banco_ver' => 1],
            ['id' => 99, 'descripcion' => 'Otro', 'banco_ver' => 0],
        ];


        foreach ($formasCobro as $item) {
            FormaCobro::updateOrCreate(
                ['id' => $item['id']],
                [
                    'descripcion' => $item['descripcion'],
                    'banco_ver' => $item['banco_ver'],
                    'estado_id' => 1,
                    'user_id' => 1,
                ]
            );
        }
    }
}
