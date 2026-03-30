<?php

namespace App\Services;

use App\Models\Entidad;
use App\Models\Factura;
use App\Models\Sifen;
use App\Models\Timbrado;
use App\Models\Secuencia;
use Carbon\Carbon;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use RobRichards\XMLSecLibs\XMLSecurityDSig;
use RobRichards\XMLSecLibs\Utils\XPath;
use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class SifenServices
{

    protected $entidad, $sifenHelper;

    public function __construct()
    {
        $this->entidad = Entidad::find(1);
    }

    public function envioEvento(Sifen $sifen, string $de, int $secuencia, int $tipoEvento)
    {
        try {

            $de  = str_replace('<?xml version="1.0" encoding="UTF-8" standalone="no"?>', '', $de);
            $ruta_cert = storage_path('app/public/' . $this->entidad->firma);
            $password = $this->entidad->pass_firma;
            $xml = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
                        <env:Envelope xmlns:env="http://www.w3.org/2003/05/soap-envelope"
                            xmlns:xsd="http://ekuatia.set.gov.py/sifen/xsd">
                            <env:Header/>
                            <env:Body>
                                <xsd:rEnviEventoDe xmlns:xsd="http://ekuatia.set.gov.py/sifen/xsd">
                                    <xsd:dId>' . $secuencia . '</xsd:dId>
                                    <xsd:dEvReg>' . $de . '</xsd:dEvReg>
                                </xsd:rEnviEventoDe>
                            </env:Body>
                        </env:Envelope>';

            if ($this->entidad->ambiente == 0){
                $url = 'https://sifen-test.set.gov.py/de/ws/eventos/evento.wsdl';
            }else{
                $url = "https://sifen.set.gov.py/de/ws/eventos/evento.wsdl";
            }
            
            //dd($xml);
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/soap+xml'));
            curl_setopt($ch, CURLOPT_SSLCERT, $ruta_cert);
            curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'P12'); //para usar en formato.p12 en caso de .pem quitar
            curl_setopt($ch, CURLOPT_SSLCERTPASSWD, $password);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            // dd($xml);
            // Ejecutar solicitud cURL
            $response = curl_exec($ch);

            if ($response === false) {
                throw new \Exception('ERROR DE CURL - ' . curl_error($ch));
            }

            $xml = simplexml_load_string($response);
            $xml->registerXPathNamespace('env', 'http://www.w3.org/2003/05/soap-envelope');
            $xml->registerXPathNamespace('sifen', 'http://ekuatia.set.gov.py/sifen/xsd');

            $dFecProcNode = $xml->xpath('//sifen:dFecProc');
            $dEstResNode  = $xml->xpath('//sifen:dEstRes');
            $dCodResNode  = $xml->xpath('//sifen:dCodRes');
            $dMsgResNode  = $xml->xpath('//sifen:dMsgRes');
            $dProtAutNode = $xml->xpath('//sifen:dProtAut');
            $dProtAut = $dProtAutNode ? (string)$dProtAutNode[0] : null;

            if (!$dEstResNode || !$dFecProcNode || !$dMsgResNode || !$dCodResNode) {
                throw new \Exception('Nodos esperados no encontrados en respuesta de SIFEN.');
            }

            $dFecProc = (string) $dFecProcNode[0];
            $dEstRes  = (string) $dEstResNode[0];   // 'Aprobado' / 'Rechazado' / etc
            $dCodRes  = (string) $dCodResNode[0];
            $dMsgRes  = (string) $dMsgResNode[0];

            $eventoNombre = match ($tipoEvento) {
                1 => 'NOMINACION',
                2 => 'CANCELACION',
                3 => 'INUTILIZACION',
                default => 'DESCONOCIDO',
            };

            $sifen->update([
                'evento' => $eventoNombre,
                'sifen_evento_codrespuesta' => $dCodRes,
                'sifen_evento_msjrespuesta' => $dMsgRes,   // <-- creá/usa este campo
                'sifen_evento_estado' => $dEstRes,
                'sifen_cod' => $dProtAut
            ]);

            return [
                'status'  => $dEstRes,      // texto
                'ok'      => $dEstRes !== 'Rechazado', // boolean
                'code'    => $dCodRes,
                'mensaje' => $dMsgRes,
                'fecha'   => $dFecProc,
                'raw'     => $response, // opcional: guardar/debug
            ];

        } catch (\Exception $e) {
            return [
                'status'  => 'Rechazado',
                'ok'      => false,
                'code'    => 0,
                'mensaje' => $e->getMessage(),
                'fecha'   => '',
            ];
        }
    }

    public function inutizacion(Sifen $sifen, string $motivo)
    {
        try {

            $this->directorios();

            $datajson = [
                'tipoEvento' => 2,
                'cdc' => $sifen->cdc,
                'motivo' => $motivo
            ];

            $factura = Factura::find($sifen->factura_id);
            $timbrado = Timbrado::find($factura->timbrado_id);
            $esta = $factura->establecimiento->sucursal;
            $secuencia = 400000;
            $cdc = $sifen->cdc;

            if ($datajson['tipoEvento'] == 1) {
                $timbrado = $timbrado->timbrado; //$datos['timbrado']
                $tipoDoc = $factura->tipo_documento_id; //$datos['tipoDoc'];
                $establecimiento = $esta->sucursal; //$datos['establecimiento'];
                $punto = $esta->general; //$datos['punto'];
                // $desde = $datos['desde'];
                // $hasta = $datos['hasta'];
                $desde = 1;
                $hasta = 1;
                $motivo = $motivo;//$datos['motivo'];
                $xmlString = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
                            <gGroupGesEve xmlns="http://ekuatia.set.gov.py/sifen/xsd"
                            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                            xsi:schemaLocation="http://ekuatia.set.gov.py/sifen/xsd siRecepEvento_v150.xsd">
                            <rGesEve xsi:schemaLocation="http://ekuatia.set.gov.py/sifen/xsd siRecepEvento_v150.xsd">
                                <rEve Id="' . $sifen->secuencia . '">
                                    <dFecFirma>' . $sifen->fechaFirma . '</dFecFirma>
                                    <dVerFor>150</dVerFor>
                                    <gGroupTiEvt>
                                        <rGeVeInu>
                                            <dNumTim>' . $timbrado . '</dNumTim>
                                            <dEst>' . $establecimiento . '</dEst>
                                            <dPunExp>' . $punto . '</dPunExp>
                                            <dNumIn>' . $desde . '</dNumIn>
                                            <dNumFin>' . $hasta . '</dNumFin>
                                            <iTiDE>' . $tipoDoc . '</iTiDE>
                                            <mOtEve>' . $motivo . '</mOtEve>
                                        </rGeVeInu>
                                    </gGroupTiEvt>
                                </rEve>
                            </rGesEve>
                        </gGroupGesEve>';

                $xml = $xmlString;
                $relativePath = 'eventos/event_' . $cdc . '_' . $secuencia . '.xml';
                $absolutePath = $this->firmarXML($xml, $relativePath, $secuencia, $cdc);

                $xmlFirmado = file_get_contents($absolutePath);
                return $xmlFirmado;
            }
        } catch (\Exception $e) {
            Log::error('Fallo al generar XML Evento: ' . $e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function nominacion(Sifen $sifen)
    {
        try {
            $factura = Factura::find($sifen->factura_id);
            $persona = $factura->persona;
            $secuencia = 400000;
            $cedula_ad = (empty($persona->ruc) ? $persona->documendo : $persona->ruc);
            $nombre_ad = $persona->nombre .' '. $persona->apellido;

            $cdc = $sifen->cdc;
            $ruc = $cedula_ad;
            $nombre = htmlspecialchars($nombre_ad, ENT_XML1, 'UTF-8');
            // ACA DEBO TENER EN CUENTA SI ES MUNICIPIO O PERSONA O EMPRESA
            // CAMBIAR SI LLEGO A IMPLEMENTAR OTRO LADO
            $iTiOpe = "2"; // B2C
            $iNatRec = (strpos($ruc, '-') !== false) ? 1 : 2;
            // Verificar si el RUC comienza con 800 o 801
            $iTiContRec = null;
            $dNumIDRec  = null;
            if ($iNatRec === 1) {
                $iTiContRec  = (substr($ruc, 0, 3) === '800' || substr($ruc, 0, 3) === '801') ? 2 : 1;
                $rucCompleto = explode('-', $ruc);
                $druc        = $rucCompleto[0];
                $iTiOpe      = '1';

            } else {
                $dNumIDRec = $ruc;
                $iTiOpe    = '2';
            }

            $xmlString = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
                <gGroupGesEve
                    xmlns="http://ekuatia.set.gov.py/sifen/xsd"
                    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://ekuatia.set.gov.py/sifen/xsd siRecepEvento_v150.xsd">
                    <rGesEve xmlns="http://ekuatia.set.gov.py/sifen/xsd">
                        <rEve Id="' . $sifen->secuencia . '">
                            <dFecFirma>' . $sifen->fechaFirma . '</dFecFirma>
                            <dVerFor>150</dVerFor>
                            <gGroupTiEvt>
                                <rGEveNom>
                                    <Id>' . $cdc . '</Id>
                                    <mOtEve>No se puso nombre</mOtEve>
                                    <iNatRec>' . $iNatRec . '</iNatRec>
                                    <iTiOpe>' . $iTiOpe . '</iTiOpe>
                                    <cPaisRec>PRY</cPaisRec>
                                    <dDesPaisRe>Paraguay</dDesPaisRe>';
            if ($iNatRec === 1) {
                $xmlString .= '
                <iTiContRec>' . $iTiContRec . '</iTiContRec>
                <dRucRec>' . trim($druc) . '</dRucRec>
                <dDVRec>' . substr($ruc, -1) . '</dDVRec>';
            } else {
                $xmlString .= '
                <iTipIDRec>1</iTipIDRec>
                <dDTipIDRec>Cédula paraguaya</dDTipIDRec>
                <dNumIDRec>' . $dNumIDRec . '</dNumIDRec>';
            }

            $xmlString .= '
            <dNomRec>' . trim($nombre) . '</dNomRec>
                            </rGEveNom>
                        </gGroupTiEvt>
                    </rEve>
                </rGesEve>
            </gGroupGesEve>';

            $xml = $xmlString;
            $relativePath = 'eventos/event_' . $cdc . '_' . $secuencia . '.xml';
            $absolutePath = $this->firmarXML($xml, $relativePath, $secuencia, $cdc);

            $xmlFirmado = file_get_contents($absolutePath);
            return $xmlFirmado;
        } catch (\Exception $e) {
            Log::error('Fallo al generar XML Evento: ' . $e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function cancelacion(Sifen $sifen, string $motivo)
    {
        try {
            $cdc    = $sifen->cdc;
            $fechaFirma = date("Y-m-d\TH:i:s", strtotime(date("d-m-Y H:i:s")));
            $codSecuencia1 = $this->nextSecuenciaGlobal();
            $xmlString = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
                        <gGroupGesEve xmlns="http://ekuatia.set.gov.py/sifen/xsd"
                        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                        xsi:schemaLocation="http://ekuatia.set.gov.py/sifen/xsd siRecepEvento_v150.xsd">
                        <rGesEve xsi:schemaLocation="http://ekuatia.set.gov.py/sifen/xsd siRecepEvento_v150.xsd">
                            <rEve Id="' . $codSecuencia1 . '">
                                <dFecFirma>' . $fechaFirma . '</dFecFirma>
                                <dVerFor>150</dVerFor>
                                <gGroupTiEvt>
                                    <rGeVeCan>
                                        <Id>' . $cdc . '</Id>
                                        <mOtEve>' . $motivo . '</mOtEve>
                                    </rGeVeCan>
                                </gGroupTiEvt>
                            </rEve>
                        </rGesEve>
                    </gGroupGesEve>';
            $xml = $xmlString;
            $relativePath = 'eventos/event_' . $cdc . '_' . $codSecuencia1 . '.xml';
            $absolutePath = $this->firmarXML($xml, $relativePath, $codSecuencia1, $cdc);
            $xmlFirmado = file_get_contents($absolutePath);
            $sifen->update([
                'sifen_evento_xml' => $relativePath,
                'secuencia_evento' => $codSecuencia1
            ]);

            return $xmlFirmado;

        } catch (\Exception $e) {
            Log::error('Fallo al generar XML Evento: ' . $e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function directorios(){

        if (!Storage::disk('public')->exists('eventos')) {
            Storage::disk('public')->makeDirectory('eventos');
        }

        return true;
    }

    public function firmarXML(string $xmlString, string $relativePath, int $secuencia, string $cdc)
    {

        $xml = $xmlString;
        $doc = new DOMDocument();
        $doc->loadXML($xml, true);
        $ruta_cert = storage_path('app/public/' . $this->entidad->firma);
        $pkcs12 = file_get_contents($ruta_cert);
        $priv_key = null;
        $certs    = array();
        //$password = $p12_pass;
        $password = $this->entidad->pass_firma;
        if (openssl_pkcs12_read($pkcs12, $certs, $password)) {
            $priv_key = $certs['pkey'];
            $cert     = $certs['cert'];
        } else {
            throw new \Exception("Error de contraseña: Verifica que la contraseña de tu clave privada sea correcta." . $ruta_cert);
        }

        $key = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, array('type' => 'private'));
        $key->loadKey($priv_key);

        $objDSig = new XMLSecurityDSig('', array('prefix' => 'ds'));
        //$objDSig = new XMLSecurityDSig();
        $objDSig->setCanonicalMethod(XMLSecurityDSig::EXC_C14N);

        //$objDSig->setCanonicalMethod(RobRichards\XMLSecLibs\XMLSecurityDSig::C14N);
        $rEvenode = $doc->documentElement->getElementsByTagName('rEve')->item(0);
        $objDSig->addReference(
            $rEvenode,
            XMLSecurityDSig::SHA256,
            array(
                'http://www.w3.org/2000/09/xmldsig#enveloped-signature',
                'http://www.w3.org/2001/10/xml-exc-c14n#',
            ),
            array(
                'id_name'   => 'Id',
                'overwrite' => false,

            )
        );

        $objDSig->sign($key);
        $objDSig->add509Cert($cert);

        // Obtener el nodo de firma
        $signatureNode = $objDSig->sigNode;
        $rGesEveNode   = $doc->getElementsByTagName("rGesEve")->item(0);

        // Importar el nodo de firma al documento
        $importedSignatureNode = $doc->importNode($signatureNode, true);

        // Insertar el nodo de firma importado antes del cierre de la etiqueta </rGesEve>
        $rGesEveNode->insertBefore($importedSignatureNode, $rEvenode->nextSibling);
        $xml_firmado = $doc->saveXML();
        $relativePath = 'eventos/event_' . $cdc . '_' . $secuencia . '.xml';
        Storage::disk('public')->put($relativePath, $xml_firmado);
        $absolutePath = Storage::disk('public')->path($relativePath);

        return $absolutePath;
    }

    public function lotear(Sifen $sifen)
    {
        try {

            $xml_content = '<rLoteDE>';
            $cdc = $sifen->cdc;
            $absolutePathFirma = Storage::disk('public')->path($sifen->documento_xml);
            $xml = file_get_contents($absolutePathFirma);

            if (!Storage::disk('public')->exists($sifen->documento_xml)) {
                throw new \Exception('Archivo XML firmado no encontrado.');
            }

            $xml = str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $xml);
            $xml_content .= $xml;
            $xml_content .= '</rLoteDE>';

            if (!Storage::disk('public')->exists('zip_xml')) {
                Storage::disk('public')->makeDirectory('zip_xml');
            }

            if (!Storage::disk('public')->exists('zip_documento')) {
                Storage::disk('public')->makeDirectory('zip_documento');
            }

            $relativePathFirma = 'zip_xml/' . $sifen->secuencia . '_' . $sifen->tipo_doc . '.xml';
            Storage::disk('public')->put($relativePathFirma, $xml_content);
            $relativeZipPath = 'zip_documento/' . $sifen->secuencia . '_' . $sifen->tipo_doc . '.zip';
            $absoluteZipPath = Storage::disk('public')->path($relativeZipPath);
            $zip = new ZipArchive();
            $zip_name = 'zip_documento/' . $sifen->secuencia . '_' . $sifen->tipo_doc . '.zip';
            if ($zip->open($absoluteZipPath, ZipArchive::CREATE) !== true) {
                throw new \Exception("No se pudo crear el archivo ZIP");
            }
            $zip->addFile($absolutePathFirma, basename($relativePathFirma));
            $zip->close();

            return $absoluteZipPath;

        } catch (\Exception $e) {
            Log::error('Fallo al generar XML: ' . $e->getMessage());
            throw new \Exception($e->getMessage());
        }

    }

    public function enviar_zip(Sifen $sifen)
    {
        try {

            $cdc = $sifen->cdc;
            $absolutePathFirma = Storage::disk('public')->path($sifen->documento_xml);
            $xml = file_get_contents($absolutePathFirma);
            $xml = str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $xml);

            if (!Storage::disk('public')->exists($sifen->documento_xml)) {
                throw new \Exception('Archivo XML firmado no encontrado.');
            }
            //$xml_content = '<rLoteDE>';
            $xml_content = '<rLoteDE>';
            $xml_content .= $xml;
            $xml_content .= '</rLoteDE>';

            if (!Storage::disk('public')->exists('zip_xml')) {
                Storage::disk('public')->makeDirectory('zip_xml');
            }

            if (!Storage::disk('public')->exists('zip_documento')) {
                Storage::disk('public')->makeDirectory('zip_documento');
            }

           $relativePathFirma = 'zip_xml/' . $sifen->secuencia . '_' . $sifen->tipo_doc . '.xml';
            Storage::disk('public')->put($relativePathFirma, $xml_content);

            $absoluteLoteXml = Storage::disk('public')->path($relativePathFirma);

            $zip_name = 'zip_documento/' . $sifen->secuencia . '_' . $sifen->tipo_doc . '.zip';
            $absoluteZipPath = Storage::disk('public')->path($zip_name);
            $relativePath = $zip_name; // ← Este es el valor que necesitás
            $zip = new ZipArchive();
            if ($zip->open($absoluteZipPath, ZipArchive::CREATE) !== true) {
                throw new \Exception("No se pudo crear el archivo ZIP");
            }

            $zip->addFile($absoluteLoteXml, basename($relativePathFirma));
            $zip->close();

            $url = config('facturacion.link_api')[($this->entidad->ambiente == 1) ? 'produccion' : 'test'];

            $ruta_cert = storage_path('app/public/' . $this->entidad->firma);
            $password = $this->entidad->pass_firma;
            // dd($url);
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            //curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/soap+xml'));
            curl_setopt($ch, CURLOPT_SSLCERT, $ruta_cert);
            curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'P12'); //para usar en formato.p12 en caso de .pem quitar
            curl_setopt($ch, CURLOPT_SSLCERTPASSWD, $password);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $codSecuencia1 = $sifen->secuencia;
            $relativePath = $relativePath; // debe ser algo como 'enviado/xxxx.xml'
            if (!Storage::disk('public')->exists($relativePath)) {
                throw new \Exception('El archivo no existe: ' . $relativePath);
            }

            $archivo_comprimido = Storage::disk('public')->get($relativePath);
            $archivo_comprimido_base64 = base64_encode($archivo_comprimido);
            $xmlenvio = '
            <env:Envelope xmlns:env="http://www.w3.org/2003/05/soap-envelope">
                <env:Header/>
                <env:Body>
                    <rEnvioLote xmlns="http://ekuatia.set.gov.py/sifen/xsd">
                        <dId>' . $codSecuencia1 . '</dId>
                        <xDE>' . $archivo_comprimido_base64 . '</xDE>
                    </rEnvioLote>
                </env:Body>
            </env:Envelope>';

            curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlenvio);
            $response = curl_exec($ch);
            if ($response === false) {
                throw new \Exception('Error de cURL: ' . curl_error($ch));
            } else {
                $xmlResponse = simplexml_load_string($response);
                $xmls = explode('<?xml', $response);
                foreach ($xmls as $xml) {
                    if (empty(trim($xml))) {
                        continue;
                    }

                    // Formatear XML
                    $newDoc = new DOMDocument();
                    $newDoc->preserveWhiteSpace = false;
                    $newDoc->formatOutput = true;
                    $newDoc->loadXML('<?xml' . $xml);

                    // Imprimir XML formateado en pantalla
                    echo '<pre>' . htmlspecialchars($newDoc->saveXML()) . '</pre>';
                }

                $xmlResponse->registerXPathNamespace('ns', 'http://ekuatia.set.gov.py/sifen/xsd');

                $fecha_proceso = (string) $xmlResponse->xpath('//ns:dFecProc')[0];
                $dProtConsLoteXPath = $xmlResponse->xpath('//ns:dProtConsLote');
                $dProtConsLote = isset($dProtConsLoteXPath[0]) ? (string) $dProtConsLoteXPath[0] : '';
                $fecha_original = $fecha_proceso;
                $fecha_proceso = Carbon::parse($fecha_original)->format('Y-m-d H:i:s');
                $pasa = false;

                if (!empty($dProtConsLote)) {
                    $codigo_res = (string) $xmlResponse->xpath('//ns:dCodRes')[0];
                    $mensaje_res = (string) $xmlResponse->xpath('//ns:dMsgRes')[0];
                    $sifen->update([
                        'sifen_envio_fecha' => $fecha_proceso,
                        'sifen_envio_codrespuesta' => $codigo_res,
                        'sifen_envio_msjrespuesta' => $mensaje_res,
                        'sifen_envio_xml' => $response,
                        'sifen_num_transaccion' => $dProtConsLote,
                        'enviado_sifen' => 'Y',
                        'sifen_estado' => 'ENVIADO'
                    ]);
                    $pasa = true;
                }else {
                    $get1 = function($path) use ($xmlResponse) {
                        $n = $xmlResponse->xpath($path);
                        return isset($n[0]) ? (string)$n[0] : '';
                    };

                    $codigo_res  = $get1('//ns:dCodRes');
                    $mensaje_res = $get1('//ns:dMsgRes');
                    $estado_res  = $get1('//ns:dEstRes');
                    $fecha_fmt = !empty($fecha_proceso)
                    ? \Carbon\Carbon::parse($fecha_proceso)->format('Y-m-d H:i:s')
                    : now()->format('Y-m-d H:i:s');
                    $enviado = in_array($estado_res, ['Aprobado', 'Aprobado con observación']) ? 'Y' : 'N';
                    $pasa = false;
                    $sifen->update([
                        'sifen_envio_fecha'        => $fecha_fmt,
                        'sifen_envio_codrespuesta' => $codigo_res,
                        'sifen_envio_msjrespuesta' => $mensaje_res,
                        'sifen_envio_xml'          => $response,
                        'enviado_sifen'            => $enviado,
                        'sifen_estado'             => strtoupper($estado_res ?: 'RECHAZADO'),
                    ]);
                }
            }

            curl_close($ch);
            // Storage::put("debug/envio_{$codSecuencia1}.xml", $xmlenvio);
            // Storage::put("debug/respuesta_{$codSecuencia1}.xml", $response);
            return [$pasa, "Código: $codigo_res. Mensaje: " . html_entity_decode($mensaje_res)];

        } catch (\Exception $e) {
            Log::error('Fallo al generar XML: ' . $e->getMessage());
            return [false, 'Excepción: ' . $e->getMessage()];
        }
    }

    public function enviar_directo(Sifen $sifen)
    {
        try {
            // 1) Cargar DE firmado
            if (!Storage::disk('public')->exists($sifen->documento_xml)) {
                throw new \Exception('Archivo XML firmado no encontrado.');
            }
            $absolutePathFirma = Storage::disk('public')->path($sifen->documento_xml);
            $xmlFirmado = file_get_contents($absolutePathFirma); // <rDE ...>...</rDE> firmado (no tocar)

            // 2) SOAP siRecepDE (un solo DE)
            //$codSecuencia1 = $sifen->secuencia + 1; // tu correlativo numérico (1–15 dígitos)}
            $codSecuencia1 = $this->nextSecuenciaGlobal();
            $xmlFirmado = str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $xmlFirmado);
            $xmlFirmado = trim($xmlFirmado);

            $xmlenvio = '
            <soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope">
            <soap:Header/>
            <soap:Body>
                <rEnviDe xmlns="http://ekuatia.set.gov.py/sifen/xsd">
                <dId>'.$codSecuencia1.'</dId>
                <xDE>'.$xmlFirmado.'</xDE>
                </rEnviDe>
            </soap:Body>
            </soap:Envelope>';

            // 3) cURL
            $url = config('facturacion.link_api_directo')[($this->entidad->ambiente == 1) ? 'produccion' : 'test']; // siRecepDE
            //$url = 'https://sifen-test.set.gov.py/de/ws/sync/recibe.wsdl';
            $ruta_cert = storage_path('app/public/' . $this->entidad->firma);
            //$password = 'LqO#9j0E';
            $password = $this->entidad->pass_firma;
            //dd($xmlenvio, $codSecuencia1);
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlenvio);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/soap+xml']);
            curl_setopt($ch, CURLOPT_SSLCERT, $ruta_cert);
            curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'P12');
            curl_setopt($ch, CURLOPT_SSLCERTPASSWD, $password);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($ch);
            if ($response === false) {
                throw new \Exception('Error de cURL: ' . curl_error($ch));
            }
            curl_close($ch);

            // 4) Guardar debug
            // Storage::put("debug/envio_directo_{$codSecuencia1}.xml", $xmlenvio);
            // Storage::put("debug/respuesta_directo_{$codSecuencia1}.xml", $response);

            // 5) Parseo robusto de la respuesta siRecepDE
            $xmlResponse = @simplexml_load_string($response);
            if ($xmlResponse === false) {
                // Guarda igualmente la respuesta cruda y marca rechazado
                $sifen->update([
                    'sifen_envio_fecha'        => now(),
                    'sifen_envio_codrespuesta' => null,
                    'sifen_envio_msjrespuesta' => 'Respuesta no válida (no XML).',
                    'sifen_envio_xml'          => $response,
                    'enviado_sifen'            => 'N',
                    'sifen_estado'             => 'RECHAZADO',
                    'secuencia'             => $codSecuencia1,
                    'sifen_idprod' => 0,
                ]);
                return [false, 'Respuesta no válida (no XML).'];
            }

            $xmlResponse->registerXPathNamespace('ns', 'http://ekuatia.set.gov.py/sifen/xsd');

            $get = function ($path) use ($xmlResponse) {
                $n = $xmlResponse->xpath($path);
                return isset($n[0]) ? (string)$n[0] : '';
            };

            // Campos típicos de rRetEnviDe/rProtDe
            $estado    = $get('//ns:dEstRes');                // Aprobado / Aprobado con observación / Rechazado
            $cod       = $get('//ns:dCodRes');                // p.ej. 0160, etc.
            $mensaje   = $get('//ns:dMsgRes');                // texto mensaje
            $fecProc   = $get('//ns:dFecProc');               // fecha proceso
            $cdcResp   = $get('//ns:rProtDe/ns:id');          // CDC devuelto (si viene)
            $idProtAut = $get('//ns:dProtAut');
            $fecha_fmt = !empty($fecProc) ? \Carbon\Carbon::parse($fecProc)->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s');

            $enviado = in_array($estado, ['Aprobado', 'Aprobado con observación']) ? 'Y' : 'N';

            // 6) Actualización del modelo
            $sifen->update([
                'sifen_envio_fecha'        => $fecha_fmt,
                'sifen_envio_codrespuesta' => $cod,
                'sifen_envio_msjrespuesta' => $mensaje,
                'sifen_envio_xml'          => $response,
                'enviado_sifen'            => $enviado,
                'sifen_estado'             => strtoupper($estado ?: 'RECHAZADO'),
                'cdc'                      => $cdcResp ?: $sifen->cdc, // mantener CDC si no viene
                'secuencia'             => $codSecuencia1,
                'sifen_idprod' => $idProtAut ?: $sifen->sifen_idprod,
            ]);

            // 7) Retorno amigable
            $ok = ($enviado === 'Y');
            $texto = "Estado: ".($estado ?: 'RECHAZADO')." | Código: ".($cod ?: '—')." | Mensaje: ".($mensaje ?: '—');

            return [
                $ok,
                $texto,
                'estado'  => $estado ?: 'RECHAZADO',
                'codigo'  => $cod,
                'mensaje' => $mensaje,
                'cdc'     => $cdcResp ?: $sifen->cdc,
                'fecha'   => $fecha_fmt,
            ];

        } catch (\Exception $e) {
            // Error general
            return [false, 'Excepción: ' . $e->getMessage()];
        }
    }

    public function consultar(Sifen $sifen)
    {
        $lotenum = $sifen->sifen_idprod;
        $codSecuencia = $sifen->secuencia;
        $num = $lotenum;

        $ruta_cert = storage_path('app/public/' . $this->entidad->firma);
        $password = $this->entidad->pass_firma;

        $xml2 = '<env:Envelope xmlns:env="http://www.w3.org/2003/05/soap-envelope">
        <env:Header/>
        <env:Body>
            <rEnviConsLoteDe xmlns="http://ekuatia.set.gov.py/sifen/xsd">
                <dId>' . $codSecuencia . '</dId>
                <dProtConsLote>' . $num . '</dProtConsLote>
            </rEnviConsLoteDe>
        </env:Body>
        </env:Envelope>';

        $url = config('facturacion.link_consulta')[($this->entidad->ambiente == 1) ? 'produccion' : 'test'];
        //dd($xml2, $url);
        $ch = curl_init($url);
        // Establecer opciones de solicitud cURL
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml2);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/soap+xml'));
        curl_setopt($ch, CURLOPT_SSLCERT, $ruta_cert);
        curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'P12'); //para usar en formato.p12 en caso de .pem quitar
        curl_setopt($ch, CURLOPT_SSLCERTPASSWD, $password);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        if ($response === false) {
            echo 'Error de cURL: ' . curl_error($ch);
        } else {
            $doc = new DOMDocument();
            $doc->loadXML($response);
            $xpath = new DOMXPath($doc);
            dd($response);
            // Registrar espacio de nombres
            $xmlRes = simplexml_load_string($response);
            $xmlRes->registerXPathNamespace('ns', 'http://ekuatia.set.gov.py/sifen/xsd');

            // Acceder a los valores de los elementos y guardarlos en variables
            $fecha_proceso = (string) $xmlRes->xpath('//ns:dFecProc')[0];
            $codigo_res    = (string) $xmlRes->xpath('//ns:dCodResLot')[0];
            $mensaje_res   = (string) $xmlRes->xpath('//ns:dMsgResLot')[0];

            $xmls = explode('<?xml', $response);

            foreach ($xmls as $xml) {
                if (empty(trim($xml))) {
                    continue;
                }

                // Formatear XML
                $newDoc = new DOMDocument();
                $newDoc->preserveWhiteSpace = false;
                $newDoc->formatOutput       = true;
                $newDoc->loadXML('<?xml' . $xml);

                // Imprimir XML formateado en pantalla
                echo '<pre>' . htmlspecialchars($newDoc->saveXML()) . '</pre>';
            }
        
            $xpath->registerNamespace('ns2', 'http://ekuatia.set.gov.py/sifen/xsd');
            $gResProcLoteList = $xpath->query('//ns2:gResProcLote');
            
            foreach ($gResProcLoteList as $gResProcLote) {
                // Extraer el id y dMsgRes
                $id = $xpath->evaluate('string(./ns2:id)', $gResProcLote); //cdc

                $dEstRes  = $xpath->evaluate('string(./ns2:dEstRes)', $gResProcLote); // estado
                $dCodRes  = $xpath->evaluate('string(./ns2:gResProc/ns2:dCodRes)', $gResProcLote); //dCodRes
                $dMsgRes  = $xpath->evaluate('string(./ns2:gResProc/ns2:dMsgRes)', $gResProcLote); //mensaje
                $dProtAut = $xpath->evaluate('string(./ns2:dProtAut)', $gResProcLote); //numero transaccion sifen
                $dMsgRes  = str_replace("'", "\"", $dMsgRes);
                if (empty($dProtAut)) {
                    $dProtAut = '0';
                }

                // $sifen->update([
                //     // 'sifen_num_transaccion' => $dProtAut,
                //     'sifen_cod' => $dCodRes,
                //     'sifen_estado' => $dEstRes,
                //     'sifen_mensaje' => $dMsgRes
                // ]);

            }
        }

        curl_close($ch);
        return [
            'fecha'   => $fecha_proceso,
            'dProtAut'  => $dProtAut,
            'codigo'  => $dCodRes,
            'mensaje' => $dMsgRes,
            'mensaje_res' => $mensaje_res,
            // 'raw'     => $response,
        ];
    }

    public function consultar_cdc(Sifen $sifen)
    {

        try {

            $ruta_cert = storage_path('app/public/' . $this->entidad->firma);
            $password = $this->entidad->pass_firma;
            $url = config('facturacion.link_consulta_cdc')[($this->entidad->ambiente == 1) ? 'produccion' : 'test'];

            $cdc = $sifen->cdc;

            $xml = <<<XML
            <?xml version="1.0" encoding="UTF-8"?>
            <env:Envelope xmlns:env="http://www.w3.org/2003/05/soap-envelope">
            <env:Header/>
            <env:Body>
                <ns1:rEnviConsDe xmlns:ns1="http://ekuatia.set.gov.py/sifen/xsd">
                <ns1:IdConsDe>$cdc</ns1:IdConsDe>
                </ns1:rEnviConsDe>
            </env:Body>
            </env:Envelope>
            XML;
            //dd($xml);
            //dd($url);
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/soap+xml']);
            curl_setopt($ch, CURLOPT_SSLCERT, $ruta_cert);
            curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'P12');
            curl_setopt($ch, CURLOPT_SSLCERTPASSWD, $password);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($ch);
            curl_close($ch);

            if ($response === false) {
                throw new \Exception("Error al consultar documento por CDC");
            }

            // Parsear la respuesta

            $xmlResponse = simplexml_load_string($response);
            $xmlResponse->registerXPathNamespace('ns', 'http://ekuatia.set.gov.py/sifen/xsd');

            $fecha   = (string) ($xmlResponse->xpath('//ns:dFecProc')[0] ?? '');
            $estado  = (string) ($xmlResponse->xpath('//ns:dEstRes')[0] ?? '');
            $codigo  = (string) ($xmlResponse->xpath('//ns:gResProc/ns:dCodRes')[0] ?? '');
            $mensaje = (string) ($xmlResponse->xpath('//ns:gResProc/ns:dMsgRes')[0] ?? '');

            return [
                'fecha'   => $fecha,
                'estado'  => $estado,
                'codigo'  => $codigo,
                'mensaje' => html_entity_decode($mensaje),
                'raw'     => $response,
            ];
        } catch (\Exception $e) {
            Log::error('Fallo al generar XML: ' . $e->getMessage());
            throw new \Exception($e->getMessage());
        }

    }

    public function consultar_cdc_sin_modelo($sifen)
    {

        try {

            $ruta_cert = storage_path('app/public/' . $this->entidad->firma);
            $password = $this->entidad->pass_firma;
            $url = config('facturacion.link_consulta_cdc')[($this->entidad->ambiente == 1) ? 'produccion' : 'test'];
            // $url = 'https://sifen.set.gov.py/de/ws/consultas/consulta.wsdl';

            $cdc = $sifen;

            if (!file_exists($ruta_cert)) {
                throw new \Exception("El archivo de certificado no existe: " . $ruta_cert);
            }
            //dd($this->entidad->ambiente, $url, $ruta_cert);
            //dd($url);
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/soap+xml']);
            curl_setopt($ch, CURLOPT_SSLCERT, $ruta_cert);
            curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'P12');
            curl_setopt($ch, CURLOPT_SSLCERTPASSWD, $password);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $xml = '<?xml version="1.0" encoding="UTF-8"?>
                <env:Envelope xmlns:env="http://www.w3.org/2003/05/soap-envelope">
                    <env:Header/>
                    <env:Body>
                        <rEnviConsDeRequest xmlns="http://ekuatia.set.gov.py/sifen/xsd">
                            <dId>100</dId>
                            <dCDC>' . $cdc . '</dCDC>
                        </rEnviConsDeRequest>
                    </env:Body>
                </env:Envelope>';
            curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);

            $response = curl_exec($ch);
            curl_close($ch);

            if ($response === false) {
                throw new \Exception("Error al consultar documento por CDC");
            }

            // Parsear la respuesta

            $xmlResponse = simplexml_load_string($response);
            $xmlResponse->registerXPathNamespace('ns', 'http://ekuatia.set.gov.py/sifen/xsd');

            $codigo   = (string) ($xmlResponse->xpath('//ns:dCodRes')[0] ?? '');
            $estado   = (string) ($xmlResponse->xpath('//ns:dEstRes')[0] ?? '');
            $mensaje  = (string) ($xmlResponse->xpath('//ns:dMsgRes')[0] ?? '');
            $fecha    = (string) ($xmlResponse->xpath('//ns:dFecProc')[0] ?? '');
            $xContenDE = (string) ($xmlResponse->xpath('//ns:xContenDE')[0] ?? '');

            $dProtAut = 0;
            if (preg_match('/<dProtAut>(.*?)<\/dProtAut>/', html_entity_decode($xContenDE), $m)) {
                $dProtAut = $m[1];
            }

            $estadoDE = '';
            if (preg_match('/<dEstRes>(.*?)<\/dEstRes>/', $xContenDE, $m)) {
                $estadoDE = $m[1];
            }

            $aprobado = !empty($dProtAut);

            return [
                'aprobado'     => $aprobado,
                'id'           => $cdc,
                'codigo'       => $codigo,
                'estado_sifen' => $estado,
                'mensaje'      => html_entity_decode($mensaje),
                'idprod'       => $dProtAut,
                'fecha'        => $fecha,
                'estadoDE' => $estadoDE
            ];

        } catch (\Exception $e) {
            Log::error('Fallo al generar XML: ' . $e->getMessage());
            throw new \Exception($e->getMessage());
        }

    }

    private function nextSecuenciaGlobal(): int
    {
        return DB::transaction(function () {
            $seq = Secuencia::whereKey(1)->lockForUpdate()->firstOrFail();
            $seq->secuencia = (int) $seq->secuencia + 1;
            $seq->save();

            return (int) $seq->secuencia;
        });
    }

}
