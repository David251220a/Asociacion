<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Planilla de Aportes</title>

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

        .p-10 { padding: 10px; }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .no-border td {
            border: none;
            padding: 3px;
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
    </style>
</head>

<body>

@php
    $numeroPlanilla = str_pad($data->planilla_numero, 5, '0', STR_PAD_LEFT) . '/' . $data->planilla_anio;

    $estadoPago = $data->pagado == 1 ? 'PAGADO' : 'PENDIENTE';

    $totalEsperado = $data->planillaDetalle->sum('monto_esperado');
    $totalPagado   = $data->planillaDetalle->sum('pagado');
    $totalSaldo    = $data->planillaDetalle->sum('saldo');
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
                        <div class="title">PLANILLA DE APORTES</div>
                        <div class="subtitle">N° {{ $numeroPlanilla }}</div>
                    </td>

                    <td style="width: 30%;" class="top">
                        <table class="no-border">
                            <tr>
                                <td class="bold">Fecha:</td>
                                <td>{{ \Carbon\Carbon::parse($data->fecha)->format('d/m/Y') }}</td>
                            </tr>
                            <tr>
                                <td class="bold">Mes/Año:</td>
                                <td>{{ str_pad($data->mes, 2, '0', STR_PAD_LEFT) }}/{{ $data->anio }}</td>
                            </tr>
                            <tr>
                                <td class="bold">Estado:</td>
                                <td>{{ $estadoPago }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    {{-- DATOS --}}
    <div class="box">
        <div class="box-title">Datos de la Planilla</div>

        <div class="p-10">
            <table class="no-border">
                <tr>
                    <td style="width: 20%;" class="bold">Institución:</td>
                    <td style="width: 40%;">{{ $data->institucion->descripcion }}</td>
                    <td style="width: 20%;" class="bold">Tipo Asociado:</td>
                    <td style="width: 20%;">{{ $data->tipoAsociado->descripcion }}</td>
                </tr>

                <tr>
                    <td class="bold">Cantidad:</td>
                    <td>{{ number_format($data->cantidad, 0, ',', '.') }}</td>

                    <td class="bold">Total:</td>
                    <td>{{ number_format($data->total, 0, ',', '.') }}</td>
                </tr>

                <tr>
                    <td class="bold">Monto Pagado:</td>
                    <td>{{ number_format($data->monto_pagado, 0, ',', '.') }}</td>

                    <td class="bold">Fecha Pago:</td>
                    <td>
                        @if($data->fecha_pagado)
                            {{ \Carbon\Carbon::parse($data->fecha_pagado)->format('d/m/Y') }}
                        @else
                            -
                        @endif
                    </td>
                </tr>
            </table>
        </div>
    </div>

    {{-- DETALLE --}}
    <table class="bordered">
        <thead>
            <tr>
                <th style="width: 6%;">N°</th>
                <th style="width: 14%;">Documento</th>
                <th style="width: 34%;">Asociado</th>
                <th style="width: 15%;">Esperado</th>
                <th style="width: 15%;">Pagado</th>
                <th style="width: 16%;">Saldo</th>
            </tr>
        </thead>

        <tbody>
            @foreach($data->planillaDetalle as $index => $item)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td class="center">{{ $item->asociado->persona->documento }}</td>
                    <td>
                        {{ $item->asociado->persona->nombre }}
                        {{ $item->asociado->persona->apellido }}
                    </td>
                    <td class="right">{{ number_format($item->monto_esperado, 0, ',', '.') }}</td>
                    <td class="right">{{ number_format($item->pagado, 0, ',', '.') }}</td>
                    <td class="right">{{ number_format($item->saldo, 0, ',', '.') }}</td>
                </tr>
            @endforeach

            <tr>
                <td colspan="3" class="right bold">TOTALES</td>
                <td class="right bold">{{ number_format($totalEsperado, 0, ',', '.') }}</td>
                <td class="right bold">{{ number_format($totalPagado, 0, ',', '.') }}</td>
                <td class="right bold">{{ number_format($totalSaldo, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

</div>

</body>
</html>
