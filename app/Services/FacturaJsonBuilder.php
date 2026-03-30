<?php

namespace App\Services;

use App\Models\Entidad;
use App\Models\Establecimiento;
use App\Models\Factura;
use App\Models\Timbrado;
use Carbon\Carbon;

class FacturaJsonBuilder
{
    protected $factura, $entidad, $timbrado, $facturaDetalle, $facturaPago, $establecimiento;

    public function __construct(Factura $factura)
    {
        $this->factura = $factura;
        $this->entidad = Entidad::find(1);
        $this->timbrado = Timbrado::find($factura->timbrado_id);
        $this->establecimiento = Establecimiento::find($factura->establecimiento_id);
        $this->facturaDetalle = $factura->detalle;
        $this->facturaPago = $factura->forma_pagos;
    }

    public function jsonContado()
    {
        $codigoSeguridadAleatorio = random_int(100000000, 999999999);
        // $fecha = $this->factura->created_at . ' ' . now()->format('H:i:s');
       $fecha = $this->factura->fecha_factura;

        $json = [
            'fecha' => $fecha,
            'establecimiento' => $this->factura->factura_sucursal,
            'punto' => $this->factura->factura_general,
            'numero' => str_pad($this->factura->factura_numero, 7, '0', STR_PAD_LEFT),
            'descripcion' => $this->factura->concepto,
            'tipoDocumento' => $this->factura->tipo_documento_id, // 1 = Factura
            'tipoEmision' => 1,
            'tipoTransaccion' => $this->factura->tipo_transaccion_id,
            'receiptid' => $this->factura->id,
            'condicionPago' => $this->factura->condicion_pago,
            'moneda' => 'PYG',
            'cambio' => 0,
            'codigoSeguridadAleatorio' => strval($codigoSeguridadAleatorio),
        ];

        $persona = $this->factura->persona;

        $json['cliente'] = [
            'ruc' => $persona->ruc ?: $persona->documento,
            'nombre' => $persona->nombre . ' ' . $persona->apellido,
            'direccion' => $persona->direccion ?? 'N/A',
            'cpais' => 'PRY',
            'correo' => $persona->email ?? 'no-reply@email.com',
            'numCasa' => 0,
            'diplomatico' => false,
            'dncp' => 0
        ];

        // $json['documentoAsociado'] = [
        //     'remision' => false,
        //     'tipoDocumentoAsoc' => 1,
        //     'cdcAsociado' => '',
        //     'establecimientoAsoc' => '',
        //     'puntoAsoc' => '',
        //     'numeroAsoc' => '',
        //     'tipoDocuemntoIm' => '1',
        //     'fechaDocIm' => '',
        //     'timbradoAsoc' => ''
        // ];

        $items = [];
        $item_id = 0;
        foreach ($this->facturaDetalle as $item) {
            $ivaAfecta = 3;
            $tasa = 0;
            if($item->iva_afecta <> 3){
                $ivaAfecta = 1;
                $tasa = $item->iva_afecta;
            }

            $items[] = [
                'descripcion' => $item->descripcion,
                'codigo' => str_pad($item_id, 4, '0', STR_PAD_LEFT) ?? '0000',
                'unidadMedida' => 77.0, // General, por defecto
                'ivaTasa' => $tasa,
                'ivaAfecta' => $ivaAfecta,
                'cantidad' => $item->cantidad,
                'precioUnitario' => floatval($item->precio_unitario),
                'precioTotal' => floatval($item->monto_total),
                'baseGravItem' => floatval($item->grabado) + floatval($item->exento),
                'liqIvaItem' => floatval($item->iva),
            ];

            $item_id += 1;
        }
        $json['items'] = $items;

        $pagos = [];
        $totalPago = 0;

        foreach ($this->facturaPago as $pago) {
            $pagos[] = [
                'tipoPago' => (string) $pago->forma_cobro_id,
                'descripcion_pago' => $pago->banco->descripcion,
                'monto' => floatval($pago->monto)
            ];
            $totalPago += $pago->monto;
        }

        $json['pagos'] = $pagos;
        $json['totalPago'] = $totalPago;
        $json['totalRedondeo'] = 0;

        return $json;
    }

    // Función: genera un código de seguridad aleatorio de 8 dígitos
    protected function generarCodigoAleatorio()
    {
        return str_pad(mt_rand(0, 99999999), 8, '0', STR_PAD_LEFT);
    }

    // Función: calcula el dígito verificador usando el algoritmo Módulo 11
    protected function calculaDigitoVerificador($cadena)
    {
        $k = 2;
        $total = 0;

        for ($i = strlen($cadena) - 1; $i >= 0; $i--) {
            $total += intval($cadena[$i]) * $k;
            $k = ($k < 11) ? $k + 1 : 2;
        }

        $resto = $total % 11;

        if ($resto > 1) {
            return 11 - $resto;
        }

        return 0;
    }

    // Función: genera el CDC a partir de los datos
    protected function generarCDC($datos)
    {
        $secuencia = implode('', [
            '0', // Prefijo para factura electrónica
            $datos['tipoDocumento'],
            str_pad($datos['ruc'], 8, '0', STR_PAD_LEFT),
            $this->calculaDigitoVerificador($datos['ruc']),
            $datos['establecimiento'],
            $datos['punto'],
            str_pad($datos['numero'], 7, '0', STR_PAD_LEFT),
            $datos['tipoContribuyente'],
            Carbon::parse($datos['fecha'])->format('Ymd'),
            $datos['tipoEmision'],
            $datos['codigoSeguridadAleatorio']
        ]);

        $dv = $this->calculaDigitoVerificador($secuencia);
        return $secuencia . $dv;
    }


    // Función: convierte en base64 y limpia saltos de línea
    protected function base64Clean($data)
    {
        return str_replace(["\r", "\n"], '', base64_encode($data));
    }

}
