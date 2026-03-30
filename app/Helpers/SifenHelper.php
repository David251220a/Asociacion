<?php

namespace App\Helpers;

use App\Models\Ciudad;
use App\Models\Departamento;
use App\Models\Distrito;
use DateTime;

class SifenHelper
{
    /****************AGREGAR CERO A LA IZQUIERDA RECIBE 2 VALORES **********/
    public static function leftzero($value, $size)
    {
        $re = str_pad($value, $size, '0', STR_PAD_LEFT);
        return $re;
    }

    /****************************modulo 11 test ****************************/
    public static function mr_digito_verificador($p_numero, $p_basemax = 11)
    {
        $v_total      = 0;
        $v_resto      = 0;
        $k            = 2;
        $v_numero_aux = 0;
        $v_numero_al  = "";
        $v_digit      = 0;

        for ($i = 0; $i < strlen($p_numero); $i++) {
            $v_caracter = strtoupper($p_numero[$i]);

            if (ord($v_caracter) >= 48 && ord($v_caracter) <= 57) {
                $v_numero_al .= $v_caracter;
            } else {
                $v_numero_al .= ord($v_caracter);
            }
        }

        $k       = 2;
        $v_total = 0;
        for ($i = strlen($v_numero_al) - 1; $i >= 0; $i--) {
            if ($k > $p_basemax) {
                $k = 2;
            }
            $v_numero_aux = intval($v_numero_al[$i]);
            $v_total += $v_numero_aux * $k;
            $k++;
        }
        $v_resto = $v_total % 11;
        if ($v_resto > 1) {
            $v_digit = 11 - $v_resto;
        } else {
            $v_digit = 0;
        }

        return $v_digit;
    }

    /******Formato fecha segun set */
    public static function formatDate($date)
    {

        $fecha_tmp = DateTime::createFromFormat('Y-m-d H:i:s', $date);
        $fecha_str = $fecha_tmp->format(DateTime::ATOM);
        return $fecha_str;
    }

    public static function formatoFechaSet($fecha)
    {
        $nuevoFormato = date("Y-m-d\TH:i:s", strtotime($fecha));
        return $nuevoFormato;
    }

    public static function formatoFechaCDC($fecha)
    {
        $nuevoFormato = date("Ymd", strtotime($fecha));
        return $nuevoFormato;
    }

    /************Valores segun  tipoEmision ******/
    public static function tipoEmision($value)
    {
        if ($value == '1') {
            return 'Normal';
        } else if ($value == '2') {
            return 'Contingencia';
        } else {
            return 'No existe tipo de emision';
        }
        //retorna la descripcion de tipoEmision para descripcionEmision
    }

    public static function tipoTransaccion($value)
    {
        switch ($value) {
            case 1:
                return 'Venta de mercadería';
                break;
            case 2:
                return 'Prestación de servicios';
                break;
            case 3:
                return 'Mixto” (Venta de mercadería y servicios)';
                break;
            case 4:
                return 'Venta de activo fijo';
                break;
            case 5:
                return 'Venta de divisas';
                break;
            case 6:
                return 'Venta de activo fijo';
                break;
            case 7:
                return 'Promoción o entrega de muestras';
                break;
            case 8:
                return 'Donación';
                break;
            case 9:
                return 'Anticipo';
                break;

        }
    }

    /*
    6= “Compra de divisas”
    7= “”
    8= ”
    9= “”
    10= “Compra de productos”
    11= “Compra de servicios”
    12= “Venta de crédito fiscal”
    13= ”Muestras médicas (Art. 3 RG
    24/2014)”
    */


