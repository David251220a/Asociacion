<?php

namespace App\Http\Controllers;

use App\Models\Entidad;
use App\Models\Factura;
use App\Models\FacturaAporte;
use App\Models\OrdenPago;
use App\Models\Planilla;
use App\Models\Recibo;
use App\Models\ReciboAporte;
use App\Models\ReciboDonacion;
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

    public function recibo(Recibo $recibo)
    {
        $entidad = Entidad::find(1);
        $data = collect();

        if (($recibo->tipo_recibo_id == 4) || ($recibo->tipo_recibo_id == 5)) {
            $detalle = ReciboAporte::where('recibo_id', $recibo->id)->first();

            if ($detalle) {
                if ((int) $detalle->planilla === 0) {
                    $planillaId = str_pad($detalle->planilla_numero, 5, '0', STR_PAD_LEFT) . '/' . $detalle->planilla_anio;
                    $descripcion = "APORTE {$detalle->mes}/{$detalle->anio} PLANILLA N° {$planillaId}";
                } else {
                    $descripcion = "APORTE MES " . strtoupper($this->nombreMes($detalle->mes)) . "/{$detalle->anio}";
                }

                $data = collect([
                    (object)[
                        'descripcion' => $descripcion,
                        'cantidad' => 1,
                        'precio' => $recibo->monto_total,
                        'exento' => $recibo->monto_total,
                        'grabado_5' => 0,
                        'grabado_10' => 0,
                        'iva_10' => 0,
                        'iva_5' => 0,
                        'total' => $recibo->monto_total,
                    ]
                ]);
            }
        }

        if ($recibo->tipo_recibo_id == 6) {

            $detalle = ReciboDonacion::where('recibo_id', $recibo->id)->first();

            if ($detalle) {
                $data = collect([
                    (object)[
                        'descripcion' => 'DONACIÓN',
                        'cantidad' => 1,
                        'precio' => $detalle->monto,
                        'exento' => $detalle->monto,
                        'grabado_5' => 0,
                        'grabado_10' => 0,
                        'iva_10' => 0,
                        'iva_5' => 0,
                        'total' => $detalle->monto,
                    ]
                ]);
            }
        }

        $pdf = Pdf::loadView('pdf.recibo', [
            'recibo' => $recibo,
            'data' => $data,
            'entidad' => $entidad
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('recibo.pdf');
    }

    public function orden(OrdenPago $ordenPago)
    {
        $entidad = Entidad::find(1);
        $data = $ordenPago;
        $pdf = Pdf::loadView('pdf.orden', [
            'data' => $data,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('orden_pago.pdf');
    }

    public function planilla(Planilla $planilla)
    {
        $entidad = Entidad::find(1);
        $data = $planilla;
        $pdf = Pdf::loadView('pdf.planilla', [
            'data' => $data,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('Planilla Aporte.pdf');
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
