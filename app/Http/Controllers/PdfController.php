<?php

namespace App\Http\Controllers;

use App\Models\Factura;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;

class PdfController extends Controller
{
    public function factura(Factura $factura)
    {
        $textoQr = $factura->sifen->link_qr;
        // $textoQr = 'https://www.google.com';
        $result = Builder::create()
        ->writer(new PngWriter())
        ->data($textoQr)
        ->size(750)
        ->margin(25)
        ->build();

        $qrBase64 = base64_encode($result->getString());
        
        $pdf = Pdf::loadView('pdf.factura', [
            'qrBase64' => $qrBase64,
            'factura' => $factura,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('factura.pdf');
    }
}