    public static function tipoDocumentoElectronico($value)
    {
        switch ($value) {
            case 1;
                return 'Factura electrónica';
                break;
            case 2;
                return 'Factura electrónica de exportación (Futuro)';
                break;
            case 3;
                return 'Factura electrónica de importación (Futuro)';
                break;
            case 4;
                return 'Autofactura electrónica';
                break;
            case 5;
                return 'Nota de crédito electrónica';
                break;
            case 6;
                return 'Nota de débito electrónica';
                break;
            case 7;
                return 'Nota de remisión electrónica';
                break;
            case 8;
                return '= Comprobante de retención electrónico (Futuro)';
                break;

        }

    }

    public static function desImpuesto($value)
    {

        switch ($value) {
            case 1;
                return 'IVA';
                break;
            case 2;
                return 'ISC';
                break;
            case 3;
                return 'Renta';
                break;
            case 4;
                return 'Ninguno';
                break;
            case 5;
                return 'IVA - Renta';
                break;
        }

    }

    public static function tipoREgimen($value)
    {
        switch ($value) {
            case 1:
                return 'Régimen de Turismo';
                break;
            case 2:
                return 'Importador';
                break;
            case 3:
                return 'Exportador';
                break;
            case 4:
                return 'Maquila';
                break;
            case 5:
                return 'Ley N° 60/90';
                break;
            case 6:
                return 'Régimen del Pequeño Productor';
                break;
            case 7:
                return 'Régimen del Mediano Productor';
                break;
            case 8:
                return 'Régimen Contable';
                break;

        }
    }

    public static function MensajeErrorCreandoXML($value)
    {

        switch ($value) {
            case 1:
                return 'Error Interno';
                break;
            case 2:
                return 'Error con Sifen';
                break;

        }
    }

    public static function paisNombre($value)
    {

        switch ($value) {
            case "PRY":
                return 'Paraguay';
                break;
            case "ARG":
                return 'Argentina';
                break;
            case "BRA":
                return 'Brasil';
                break;
            case "USA":
                return 'Estados Unidos de América';
                break;
            case "BOL":
                return 'Bolivia';
                break;
            case "CHL":
                return 'Chile';
                break;
            case "COL":
                return 'Colombia';
                break;
            case "CUB":
                return 'Cuba';
                break;
            case "ECU":
                return 'Ecuador';
                break;
            case "SLV":
                return 'El Salvador';
                break;
            case "GTM":
                return 'Guatemala';
                break;
            case "HND":
                return 'Honduras';
                break;
            case "MEX":
                return 'México';
                break;
            case "NIC":
                return 'Nicaragua';
                break;
            case "PAN":
                return 'Panamá';
                break;
            case "PER":
                return 'Perú';
                break;
            case "PRI":
                return 'Puerto Rico';
                break;
            case "URY":
                return 'Uruguay';
                break;
            case "VEN":
                return 'Venezuela';
                break;
            case "JPN":
                return 'Japon';
                break;
            case "CHN":
                return 'China';
                break;
            case "ESP":
                return 'España';
                break;
        }

    }

    public static function paisNombreCode($value)
    {

        switch ($value) {
            case "Paraguay":
                return 'PRY';
                break;
            case "Argentina":
                return 'ARG';
                break;
            case "Brasil":
                return 'BRA';
                break;
            case "Estados Unidos":
                return 'USA';
                break;
            case "Bolivia":
                return 'BOL';
                break;
            case "Chile":
                return 'CHL';
                break;
            case "Colombia":
                return 'COL';
                break;
            case "Cuba":
                return 'CUB';
                break;
            case "Ecuador":
                return 'ECU';
                break;
            case "El Salvador":
                return 'SLV';
                break;
            case "Guatemala":
                return 'GTM';
                break;
            case "Honduras":
                return 'HND';
                break;
            case "México":
                return 'MEX';
                break;
            case "Nicaragua":
                return 'NIC';
                break;
            case "Panamá":
                return 'PAN';
                break;
            case "Perú":
                return 'PER';
                break;
            case "Puerto Rico":
                return 'PRI';
                break;
            case "Uruguay":
                return 'URY';
                break;
            case "Venezuela":
                return 'VEN';
                break;
            case "Japon":
                return 'JPN';
                break;
            case "China":
                return 'CHN';
                break;
            case "España":
                return 'ESP';
                break;
        }

    }

