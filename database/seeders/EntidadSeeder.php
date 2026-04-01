<?php

namespace Database\Seeders;

use App\Models\Entidad;
use App\Models\Timbrado;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EntidadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Entidad::create([
            'razon_social' => 'Caja de Jubilaciones y Pensiones del Personal Municipal',
            'nombra_fantasia' => 'Caja de Jubilaciones y Pensiones del Personal Municipal',
            'ruc' => '80000492-2',
            'ruc_sin_digito' => '80000492',
            'digito_verificador' => 2,
            'tipo_contribuyente' => 2,
            'tipo_regimen' => null,
            'email' => 'cajamunicipal.presidencia@gmail.com',
            'tipo_transaccion_id' => 2,
            'ambiente' => 0,
            'departamento_id' => 1,
            'distrito_id' => 1,
            'telefono' => '021497189',
            'direccion' => 'Benjamin Constant 955 c/ Colon y Montevideo',
            'ciudad_id' => 1,
            'codigo_set_id' => '003',
            'codigo_cliente_set' => 'B326123F3fd345C3a60F333B2025Ee9E',
            'firma' => 'app/keys/firma.p12',
            'pass_firma' => 'LqO#9j0E',
            'monto_aporte_general' => 20000,
            'monto_aporte_inicial' => 30000,
            'meses_inicial' => 5,
            'mision' => 'Mejorar la calidad de vida de los asociados, promoviendo su bienestar social, cultural y recreativo, asi como defendiendo sus derechos',
            'vision' => 'Ser reconocidos como una asociacion solida y sostenible, referente de unidad y apoyo mutuo para los asociados municipalistas del pais'
        ]);

        Timbrado::create([
            'entidad_id' => 1,
            'timbrado' => '80000492',
            'fecha_inicio' => '2024-12-04',
            'estado_id' => 1,
        ]);
    }
}
