<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <title>
        Estado de cuenta del préstamo
    </title>

    <style>
        @page {
            size: A4 landscape;
            margin: 22px 25px 32px 25px;
        }

        * {
            font-family: DejaVu Sans, sans-serif;
        }

        body {
            margin: 0;
            padding: 0;
            color: #111111;
            font-size: 8px;
        }

        .encabezado {
            width: 100%;
            margin-bottom: 8px;
            border-collapse: collapse;
        }

        .encabezado td {
            vertical-align: middle;
        }

        .entidad {
            width: 68%;
        }

        .prestamo-numero {
            width: 32%;
            padding: 7px;
            border: 1px solid #222222;
            text-align: center;
        }

        .nombre-entidad {
            margin: 0 0 3px 0;
            font-size: 15px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .ruc-entidad {
            font-size: 9px;
        }

        .numero-etiqueta {
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .numero-valor {
            margin-top: 4px;
            font-size: 14px;
            font-weight: bold;
        }

        .titulo {
            margin: 10px 0 3px 0;
            font-size: 14px;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
        }

        .fecha-corte {
            margin-bottom: 8px;
            text-align: center;
        }

        .linea {
            margin-bottom: 9px;
            border-bottom: 2px solid #222222;
        }

        .datos-prestamo {
            width: 100%;
            margin-bottom: 9px;
            border-collapse: collapse;
        }

        .datos-prestamo td {
            padding: 4px 6px;
            border: 1px solid #777777;
        }

        .datos-prestamo .etiqueta {
            width: 12%;
            background-color: #eeeeee;
            font-weight: bold;
            text-transform: uppercase;
        }

        .datos-prestamo .valor {
            width: 21%;
        }

        .estado-general {
            font-weight: bold;
            text-transform: uppercase;
        }

        .resumen {
            width: 100%;
            margin-bottom: 11px;
            border-collapse: collapse;
        }

        .resumen td {
            width: 12.5%;
            padding: 6px 4px;
            border: 1px solid #444444;
            text-align: center;
        }

        .resumen-etiqueta {
            margin-bottom: 4px;
            color: #555555;
            font-size: 7px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .resumen-valor {
            font-size: 10px;
            font-weight: bold;
        }

        .valor-pagado {
            color: #146c43;
        }

        .valor-saldo {
            color: #a00000;
        }

        .titulo-seccion {
            margin-top: 10px;
            margin-bottom: 5px;
            padding-bottom: 3px;
            border-bottom: 1px solid #222222;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .tabla {
            width: 100%;
            border-collapse: collapse;
        }

        .tabla thead {
            display: table-header-group;
        }

        .tabla tr {
            page-break-inside: avoid;
        }

        .tabla th {
            padding: 4px 3px;
            border: 1px solid #222222;
            background-color: #dddddd;
            font-size: 6.5px;
            text-align: center;
            text-transform: uppercase;
        }

        .tabla td {
            padding: 4px 3px;
            border: 1px solid #555555;
            font-size: 7px;
            vertical-align: middle;
        }

        .centro {
            text-align: center;
        }

        .derecha {
            text-align: right;
        }

        .monto {
            white-space: nowrap;
            text-align: right;
        }

        .saldo {
            color: #a00000;
            font-weight: bold;
        }

        .situacion {
            padding: 2px 4px;
            border: 1px solid #555555;
            font-size: 6px;
            font-weight: bold;
            text-align: center;
            white-space: nowrap;
        }

        .pagada {
            color: #155724;
            background-color: #d4edda;
        }

        .generada {
            color: #004085;
            background-color: #cce5ff;
        }

        .atrasada {
            color: #721c24;
            background-color: #f8d7da;
        }

        .pendiente {
            color: #383d41;
            background-color: #e2e3e5;
        }

        .anulada {
            color: #721c24;
            background-color: #d6d8d9;
        }

        .observacion {
            font-size: 6px;
        }

        .nota {
            margin-top: 8px;
            padding: 5px;
            border: 1px solid #999999;
            background-color: #f4f4f4;
            font-size: 7px;
        }

        .pie {
            position: fixed;
            right: 0;
            bottom: -21px;
            left: 0;
            padding-top: 4px;
            border-top: 1px solid #999999;
            color: #555555;
            font-size: 6px;
        }

        .pie-izquierda {
            float: left;
            width: 65%;
            text-align: left;
        }

        .pie-derecha {
            float: right;
            width: 35%;
            text-align: right;
        }

        .marca-anulada {
            position: fixed;
            top: 260px;
            left: 280px;
            color: #e7bcbc;
            font-size: 65px;
            font-weight: bold;
        }
    </style>
</head>

<body>

    @php
        $persona = $prestamo->persona
            ?? optional($prestamo->solicitud)->persona;

        $tipoPrestamo = $prestamo->tipoPrestamo
            ?? optional($prestamo->solicitud)->tipoPrestamo;

        $numeroPrestamo =
            $prestamo->numero_prestamo
            ?? $prestamo->numero;

        $nombreEntidad =
            $entidad->nombre
            ?? $entidad->razon_social
            ?? 'ENTIDAD';

        $rucEntidad =
            $entidad->ruc
            ?? $entidad->ruc_sin_digito
            ?? null;

        $estadoPrestamoId =
            (int) $prestamo->estado_prestamo_id;

        $estadoPrestamo =
            optional($prestamo->estadoPrestamo)->descripcion
            ?? optional($prestamo->estadoPrestamo)->nombre
            ?? match ($estadoPrestamoId) {
                1 => 'PENDIENTE DE DESEMBOLSO',
                2 => 'VIGENTE',
                3 => 'CANCELADO',
                4 => 'ANULADO',
                default => 'SIN ESTADO',
            };

        $montoIvaPrestamo =
            $prestamo->monto_iva
            ?? $prestamo->iva
            ?? 0;
    @endphp

    @if ($estadoPrestamoId === 4)
        <div class="marca-anulada">
            ANULADO
        </div>
    @endif

    {{-- ENCABEZADO --}}
    <table class="encabezado">
        <tr>
            <td class="entidad">
                <div class="nombre-entidad">
                    {{ $nombreEntidad }}
                </div>

                @if ($rucEntidad)
                    <div class="ruc-entidad">
                        RUC: {{ $rucEntidad }}
                    </div>
                @endif
            </td>

            <td class="prestamo-numero">
                <div class="numero-etiqueta">
                    Préstamo N.º
                </div>

                <div class="numero-valor">
                    {{
                        str_pad(
                            $numeroPrestamo,
                            5,
                            '0',
                            STR_PAD_LEFT
                        )
                    }}/{{ $prestamo->anio }}
                </div>
            </td>
        </tr>
    </table>

    <div class="titulo">
        Estado de cuenta del préstamo
    </div>

    <div class="fecha-corte">
        Saldo actualizado al
        <strong>{{ now()->format('d/m/Y') }}</strong>
    </div>

    <div class="linea"></div>

    {{-- DATOS GENERALES --}}
    <table class="datos-prestamo">
        <tr>
            <td class="etiqueta">
                Asociado
            </td>

            <td class="valor">
                <strong>
                    {{
                        mb_strtoupper(
                            trim(
                                optional($persona)->nombre
                                . ' '
                                . optional($persona)->apellido
                            ),
                            'UTF-8'
                        )
                    }}
                </strong>
            </td>

            <td class="etiqueta">
                Documento
            </td>

            <td class="valor">
                {{ optional($persona)->documento ?? 'N/A' }}
            </td>

            <td class="etiqueta">
                Tipo
            </td>

            <td class="valor">
                {{
                    optional($tipoPrestamo)->descripcion
                    ?? optional($tipoPrestamo)->nombre
                    ?? 'SIN TIPO'
                }}
            </td>
        </tr>

        <tr>
            <td class="etiqueta">
                Estado
            </td>

            <td class="valor">
                <span class="estado-general">
                    {{ $estadoPrestamo }}
                </span>
            </td>

            <td class="etiqueta">
                Desembolso
            </td>

            <td class="valor">
                @if ($prestamo->fecha_desembolso)
                    {{
                        \Carbon\Carbon::parse(
                            $prestamo->fecha_desembolso
                        )->format('d/m/Y')
                    }}
                @else
                    PENDIENTE
                @endif
            </td>

            <td class="etiqueta">
                Cuotas
            </td>

            <td class="valor">
                {{ $prestamo->detalles->count() }}
            </td>
        </tr>
    </table>

    {{-- RESUMEN --}}
    <table class="resumen">
        <tr>
            <td>
                <div class="resumen-etiqueta">
                    Capital
                </div>

                <div class="resumen-valor">
                    {{
                        number_format(
                            (int) $prestamo->monto_capital,
                            0,
                            ',',
                            '.'
                        )
                    }}
                </div>
            </td>

            <td>
                <div class="resumen-etiqueta">
                    Interés
                </div>

                <div class="resumen-valor">
                    {{
                        number_format(
                            (int) $prestamo->monto_interes,
                            0,
                            ',',
                            '.'
                        )
                    }}
                </div>
            </td>

            <td>
                <div class="resumen-etiqueta">
                    IVA
                </div>

                <div class="resumen-valor">
                    {{
                        number_format(
                            (int) $montoIvaPrestamo,
                            0,
                            ',',
                            '.'
                        )
                    }}
                </div>
            </td>

            <td>
                <div class="resumen-etiqueta">
                    Total préstamo
                </div>

                <div class="resumen-valor">
                    {{
                        number_format(
                            (int) $prestamo->monto_total,
                            0,
                            ',',
                            '.'
                        )
                    }}
                </div>
            </td>

            <td>
                <div class="resumen-etiqueta">
                    Total pagado
                </div>

                <div class="resumen-valor valor-pagado">
                    {{
                        number_format(
                            (int) $prestamo->monto_pagado,
                            0,
                            ',',
                            '.'
                        )
                    }}
                </div>
            </td>

            <td>
                <div class="resumen-etiqueta">
                    Saldo
                </div>

                <div class="resumen-valor valor-saldo">
                    {{
                        number_format(
                            (int) $prestamo->saldo_total,
                            0,
                            ',',
                            '.'
                        )
                    }}
                </div>
            </td>

            <td>
                <div class="resumen-etiqueta">
                    Punitorio
                </div>

                <div class="resumen-valor">
                    {{
                        number_format(
                            $moraPendiente,
                            0,
                            ',',
                            '.'
                        )
                    }}
                </div>
            </td>

            <td>
                <div class="resumen-etiqueta">
                    IVA punitorio
                </div>

                <div class="resumen-valor">
                    {{
                        number_format(
                            $moraIvaPendiente,
                            0,
                            ',',
                            '.'
                        )
                    }}
                </div>
            </td>
        </tr>
    </table>

    {{-- CUOTAS --}}
    <div class="titulo-seccion">
        Detalle de cuotas
    </div>

    <table class="tabla">
        <thead>
            <tr>
                <th style="width: 4%;">
                    Cuota
                </th>

                <th style="width: 8%;">
                    Vencimiento
                </th>

                <th style="width: 9%;">
                    Capital
                </th>

                <th style="width: 9%;">
                    Interés
                </th>

                <th style="width: 7%;">
                    IVA
                </th>

                <th style="width: 8%;">
                    Punitorio
                </th>

                <th style="width: 7%;">
                    IVA P.
                </th>

                <th style="width: 9%;">
                    Pagado
                </th>

                <th style="width: 9%;">
                    Saldo
                </th>

                <th style="width: 12%;">
                    Última planilla
                </th>

                <th style="width: 18%;">
                    Situación
                </th>
            </tr>
        </thead>

        <tbody>
            @forelse ($prestamo->detalles as $cuota)
                @php
                    $montoIva =
                        $cuota->monto_iva
                        ?? $cuota->iva
                        ?? 0;

                    $ultimaGeneracion =
                        $cuota->ultima_generacion;
                @endphp

                <tr>
                    <td class="centro">
                        <strong>
                            {{ $cuota->numero_cuota }}
                        </strong>
                    </td>

                    <td class="centro">
                        {{
                            \Carbon\Carbon::parse(
                                $cuota->fecha_vencimiento
                            )->format('d/m/Y')
                        }}
                    </td>

                    <td class="monto">
                        {{
                            number_format(
                                (int) $cuota->monto_capital,
                                0,
                                ',',
                                '.'
                            )
                        }}
                    </td>

                    <td class="monto">
                        {{
                            number_format(
                                (int) $cuota->monto_interes,
                                0,
                                ',',
                                '.'
                            )
                        }}
                    </td>

                    <td class="monto">
                        {{
                            number_format(
                                (int) $montoIva,
                                0,
                                ',',
                                '.'
                            )
                        }}
                    </td>

                    <td class="monto">
                        {{
                            number_format(
                                (int) $cuota->monto_mora,
                                0,
                                ',',
                                '.'
                            )
                        }}
                    </td>

                    <td class="monto">
                        {{
                            number_format(
                                (int) $cuota->monto_mora_iva,
                                0,
                                ',',
                                '.'
                            )
                        }}
                    </td>

                    <td class="monto">
                        {{
                            number_format(
                                (int) $cuota->monto_pagado,
                                0,
                                ',',
                                '.'
                            )
                        }}
                    </td>

                    <td class="monto saldo">
                        {{
                            number_format(
                                (int) $cuota->saldo_total,
                                0,
                                ',',
                                '.'
                            )
                        }}
                    </td>

                    <td class="centro">
                        @if ($ultimaGeneracion)
                            {{
                                str_pad(
                                    $ultimaGeneracion->planilla_numero,
                                    7,
                                    '0',
                                    STR_PAD_LEFT
                                )
                            }}/{{ $ultimaGeneracion->planilla_anio }}

                            <br>

                            {{
                                str_pad(
                                    $ultimaGeneracion->mes,
                                    2,
                                    '0',
                                    STR_PAD_LEFT
                                )
                            }}/{{ $ultimaGeneracion->anio }}
                        @else
                            SIN GENERAR
                        @endif
                    </td>

                    <td class="centro">
                        <span
                            class="situacion
                            {{ $cuota->clase_situacion }}"
                        >
                            {{ $cuota->situacion_calculada }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td
                        colspan="11"
                        class="centro"
                    >
                        El préstamo no posee cuotas activas.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- MOVIMIENTOS --}}
    <div class="titulo-seccion">
        Movimientos del préstamo
    </div>

    <table class="tabla">
        <thead>
            <tr>
                <th style="width: 8%;">
                    Generación
                </th>

                <th style="width: 8%;">
                    Pago
                </th>

                <th style="width: 5%;">
                    Cuota
                </th>

                <th style="width: 9%;">
                    Capital
                </th>

                <th style="width: 9%;">
                    Interés
                </th>

                <th style="width: 8%;">
                    IVA
                </th>

                <th style="width: 9%;">
                    Punitorio
                </th>

                <th style="width: 10%;">
                    Total aplicado
                </th>

                <th style="width: 10%;">
                    Estado
                </th>

                <th style="width: 24%;">
                    Observación
                </th>
            </tr>
        </thead>

        <tbody>
            @forelse ($movimientos as $movimiento)
                @php
                    $estadoMovimiento = match (
                        (int) $movimiento->estado_pago_id
                    ) {
                        1 => 'PENDIENTE',
                        2 => 'PAGADO',
                        3 => 'PARCIAL',
                        4 => 'ANULADO',
                        5 => 'GENERADO',
                        6 => 'NO PAGADO',
                        default => 'SIN ESTADO',
                    };

                    $claseMovimiento = match (
                        (int) $movimiento->estado_pago_id
                    ) {
                        2 => 'pagada',
                        3, 6 => 'atrasada',
                        4 => 'anulada',
                        5 => 'generada',
                        default => 'pendiente',
                    };

                    $punitorioPagado =
                        (int) $movimiento->monto_mora_pagado
                        + (int) $movimiento
                            ->monto_mora_iva_pagado;

                    $numeroCuota = optional(
                        $prestamo->detalles->firstWhere(
                            'id',
                            $movimiento->prestamo_detalle_id
                        )
                    )->numero_cuota;
                @endphp

                <tr>
                    <td class="centro">
                        @if ($movimiento->fecha_generacion)
                            {{
                                \Carbon\Carbon::parse(
                                    $movimiento->fecha_generacion
                                )->format('d/m/Y')
                            }}
                        @else
                            -
                        @endif
                    </td>

                    <td class="centro">
                        @if ($movimiento->fecha_pago)
                            {{
                                \Carbon\Carbon::parse(
                                    $movimiento->fecha_pago
                                )->format('d/m/Y')
                            }}
                        @else
                            -
                        @endif
                    </td>

                    <td class="centro">
                        {{ $numeroCuota ?? '-' }}
                    </td>

                    <td class="monto">
                        {{
                            number_format(
                                (int) $movimiento
                                    ->monto_capital_pagado,
                                0,
                                ',',
                                '.'
                            )
                        }}
                    </td>

                    <td class="monto">
                        {{
                            number_format(
                                (int) $movimiento
                                    ->monto_interes_pagado,
                                0,
                                ',',
                                '.'
                            )
                        }}
                    </td>

                    <td class="monto">
                        {{
                            number_format(
                                (int) $movimiento
                                    ->monto_iva_pagado,
                                0,
                                ',',
                                '.'
                            )
                        }}
                    </td>

                    <td class="monto">
                        {{
                            number_format(
                                $punitorioPagado,
                                0,
                                ',',
                                '.'
                            )
                        }}
                    </td>

                    <td class="monto">
                        {{
                            number_format(
                                (int) $movimiento->monto_pagado,
                                0,
                                ',',
                                '.'
                            )
                        }}
                    </td>

                    <td class="centro">
                        <span
                            class="situacion
                            {{ $claseMovimiento }}"
                        >
                            {{ $estadoMovimiento }}
                        </span>
                    </td>

                    <td class="observacion">
                        {{ $movimiento->observaciones ?? '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td
                        colspan="10"
                        class="centro"
                    >
                        Todavía no existen movimientos registrados.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="nota">
        <strong>Observación:</strong>

        las cuotas se consideran atrasadas cuando una planilla fue procesada
        y quedó saldo pendiente. El interés punitorio se incorpora durante
        la siguiente generación de planilla, no durante la impresión de este
        estado de cuenta.
    </div>

    <div class="pie">
        <div class="pie-izquierda">
            Estado de cuenta del préstamo N.º
            {{
                str_pad(
                    $numeroPrestamo,
                    5,
                    '0',
                    STR_PAD_LEFT
                )
            }}/{{ $prestamo->anio }}
            -
            {{ $nombreEntidad }}
        </div>

        <div class="pie-derecha">
            Emitido el {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>

</body>
</html>
