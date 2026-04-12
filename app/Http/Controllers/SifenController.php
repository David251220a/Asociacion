<?php

namespace App\Http\Controllers;

use App\Models\Factura;
use App\Models\Sifen;
use App\Services\FacturaJsonBuilder;
use App\Services\FacturaXMLBuilder;
use App\Services\SifenServices;
use Illuminate\Http\Request;

class SifenController extends Controller
{
    public $sifen;

    public function __construct(SifenServices $sifen)
    {
        $this->sifen = $sifen;
    }

    public function enviar(Factura $factura)
    {
        
        try {
            $sifen = Sifen::where('factura_id', $factura->id)->first();

            if (empty($sifen)) {
                $sifen = Sifen::create([
                    'factura_id' => $factura->id,
                    'cdc' => '',
                    'tipo_doc' => $factura->tipo_documento_id,
                    'documento_xml' => '',
                    'documento_pdf' => '',
                    'documento_zip' => null,
                    'zipeado' => 'N',
                    'secuencia' => 0,
                    'sifen_num_transaccion' => 0,
                    'sifen_estado' => 'PENDIENTE',
                    'sifen_mensaje' => ' ',
                    'fecha_firma' => $factura->fecha_factura,
                    'link_qr' => '',
                    'evento' => '',
                    'sifen_cod' => 0,
                    'tipo_transaccion' => $factura->tipo_transaccion_id,
                    'condicion_pago' => $factura->condicion_pago,
                    'moneda' => 'PYG',
                    'correo_enviado' => 'N',
                    'enviado_sifen' => 'N',
                    'sifen_respuesta_consulta_xml' => '',
                    'sifen_idprod' => 0
                ]);
            }

            $builder = new FacturaJsonBuilder($factura);
            $xml = new FacturaXMLBuilder();
            $json = [];

            if ($factura->tipo_documento_id == 1) {
               $respuestaJson = $builder->jsonContadoAporte();

                if (!$respuestaJson['success']) {
                    // return [
                    //     'success' => false,
                    //     'message' => $respuestaJson['message'],
                    //     'data' => null,
                    // ];
                    throw new \Exception($respuestaJson['message']);
                }

                $json = $respuestaJson['data'];
            } else {
                throw new \Exception('Tipo de documento no soportado todavía.');
            }

            $documento = $xml->generate($json, $factura->timbrado_id);
            $data = $documento['data'];
            if (!$documento['success']) {
                throw new \Exception($documento['message']);
                // return [
                //     'success' => false,
                //     'message' => $documento['message'],
                //     'data' => null,
                // ];
            }
            $sifen->update([
                'cdc' => $data['cdc'],
                'documento_xml' => $data['archivo_xml'],
                'documento_pdf' => 'facturas/' . $data['cdc'] . '.pdf',
                'documento_zip' => null,
                'zipeado' => 'N',
                'sifen_num_transaccion' => 0,
                'sifen_estado' => 'PENDIENTE',
                'sifen_mensaje' => '',
                'fecha_firma' => $data['fecha_firma'],
                'link_qr' => $data['link_qr'],
                'evento' => null,
                'sifen_cod' => 0,
            ]);
            
            $res = $this->sifen->enviar_directo($sifen);
            return redirect()->route('factura.show', $factura)->with('message', $res['message']);

        } catch (\Throwable $e) {
            return redirect()->route('factura.show')->with('message',  $e->getMessage());
            // return [
            //     'success' => false,
            //     'message' => $e->getMessage(),
            //     'data' => null,
            // ];
        }

    }
}
