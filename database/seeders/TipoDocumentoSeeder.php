<?php

namespace Database\Seeders;

use App\Models\TipoDocumento;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TipoDocumentoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $doc = ['Factura electrónica', 'Factura electrónica de exportación (Futuro)', 'Factura electrónica de importación (Futuro)', 'Autofactura electrónica'
        , 'Nota de crédito electrónica', 'Nota de débito electrónica', 'Nota de remisión electrónica', 'Comprobante de retención electrónico (Futuro)'];
        foreach ($doc as $item) {
            TipoDocumento::firstOrCreate([
                'descripcion' => $item
            ]);
        }
    }
}
