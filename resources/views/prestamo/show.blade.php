@extends('layouts.admin')

@section('styles')
    <link
        rel="stylesheet"
        type="text/css"
        href="{{ asset('assets/css/elements/alert.css') }}"
    >

    <style>
        .cabecera-cuenta {
            padding: 18px;
            margin-bottom: 20px;
            border: 1px solid #dcdcdc;
            border-left: 5px solid #1b55e2;
            border-radius: 5px;
            background-color: #f8f9fa;
        }

        .numero-prestamo {
            margin: 0;
            color: #1b55e2;
            font-size: 22px;
            font-weight: bold;
        }

        .nombre-asociado {
            margin-top: 5px;
            color: #1b2e4b;
            font-size: 17px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .dato-secundario {
            color: #6c757d;
            font-size: 12px;
        }

        .resumen-cuenta {
            width: 100%;
            margin-bottom: 22px;
            border-collapse: collapse;
        }

        .resumen-cuenta td {
            width: 20%;
            padding: 14px;
            border: 1px solid #dcdcdc;
            vertical-align: top;
        }

        .resumen-etiqueta {
            margin-bottom: 5px;
            color: #6c757d;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .resumen-valor {
            color: #1b2e4b;
            font-size: 16px;
            font-weight: bold;
        }

        .resumen-saldo {
            color: #c62828;
        }

        .resumen-pagado {
            color: #198754;
        }

        .titulo-seccion {
            padding-bottom: 7px;
            margin-top: 25px;
            margin-bottom: 12px;
            border-bottom: 2px solid #1b55e2;
            color: #1b2e4b;
            font-size: 15px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .tabla-cuenta th {
            white-space: nowrap;
            vertical-align: middle !important;
        }

        .tabla-cuenta td {
            vertical-align: middle !important;
        }

        .monto {
            font-weight: bold;
            text-align: right;
            white-space: nowrap;
        }

        .monto-saldo {
            color: #c62828;
        }

        .badge-situacion {
            display: inline-block;
            min-width: 90px;
            padding: 6px 9px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .estado-pendiente {
            color: #383d41;
            background-color: #e2e3e5;
        }

        .estado-generado {
            color: #004085;
            background-color: #cce5ff;
        }

        .estado-pagado {
            color: #155724;
            background-color: #d4edda;
        }

        .estado-atrasado {
            color: #721c24;
            background-color: #f8d7da;
        }

        .estado-anulado {
            color: #721c24;
            background-color: #d6d8d9;
        }

        .planilla-numero {
            color: #1b55e2;
            font-weight: bold;
            white-space: nowrap;
        }

        .sin-datos {
            padding: 30px !important;
            color: #6c757d;
            text-align: center;
        }
    </style>
@endsection

@section('content')

    @php
        $persona = $prestamo->persona
            ?? optional($prestamo->solicitud)->persona;

        $tipoPrestamo = $prestamo->tipoPrestamo
            ?? optional($prestamo->solicitud)->tipoPrestamo;

        $numeroPrestamo =
            $prestamo->numero_prestamo
            ?? $prestamo->numero;

        $estadoPrestamoId =
            (int) $prestamo->estado_prestamo_id;

        $estadoPrestamoDescripcion =
            optional($prestamo->estadoPrestamo)->descripcion
            ?? optional($prestamo->estadoPrestamo)->nombre
            ?? match ($estadoPrestamoId) {
                1 => 'PENDIENTE DE DESEMBOLSO',
                2 => 'VIGENTE',
                3 => 'CANCELADO',
                4 => 'ANULADO',
                default => 'SIN ESTADO',
            };

        $clasePrestamo = match ($estadoPrestamoId) {
            1 => 'estado-generado',
            2 => 'estado-pagado',
            3 => 'estado-generado',
            4 => 'estado-anulado',
            default => 'estado-pendiente',
        };
    @endphp

    <div class="col-lg-12 layout-spacing">
        <div class="statbox widget box box-shadow">
            <div class="widget-content widget-content-area">

                @include('varios.mensaje')

                <div class="d-flex align-items-center mb-4">
                    <div>
                        <h4 class="mb-1">
                            Estado de cuenta del préstamo
                        </h4>

                        <div class="dato-secundario">
                            Detalle de cuotas, pagos y saldos
                        </div>
                    </div>

                    <div class="ml-auto">
                        <a
                            href="{{ route('prestamos.index') }}"
                            class="btn btn-secondary"
                        >
                            <i class="fa fa-arrow-left"></i>
                            Volver
                        </a>

                        @if (Route::has('prestamos.imprimir'))
                            <a
                                href="{{ route(
                                    'prestamos.imprimir',
                                    $prestamo->id
                                ) }}"
                                target="_blank"
                                class="btn btn-primary"
                            >
                                <i class="fa fa-print"></i>
                                Imprimir
                            </a>
                        @endif
                    </div>
                </div>

                {{-- CABECERA --}}
                <div class="cabecera-cuenta">
                    <div class="row">
                        <div class="col-md-8">
                            <h3 class="numero-prestamo">
                                Préstamo N.º
                                {{
                                    str_pad(
                                        $numeroPrestamo,
                                        5,
                                        '0',
                                        STR_PAD_LEFT
                                    )
                                }}/{{ $prestamo->anio }}
                            </h3>

                            <div class="nombre-asociado">
                                {{
                                    trim(
                                        optional($persona)->nombre
                                        . ' '
                                        . optional($persona)->apellido
                                    )
                                    ?: 'SIN DATOS DEL ASOCIADO'
                                }}
                            </div>

                            <div class="dato-secundario">
                                Documento:
                                <strong>
                                    {{
                                        optional($persona)->documento
                                        ?? 'N/A'
                                    }}
                                </strong>

                                &nbsp; | &nbsp;

                                Tipo:
                                <strong>
                                    {{
                                        optional(
                                            $tipoPrestamo
                                        )->descripcion
                                        ?? optional(
                                            $tipoPrestamo
                                        )->nombre
                                        ?? 'SIN TIPO'
                                    }}
                                </strong>
                            </div>
                        </div>

                        <div class="col-md-4 text-right">
                            <span
                                class="badge-situacion
                                {{ $clasePrestamo }}"
                            >
                                {{ $estadoPrestamoDescripcion }}
                            </span>

                            <div class="dato-secundario mt-2">
                                Fecha de desembolso:

                                <strong>
                                    @if ($prestamo->fecha_desembolso)
                                        {{
                                            \Carbon\Carbon::parse(
                                                $prestamo->fecha_desembolso
                                            )->format('d/m/Y')
                                        }}
                                    @else
                                        PENDIENTE
                                    @endif
                                </strong>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- RESUMEN FINANCIERO --}}
                <table class="resumen-cuenta">
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
                                        (int) $prestamo->monto_iva,
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

                            <div class="resumen-valor resumen-pagado">
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
                                Saldo total
                            </div>

                            <div class="resumen-valor resumen-saldo">
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
                    </tr>

                    <tr>
                        <td>
                            <div class="resumen-etiqueta">
                                Saldo capital
                            </div>

                            <div class="resumen-valor">
                                {{
                                    number_format(
                                        (int) $prestamo->saldo_capital,
                                        0,
                                        ',',
                                        '.'
                                    )
                                }}
                            </div>
                        </td>

                        <td>
                            <div class="resumen-etiqueta">
                                Saldo interés
                            </div>

                            <div class="resumen-valor">
                                {{
                                    number_format(
                                        (int) $prestamo->saldo_interes,
                                        0,
                                        ',',
                                        '.'
                                    )
                                }}
                            </div>
                        </td>

                        <td>
                            <div class="resumen-etiqueta">
                                Saldo IVA
                            </div>

                            <div class="resumen-valor">
                                {{
                                    number_format(
                                        (int) $prestamo->saldo_iva,
                                        0,
                                        ',',
                                        '.'
                                    )
                                }}
                            </div>
                        </td>

                        <td>
                            <div class="resumen-etiqueta">
                                Punitorio pendiente
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

                <div class="table-responsive">
                    <table
                        class="table table-bordered table-hover tabla-cuenta"
                    >
                        <thead>
                            <tr>
                                <th class="text-center">
                                    Cuota
                                </th>

                                <th class="text-center">
                                    Vencimiento
                                </th>

                                <th class="text-right">
                                    Capital
                                </th>

                                <th class="text-right">
                                    Interés
                                </th>

                                <th class="text-right">
                                    IVA
                                </th>

                                <th class="text-right">
                                    Punitorio
                                </th>

                                <th class="text-right">
                                    Total
                                </th>

                                <th class="text-right">
                                    Pagado
                                </th>

                                <th class="text-right">
                                    Saldo
                                </th>

                                <th class="text-center">
                                    Última planilla
                                </th>

                                <th class="text-center">
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

                                    $punitorioTotal =
                                        (int) $cuota->monto_mora
                                        + (int) $cuota->monto_mora_iva;

                                    $ultimaGeneracion =
                                        $cuota->ultima_generacion;
                                @endphp

                                <tr>
                                    <td class="text-center">
                                        <strong>
                                            {{ $cuota->numero_cuota }}
                                        </strong>
                                    </td>

                                    <td class="text-center">
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
                                                $punitorioTotal,
                                                0,
                                                ',',
                                                '.'
                                            )
                                        }}
                                    </td>

                                    <td class="monto">
                                        {{
                                            number_format(
                                                (int) $cuota->monto_total,
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

                                    <td class="monto monto-saldo">
                                        {{
                                            number_format(
                                                (int) $cuota->saldo_total,
                                                0,
                                                ',',
                                                '.'
                                            )
                                        }}
                                    </td>

                                    <td class="text-center">
                                        @if ($ultimaGeneracion)
                                            <span class="planilla-numero">
                                                {{
                                                    str_pad(
                                                        $ultimaGeneracion
                                                            ->planilla_numero,
                                                        7,
                                                        '0',
                                                        STR_PAD_LEFT
                                                    )
                                                }}/{{ $ultimaGeneracion
                                                    ->planilla_anio }}
                                            </span>

                                            <div class="dato-secundario">
                                                {{
                                                    str_pad(
                                                        $ultimaGeneracion->mes,
                                                        2,
                                                        '0',
                                                        STR_PAD_LEFT
                                                    )
                                                }}/{{ $ultimaGeneracion->anio }}
                                            </div>
                                        @else
                                            <span class="text-muted">
                                                Sin generar
                                            </span>
                                        @endif
                                    </td>

                                    <td class="text-center">
                                        <span
                                            class="badge-situacion
                                            {{ $cuota->clase_situacion }}"
                                        >
                                            {{
                                                $cuota
                                                    ->situacion_calculada
                                            }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td
                                        colspan="11"
                                        class="sin-datos"
                                    >
                                        El préstamo no posee cuotas activas.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- MOVIMIENTOS --}}
                <div class="titulo-seccion">
                    Movimientos del préstamo
                </div>

                <div class="table-responsive">
                    <table
                        class="table table-bordered table-hover tabla-cuenta"
                    >
                        <thead>
                            <tr>
                                <th class="text-center">
                                    Generación
                                </th>

                                <th class="text-center">
                                    Pago
                                </th>

                                <th class="text-center">
                                    Cuota
                                </th>

                                <th class="text-right">
                                    Capital
                                </th>

                                <th class="text-right">
                                    Interés
                                </th>

                                <th class="text-right">
                                    IVA
                                </th>

                                <th class="text-right">
                                    Punitorio
                                </th>

                                <th class="text-right">
                                    Total aplicado
                                </th>

                                <th class="text-center">
                                    Estado
                                </th>

                                <th>
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
                                        2 => 'estado-pagado',
                                        3, 6 => 'estado-atrasado',
                                        4 => 'estado-anulado',
                                        5 => 'estado-generado',
                                        default => 'estado-pendiente',
                                    };

                                    $punitorioPagado =
                                        (int) $movimiento
                                            ->monto_mora_pagado
                                        + (int) $movimiento
                                            ->monto_mora_iva_pagado;
                                @endphp

                                <tr>
                                    <td class="text-center">
                                        @if (
                                            $movimiento->fecha_generacion
                                        )
                                            {{
                                                \Carbon\Carbon::parse(
                                                    $movimiento
                                                        ->fecha_generacion
                                                )->format('d/m/Y')
                                            }}
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td class="text-center">
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

                                    <td class="text-center">
                                        {{
                                            optional(
                                                $prestamo->detalles->firstWhere(
                                                    'id',
                                                    $movimiento
                                                        ->prestamo_detalle_id
                                                )
                                            )->numero_cuota
                                            ?? '-'
                                        }}
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
                                                (int) $movimiento
                                                    ->monto_pagado,
                                                0,
                                                ',',
                                                '.'
                                            )
                                        }}
                                    </td>

                                    <td class="text-center">
                                        <span
                                            class="badge-situacion
                                            {{ $claseMovimiento }}"
                                        >
                                            {{ $estadoMovimiento }}
                                        </span>
                                    </td>

                                    <td>
                                        {{
                                            $movimiento->observaciones
                                            ?? '-'
                                        }}

                                        @if (
                                            $movimiento->recibo_id
                                            && Route::has('recibo.show')
                                        )
                                            <div class="mt-1">
                                                <a
                                                    href="{{ route(
                                                        'recibo.show',
                                                        $movimiento->recibo_id
                                                    ) }}"
                                                >
                                                    Ver recibo
                                                </a>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td
                                        colspan="10"
                                        class="sin-datos"
                                    >
                                        Todavía no existen movimientos para
                                        este préstamo.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

@endsection