    public static function tipoPago($value)
    {

        switch ($value) {
            case "1":
                return 'Efectivo';
                break;
            case "2":
                return 'Cheque';
                break;
            case "3":
                return 'Tarjeta de crédito';
                break;
            case "4":
                return 'Tarjeta de débito';
                break;
            case "5":
                return 'Transferencia';
                break;
            case "6":
                return 'Giro';
                break;
            case "9":
                return 'Vale';
                break;
            case "15":
                return 'Permuta';
                break;
            case "99":
                return 'Multiple Pagos';
                break;

        }
    }

    public static function idenTarjeta($value)
    {

        switch ($value) {
            case "Visa":
                return '1';
                break;
            case "Mastercard":
                return '2';
                break;
            case "American Express":
                return '3';
                break;
            case "Maestro":
                return '4';
                break;
            case "Panal":
                return '5';
                break;
            case "Cabal":
                return '6';
                break;
            case "Otro":
                return '99';
                break;
        }
    }

    public static function unidadMedida($value)
    {

        switch ($value) {
            case "77":
                return 'UNI';
                break;
            case "83":
                return 'kg';
                break;
        }

    }

    public static function tipoGravada($value)
    {

        switch ($value) {
            case "1":
                return 'Gravado IVA';
                break;
            case "2":
                return 'Exonerado (Art. 83- Ley 125/91)';
                break;
            case "3":
                return 'Exento';
                break;
        }

    }

    public static function formatoTotales($numero, $decimales)
    {
        // redondear el número a la cantidad de decimales indicada
        $numero_redondeado = round($numero, $decimales);
        // completar con ceros si es necesario
        if ($numero_redondeado < 0) {
            return number_format($numero_redondeado * -1, $decimales, '.', '');
        } else {
            return number_format($numero_redondeado, $decimales, '.', '');
        }

    }

    public static function hash256($value)
    {
        $hash = hash("sha256", $value);
        return $hash;
    }

    public static function cdcSeparados($value)
    {
        $cantidad   = strlen($value);
        $grupos     = $cantidad / 4;
        $contador   = 0;
        $subcadenas = "";

        for ($i = 1; $i <= $grupos; $i++) {
            $subcadena = substr($value, $contador, 4);
            $subcadenas .= $subcadena . " ";
            $contador += 4;
        }

        return $subcadenas;

    }

    public static function rucCliente($value, $diplomatico,$cpais)
    {

        if ($value != null || !empty($value)) {
            if (strpos($value, "-") == true && $cpais=='PRY' && $diplomatico == false) {
                $ruc = '&dRucRec=' . substr($value, 0, strpos($value, '-'));

            } else {
                $ruc = '&dNumIDRec=' . $value;
            }

        } else {
            $ruc = '&dNumIDRec=0';

        }

        return $ruc;

    }

    public static function hashValores($value)
    {

        return bin2hex($value);
    }

    /******   FUNCIONES PARA CREAR LINK DEL QR    ******/
    public static function preLinkQR($qrVersion, $cdc, $dFeEmiDE, $rucLink, $dTotGralOpe, $dTotIVA, $cantItems, $digestValue, $idCSC)
    {
        $prepareLink = $qrVersion . $cdc . '&dFeEmiDE=' . $dFeEmiDE . $rucLink . '&dTotGralOpe=' . $dTotGralOpe;
        $prepareLink .= '&dTotIVA=' . $dTotIVA . '&cItems=' . $cantItems . '&DigestValue=' . $digestValue . '&IdCSC=' . $idCSC;
        return $prepareLink;
    }

