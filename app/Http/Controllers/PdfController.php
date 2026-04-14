<?php

namespace App\Http\Controllers;

use App\Models\Factura;
use App\Models\FacturaAporte;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;

class PdfController extends Controller
{
    public function factura(Factura $factura)
    {
        $textoQr = $factura->sifen->link_qr;
        $result = Builder::create()
        ->writer(new PngWriter())
        ->data($textoQr)
        ->size(750)
        ->margin(25)
        ->build();

        $qrBase64 = base64_encode($result->getString());

        $data = collect();

        if ($factura->tipo_factura_id == 1) {
            $detalle = FacturaAporte::where('factura_id', $factura->id)->first();

            if ($detalle) {
                if ((int) $detalle->planilla === 0) {
                    $planillaId = str_pad($detalle->planilla_numero, 5, '0', STR_PAD_LEFT) . '/' . $detalle->planilla_anio;
                    $descripcion = "APORTE {$factura->mes}/{$factura->anio} PLANILLA N° {$planillaId}";
                } else {
                    $descripcion = "APORTE MES " . strtoupper($this->nombreMes($detalle->mes)) . "/{$detalle->anio}";
                }

                $data = collect([
                    (object)[
                        'descripcion' => $descripcion,
                        'cantidad' => 1,
                        'precio' => $factura->monto_total,
                        'exento' => $factura->monto_total,
                        'grabado_5' => 0,
                        'grabado_10' => 0,
                        'iva_10' => 0,
                        'iva_5' => 0,
                        'total' => $factura->monto_total,
                    ]
                ]);
            }
        }
        
        $pdf = Pdf::loadView('pdf.factura', [
            'qrBase64' => $qrBase64,
            'factura' => $factura,
            'data' => $data,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('factura.pdf');
    }

    private function nombreMes($mes)
    {
        $meses = [
            1 => 'ENERO',
            2 => 'FEBRERO',
            3 => 'MARZO',
            4 => 'ABRIL',
            5 => 'MAYO',
            6 => 'JUNIO',
            7 => 'JULIO',
            8 => 'AGOSTO',
            9 => 'SEPTIEMBRE',
            10 => 'OCTUBRE',
            11 => 'NOVIEMBRE',
            12 => 'DICIEMBRE',
        ];

        return $meses[(int) $mes] ?? '';
    }
}
