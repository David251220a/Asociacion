<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <title>
        Planilla de aportes y préstamos
    </title>

    <style>
        @page {
            size: A4 portrait;
            margin: 10mm 8mm 12mm 8mm;
        }

        body {
            margin: 0;
            color: #000000;
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
        }

        .box {
            margin-bottom: 7px;
            border: 1px solid #8f8f8f;
        }

        .box-title {
            padding: 4px;
            border-bottom: 1px solid #8f8f8f;
            background-color: #e9e9e9;
            font-size: 11px;
            font-weight: bold;
            text-align: center;
        }

        .p-8 {
            padding: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .no-border td {
            padding: 3px;
            border: none;
        }

        .bordered {
            table-layout: fixed;
        }

        .bordered thead {
            display: table-header-group;
        }

        .bordered tr {
            page-break-inside: avoid;
        }

        .bordered th,
        .bordered td {
            padding: 4px 3px;
            border: 1px solid #8f8f8f;
        }

        .bordered th {
            background-color: #e9e9e9;
            font-size: 7px;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
        }

        .bordered td {
            font-size: 7.5px;
            vertical-align: middle;
        }

        .logo {
            width: 150px;
            height: auto;
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

        .title {
            font-size: 15px;
            font-weight: bold;
        }

        .subtitle {
            margin-top: 3px;
            font-size: 11px;
            font-weight: bold;
        }

        .fila-total td {
            background-color: #e9e9e9;
            font-weight: bold;
        }

        .total-recuperado {
            color: #146c43;
            font-weight: bold;
        }

        .resumen {
            width: 100%;
            border-collapse: collapse;
        }

        .resumen td {
            width: 33.33%;
            padding: 6px;
            border: 1px solid #8f8f8f;
            text-align: center;
        }

        .resumen-etiqueta {
            margin-bottom: 3px;
            color: #555555;
            font-size: 7px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .resumen-valor {
            font-size: 11px;
            font-weight: bold;
        }

        .saldo {
            color: #a00000;
        }

        .pie {
            position: fixed;
            right: 0;
            bottom: -20px;
            left: 0;
            padding-top: 3px;
            border-top: 1px solid #aaaaaa;
            color: #555555;
            font-size: 6px;
            text-align: right;
        }
    </style>
</head>

<body>

@php
    $numeroPlanilla =
        str_pad(
            $data->planilla_numero,
            5,
            '0',
            STR_PAD_LEFT
        )
        . '/'
        . $data->planilla_anio;

    $estadoPago = (int) $data->pagado === 1
        ? 'PAGADO'
        : 'PENDIENTE';
@endphp

<div class="page">

    {{-- CABECERA --}}
    <div class="box">
        <div class="p-8">
            <table class="no-border">
                <tr>
                    <td
                        style="width: 32%;"
                        class="top"
                    >
                        <img
                            src="{{
                                public_path(
                                    'storage/iconos/logo.jpg'
                                )
                            }}"
                            class="logo"
                            alt="Logo"
                        >
                    </td>

                    <td
                        style="width: 40%;"
                        class="center top"
                    >
                        <div class="title">
                            PLANILLA DE APORTES Y PRÉSTAMOS
                        </div>

                        <div class="subtitle">
                            N.º {{ $numeroPlanilla }}
                        </div>
                    </td>

                    <td
                        style="width: 28%;"
                        class="top"
                    >
                        <table class="no-border">
                            <tr>
                                <td class="bold">
                                    Fecha:
                                </td>

                                <td>
                                    {{
                                        \Carbon\Carbon::parse(
                                            $data->fecha
                                        )->format('d/m/Y')
                                    }}
                                </td>
                            </tr>

                            <tr>
                                <td class="bold">
                                    Periodo:
                                </td>

                                <td>
                                    {{
                                        str_pad(
                                            $data->mes,
                                            2,
                                            '0',
                                            STR_PAD_LEFT
                                        )
                                    }}/{{ $data->anio }}
                                </td>
                            </tr>

                            <tr>
                                <td class="bold">
                                    Estado:
                                </td>

                                <td>
                                    {{ $estadoPago }}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    {{-- DATOS DE LA PLANILLA --}}
    <div class="box">
        <div class="box-title">
            Datos de la planilla
        </div>

        <div class="p-8">
            <table class="no-border">
                <tr>
                    <td
                        style="width: 18%;"
                        class="bold"
                    >
                        Institución:
                    </td>

                    <td style="width: 42%;">
                        {{
                            optional(
                                $data->institucion
                            )->descripcion
                            ?? '-'
                        }}
                    </td>

                    <td
                        style="width: 18%;"
                        class="bold"
                    >
                        Tipo asociado:
                    </td>

                    <td style="width: 22%;">
                        {{
                            optional(
                                $data->tipoAsociado
                            )->descripcion
                            ?? '-'
                        }}
                    </td>
                </tr>

                <tr>
                    <td class="bold">
                        Cantidad:
                    </td>

                    <td>
                        {{
                            number_format(
                                $data->planillaDetalle->count(),
                                0,
                                ',',
                                '.'
                            )
                        }}
                    </td>

                    <td class="bold">
                        Fecha de pago:
                    </td>

                    <td>
                        @if ($data->fecha_pagado)
                            {{
                                \Carbon\Carbon::parse(
                                    $data->fecha_pagado
                                )->format('d/m/Y')
                            }}
                        @else
                            -
                        @endif
                    </td>
                </tr>
            </table>
        </div>
    </div>

    {{-- RESUMEN --}}
    <table class="resumen">
        <tr>
            <td>
                <div class="resumen-etiqueta">
                    Total aportes
                </div>

                <div class="resumen-valor">
                    {{
                        number_format(
                            $totales['aporte'],
                            0,
                            ',',
                            '.'
                        )
                    }}
                </div>
            </td>

            <td>
                <div class="resumen-etiqueta">
                    Total préstamos
                </div>

                <div class="resumen-valor">
                    {{
                        number_format(
                            $totales['prestamo'],
                            0,
                            ',',
                            '.'
                        )
                    }}
                </div>
            </td>

            <td>
                <div class="resumen-etiqueta">
                    Total esperado
                </div>

                <div class="resumen-valor">
                    {{
                        number_format(
                            $totales['esperado'],
                            0,
                            ',',
                            '.'
                        )
                    }}
                </div>
            </td>
        </tr>

        <tr>
            <td colspan="2">
                <div class="resumen-etiqueta">
                    Total recuperado
                </div>

                <div class="resumen-valor total-recuperado">
                    {{
                        number_format(
                            $totales['recuperado'],
                            0,
                            ',',
                            '.'
                        )
                    }}
                </div>
            </td>

            <td>
                <div class="resumen-etiqueta">
                    Saldo pendiente
                </div>

                <div class="resumen-valor saldo">
                    {{
                        number_format(
                            $totales['saldo'],
                            0,
                            ',',
                            '.'
                        )
                    }}
                </div>
            </td>
        </tr>
    </table>

    <br>

    {{-- DETALLE --}}
    <table class="bordered">
        <thead>
            <tr>
                <th style="width: 5%;">
                    N.º
                </th>

                <th style="width: 13%;">
                    Documento
                </th>

                <th style="width: 28%;">
                    Asociado
                </th>

                <th style="width: 12%;">
                    Aporte
                </th>

                <th style="width: 12%;">
                    Préstamo
                </th>

                <th style="width: 14%;">
                    Total esperado
                </th>

                <th style="width: 16%;">
                    Total recuperado
                </th>
            </tr>
        </thead>

        <tbody>
            @foreach (
                $data->planillaDetalle as $index => $item
            )
                <tr>
                    <td class="center">
                        {{ $index + 1 }}
                    </td>

                    <td class="center">
                        {{
                            optional(
                                optional(
                                    $item->asociado
                                )->persona
                            )->documento
                            ?? '-'
                        }}
                    </td>

                    <td>
                        {{
                            trim(
                                optional(
                                    optional(
                                        $item->asociado
                                    )->persona
                                )->nombre
                                . ' '
                                . optional(
                                    optional(
                                        $item->asociado
                                    )->persona
                                )->apellido
                            )
                        }}
                    </td>

                    <td class="right">
                        {{
                            number_format(
                                $item->aporte_esperado,
                                0,
                                ',',
                                '.'
                            )
                        }}
                    </td>

                    <td class="right">
                        {{
                            number_format(
                                $item->prestamo_esperado,
                                0,
                                ',',
                                '.'
                            )
                        }}
                    </td>

                    <td class="right">
                        {{
                            number_format(
                                $item->total_esperado,
                                0,
                                ',',
                                '.'
                            )
                        }}
                    </td>

                    <td class="right total-recuperado">
                        {{
                            number_format(
                                $item->total_recuperado,
                                0,
                                ',',
                                '.'
                            )
                        }}
                    </td>
                </tr>
            @endforeach

            <tr class="fila-total">
                <td
                    colspan="3"
                    class="right"
                >
                    TOTALES
                </td>

                <td class="right">
                    {{
                        number_format(
                            $totales['aporte'],
                            0,
                            ',',
                            '.'
                        )
                    }}
                </td>

                <td class="right">
                    {{
                        number_format(
                            $totales['prestamo'],
                            0,
                            ',',
                            '.'
                        )
                    }}
                </td>

                <td class="right">
                    {{
                        number_format(
                            $totales['esperado'],
                            0,
                            ',',
                            '.'
                        )
                    }}
                </td>

                <td class="right">
                    {{
                        number_format(
                            $totales['recuperado'],
                            0,
                            ',',
                            '.'
                        )
                    }}
                </td>
            </tr>
        </tbody>
    </table>

</div>

<div class="pie">
    Planilla N.º {{ $numeroPlanilla }}
    - Impreso el {{ now()->format('d/m/Y H:i') }}
</div>

</body>
</html>