    public static function gencHashQR($prepareLink, $_dCSC)
    {
        $cHashQR = hash('sha256', $prepareLink . $_dCSC); //concateno con el codigoSecreto de la set para tener el cHash en 256
        return $cHashQR;
    }

    public static function linkQR($faclinkqr, $prepareLink, $cHashQR)
    {
        $linkQR = $faclinkqr . $prepareLink . '&cHashQR=' . $cHashQR;
        return $linkQR;
    }

    public static function condicionPago($value)
    {
        switch ($value) {
            case "1":
                return 'Contado';
                break;
            case "2":
                return 'Crédito';
                break;

        }
    }

    public static function condicionCredito($value)
    {
        switch ($value) {
            case "1":
                return 'Plazo';
                break;
            case "2":
                return 'Cuota';
                break;

        }
    }

    public static function tipoMoneda($value)
    {
        switch ($value) {
            case "PYG":
                return 'Guarani';
                break;
            case "BRA":
                return 'Real';
                break;
        case "USD":
                return 'Us Dollar';
                break;
        case "EUR":
                return 'Euro';
                break;

        }
    }

    /*****  Funciones para Nota Remisiones  ****/
    public static function motivoRemision($value)
    {

        switch ($value) {
            case "1":
                return 'Traslado por ventas';
                break;
            case "2":
                return 'Traslado por consignación';
                break;
            case "3":
                return 'Exportación';
                break;
            case "4":
                return 'Traslado por compra';
                break;
            case "5":
                return 'Importación';
                break;
            case "6":
                return 'Traslado por devolución';
                break;
            case "7":
                return 'Traslado entre locales de la empresa';
                break;
            case "8":
                return 'Traslado de bienes por transformación';
                break;
            case "9":
                return 'Traslado de bienes por reparación';
                break;

        }

    }

    public static function respRemision($value)
    {

        switch ($value) {
            case "1":
                return 'Emisor de la factura';
                break;
            case "2":
                return 'Poseedor de la factura y bienes';
                break;
            case "3":
                return 'Empresa transportista';
                break;
            case "4":
                return 'Despachante de Aduanas';
                break;
            case "5":
                return 'Agente de transporte o intermediario';
                break;
        }

    }

    public static function tipoTransporte($value)
    {

        switch ($value) {
            case "1":
                return 'Propio';
                break;
            case "2":
                return 'Tercero';
                break;

        }

    }

    public static function tipoModalidad($value)
    {

        switch ($value) {
            case "1":
                return 'Terrestre';
                break;
            case "2":
                return 'Fluvial';
                break;
            case "3":
                return 'Aéreo';
                break;
            case "4":
                return 'Multimodal';
                break;
        }

    }

    public static function responsableFlete($value)
    {

        switch ($value) {
            case "1":
                return 'Emisor de la factura Electrónica';
                break;
            case "2":
                return 'Receptor de la Factura Electrónica';
                break;
            case "3":
                return 'Tercero';
                break;
            case "4":
                return 'Agente intermediario del transporte (cuando intervenga)';
                break;
            case "5":
                return 'Transporte propio';
                break;
        }

    }

    public static function codigoDepartamento($value)
    {
        $dep = Departamento::find($value);
        if($dep){
            return $dep->descripcion;
        }else{
            return '--';
        }
    }

    public static function codigoDistrito($value)
    {
        $dis = Distrito::find($value);
        if($dis){
            return $dis->descripcion;
        }else{
            return '--';
        }
    }

    public static function codigoCiudad($value)
    {
        $ciudad = Ciudad::find($value);
        if($ciudad){
            return $ciudad->descripcion;
        } else{
            return '--';
        }
    }

    public static function tipoDocumentoTransportista($value)
    {

        switch ($value) {
            case "1":
                return 'Cédula paraguaya';
                break;
            case "2":
                return 'Pasaporte';
                break;
            case "3":
                return 'Cédula extranjera';
                break;
            case "4":
                return 'Carnet de residencia';
                break;

        }
    }
}
