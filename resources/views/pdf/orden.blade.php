<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Orden de Pago</title>

    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 10mm 12mm 10mm;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #000;
            margin: 0;
        }

        .box {
            border: 1px solid #8f8f8f;
            margin-bottom: 8px;
        }

        .box-title {
            background: #e9e9e9;
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            padding: 5px;
            border-bottom: 1px solid #8f8f8f;
        }

        .p-10 {
            padding: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .no-border td {
            border: none;
        }

        .bordered th,
        .bordered td {
            border: 1px solid #8f8f8f;
            padding: 5px;
        }

        th {
            background: #efefef;
            text-align: center;
            font-weight: bold;
        }

        .logo {
            width: 160px;
            height: auto;
        }

        .center { text-align: center; }
        .right { text-align: right; }
        .bold { font-weight: bold; }
        .top { vertical-align: top; }

        .title {
            font-size: 18px;
            font-weight: bold;
        }

        .subtitle {
            font-size: 13px;
            font-weight: bold;
        }

        .small {
            font-size: 10px;
        }

        .firma {
            margin-top: 45px;
        }

        .firma td {
            text-align: center;
            padding-top: 25px;
        }

        .linea {
            border-top: 1px solid #000;
            width: 180px;
            margin: auto;
        }
    </style>
</head>

<body>

@php
    $numeroOrden = str_pad($data->numero, 7, '0', STR_PAD_LEFT) . '/' . $data->anio;

    $estado = 'PENDIENTE';
    if ($data->estado_pago == 1) {
        $estado = 'PAGADO';
    } elseif ($data->estado_pago == 2) {
        $estado = 'ANULADO';
    }

    $totalDetalle = $data->detalles->sum('subtotal');
    $totalPagado  = $data->pagos->sum('monto');
@endphp

<div class="page">

    {{-- CABECERA --}}
    <div class="box">
        <div class="p-10">
            <table class="no-border">
                <tr>
                    <td style="width: 35%;" class="top">
                        <img src="{{ public_path('storage/iconos/logo.jpg') }}" class="logo">
                    </td>

                    <td style="width: 35%;" class="center top">
                        <div class="title">ORDEN DE PAGO</div>
                        <div class="subtitle">N° {{ $numeroOrden }}</div>
                    </td>

                    <td style="width: 30%;" class="top">
                        <table class="no-border">
                            <tr>
                                <td class="bold">Fecha:</td>
                                <td>{{ \Carbon\Carbon::parse($data->fecha)->format('d/m/Y') }}</td>
                            </tr>
                            <tr>
                                <td class="bold">Estado:</td>
                                <td>{{ $estado }}</td>
                            </tr>
                            <tr>
                                <td class="bold">Total:</td>
                                <td>{{ number_format($data->total, 0, ',', '.') }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    {{-- DATOS DEL BENEFICIARIO --}}
    <div class="box">
        <div class="box-title">Datos del Beneficiario</div>

        <div class="p-10">
            <table class="no-border">
                <tr>
                    <td style="width: 25%;"><span class="bold">Documento:</span></td>
                    <td style="width: 25%;">{{ optional($data->persona)->documento }}</td>

                    <td style="width: 20%;"><span class="bold">RUC:</span></td>
                    <td style="width: 30%;">{{ optional($data->persona)->ruc }}</td>
                </tr>

                <tr>
                    <td><span class="bold">Beneficiario:</span></td>
                    <td colspan="3">
                        {{ optional($data->persona)->nombre }} {{ optional($data->persona)->apellido }}
                    </td>
                </tr>

                <tr>
                    <td><span class="bold">Tipo Egreso:</span></td>
                    <td colspan="3">{{ optional($data->tipo_egreso)->descripcion }}</td>
                </tr>

                <tr>
                    <td><span class="bold">Concepto:</span></td>
                    <td colspan="3">{{ $data->concepto }}</td>
                </tr>

                <tr>
                    <td><span class="bold">Observación:</span></td>
                    <td colspan="3">{{ $data->descripcion }}</td>
                </tr>
            </table>
        </div>
    </div>

    {{-- DETALLE --}}
    <table class="bordered">
        <thead>
            <tr>
                <th style="width: 8%;">N°</th>
                <th style="width: 48%;">Descripción</th>
                <th style="width: 12%;">Cantidad</th>
                <th style="width: 16%;">Precio</th>
                <th style="width: 16%;">Subtotal</th>
            </tr>
        </thead>

        <tbody>
            @foreach($data->detalles as $index => $item)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td>{{ $item->descripcion }}</td>
                    <td class="right">{{ number_format($item->cantidad, 0, ',', '.') }}</td>
                    <td class="right">{{ number_format($item->precio, 0, ',', '.') }}</td>
                    <td class="right">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach

            <tr>
                <td colspan="4" class="right bold">TOTAL DETALLE</td>
                <td class="right bold">{{ number_format($totalDetalle, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    {{-- FORMA DE PAGO --}}
    <div class="box" style="margin-top: 8px;">
        <div class="box-title">Forma de Pago</div>

        <div class="p-10">
            <table class="bordered">
                <thead>
                    <tr>
                        <th style="width: 15%;">Fecha</th>
                        <th style="width: 22%;">Forma</th>
                        <th style="width: 25%;">Banco</th>
                        <th style="width: 20%;">Comprobante</th>
                        <th style="width: 18%;">Monto</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($data->pagos as $item)
                        <tr>
                            <td class="center">
                                {{ \Carbon\Carbon::parse($item->fecha_pago)->format('d/m/Y') }}
                            </td>
                            <td>{{ optional($item->forma)->descripcion }}</td>
                            <td>{{ optional($item->banco)->descripcion }}</td>
                            <td>{{ $item->numero_comprobante }}</td>
                            <td class="right">{{ number_format($item->monto, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach

                    <tr>
                        <td colspan="4" class="right bold">TOTAL PAGADO</td>
                        <td class="right bold">{{ number_format($totalPagado, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- FIRMAS --}}
    <table class="firma">
        <tr>
            <td style="width: 50%;">
                <div class="linea"></div>
                <div>Preparado por</div>
            </td>

            <td style="width: 50%;">
                <div class="linea"></div>
                <div>Recibí conforme</div>
            </td>
        </tr>
    </table>

</div>

</body>
</html>
