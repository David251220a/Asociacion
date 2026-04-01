<?php

namespace Database\Seeders;

use App\Models\Persona;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PersonaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Persona::create([
            'departamento_id' => 1,
            'distrito_id' => 1,
            'ciudad_id' => 1,
            'tipo_persona_id' => 1,
            'sexo_id' => 1,
            'estado_civil_id' => 1,
            'tipo_vivienda_id' => 1,
            'documento' => '0',
            'ruc' => '',
            'nombre' => 'SIN NOMBRE',
            'apellido' => '',
            'fecha_nacimiento' => null,
            'direccion' => '',
            'barrio' => '',
            'celular' => '0',
            'email' => 'noreply@gmail.com',
            'vivienda' => '',
            'documento_frente' => '',
            'documento_reverso' => '',
            'selfi' => '',
            'estado_id' => 1,
            'user_id' => 1,
            'usuario_modificacion' => 1,
        ]);

        // Persona::create([
        //     'departamento_id' => 1,
        //     'distrito_id' => 1,
        //     'ciudad_id' => 1,
        //     'tipo_persona_id' => 1,
        //     'sexo_id' => 1,
        //     'estado_civil_id' => 1,
        //     'tipo_vivienda_id' => 1,
        //     'documento' => '4918642',
        //     'ruc' => '4918642',
        //     'nombre' => 'DAVID EMMANUEL',
        //     'apellido' => 'ORTIZ MIERES',
        //     'fecha_nacimiento' => '1998-11-11',
        //     'direccion' => 'RUTA 1 KM 19',
        //     'barrio' => 'SAN JUAN',
        //     'celular' => '0976820842',
        //     'email' => 'davidortiz25122010@gmail.com',
        //     'vivienda' => '',
        //     'documento_frente' => '',
        //     'documento_reverso' => '',
        //     'selfi' => '',
        //     'estado_id' => 1,
        //     'user_id' => 1,
        //     'usuario_modificacion' => 1,
        // ]);
    }
}
