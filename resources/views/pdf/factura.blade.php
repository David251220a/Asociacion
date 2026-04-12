<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura Electrónica</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 10mm 12mm 10mm;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #222;
            margin: 0;
            padding: 0;
        }

        .page {
            width: 100%;
        }

        .box {
            border: 1px solid #8f8f8f;
            margin-bottom: 8px;
        }

        .box-title {
            background: #e9e9e9;
            text-align: center;
            font-size: 12px;
            padding: 4px 0;
            border-bottom: 1px solid #8f8f8f;
        }

        .p-10 {
            padding: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .no-border td,
        .no-border th {
            border: none;
        }

        .issuer-table td {
            vertical-align: top;
        }

        .logo {
            width: 180px;
            height: auto;
            margin-bottom: 6px;
        }

        .small {
            font-size: 10px;
        }

        .normal {
            font-size: 11px;
        }

        .big {
            font-size: 14px;
            font-weight: bold;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }

        .top {
            vertical-align: top;
        }

        .middle {
            vertical-align: middle;
        }

        .bordered td,
        .bordered th {
            border: 1px solid #8f8f8f;
            padding: 4px 5px;
        }

        .data-block td {
            padding: 2px 4px;
            vertical-align: top;
        }

        .items-table {
            table-layout: fixed;
        }

        .items-table thead {
            display: table-header-group;
        }

        .items-table tfoot {
            display: table-row-group;
        }

        .items-table tr {
            page-break-inside: avoid;
        }

        .items-table th {
            background: #efefef;
            font-size: 10px;
            text-align: center;
            padding: 5px 4px;
        }

        .items-table td {
            font-size: 10px;
            padding: 4px 4px;
            vertical-align: top;
            word-wrap: break-word;
        }

        .totals-table td {
            border: 1px solid #8f8f8f;
            padding: 3px 6px;
            font-size: 10px;
        }

        .footer-box {
            border: 1px solid #8f8f8f;
            padding: 6px 8px;
            font-size: 10px;
        }

        .qr-box {
            width: 180px;
            text-align: center;
            vertical-align: top;
        }

        .qr-box img {
            width: 160px;
            height: 160px;
            margin-top: 2px;
        }

        .cdc-box {
            background: #cdddf0;
            padding: 5px 8px;
            display: inline-block;
            font-size: 12px;
            margin-top: 4px;
        }

        .muted {
            color: #555;
        }

        .check {
            display: inline-block;
            width: 9px;
            height: 9px;
            border: 1px solid #999;
            margin: 0 4px;
            vertical-align: middle;
            background: #d9d9d9;
        }

        .check-empty {
            background: #fff;
        }

        .mb-4 {
            margin-bottom: 4px;
        }

        .mb-6 {
            margin-bottom: 6px;
        }

        .mb-8 {
            margin-bottom: 8px;
        }

        .mt-4 {
            margin-top: 4px;
        }

        .nowrap {
            white-space: nowrap;
        }

        .w-10 { width: 10%; }
        .w-12 { width: 12%; }
        .w-14 { width: 14%; }
        .w-15 { width: 15%; }
        .w-18 { width: 18%; }
        .w-20 { width: 20%; }
        .w-25 { width: 25%; }
        .w-30 { width: 30%; }
        .w-35 { width: 35%; }

        .text-justify {
            text-align: justify;
        }
    </style>
</head>
<body>
    @php
        // Datos estáticos de ejemplo
        $emisor = [
            'ruc' => '2365438-8',
            'timbrado' => '1000032',
            'fecha_inicio' => '01/07/2017',
            'fecha_fin' => '31/07/2018',
            'nombre' => 'Sistema Integrado de Facturación Electrónica Nacional',
            'direccion' => 'Avenida Gonzalez Vidal N° 1434',
            'ciudad' => 'Asunción',
            'telefono' => '021 417 7018',
            'correo' => 'facturacionelectronica@set.gov.py',
            'actividad' => 'Facturación electrónica',
        ];

        $factura = [
            'numero' => '001-001-0000001',
            'fecha_emision' => '28-09-2017 16:22:48',
            'condicion' => 'Contado',
            'moneda' => 'PYG',
            'tipo_cambio' => '',
            'tipo_global' => '',
            'documento_asociado' => 'CDC o preimpreso (opcional)',
            'tipo_documento_asociado' => 'FE o preimpreso (opcional)',
            'ruc_cliente' => '1131421-4',
            'cliente' => 'Mabel Mendoza Salinas',
            'direccion_cliente' => 'Mcal. López y Yegros',
            'telefono_cliente' => '021 123 456',
            'correo_cliente' => 'msalinas@gmail.com',
            'tipo_operacion' => 'Venta de Mercadería',
            'cdc' => '0144 4444 0170 0100 1001 4528 2250 1201 7158 7322 6094',
        ];

        $items = [
            ['cod' => 'INF012', 'descripcion' => 'Disco duro', 'unidad' => 'UNI', 'cantidad' => 1, 'precio' => 110000, 'descuento' => 0, 'exentas' => 0, 'iva5' => 0, 'iva10' => 110000],
            ['cod' => 'INF013', 'descripcion' => 'Pendrive', 'unidad' => 'UNI', 'cantidad' => 1, 'precio' => 80000, 'descuento' => 0, 'exentas' => 0, 'iva5' => 0, 'iva10' => 80000],
            ['cod' => 'INF020', 'descripcion' => 'Mouse inalámbrico', 'unidad' => 'UNI', 'cantidad' => 1, 'precio' => 10000, 'descuento' => 0, 'exentas' => 0, 'iva5' => 0, 'iva10' => 10000],
        ];

        // Para probar varias páginas, puedes duplicar ítems:
        // for ($i = 1; $i <= 40; $i++) {
        //     $items[] = ['cod' => 'COD'.$i, 'descripcion' => 'Producto de prueba número '.$i, 'unidad' => 'UNI', 'cantidad' => 1, 'precio' => 15000, 'descuento' => 0, 'exentas' => 0, 'iva5' => 0, 'iva10' => 15000];
        // }

        $subtotal = collect($items)->sum(function ($item) {
            return ($item['precio'] * $item['cantidad']) - $item['descuento'];
        });

        $totalOperacion = $subtotal;
        $totalGuaranies = $subtotal;
        $iva5Base = collect($items)->sum('iva5');
        $iva10Base = collect($items)->sum('iva10');
        $liquidacion5 = $iva5Base > 0 ? round($iva5Base / 21, 0) : 0;
        $liquidacion10 = $iva10Base > 0 ? round($iva10Base / 11, 0) : 0;
        $totalIva = $liquidacion5 + $liquidacion10;
    @endphp

    <div class="page">

        {{-- CABECERA PRINCIPAL --}}
        <div class="box">
            <div class="box-title">KuDE de Factura Electrónica</div>

            <div class="p-10">
                <table class="no-border issuer-table">
                    <tr>
                        <td style="width: 54%;" class="top">
                            {{-- Reemplaza por tu ruta real --}}
                            <img src="{{ public_path('images/logo-ekuatia.png') }}" class="logo" alt="Logo">

                            <div>{{ $emisor['nombre'] }}</div>
                            <div>{{ $emisor['direccion'] }}</div>
                            <div>Ciudad: {{ $emisor['ciudad'] }}</div>
                            <div>Teléfono: {{ $emisor['telefono'] }}</div>
                            <div>{{ $emisor['correo'] }}</div>
                            <div>Actividad económica: {{ $emisor['actividad'] }}</div>
                        </td>

                        <td style="width: 46%;" class="top">
                            <table class="no-border">
                                <tr>
                                    <td class="bold nowrap">RUC:</td>
                                    <td>{{ $emisor['ruc'] }}</td>
                                </tr>
                                <tr>
                                    <td class="bold nowrap">Timbrado N°:</td>
                                    <td>{{ $emisor['timbrado'] }}</td>
                                </tr>
                                <tr>
                                    <td class="bold nowrap">Fecha de Inicio de Vigencia:</td>
                                    <td>{{ $emisor['fecha_inicio'] }}</td>
                                </tr>
                                <tr>
                                    <td class="bold nowrap">Fecha de Fin de Vigencia:</td>
                                    <td>{{ $emisor['fecha_fin'] }}</td>
                                </tr>
                            </table>

                            <div style="margin-top: 28px;" class="center">
                                <div class="big">FACTURA ELECTRÓNICA</div>
                                <div class="normal">{{ $factura['numero'] }}</div>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- DATOS DE FACTURA / CLIENTE --}}
        <div class="box p-10">
            <table class="no-border data-block">
                <tr>
                    <td style="width: 50%;" class="top">
                        <div><span class="bold">Fecha y hora de emisión:</span> {{ $factura['fecha_emision'] }}</div>
                        <div>
                            <span class="bold">Condición de venta:</span>
                            Contado <span class="check"></span>
                            Crédito <span class="check check-empty"></span>
                        </div>
                        <div><span class="bold">Cuotas:</span></div>
                        <div><span class="bold">Moneda:</span> {{ $factura['moneda'] }} &nbsp;&nbsp;&nbsp; <span class="bold">Tipo de Cambio:</span> {{ $factura['tipo_cambio'] }}</div>
                        <div><span class="bold">Tipo de cambio global o por ítem (opcional)</span></div>
                        <div><span class="bold">Documento asociado</span> {{ $factura['documento_asociado'] }}</div>
                        <div><span class="bold">Tipo de documento asociado:</span> {{ $factura['tipo_documento_asociado'] }}</div>
                    </td>

                    <td style="width: 50%;" class="top">
                        <div><span class="bold">RUC/Documento de Identidad N°:</span> {{ $factura['ruc_cliente'] }}</div>
                        <div><span class="bold">Nombre o Razón Social:</span> {{ $factura['cliente'] }}</div>
                        <div><span class="bold">Dirección:</span> {{ $factura['direccion_cliente'] }}</div>
                        <div><span class="bold">Teléfono:</span> {{ $factura['telefono_cliente'] }}</div>
                        <div><span class="bold">Correo Electrónico:</span> {{ $factura['correo_cliente'] }}</div>
                        <div><span class="bold">Tipo de Operación:</span> {{ $factura['tipo_operacion'] }}</div>
                    </td>
                </tr>
            </table>
        </div>

        {{-- DETALLE DE ÍTEMS --}}
        <table class="items-table bordered">
            <thead>
                <tr>
                    <th style="width: 10%;">Cod</th>
                    <th style="width: 31%;">Descripción</th>
                    <th style="width: 11%;">Unidad de medida</th>
                    <th style="width: 9%;">Cantidad</th>
                    <th style="width: 13%;">Precio Unitario</th>
                    <th style="width: 10%;">Descuento</th>
                    <th style="width: 8%;">Exentas</th>
                    <th style="width: 8%;">5%</th>
                    <th style="width: 10%;">10%</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                    <tr>
                        <td>{{ $item['cod'] }}</td>
                        <td>{{ $item['descripcion'] }}</td>
                        <td class="center">{{ $item['unidad'] }}</td>
                        <td class="center">{{ number_format($item['cantidad'], 0, ',', '.') }}</td>
                        <td class="right">{{ number_format($item['precio'], 0, ',', '.') }}</td>
                        <td class="right">{{ number_format($item['descuento'], 0, ',', '.') }}</td>
                        <td class="right">{{ number_format($item['exentas'], 0, ',', '.') }}</td>
                        <td class="right">{{ number_format($item['iva5'], 0, ',', '.') }}</td>
                        <td class="right">{{ number_format($item['iva10'], 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- TOTALES --}}
        <table class="totals-table" style="margin-top: 0;">
            <tr>
                <td style="width: 80%;" class="bold">SUBTOTAL:</td>
                <td style="width: 20%;" class="right">{{ number_format($subtotal, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="bold">TOTAL DE LA OPERACIÓN:</td>
                <td class="right">{{ number_format($totalOperacion, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="bold">TOTAL EN GUARANÍES</td>
                <td class="right">{{ number_format($totalGuaranies, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>
                    <span class="bold">LIQUIDACIÓN IVA:</span>
                    &nbsp;&nbsp;&nbsp;
                    (5%) {{ number_format($liquidacion5, 0, ',', '.') }}
                    &nbsp;&nbsp;&nbsp;
                    (10%) {{ number_format($liquidacion10, 0, ',', '.') }}
                    &nbsp;&nbsp;&nbsp;
                    <span class="bold">TOTAL IVA:</span>
                </td>
                <td class="right">{{ number_format($totalIva, 0, ',', '.') }}</td>
            </tr>
        </table>

        {{-- QR / CDC / LEYENDA --}}
        <div class="box p-10">
            <table class="no-border">
                <tr>
                    <td class="qr-box top">
                        {{-- Reemplazar por tu QR real --}}
                        <div>
                            <img src="data:image/png;base64,{{ $qrBase64 }}" alt="QR">
                        </div>
                    </td>
                    <td class="top">
                        <div class="mb-4">
                            Consulte la validez de esta Factura Electrónica con el número de CDC impreso abajo en:
                        </div>
                        <div class="mb-4" style="color:#2d6ac8;">
                            https://ekuatia.set.gov.py/consultas/
                        </div>
                        <div class="cdc-box">
                            CDC: {{ $factura['cdc'] }}
                        </div>
                    </td>
                </tr>
            </table>

            <div class="mt-4 bold">
                ESTE DOCUMENTO ES UNA REPRESENTACIÓN GRÁFICA DE UN DOCUMENTO ELECTRÓNICO (XML)
            </div>

            <div class="mt-4 bold">Información de interés del facturador electrónico emisor.</div>
            <div class="mt-4">
                Si su documento electrónico presenta algún error, podrá solicitar la modificación dentro de las 72 horas siguientes de la emisión de este comprobante.
            </div>
        </div>

    </div>
</body>
</html>