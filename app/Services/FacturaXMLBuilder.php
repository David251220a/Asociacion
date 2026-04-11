<?php

namespace App\Services;

use App\Models\Entidad;
use App\Models\Timbrado;
use App\Helpers\SifenHelper;
use App\Models\Establecimiento;
use App\Models\Factura;
use App\Models\Sifen;
use Carbon\Carbon;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use RobRichards\XMLSecLibs\XMLSecurityDSig;
use RobRichards\XMLSecLibs\Utils\XPath;
use DOMDocument;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FacturaXMLBuilder
{

    protected $entidad, $sifenHelper;

    public function __construct()
    {
        $this->entidad = Entidad::find(1);
        $this->sifenHelper = new SifenHelper();
    }

    public function generate(array $json, int $timbrado_id): array
    {
        try {

            $nombreArchivo = '';
            $datos = $json;
            $timbrado = Timbrado::find($timbrado_id);
            $factura = $this->buscar_factura($datos['receiptid']);
            $ent_establecimiento = Establecimiento::find($factura->establecimiento_id);
            // PRIMERA PARTE DE CONSTANTES.PHP
            $dNumTim= $timbrado->timbrado;
            $dFeIniT  = $timbrado->fecha_inicio;
            $_idEmpresa = $this->entidad->id;
            $dEmailE = $this->entidad->email;
            $iTipCont = $this->entidad->tipo_contribuyente;
            $dRucEm = $this->entidad->ruc_sin_digito;
            $dDVEmi = $this->entidad->digito_verificador;
            $ambiente   = $this->entidad->ambiente;
            $linkQrBase = config('facturacion.link_qr')[($ambiente == 1) ? 'produccion' : 'test'];
            $versionQr = config('facturacion.qr_version');
            if($this->entidad->ambiente == 1){
                $dNomEmi = $this->entidad->razon_social;
                $_codeCSC = $timbrado->codigo_set_id;
                $_dCSC = $timbrado->codigo_cliente_set;
                // $dNumTim  = $row['timbrado']; ESTE Y EL DE ABAJO ESTA DECLARADO ARRIBA
                // $dFeIniT  = $row['fecha_timbrado'];
            }else{
                $dNomEmi  = "DE generado en ambiente de prueba - sin valor comercial ni fiscal"; //en caso de hacer en ambiente de prueba llevar
                // $dNumTim  = $row['timbrado_test']; ESTE Y EL DE ABAJO ESTA DECLARADO ARRIBA
                // $dFeIniT  = $row['fecha_timbrado'];
                $_codeCSC = "001";
                $_dCSC    = "ABCD0000000000000000000000000000";
                // $_dCSC    = "B326123F3fd345C3a60F333B2025Ee9E";
            }
            $iTImp = '1';
            $cActEco = "";
            foreach ($this->entidad->actividades as $item) {
                $cActEco .= "<gActEco>\n";
                $cActEco .= "<cActEco>" . htmlspecialchars($item->codigo, ENT_XML1, 'UTF-8') . "</cActEco>\n";
                $cActEco .= "<dDesActEco>" . htmlspecialchars($item->descripcion, ENT_XML1, 'UTF-8') . "</dDesActEco>\n";
                $cActEco .= "</gActEco>\n";
            }

            $gOblAfe = "";
            foreach ($this->entidad->obligaciones as $item) {
                $gOblAfe .= "<gOblAfe>\n";
                $gOblAfe .= "<cOblAfe>" . htmlspecialchars($item->codigo, ENT_XML1, 'UTF-8') . "</cOblAfe>\n";
                $gOblAfe .= "<dDesOblAfe>" . htmlspecialchars($item->descripcion, ENT_XML1, 'UTF-8') . "</dDesOblAfe>\n";
                $gOblAfe .= "</gOblAfe>\n";
            }
            // SIEMPRE 1 POR QUE SABEMOS QUE TENES LOS ARCHIVOS .P12
            $_certificado = 1;
            // ACA TERMINA CONSTANTES.PHP

            $receiptid = $datos['receiptid'];
            $fechaDoc = $datos['fecha'];

            if ($fechaDoc < $timbrado->fecha_inicio) {
                throw new \Exception('ERROR - El número de timbrado no se encuentra vigente a la fecha de emisión del comprobante..');
            }
            
            /*gTimb*/
            $establecimiento = $this->sifenHelper->leftzero($datos['establecimiento'], 3);
            $tipoDocumento = $datos['tipoDocumento'];
            $punto = $this->sifenHelper->leftzero($datos['punto'], 3);
            $numero = $this->sifenHelper->leftzero($datos['numero'], 7);
            $codigoSeguridad = $datos['codigoSeguridadAleatorio'];
            $descripcion = $datos['descripcion'];
            $tipoEmision = $datos['tipoEmision']; //1= Normal 2= Contingencia
            $dDesTipEmi  = $this->sifenHelper->tipoEmision($datos['tipoEmision']);
            $dDesTiDE    = $factura->tipodocumentofactura->descripcion;
            /*/gTimb*/

            /* gDatGralOpe */
            $dFeEmiDE = $this->sifenHelper->formatoFechaSet($datos['fecha']); // formato 2020-05-07T15:03:57
            /* gDatGralOpe */
            /* gDatOpe */
            $iTipTra    = $datos['tipoTransaccion'];
            $dDesTipTra = $factura->tipoTransaccionFactura->descripcion;
            $iTImp      = $iTImp; //$datos['tipoImpuesto'];  //default 5 iva -renta
            $dDesTImp   = $this->sifenHelper->desImpuesto($iTImp); //default 5 iva -renta segun dani
            /*/gDatOpe */

            /*gEmis*/
            $iTipCont   = $iTipCont;
            $cTipReg    = "2"; //$datos['tiporegimen']; //default solalinde 2
            $dDirEmi    = $this->entidad->direccion;
            $dNumCas    = $this->entidad->numero_casa;
            $cDepEmi    = $this->entidad->departamento_id;
            $dDesDepEmi = $this->entidad->departamentos->descripcion;
            $cDisEmi    = $this->entidad->distrito_id;
            $dDesDisEmi = $this->entidad->distritos->descripcion;
            $cCiuEmi    = $this->entidad->ciudad_id;
            $dDesCiuEmi = $this->entidad->ciudades->descripcion;
            $dTelEmi    = $ent_establecimiento->telefono;
            $remisionAsoc = data_get($datos, 'documentoAsociado.remision', false);
            if ($tipoDocumento == "5" || $remisionAsoc == true) {
                if (!isset($datos['documentoAsociado']['tipoDocumentoAsoc']) || $datos['documentoAsociado']['tipoDocumentoAsoc'] == "1") {
                    $iTipDocAso = "1";
                    $dCdCDERef  = $datos['documentoAsociado']['cdcAsociado'];
                } else{
                    $iTipDocAso  = $datos['documentoAsociado']['tipoDocumentoAsoc']; //1 Electronico / 2 Impreso
                    $dEstDocAso  = $datos['documentoAsociado']['establecimientoAsoc'];
                    $dPExpDocAso = $datos['documentoAsociado']['puntoAsoc'];
                    $dNumDocAso  = $datos['documentoAsociado']['numeroAsoc'];
                    $iTipoDocAso = $datos['documentoAsociado']['tipoDocuemntoIm']; //1Factura, 2Ntc , 3 ntd, 4 nota de remision
                    $dFecEmiDI   = $datos['documentoAsociado']['fechaDocIm'];
                    $dNTimDI     = $datos['documentoAsociado']['timbradoAsoc'];
                }
            }
            /*gActEco*/
            /*/gActEco*/
            /*/gEmis*/
            /*gDatRec*///gRupo de campos que identifican al receptor
            $cPaisRec = (empty($datos['cliente']['cpais']) || !isset($datos['cliente']['cpais'])) ? 'PRY' : $datos['cliente']['cpais'];
            $dTiCam = 0;
            $condicionPago = $datos['condicionPago'];
            $cMoneOpe = $datos['moneda'];
            $dDesMoneOpe = $this->sifenHelper->tipoMoneda($cMoneOpe);
            if ($cMoneOpe == 'USD') {
                $dTiCam   = $datos['cambio']; //add en json
                $dTotalGs = $datos['totalGs']; //adden json
            }

            $paisDestinoNombre = $this->sifenHelper->paisNombre($cPaisRec);
            // EMPIEZA validacionesRucReceptor.php
            $gDatRec="";
            if ($datos['cliente']['ruc'] != null || !empty($datos['cliente']['ruc'])) {
                $tipoContribuyente = "";
                $RucPOS = $datos['cliente']['ruc'];
                $dNomRec = htmlspecialchars($datos['cliente']['nombre'], ENT_XML1, 'UTF-8') ?? ' ';
                $dDirRec = $datos['cliente']['direccion'] ?? '';
                $numCasa = $datos['cliente']['numCasa'] ?? '';
                $correo  = $datos['cliente']['correo'] ?? null;

                if (isset($datos['cliente']['diplomatico']) && $datos['cliente']['diplomatico'] == true) {
                    $diplomatico = true;
                    $iTipIDRec   = '6';
                } else {
                    $diplomatico = false;
                }

                if ($tipoDocumento == 7) {
                    //si es remision se necesita mas datos del cliente
                    // TOMAR EN CUENTA CUANDO NOTA REMISION PASAR LOS CODIGO AL JSON DEL CLIENTE
                    // DEPARTAMENTO_ID, DISTRICTO_ID, CIUDAD_ID
                    $dDirRec = $datos['cliente']['direccion'];
                    $cDepRec = $datos['cliente']['departamento'];
                    $numCasa = $datos['cliente']['numCasa'];
                    $cDisRec = $datos['cliente']['distrito'];
                    $cCiuRec = $datos['cliente']['ciudad'];
                }

                $existeEnbd = "Y";

                if ($existeEnbd == "Y") {
                    /**
                    * si existe en la base de datos verifico que ese activo o suspencion temporal
                    * para ingresar aqui como CONTRIBUYENTE, en caso de cancelado pasa a ser
                    * NO CONTRIBUYENTE
                    */
                    if (strpos($RucPOS, "-") == true and $diplomatico == false and $cPaisRec == 'PRY') {
                        $strRuc = substr($RucPOS, 0, 3);
                        if ($strRuc == "800" || $strRuc == "801") {
                            $tipoContribuyente = "2"; //juridica
                            $iTiOpe = '1';
                        } else {
                            $tipoContribuyente = "1"; //fisica
                            $iTiOpe = '2';
                        }

                        $dRucRec = substr($RucPOS, 0, strpos($RucPOS, '-'));
                        $dDVRec  = substr($RucPOS, strpos($RucPOS, '-') + 1);

                        $gDatRec = '
                        <iNatRec>1</iNatRec>
                        <iTiOpe>' . $iTiOpe . '</iTiOpe>
                        <cPaisRec>' . $cPaisRec . '</cPaisRec>
                        <dDesPaisRe>' . $paisDestinoNombre . '</dDesPaisRe>
                        <iTiContRec>' . $tipoContribuyente . '</iTiContRec>
                        <dRucRec>' . $dRucRec . '</dRucRec>
                        <dDVRec>' . $dDVRec . '</dDVRec>
                        <dNomRec>' . $dNomRec . '</dNomRec>';

                        if ($tipoDocumento == 7) {
                            $gDatRec .= '<dDirRec>' . $dDirRec . '</dDirRec>
                            <dNumCasRec>' . $numCasa . '</dNumCasRec>
                            <cDepRec>' . $cDepRec . '</cDepRec>
                            <dDesDepRec>' . $this->sifenHelper->codigoDepartamento($cDepRec) . '</dDesDepRec>
                            <cDisRec>' . $cDisRec . '</cDisRec>
                            <dDesDisRec>' . $this->sifenHelper->codigoDistrito($cDisRec) . '</dDesDisRec>
                            <cCiuRec>' . $cCiuRec . '</cCiuRec>
                            <dDesCiuRec>' . $this->sifenHelper->codigoCiudad($cCiuRec) . '</dDesCiuRec>
                            ';
                        }

                    } else {

                        if ($cPaisRec != 'PRY' and $diplomatico == false) {
                            $iTipIDRec  = '3';
                            $dDTipIDRec = "Cédula extranjera";
                            $gDatRec = '<iNatRec>2</iNatRec>
                            <iTiOpe>4</iTiOpe>
                            <cPaisRec>' . $cPaisRec . '</cPaisRec>
                            <dDesPaisRe>' . $paisDestinoNombre . '</dDesPaisRe>
                            <iTipIDRec>3</iTipIDRec>
                            <dDTipIDRec>Cédula extranjera</dDTipIDRec>
                            <dNumIDRec>' . $RucPOS . '</dNumIDRec>
                            <dNomRec>' . $dNomRec . '</dNomRec>
                            <dDirRec>' . $dDirRec . '</dDirRec>
                            <dNumCasRec>' . $numCasa . '</dNumCasRec>';
                        } else if ($cPaisRec == 'PRY' and $diplomatico == false) {
                            $iTipIDRec  = '1';
                            $dDTipIDRec = "Cédula paraguaya";
                            $gDatRec = '<iNatRec>2</iNatRec>
                            <iTiOpe>2</iTiOpe>
                            <cPaisRec>' . $cPaisRec . '</cPaisRec>
                            <dDesPaisRe>' . $paisDestinoNombre . '</dDesPaisRe>
                            <iTipIDRec>' . $iTipIDRec . '</iTipIDRec>
                            <dDTipIDRec>' . $dDTipIDRec . '</dDTipIDRec>
                            <dNumIDRec>' . $RucPOS . '</dNumIDRec>
                            <dNomRec>' . $dNomRec . '</dNomRec>';

                            if ($tipoDocumento == 7) {
                                $gDatRec .= '<dDirRec>' . $dDirRec . '</dDirRec>
                                <dNumCasRec>' . $numCasa . '</dNumCasRec>
                                <cDepRec>' . $cDepRec . '</cDepRec>
                                <dDesDepRec>' . $this->sifenHelper->codigoDepartamento($cDepRec) . '</dDesDepRec>
                                <cDisRec>' . $cDisRec . '</cDisRec>
                                <dDesDisRec>' . $this->sifenHelper->codigoDistrito($cDisRec) . '</dDesDisRec>
                                <cCiuRec>' . $cCiuRec . '</cCiuRec>
                                <dDesCiuRec>' . $this->sifenHelper->codigoCiudad($cCiuRec) . '</dDesCiuRec>
                                ';
                            }
                        }

                        if ($diplomatico == true) {
                            $iTipIDRec  = '6';
                            $dDTipIDRec = 'Tarjeta Diplomática de exoneración fiscal';
                            $gDatRec    = '<iNatRec>2</iNatRec>
                            <iTiOpe>2</iTiOpe>
                            <cPaisRec>' . $cPaisRec . '</cPaisRec>
                            <dDesPaisRe>' . $paisDestinoNombre . '</dDesPaisRe>
                            <iTipIDRec>' . $iTipIDRec . '</iTipIDRec>
                            <dDTipIDRec>' . $dDTipIDRec . '</dDTipIDRec>
                            <dNumIDRec>' . $RucPOS . '</dNumIDRec>
                            <dNomRec>' . $dNomRec . '</dNomRec>';
                            //si es diplomatico necesita todos los campos de direccion
                            if ($tipoDocumento == 7) {
                                $gDatRec .= '<dDirRec>' . $dDirRec . '</dDirRec>
                                <dNumCasRec>' . $numCasa . '</dNumCasRec>
                                <cDepRec>' . $cDepRec . '</cDepRec>
                                <dDesDepRec>' . $this->sifenHelper->codigoDepartamento($cDepRec) . '</dDesDepRec>
                                <cDisRec>' . $cDisRec . '</cDisRec>
                                <dDesDisRec>' . $this->sifenHelper->codigoDistrito($cDisRec) . '</dDesDisRec>
                                <cCiuRec>' . $cCiuRec . '</cCiuRec>
                                <dDesCiuRec>' . $this->sifenHelper->codigoCiudad($cCiuRec) . '</dDesCiuRec>
                                ';
                            }
                        }

                        // echo $dRucRec;
                        /* por ahora es solo cedula */
                        // echo 'es cedula u otro';
                    }
                } else {
                    /**
                    * es no contribuyente o
                    * no esta en la base de datos
                    */
                    $iTipIDRec = 5;
                    $dDTipIDRec = 'Innominado';
                    $gDatRec = '<iNatRec>2</iNatRec>
                    <iTiOpe>2</iTiOpe>
                    <cPaisRec>' . $cPaisRec . '</cPaisRec>
                    <iTipIDRec>' . $iTipIDRec . '</iTipIDRec>
                    <dDTipIDRec>' . $dDTipIDRec . '</dDTipIDRec>
                    <dNumIDRec>' . $RucPOS . '</dNumIDRec>
                    <dNomRec>' . $dNomRec . '</dNomRec>';
                }
            } else {
                $RucPOS = null;
                $diplomatico = false;
                /**
                 * Si llego aqui es porque la factura es sin cliente
                 */
                $gDatRec = '<iNatRec>2</iNatRec>
                <iTiOpe>2</iTiOpe>
                <cPaisRec>PRY</cPaisRec>
                <dDesPaisRe>Paraguay</dDesPaisRe>
                <iTipIDRec>5</iTipIDRec>
                <dDTipIDRec>Innominado</dDTipIDRec>
                <dNumIDRec>0</dNumIDRec>
                <dNomRec>Sin Nombre</dNomRec>';
            }
            // TERMINA validacionesRucReceptor.php

            // SI NOTA DE CREDITO HACER ESTO
            if ($tipoDocumento == "7") {
                $iMotEmiNR  = $datos['remision']['motivo'];
                $iRespEmiNR = $datos['remision']['responsableEmi'];
                $dKmR       = $datos['remision']['kmEstimado'];
                $facturaRe  = $datos['remision']['factura']; // 0 Sin factura 1 con factura no es necesario dFecEm
                if ($facturaRe == 0) {
                    $dFecEm = $datos['remision']['fechaFactura'];
                }

                /*Transporte*/
                //gTransp
                $iTipTrans  = $datos['transporte'][0]['tipoTransporte'];
                $iModTrans  = $datos['transporte'][0]['modalidad'];
                $iRespFlete = $datos['transporte'][0]['tipoResponsable'];
                $dIniTras = $datos['transporte'][0]['iniFechaEstimadaTrans'];
                $dFinTras = $datos['transporte'][0]['finFechaEstimadaTrans'];
                //gCamSal
                $dDirLocSal = $datos['transporte'][0]['salida']['direccion'];
                $dNumCasSal = $datos['transporte'][0]['salida']['numCasa'];
                $cDepSal    = $datos['transporte'][0]['salida']['departamento'];
                $dDesDepSal = $this->sifenHelper->codigoDepartamento($datos['transporte'][0]['salida']['departamento']);
                $cDisSal    = $datos['transporte'][0]['salida']['distrito'];
                $dDesDisSal = $this->sifenHelper->codigoDistrito($datos['transporte'][0]['salida']['distrito']);
                $cCiuSal    = $datos['transporte'][0]['salida']['ciudad'];
                $dDesCiuSal = $this->sifenHelper->codigoCiudad($datos['transporte'][0]['salida']['ciudad']);
                //gCamEnt
                $dDirLocEnt = $datos['transporte'][0]['entrega']['direccion'];
                $dNumCasEnt = $datos['transporte'][0]['entrega']['numCasa'];
                $cDepEnt    = $datos['transporte'][0]['entrega']['departamento'];
                $dDesDepEnt =  $this->sifenHelper->codigoDepartamento($datos['transporte'][0]['entrega']['departamento']);
                $cDisEnt    = $datos['transporte'][0]['entrega']['distrito'];
                $dDesDisEnt =  $this->sifenHelper->codigoDistrito($datos['transporte'][0]['entrega']['distrito']);
                $cCiuEnt    = $datos['transporte'][0]['entrega']['ciudad'];
                $dDesCiuEnt =  $this->sifenHelper->codigoCiudad($datos['transporte'][0]['entrega']['ciudad']);
                //gVehTras
                $dTiVehTras  = $datos['transporte'][0]['vehiculo']['tipo'];
                $dMarVeh     = $datos['transporte'][0]['vehiculo']['marca'];
                $dTipIdenVeh = $datos['transporte'][0]['vehiculo']['documentoTipo'];
                $numeroIden  = $datos['transporte'][0]['vehiculo']['numeroIden'];
                //gCamTrans
                $iNatTrans    = $datos['transporte'][0]['transportista']['tipo']; //Naturaleza del transportista
                $dNomTrans    = $datos['transporte'][0]['transportista']['nombreTr'];
                $iTipIDTrans  = $datos['transporte'][0]['transportista']['tipoDocumentoTr'];
                $dDTipIDTrans = $this->sifenHelper->tipoDocumentoTransportista($iTipIDTrans);
                $documentoTr  = $datos['transporte'][0]['transportista']['numeroTr'];
                $dNomChof     = $datos['transporte'][0]['transportista']['nombreCh'];
                $dNumIDChof   = $datos['transporte'][0]['transportista']['numeroCh'];

                $dDomFisc = $datos['transporte'][0]['transportista']['direccionTr'];
                $dDirChof = $datos['transporte'][0]['transportista']['direccionCh'];
            }

            $totalPago = $datos['totalPago'];

            // VALIDACIONES
            if ($datos['tipoDocumento'] == 7) {
                $mensajeError = $this->validaciones($json, $iNatTrans);
                if ($mensajeError !== null) {
                    throw new \Exception('ERROR - ' . $mensajeError);
                }
            }

            $duplicacion_Error = $this->verificar_duplicado($factura->id);
            if ($duplicacion_Error !== null) {
                throw new \Exception('ERROR - ' . $duplicacion_Error);
            }

            $cdcTemp = '0' . $tipoDocumento . $this->sifenHelper->leftzero($dRucEm, 8) . $dDVEmi
            . $establecimiento . $punto . $numero . $iTipCont . $this->sifenHelper->formatoFechaCDC($dFeEmiDE) . $tipoEmision . $codigoSeguridad;
            $dvCDC   = $this->sifenHelper->mr_digito_verificador($cdcTemp);

            $cdc = $cdcTemp . $dvCDC;

            //$fechaHoraFirma = date('Y-m-d\TH:i:s');
            //$fechaHoraFirma = $this->sifenFechaHoraFirma();
            $fechaHoraFirma = $dFeEmiDE;
            $genXML = '<?xml version="1.0" encoding="UTF-8"?>
            <rDE xmlns="http://ekuatia.set.gov.py/sifen/xsd" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="https://ekuatia.set.gov.py/sifen/xsd siRecepDE_v150.xsd">
                <dVerFor>150</dVerFor>
                <DE Id="' . $cdc . '">
                    <dDVId>' . $dvCDC . '</dDVId>
                    <dFecFirma>' . $fechaHoraFirma . '</dFecFirma>
                    <dSisFact>1</dSisFact>
                    <!-- B -->
                    <gOpeDE>
                        <iTipEmi>' . $tipoEmision . '</iTipEmi>
                        <dDesTipEmi>' . $dDesTipEmi . '</dDesTipEmi>
                        <dCodSeg>' . $codigoSeguridad . '</dCodSeg>
                        <dInfoEmi>1</dInfoEmi>

                    </gOpeDE>
                    <!-- C -->
                    <gTimb>
                    <iTiDE>' . $tipoDocumento . '</iTiDE>
                    <dDesTiDE>' . $dDesTiDE . '</dDesTiDE>
                    <dNumTim>' . $dNumTim . '</dNumTim>
                    <dEst>' . $establecimiento . '</dEst>
                    <dPunExp>' . $punto . '</dPunExp>
                    <dNumDoc>' . $numero . '</dNumDoc>
                    <dFeIniT>' . $dFeIniT . '</dFeIniT>
                </gTimb>
                <!--  D  -->
                <gDatGralOpe>
                <dFeEmiDE>' . $dFeEmiDE . '</dFeEmiDE>
                <!--  D1  -->
                <!--  D010  -->';

            if ($tipoDocumento != 7) {
                //no mostrar si es remision
                if ($tipoDocumento == 5) {
                    $genXML .= '<gOpeCom>
                    <iTImp>' . $iTImp . '</iTImp>
                    <dDesTImp>' . $dDesTImp . '</dDesTImp>
                    <cMoneOpe>' . $cMoneOpe . '</cMoneOpe>
                    <dDesMoneOpe>' . $dDesMoneOpe . '</dDesMoneOpe>';
                    if (!empty($gOblAfe)) {
                        $genXML .= $gOblAfe;
                    }

                    $genXML .= '</gOpeCom>';
                } else {
                    $genXML .= '<gOpeCom><iTipTra>' . $iTipTra . '</iTipTra>
                    <dDesTipTra>' . $dDesTipTra . '</dDesTipTra>
                    <iTImp>' . $iTImp . '</iTImp>
                    <dDesTImp>' . $dDesTImp . '</dDesTImp>
                    <cMoneOpe>' . $cMoneOpe . '</cMoneOpe>
                    <dDesMoneOpe>' . $dDesMoneOpe . '</dDesMoneOpe>';
                    if ($cMoneOpe != 'PYG') {
                        $genXML .= '<dCondTiCam>1</dCondTiCam>
                        <dTiCam>' . $this->sifenHelper->formatoTotales($dTiCam, 4) . '</dTiCam>';
                    }

                    if (!empty($gOblAfe)) {
                        $genXML .= $gOblAfe;
                    }
                    $genXML .= '</gOpeCom>';
                }
            }

            $genXML .= '<gEmis>
            <dRucEm>' . $dRucEm . '</dRucEm>
            <dDVEmi>' . $dDVEmi . '</dDVEmi>
            <iTipCont>' . $iTipCont . '</iTipCont>
            <cTipReg>' . $cTipReg . '</cTipReg>
            <dNomEmi>' . $dNomEmi . '</dNomEmi>
            <dDirEmi>' . $dDirEmi . '</dDirEmi>
            <dNumCas>' . $dNumCas . '</dNumCas>
            <cDepEmi>' . $cDepEmi . '</cDepEmi>
            <dDesDepEmi>' . $dDesDepEmi . '</dDesDepEmi>
            <cDisEmi>' . $cDisEmi . '</cDisEmi>
            <dDesDisEmi>' . $dDesDisEmi . '</dDesDisEmi>
            <cCiuEmi>' . $cCiuEmi . '</cCiuEmi>
            <dDesCiuEmi>' . $dDesCiuEmi . '</dDesCiuEmi>
            <dTelEmi>' . $dTelEmi . '</dTelEmi>
            <dEmailE>' . $dEmailE . '</dEmailE>
            <!--  D2.1  -->';

            $genXML .= $cActEco;
            $genXML .= '</gEmis>
            <!--  D3  -->
            <gDatRec>
            ' . $gDatRec . '
            </gDatRec>
            </gDatGralOpe>
            <!--  E  -->
            <gDtipDE>
            <!--  E1  -->';

            if ($tipoDocumento == '1') {
                //mostrar solo si c002 =1
                $genXML .= '
                <gCamFE>
                    <iIndPres>1</iIndPres>
                    <dDesIndPres>Operación presencial</dDesIndPres>
                </gCamFE>';
            } //campo que corresponde a FE

            if ($tipoDocumento == "1" || $tipoDocumento == "4") {
                $genXML .= '
                <!-- E7 -->
                    <gCamCond>
                            <iCondOpe>' . $condicionPago . '</iCondOpe>
                            <dDCondOpe>' . $this->sifenHelper->condicionPago($condicionPago) . '</dDCondOpe>
                            ';

                if ($condicionPago == 1) {
                    $cantPagos = count($datos['pagos']);
                    $pagos     = $datos['pagos'];
                    for ($i = 0; $i < $cantPagos; $i++) {
                        $genXML .= '
                            <gPaConEIni>
                                <iTiPago>' . $pagos[$i]['tipoPago'] . '</iTiPago>
                                    <dDesTiPag>' . $this->sifenHelper->tipoPago($pagos[$i]['tipoPago']) . '</dDesTiPag>
                                    <dMonTiPag>' . $pagos[$i]['monto'] . '</dMonTiPag>
                                    <cMoneTiPag>' . $cMoneOpe . '</cMoneTiPag>
                                    <dDMoneTiPag>' . $dDesMoneOpe . '</dDMoneTiPag>';
                        if ($cMoneOpe != "PYG") {
                            $genXML .= '<dTiCamTiPag>' . $dTiCam . '</dTiCamTiPag>';
                        }

                        if ($pagos[$i]['tipoPago'] == "2") {
                            $genXML .= '<gPagCheq>
                                        <dNumCheq>' . $pagos[$i]['numero'] . '</dNumCheq>
                                        <dBcoEmi>' . $pagos[$i]['banco'] . '</dBcoEmi>
                                        </gPagCheq>';
                        }

                        if ($pagos[$i]['tipoPago'] == "3" || $pagos[$i]['tipoPago'] == "4") {
                            $genXML .= '<gPagTarCD>
                                    <iDenTarj>' . $this->sifenHelper->idenTarjeta($pagos[$i]['tarjeta']['denominacion']) . '</iDenTarj>
                                    <dDesDenTarj>' . $pagos[$i]['tarjeta']['denominacion'] . '</dDesDenTarj>
                                    <dRSProTar>BANCARD SA</dRSProTar>
                                    <dRUCProTar>80013884</dRUCProTar>
                                    <dDVProTar>8</dDVProTar>
                                    <iForProPa>1</iForProPa>
                                </gPagTarCD>';
                        }

                        $genXML .= ' </gPaConEIni>';
                    }
                } else if ($condicionPago == 2) { //condicionPago Contado fin
                    $credito          = $datos['credito'];
                    $condicionCredito = $credito['condicionCredito'];
                    $descripcionPlazo = $credito['descripcion'];
                    $genXML .= '
                            <!-- E7.2 -->
                            <gPagCred>
                            <iCondCred>' . $condicionCredito . '</iCondCred>
                            <dDCondCred>' . $this->sifenHelper->condicionCredito($condicionCredito) . '</dDCondCred>';

                    if ($condicionCredito == 1) {
                        $genXML .= '<dPlazoCre>' . $descripcionPlazo . '</dPlazoCre>';
                    } else {

                        $cantidadCuotas = $credito['cantidadCuota'];

                        $genXML .= '<dCuotas>' . $cantidadCuotas . '</dCuotas>';

                        //for ($i = 0; $i < $numeroCuotas; $i++) {
                        foreach ($credito['cuotas'] as $sec) {
                            // $sec = $cuotas['cuotas'][$i];
                            $genXML .= '
                                <gCuotas>
                                    <cMoneCuo>' . $cMoneOpe . '</cMoneCuo>
                                    <dDMoneCuo>' . $dDesMoneOpe . '</dDMoneCuo>
                                    <dMonCuota>' . $sec['monto'] . '</dMonCuota>
                                    <dVencCuo>' . $sec['fechaVencimiento'] . '</dVencCuo>
                                    </gCuotas>
                                            ';
                        }
                    }
                    $genXML .= '</gPagCred>';
                }
                $genXML .= '</gCamCond>
                ';

            } else if ($tipoDocumento == 7) {
                $genXML .= '
                        <!-- E6 -->
                        <gCamNRE>
                                <iMotEmiNR>' . $iMotEmiNR . '</iMotEmiNR>
                                        <dDesMotEmiNR>' . $this->sifenHelper->motivoRemision($iMotEmiNR) . '</dDesMotEmiNR>
                                        <iRespEmiNR>' . $iRespEmiNR . '</iRespEmiNR>
                                        <dDesRespEmiNR>' . $this->sifenHelper->respRemision($iRespEmiNR) . '</dDesRespEmiNR>
                                        <dKmR>' . $dKmR . '</dKmR>';
                if ($facturaRe == 0) {
                    $genXML .= '<dFecEm>' . $dFecEm . '</dFecEm>';
                }
                $genXML .= ' </gCamNRE>
                                    <!-- E8 -->';

            } else if ($tipoDocumento == "5" || $tipoDocumento == "6") {
                //tratamiento de Nota de credito / debito
                $genXML .= '<gCamNCDE>
                        <iMotEmi>2</iMotEmi>
                        <dDesMotEmi>Devolución</dDesMotEmi>
                        </gCamNCDE>
                        <!-- E8 -->';
            }

            $totalGravExenta = 0;
            $totalGrav10     = 0;
            $totalGrav5      = 0;

            $totalIvaLiq10 = 0;
            $totalIvaLiq5  = 0;

            $totalEx = 0;
            $total5  = 0;
            $total10 = 0;

            $totalOperacion = 0;
            $cantItems      = count($datos['items']);
            $prod           = $datos['items'];

            for ($i = 0; $i < $cantItems; $i++) {
                $genXML .= '   <gCamItem>
                                <dCodInt>' . $prod[$i]['codigo'] . '</dCodInt>
                                <dDesProSer> ' . htmlspecialchars(str_replace('\/', '/', $prod[$i]['descripcion']), ENT_XML1, 'UTF-8') . '</dDesProSer>
                            <cUniMed>' . $prod[$i]['unidadMedida'] . '</cUniMed>
                            <dDesUniMed>' . $this->sifenHelper->unidadMedida($prod[$i]['unidadMedida']) . '</dDesUniMed>
                            <dCantProSer>' . $this->sifenHelper->formatoTotales($prod[$i]['cantidad'], 4) . '</dCantProSer>';
                if ($tipoDocumento != 7) {
                    $genXML .= '<gValorItem>
                                <dPUniProSer>' . $this->sifenHelper->formatoTotales($prod[$i]['precioUnitario'], 8) . '</dPUniProSer>
                                <dTotBruOpeItem>' . $this->sifenHelper->formatoTotales($prod[$i]['cantidad'] * $prod[$i]['precioUnitario'], 8) . '</dTotBruOpeItem>
                                <gValorRestaItem>
                                    <dDescItem>0</dDescItem>
                                    <dTotOpeItem>' . $this->sifenHelper->formatoTotales($prod[$i]['precioTotal'], 8) . '</dTotOpeItem>
                                </gValorRestaItem>
                            </gValorItem>
                            <gCamIVA>
                                <iAfecIVA>' . $prod[$i]['ivaAfecta'] . '</iAfecIVA>
                                <dDesAfecIVA>' . $this->sifenHelper->tipoGravada($prod[$i]['ivaAfecta']) . '</dDesAfecIVA>';

                    if ($prod[$i]['ivaAfecta'] == 3) {
                        $dPropIVA = 0;
                    } else {
                        $dPropIVA = 100;
                    }

                    $genXML .= '<dPropIVA>' . $dPropIVA . '</dPropIVA>
                                <dTasaIVA>' . $prod[$i]['ivaTasa'] . '</dTasaIVA>';
                    if ($prod[$i]['ivaTasa'] == 0) {
                        $genXML .= '<dBasGravIVA>0</dBasGravIVA>
                                <dLiqIVAItem>0</dLiqIVAItem>
                                <dBasExe>0</dBasExe>';
                    } else {
                        $genXML .= '<dBasGravIVA>' . $this->sifenHelper->formatoTotales($prod[$i]['baseGravItem'], 8) . '</dBasGravIVA>
                                <dLiqIVAItem>' . $this->sifenHelper->formatoTotales($prod[$i]['liqIvaItem'], 8) . '</dLiqIVAItem>
                                <dBasExe>0</dBasExe>';
                    }
                    $genXML .= '</gCamIVA>';
                } // no mostrar si es remision
                $genXML .= '</gCamItem>
                            ';

                $totalOperacion += $prod[$i]['precioTotal'];
                if ($prod[$i]['ivaTasa'] == 10) {
                    $totalGrav10 += $prod[$i]['baseGravItem'];
                    $totalIvaLiq10 += $prod[$i]['liqIvaItem'];
                    $total10 += $prod[$i]['precioTotal'];
                } else if ($prod[$i]['ivaTasa'] == 5) {
                    $totalGrav5 += $prod[$i]['baseGravItem'];
                    $totalIvaLiq5 += $prod[$i]['liqIvaItem'];
                    $total5 += $prod[$i]['precioTotal'];
                } else if ($prod[$i]['ivaTasa'] == 0) {
                    $totalGravExenta += $prod[$i]['baseGravItem'];
                    $totalEx += $prod[$i]['precioTotal'];
                }
            }

            if ($tipoDocumento == "1" || $tipoDocumento == "4" || $tipoDocumento == "5") {
                $genXML .= '</gDtipDE><!--  F  --><gTotSub>
                        <dSubExe>' . $this->sifenHelper->formatoTotales($totalEx, 8) . '</dSubExe>
                        <dSub5>' . $this->sifenHelper->formatoTotales($total5, 8) . '</dSub5>
                        <dSub10>' . $this->sifenHelper->formatoTotales($total10, 8) . '</dSub10>
                        <dTotOpe>' . $this->sifenHelper->formatoTotales($totalOperacion, 8) . '</dTotOpe>
                        <dTotDesc>0.00000000</dTotDesc>
                        <dTotDescGlotem>0.00000000</dTotDescGlotem>
                        <dTotAntItem>0</dTotAntItem>
                        <dTotAnt>0</dTotAnt>
                        <dPorcDescTotal>0.00000000</dPorcDescTotal>
                        <dDescTotal>0.00000000</dDescTotal>
                        <dAnticipo>0</dAnticipo>
                        <dRedon>0.0000</dRedon>
                        <dTotGralOpe>' . $this->sifenHelper->formatoTotales($totalOperacion, 8) . '</dTotGralOpe>
                        <dIVA5>' . $this->sifenHelper->formatoTotales($totalIvaLiq5, 8) . '</dIVA5>
                        <dIVA10>' . $this->sifenHelper->formatoTotales($totalIvaLiq10, 8) . '</dIVA10>
                        <dTotIVA>' . $this->sifenHelper->formatoTotales($totalIvaLiq5 + $totalIvaLiq10, 8) . '</dTotIVA>
                        <dBaseGrav5>' . $this->sifenHelper->formatoTotales($totalGrav5, 8) . '</dBaseGrav5>
                        <dBaseGrav10>' . $this->sifenHelper->formatoTotales($totalGrav10, 8) . '</dBaseGrav10>
                        <dTBasGraIVA>' . $this->sifenHelper->formatoTotales(($totalGrav10 + $totalGrav5), 8) . '</dTBasGraIVA> ';
                if ($cMoneOpe != 'PYG') {
                    $genXML .= '<dTotalGs>' . $dTotalGs . '</dTotalGs>';
                }
                $genXML .= '</gTotSub>';
            } else if ($tipoDocumento == "7") {
                //datos de remision
                $genXML .= '
                <!-- E10 -->
                <gTransp>
                <iTipTrans>' . $iTipTrans . '</iTipTrans>
                <dDesTipTrans>' . $this->sifenHelper->tipoTransporte($iTipTrans) . '</dDesTipTrans>
                <iModTrans>' . $iModTrans . '</iModTrans>
                <dDesModTrans>' . $this->sifenHelper->tipoModalidad($iModTrans) . '</dDesModTrans>
                <iRespFlete>' . $iRespFlete . '</iRespFlete>
                <dIniTras>' . $dIniTras . '</dIniTras>
                <dFinTras>' . $dFinTras . '</dFinTras>
                <cPaisDest>PRY</cPaisDest>
                <dDesPaisDest>Paraguay</dDesPaisDest>
                <!-- E10.1 -->
                <gCamSal>
                <dDirLocSal>' . $dDirLocSal . '</dDirLocSal>
                <dNumCasSal>' . $dNumCasSal . '</dNumCasSal>
                <cDepSal>' . $cDepSal . '</cDepSal>
                <dDesDepSal>' . $dDesDepSal . '</dDesDepSal>
                <cDisSal>' . $cDisSal . '</cDisSal>
                <dDesDisSal>' . $dDesDisSal . '</dDesDisSal>
                <cCiuSal>' . $cCiuSal . '</cCiuSal>
                <dDesCiuSal>' . $dDesCiuSal . '</dDesCiuSal>
                </gCamSal>
                <!-- E10.2 -->
                <gCamEnt>
                <dDirLocEnt>' . $dDirLocSal . '</dDirLocEnt>
                <dNumCasEnt>' . $dNumCasSal . '</dNumCasEnt>
                <cDepEnt>' . $cDepSal . '</cDepEnt>
                <dDesDepEnt>' . $dDesDepSal . '</dDesDepEnt>
                <cDisEnt>' . $cDisSal . '</cDisEnt>
                <dDesDisEnt>' . $dDesDisSal . '</dDesDisEnt>
                <cCiuEnt>' . $cCiuSal . '</cCiuEnt>
                <dDesCiuEnt>' . $dDesCiuSal . '</dDesCiuEnt>
                </gCamEnt>
                <!-- E10.3 -->
                <gVehTras>
                <dTiVehTras>Camioneta</dTiVehTras>
                <dMarVeh>' . $dMarVeh . '</dMarVeh>
                <dTipIdenVeh>' . $dTipIdenVeh . '</dTipIdenVeh>';
                if ($numeroIden == 1) {
                    $genXML .= '<dNroIDVeh>' . $numeroIden . '</dNroIDVeh>';
                } else {
                    $genXML .= '<dNroMatVeh>' . $numeroIden . '</dNroMatVeh>';
                }
                $genXML .= '
                </gVehTras>
                <!-- E10.4 -->
                <gCamTrans>
                <iNatTrans>' . $iNatTrans . '</iNatTrans>
                <dNomTrans>' . $dNomTrans . '</dNomTrans>
                ';
                if ($iNatTrans == 2) {
                    $genXML .=
                        '<iTipIDTrans>' . $iTipIDTrans . '</iTipIDTrans>
                        <dDTipIDTrans>' . $dDTipIDTrans . '</dDTipIDTrans>
                        <dNumIDTrans>' . $documentoTr . '</dNumIDTrans>
                    ';
                } else {
                    $dRucRec = substr($documentoTr, 0, strpos($documentoTr, '-'));
                    $dDVRec  = substr($documentoTr, strpos($documentoTr, '-') + 1);
                    $genXML .=
                        '<dRucTrans>' . $dRucRec . '</dRucTrans>
                        <dDVTrans>' . $dDVRec . '</dDVTrans>';
                }

                $genXML .= '
                <!-- E10.4 E991 -->
                <dNumIDChof>' . $dNumIDChof . '</dNumIDChof>
                <dNomChof>' . $dNomChof . '</dNomChof>
                <dDomFisc>' . $dDomFisc . '</dDomFisc>
                <dDirChof>' . $dDirChof . '</dDirChof>
                </gCamTrans>
                </gTransp>
                </gDtipDE>';
            }

            /*Si Obligatorio si C002 = 4, 5, 6
            Opcional si C002=1 o 7
            Nota de credito, debito y autofactura Obligatorios
            si no Opcional.
            */

            if ($tipoDocumento == '5' || $remisionAsoc == true) {
                if ($iTipDocAso == '1') {
                    $genXML .= '<gCamDEAsoc>
                        <iTipDocAso>1</iTipDocAso>
                        <dDesTipDocAso>Electrónico</dDesTipDocAso>
                        <dCdCDERef>' . $dCdCDERef . '</dCdCDERef>
                    </gCamDEAsoc>';
                } else if ($iTipDocAso == '2') {
                    $genXML .= '<gCamDEAsoc>
                        <iTipDocAso>2</iTipDocAso>
                        <dDesTipDocAso>Impreso</dDesTipDocAso>
                        <dNTimDI>' . $dNTimDI . '</dNTimDI>
                        <dEstDocAso>' . $dEstDocAso . '</dEstDocAso>
                        <dPExpDocAso>' . $dPExpDocAso . '</dPExpDocAso>
                        <dNumDocAso>' . $dNumDocAso . '</dNumDocAso>
                        <iTipoDocAso>' . $iTipoDocAso . '</iTipoDocAso>
                        <dDTipoDocAso>Factura</dDTipoDocAso>
                        <dFecEmiDI>' . $dFecEmiDI . '</dFecEmiDI>

                    </gCamDEAsoc>';
                }

            }

            $genXML .= '</DE></rDE>';

            //$path = "files/" . $cdc . ".xml";
            if (!Storage::disk('public')->exists('files')) {
                Storage::disk('public')->makeDirectory('files');
            }

            if (!Storage::disk('public')->exists('firmados')) {
                Storage::disk('public')->makeDirectory('firmados');
            }

            //EMPIEZA FIRMA
            $relativePath = 'files/' . $cdc . '.xml';
            $absolutePath = Storage::disk('public')->path($relativePath);
            $modo = "w+";
            if ($fp = fopen($absolutePath, $modo)) {
                fwrite($fp, $genXML);
                fclose($fp);

                // =====================================================
                // 1) Cargar XML base en DOM (SIN Signature)
                // =====================================================
                $doc = new DOMDocument();
                $doc->preserveWhiteSpace = true;
                $doc->formatOutput = false;

                // IMPORTANTE: cargar directamente el string base que generaste
                if (!$doc->loadXML($genXML)) {
                    throw new \Exception("No se pudo cargar el XML base en DOMDocument (genXML inválido).");
                }

                // =====================================================
                // 2) Cargar P12 y obtener clave privada + certificado
                // =====================================================
                $ruta_cert = storage_path('app/public/' . $this->entidad->firma);
                $password = $this->entidad->pass_firma;

                $pkcs12 = file_get_contents($ruta_cert);
                if ($pkcs12 === false) {
                    throw new \Exception("No se pudo leer el P12 en: $ruta_cert");
                }

                $certs = [];
                if (!openssl_pkcs12_read($pkcs12, $certs, $password)) {
                    throw new \Exception("Error de contraseña o P12 inválido: $ruta_cert");
                }

                $priv_key = $certs['pkey']; // clave privada
                $cert     = $certs['cert']; // certificado público

                // =====================================================
                // 3) Preparar Firma XMLDSig sobre el nodo <DE>
                // =====================================================
                $deNode = $doc->getElementsByTagName('DE')->item(0);
                if (!$deNode) {
                    throw new \Exception("No se encontró el nodo <DE> dentro del XML.");
                }

                $key = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type' => 'private']);
                $key->loadKey($priv_key);

                $objDSig = new XMLSecurityDSig('', ['prefix' => 'ds']);
                $objDSig->setCanonicalMethod(XMLSecurityDSig::EXC_C14N);

                $objDSig->addReference(
                    $deNode,
                    XMLSecurityDSig::SHA256,
                    [
                        'http://www.w3.org/2000/09/xmldsig#enveloped-signature',
                        'http://www.w3.org/2001/10/xml-exc-c14n#',
                    ],
                    [
                        'id_name'   => 'Id',
                        'overwrite' => false,
                    ]
                );

                // Firmar
                $objDSig->sign($key);
                $objDSig->add509Cert($cert);

                // Insertar Signature dentro de rDE
                // OJO: insertSignature lo mete como hijo de rDE (documentElement es rDE)
                $objDSig->insertSignature($doc->documentElement);

                // =====================================================
                // 4) Obtener DigestValue (ya calculado)
                // =====================================================
                $digestNode = $objDSig->sigNode->getElementsByTagName('DigestValue')->item(0);
                if (!$digestNode) {
                    throw new \Exception("No se pudo obtener DigestValue de la firma.");
                }
                $digestValue = $digestNode->nodeValue;

                // =====================================================
                // 5) Construir el QR (tu lógica actual)
                // =====================================================
                $rucLink = $this->sifenHelper->rucCliente($RucPOS, $diplomatico, $cPaisRec);

                $preLinkQR = $this->sifenHelper->preLinkQR(
                    $versionQr,
                    $cdc,
                    $this->sifenHelper->hashValores($dFeEmiDE),
                    $rucLink,
                    $this->sifenHelper->formatoTotales($totalOperacion, 8),
                    $this->sifenHelper->formatoTotales(($totalIvaLiq5 + $totalIvaLiq10), 8),
                    $cantItems,
                    $this->sifenHelper->hashValores($digestValue),
                    $_codeCSC
                );

                $cHashQR = $this->sifenHelper->gencHashQR($preLinkQR, $_dCSC);
                $linkQR  = $this->sifenHelper->linkQR($linkQrBase, $preLinkQR, $cHashQR);

                // =====================================================
                // 6) Insertar gCamFuFD DESPUÉS de firmar
                //    y fuera del <DE> (como hijo de <rDE>)
                // =====================================================
                $gCamFuFD = $doc->createElement('gCamFuFD');
                $dCarQR   = $doc->createElement('dCarQR');

                // SUPER IMPORTANTE: createTextNode para evitar "&" mal escapados
                $dCarQR->appendChild($doc->createTextNode($linkQR));
                $gCamFuFD->appendChild($dCarQR);

                // Insertar luego de Signature
                $signatureNode = $doc->getElementsByTagName('Signature')->item(0);
                if ($signatureNode && $signatureNode->parentNode) {
                    if ($signatureNode->nextSibling) {
                        $signatureNode->parentNode->insertBefore($gCamFuFD, $signatureNode->nextSibling);
                    } else {
                        $signatureNode->parentNode->appendChild($gCamFuFD);
                    }
                } else {
                    // fallback: al final de rDE
                    $doc->documentElement->appendChild($gCamFuFD);
                }

                // =====================================================
                // 7) Guardar XML FINAL (una sola vez)
                // =====================================================
                $xml_final = $doc->saveXML();

                if (!Storage::disk('public')->exists('firmados')) {
                    Storage::disk('public')->makeDirectory('firmados');
                }
                if (!Storage::disk('public')->exists('enviado')) {
                    Storage::disk('public')->makeDirectory('enviado');
                }

                Storage::disk('public')->put('firmados/' . $cdc . '.xml', $xml_final);
                Storage::disk('public')->put('enviado/' . $cdc . '.xml', $xml_final);

                $documento_xml = 'enviado/' . $cdc . '.xml';
                $nombreArchivo = $cdc . '.xml';

            } else {
                throw new \Exception("No se pudo crear el archivo XML en la ruta: $absolutePath");
            }
            //TERMINA FIRMA

            $resultado =  [
                'cdc' => $cdc,
                'archivo_xml' => $documento_xml,
                'link_qr' => $linkQR,
                'fecha_firma' => $fechaHoraFirma,
                'nombre_archivo' => $nombreArchivo
            ];

            return [
                'success' => true,
                'message' => 'XML generado correctamente.',
                'data' => $resultado,
            ];


        } catch (\Exception $e) {
            Log::error('Error al generar XML SIFEN', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
            ];
        }

    }

    public function buscar_factura($factura_id)
    {
        $factura = Factura::find($factura_id);
        return $factura;
    }

    public function validaciones(array $json, $iNatTrans)
    {
        $datos = $json;

        // Validar cada campo y retornar inmediatamente si hay error

        if (($datos['remision']['factura'] ?? null) == 0) {
            if (empty($datos['remision']['fechaFactura'])) {
                return "- Se espera Fecha estimada de factura para la Remision  AAAA-MM-DD";
            }
        }

        if (empty($datos['transporte'][0]['tipoTransporte']))  {
            return "- Se espera Tipo de transporte";
        }

        if (empty($datos['transporte'][0]['salida']['direccion'])) {
            return "- Se espera Dirección de salida para Remisión";
        }

        if (empty($datos['transporte'][0]['salida']['numCasa'])) {
            return "- Se espera Número de casa, si no tiene completar con (cero) 0";
        }

        if (empty($datos['transporte'][0]['salida']['departamento'])) {
            return "- Se espera código Departamento en Salida";
        }

        if (empty($datos['transporte'][0]['salida']['distrito'])) {
            return "- Se espera código Distrito en Salida";
        }

        if (empty($datos['transporte'][0]['salida']['ciudad'])) {
            return "- Se espera código Ciudad en Salida";
        }

        if (empty($datos['transporte'][0]['entrega']['departamento'])) {
            return "- Se espera código Departamento en entrega";
        }

        if (empty($datos['transporte'][0]['entrega']['distrito'])) {
            return "- Se espera código Distrito en entrega";
        }

        if (empty($datos['transporte'][0]['entrega']['ciudad'])) {
            return "- Se espera código Ciudad en entrega";
        }

        if (empty($datos['transporte'][0]['vehiculo']['tipo'])) {
            return "- Se espera Tipo de vehículo";
        }

        if (empty($datos['transporte'][0]['vehiculo']['marca'])) {
            return "- Se espera Marca del tipo de vehículo (ej: Toyota)";
        }

        if (empty($datos['transporte'][0]['vehiculo']['documentoTipo'])) {
            return "- Se espera Tipo de documento del transporte";
        }

        if (empty($datos['transporte'][0]['vehiculo']['numeroIden'])) {
            return "- Se espera Número de Identificación o Matrícula para el transporte";
        }

        if (empty($datos['transporte'][0]['transportista']['tipo'])) {
            return "- Se espera Naturaleza del transportista";
        }

        if (empty($datos['transporte'][0]['transportista']['nombreTr'])) {
            return "- Se espera Nombre del transportista";
        }

        if (empty($datos['transporte'][0]['transportista']['numeroTr'])) {
            return "- Se espera Número del transportista";
        }

        if ($iNatTrans == 2 && empty($datos['transporte'][0]['transportista']['tipoDocumentoTr'])) {
            return "- Si la naturaleza del transportista es no contribuyente, se espera Tipo de Documento y Número.";
        }

        if (empty($datos['transporte'][0]['transportista']['nombreCh'])) {
            return "- Se espera Nombre del chofer";
        }

        if (empty($datos['transporte'][0]['transportista']['numeroCh'])) {
            return "- Se espera Número C.I de chofer";
        }

        if ($datos['tipoDocumento'] == 5) {
            if (!isset($datos['cdcAsociado']) && !empty($datos['cdcAsociado'])) {
                return "- Si el documento es Nota de Crédito se espera el cdcAsociado";
            }

        }

        if ($datos['condicionPago'] == 2 && !isset($datos['credito'])) {
            return ' - Para condición de pago Crédito se espera el detalle en Crédito.. ';
        }

        if (isset($datos['credito'])) {
            $credito = $datos['credito'];
            if ($credito['condicionCredito'] == 2) {
                $cantidadCuotas   = $credito['cantidadCuota'];
                $condicionCredito = $credito['condicionCredito'];
                $numeroCuotas     = count($credito['cuotas']);
                if ($numeroCuotas != $cantidadCuotas) {
                    return " - Cantidad enviada de cuotas no coincide con el detalle";
                }
            }
        }

        if (isset($datos['credito'])) {
            $credito = $datos['credito'];
            if ($credito['condicionCredito'] == 1 && $credito['condicionCredito'] == null) {
                return " - Campo descripción que define el plazo no puede estar vacío";
            }

        }

        return null; // si no hay errores
    }

    public function verificar_duplicado($factura_id)
    {
        $existe = Sifen::where('factura_id', $factura_id)
            ->where('sifen_estado', 'APROBADO') // solo bloqueamos si ya está aprobado
            ->exists();

        if ($existe) {
            return ' - Ya envió al Sifen esta factura con estado APROBADO';
        }

        return null;
    }


    function sifenFechaHoraFirma(int $margenSegundos = 10): string
    {
        // Hora Paraguay
        $dt = Carbon::now('America/Asuncion')->subSeconds($margenSegundos);

        // Formato SIFEN: YYYY-MM-DDTHH:mm:ss (sin offset)
        return $dt->format('Y-m-d\TH:i:s');
    }

}

