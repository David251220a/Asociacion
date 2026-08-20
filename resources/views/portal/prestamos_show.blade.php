@extends('layouts.admin')

@section('styles')
    <style>
        .prestamo-show-card {
            overflow: hidden;
            border: 1px solid #e1e8ef;
            border-radius: 15px;
            background: #ffffff;
            box-shadow: 0 8px 25px rgba(30, 60, 90, .07);
        }

        .prestamo-show-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            padding: 25px 28px;
            border-bottom: 1px solid #e8edf2;
            background: linear-gradient(
                135deg,
                #ffffff 0%,
                #f2f7fb 100%
            );
        }

        .prestamo-show-header h3 {
            margin-bottom: 5px;
            color: #173a5e;
            font-weight: 700;
        }

        .prestamo-numero {
            color: #6f7f8e;
            font-size: 14px;
        }

        .prestamo-contenido {
            padding: 28px;
        }

        .prestamo-seccion {
            margin-bottom: 28px;
        }

        .prestamo-seccion:last-child {
            margin-bottom: 0;
        }

        .prestamo-seccion-titulo {
            margin-bottom: 17px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e8edf2;
            color: #1b3a5b;
            font-size: 17px;
            font-weight: 700;
        }

        .dato-prestamo {
            height: 100%;
            padding: 16px;
            border: 1px solid #e4eaf0;
            border-radius: 10px;
            background-color: #f9fbfc;
        }

        .dato-prestamo-etiqueta {
            display: block;
            margin-bottom: 5px;
            color: #8493a1;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .5px;
            text-transform: uppercase;
        }

        .dato-prestamo-valor {
            display: block;
            color: #2f485f;
            font-size: 16px;
            font-weight: 600;
        }

        .resumen-prestamo {
            padding: 22px;
            border: 1px solid #d7e4ed;
            border-radius: 12px;
            background: linear-gradient(
                135deg,
                #f8fbfd 0%,
                #edf5fa 100%
            );
        }

        .resumen-fila {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            margin-bottom: 13px;
            color: #647586;
        }

        .resumen-fila:last-child {
            margin-bottom: 0;
        }

        .resumen-fila strong {
            color: #29445d;
            text-align: right;
        }

        .resumen-total {
            margin-top: 17px;
            padding-top: 17px;
            border-top: 1px solid #d5e0e8;
        }

        .resumen-total strong {
            color: #168552;
            font-size: 20px;
        }

        .tabla-cuotas thead th {
            border-bottom: 2px solid #dce5ec;
            color: #516476;
            font-size: 12px;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .tabla-cuotas td {
            vertical-align: middle;
        }

        .numero-cuota {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            color: #1b6fc2;
            background-color: #e4f0fb;
            font-weight: 700;
        }

        .motivo-rechazo {
            padding: 18px;
            border-left: 4px solid #dc3545;
            border-radius: 6px;
            color: #721c24;
            background-color: #f8d7da;
        }

        .orden-generada {
            padding: 18px;
            border-left: 4px solid #168552;
            border-radius: 6px;
            color: #11633f;
            background-color: #e1f5ec;
        }

        @media (max-width: 767.98px) {
            .prestamo-show-header {
                flex-direction: column;
                padding: 22px 20px;
            }

            .prestamo-contenido {
                padding: 22px 18px;
            }
        }
    </style>
@endsection

@section('content')

    @php
        $totalCapital = $data->detalles->sum(
            fn ($cuota) => (int) $cuota->monto_capital
        );

        $totalInteres = $data->detalles->sum(
            fn ($cuota) => (int) $cuota->monto_interes
        );

        $totalIva = $data->detalles->sum(
            fn ($cuota) => (int) $cuota->iva
        );

        $totalPagar = $data->detalles->sum(
            fn ($cuota) => (int) $cuota->monto_total
        );

        $montoMostrar = !is_null($data->monto_aprobado)
            ? (int) $data->monto_aprobado
            : (int) $data->monto_solicitado;
    @endphp

    <div class="col-lg-12 layout-spacing">

        <div class="prestamo-show-card">

            <div class="prestamo-show-header">

                <div>
                    <h3>
                        Solicitud de préstamo de emergencia
                    </h3>

                    <div class="prestamo-numero">
                        Solicitud N.º
                        {{ str_pad(
                            $data->numero_solicitud,
                            5,
                            '0',
                            STR_PAD_LEFT
                        ) }}/{{ $data->anio }}
                    </div>
                </div>

                <span class="badge badge-{{
                    $data->estadoSolicitud->color ?? 'secondary'
                }}">
                    {{
                        $data->estadoSolicitud->descripcion
                            ?? 'SIN ESTADO'
                    }}
                </span>

            </div>

            <div class="prestamo-contenido">

                @include('varios.mensaje')

                <div class="prestamo-seccion">

                    <h5 class="prestamo-seccion-titulo">
                        Información de la solicitud
                    </h5>

                    <div class="row">

                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="dato-prestamo">
                                <span class="dato-prestamo-etiqueta">
                                    Fecha de solicitud
                                </span>

                                <span class="dato-prestamo-valor">
                                    {{
                                        \Carbon\Carbon::parse(
                                            $data->fecha_solicitud
                                        )->format('d/m/Y')
                                    }}
                                </span>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="dato-prestamo">
                                <span class="dato-prestamo-etiqueta">
                                    Monto solicitado
                                </span>

                                <span class="dato-prestamo-valor">
                                    G.
                                    {{ number_format(
                                        $data->monto_solicitado,
                                        0,
                                        ',',
                                        '.'
                                    ) }}
                                </span>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="dato-prestamo">
                                <span class="dato-prestamo-etiqueta">
                                    Cantidad de cuotas
                                </span>

                                <span class="dato-prestamo-valor">
                                    {{ $data->cantidad_cuotas }}

                                    {{
                                        (int) $data->cantidad_cuotas === 1
                                            ? 'cuota'
                                            : 'cuotas'
                                    }}
                                </span>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="dato-prestamo">
                                <span class="dato-prestamo-etiqueta">
                                    Tasa aplicada por cuota
                                </span>

                                <span class="dato-prestamo-valor">
                                    {{ number_format(
                                        $data->tasa_aplicada,
                                        2,
                                        ',',
                                        '.'
                                    ) }} %
                                </span>
                            </div>
                        </div>

                    </div>

                </div>

                <div class="prestamo-seccion">

                    <h5 class="prestamo-seccion-titulo">
                        Resumen del préstamo
                    </h5>

                    <div class="row">

                        <div class="col-lg-7 mb-4 mb-lg-0">

                            <div class="resumen-prestamo">

                                <div class="resumen-fila">
                                    <span>
                                        Capital
                                    </span>

                                    <strong>
                                        G.
                                        {{ number_format(
                                            $totalCapital,
                                            0,
                                            ',',
                                            '.'
                                        ) }}
                                    </strong>
                                </div>

                                <div class="resumen-fila">
                                    <span>
                                        Interés total
                                    </span>

                                    <strong>
                                        G.
                                        {{ number_format(
                                            $totalInteres,
                                            0,
                                            ',',
                                            '.'
                                        ) }}
                                    </strong>
                                </div>

                                @if ($totalIva > 0)
                                    <div class="resumen-fila">
                                        <span>
                                            IVA
                                        </span>

                                        <strong>
                                            G.
                                            {{ number_format(
                                                $totalIva,
                                                0,
                                                ',',
                                                '.'
                                            ) }}
                                        </strong>
                                    </div>
                                @endif

                                <div class="resumen-fila resumen-total">
                                    <span>
                                        Total a devolver
                                    </span>

                                    <strong>
                                        G.
                                        {{ number_format(
                                            $totalPagar,
                                            0,
                                            ',',
                                            '.'
                                        ) }}
                                    </strong>
                                </div>

                            </div>

                        </div>

                        <div class="col-lg-5">

                            <div class="resumen-prestamo">

                                <div class="resumen-fila">
                                    <span>
                                        Estado
                                    </span>

                                    <strong>
                                        {{
                                            $data->estadoSolicitud->descripcion
                                                ?? 'Sin estado'
                                        }}
                                    </strong>
                                </div>

                                <div class="resumen-fila">
                                    <span>
                                        Monto
                                        {{
                                            !is_null($data->monto_aprobado)
                                                ? 'aprobado'
                                                : 'solicitado'
                                        }}
                                    </span>

                                    <strong>
                                        G.
                                        {{ number_format(
                                            $montoMostrar,
                                            0,
                                            ',',
                                            '.'
                                        ) }}
                                    </strong>
                                </div>

                                @if ($data->fecha_aprobacion_rechazo)
                                    <div class="resumen-fila">
                                        <span>
                                            Fecha de resolución
                                        </span>

                                        <strong>
                                            {{
                                                \Carbon\Carbon::parse(
                                                    $data->fecha_aprobacion_rechazo
                                                )->format('d/m/Y')
                                            }}
                                        </strong>
                                    </div>
                                @endif

                            </div>

                        </div>

                    </div>

                </div>

                <div class="prestamo-seccion">

                    <h5 class="prestamo-seccion-titulo">
                        Detalle de cuotas
                    </h5>

                    <div class="table-responsive">

                        <table class="table table-hover tabla-cuotas mb-0">

                            <thead>
                                <tr>
                                    <th>Cuota</th>
                                    <th>Vencimiento</th>

                                    <th class="text-right">
                                        Capital
                                    </th>

                                    <th class="text-right">
                                        Interés
                                    </th>

                                    @if ($totalIva > 0)
                                        <th class="text-right">
                                            IVA
                                        </th>
                                    @endif

                                    <th class="text-right">
                                        Total
                                    </th>
                                </tr>
                            </thead>

                            <tbody>

                                @forelse ($data->detalles as $cuota)
                                    <tr>
                                        <td>
                                            <span class="numero-cuota">
                                                {{ $cuota->numero_cuota }}
                                            </span>
                                        </td>

                                        <td>
                                            {{
                                                \Carbon\Carbon::parse(
                                                    $cuota->fecha_vencimiento
                                                )->format('d/m/Y')
                                            }}
                                        </td>

                                        <td class="text-right">
                                            G.
                                            {{ number_format(
                                                $cuota->monto_capital,
                                                0,
                                                ',',
                                                '.'
                                            ) }}
                                        </td>

                                        <td class="text-right">
                                            G.
                                            {{ number_format(
                                                $cuota->monto_interes,
                                                0,
                                                ',',
                                                '.'
                                            ) }}
                                        </td>

                                        @if ($totalIva > 0)
                                            <td class="text-right">
                                                G.
                                                {{ number_format(
                                                    $cuota->iva,
                                                    0,
                                                    ',',
                                                    '.'
                                                ) }}
                                            </td>
                                        @endif

                                        <td class="text-right">
                                            <strong>
                                                G.
                                                {{ number_format(
                                                    $cuota->monto_total,
                                                    0,
                                                    ',',
                                                    '.'
                                                ) }}
                                            </strong>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td
                                            colspan="{{ $totalIva > 0 ? 6 : 5 }}"
                                            class="text-center text-muted py-4"
                                        >
                                            No se encontraron cuotas para
                                            esta solicitud.
                                        </td>
                                    </tr>
                                @endforelse

                            </tbody>

                            @if ($data->detalles->isNotEmpty())
                                <tfoot>
                                    <tr>
                                        <th colspan="2">
                                            Totales
                                        </th>

                                        <th class="text-right">
                                            G.
                                            {{ number_format(
                                                $totalCapital,
                                                0,
                                                ',',
                                                '.'
                                            ) }}
                                        </th>

                                        <th class="text-right">
                                            G.
                                            {{ number_format(
                                                $totalInteres,
                                                0,
                                                ',',
                                                '.'
                                            ) }}
                                        </th>

                                        @if ($totalIva > 0)
                                            <th class="text-right">
                                                G.
                                                {{ number_format(
                                                    $totalIva,
                                                    0,
                                                    ',',
                                                    '.'
                                                ) }}
                                            </th>
                                        @endif

                                        <th class="text-right">
                                            G.
                                            {{ number_format(
                                                $totalPagar,
                                                0,
                                                ',',
                                                '.'
                                            ) }}
                                        </th>
                                    </tr>
                                </tfoot>
                            @endif

                        </table>

                    </div>

                </div>

                @if ($data->motivo_rechazo)
                    <div class="prestamo-seccion">
                        <div class="motivo-rechazo">
                            <strong>
                                <i class="fas fa-times-circle mr-2"></i>
                                Motivo del rechazo
                            </strong>

                            <div class="mt-2">
                                {{ $data->motivo_rechazo }}
                            </div>
                        </div>
                    </div>
                @endif

                @if ($data->ordenPago)
                    <div class="prestamo-seccion">
                        <div class="orden-generada">
                            <strong>
                                <i class="fas fa-check-circle mr-2"></i>
                                Orden de pago generada
                            </strong>

                            <div class="mt-2">
                                Orden N.º
                                {{ str_pad(
                                    $data->ordenPago->numero,
                                    7,
                                    '0',
                                    STR_PAD_LEFT
                                ) }}/{{ $data->ordenPago->anio }}
                            </div>
                        </div>
                    </div>
                @endif

                @if ($data->observaciones)
                    <div class="prestamo-seccion">
                        <h5 class="prestamo-seccion-titulo">
                            Observaciones
                        </h5>

                        <div class="alert alert-light border mb-0">
                            {{ $data->observaciones }}
                        </div>
                    </div>
                @endif

                <div class="text-right">
                    <a
                        href="{{ route('solicitudes') }}"
                        class="btn btn-secondary"
                    >
                        <i class="fas fa-arrow-left mr-2"></i>
                        Volver a mis solicitudes
                    </a>
                </div>

            </div>

        </div>

    </div>

@endsection

@section('js')
@endsection
