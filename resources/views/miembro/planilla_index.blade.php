@extends('layouts.admin')

@section('styles')
    <link
        rel="stylesheet"
        type="text/css"
        href="{{ asset('assets/css/elements/alert.css') }}"
    >

    <style>
        .planilla-encabezado {
            padding-bottom: 16px;
            border-bottom: 1px solid #e5e7eb;
        }

        .planilla-tabla thead th {
            border-top: none;
            background: #f5f7fa;
            color: #34495e;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .planilla-tabla td {
            vertical-align: middle;
        }

        .planilla-numero {
            color: #1f2937;
            font-weight: 700;
        }

        .planilla-periodo {
            color: #334155;
            font-weight: 600;
        }

        .planilla-monto {
            font-weight: 600;
            white-space: nowrap;
        }

        .estado-planilla {
            display: inline-block;
            min-width: 85px;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-align: center;
            text-transform: uppercase;
        }

        .estado-generada {
            color: #7c5c00;
            background: #fff3cd;
        }

        .estado-pagada {
            color: #155724;
            background: #d4edda;
        }

        .estado-anulada {
            color: #721c24;
            background: #f8d7da;
        }

        @media (max-width: 767.98px) {
            .planilla-acciones .btn {
                display: block;
                width: 100%;
                margin-right: 0 !important;
            }
        }
    </style>
@endsection

@section('content')
    <div class="col-lg-12 layout-spacing">
        <div class="statbox widget box box-shadow">
            <div class="widget-content widget-content-area">

                @include('varios.mensaje')

                <div
                    class="planilla-encabezado d-flex flex-wrap
                           align-items-center justify-content-between mb-4"
                >
                    <div class="mb-2">
                        <h4 class="mb-1">
                            Planillas de miembros
                        </h4>

                        <small class="text-muted">
                            Consulta de planillas generadas,
                            pagadas y anuladas.
                        </small>
                    </div>

                    <div class="planilla-acciones d-flex flex-wrap">
                        <a
                            href="{{ route('miembros.index') }}"
                            class="btn btn-secondary mr-2 mb-2"
                        >
                            <i class="fa fa-arrow-left mr-1"></i>
                            Volver a miembros
                        </a>

                        <a
                            href="{{ route('miembros.planillas.create') }}"
                            class="btn btn-primary mb-2"
                        >
                            <i class="fa fa-plus mr-1"></i>
                            Generar planilla
                        </a>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover planilla-tabla">
                        <thead>
                            <tr>
                                <th>Planilla</th>
                                <th>Periodo</th>
                                <th>Fecha</th>
                                <th class="text-center">Miembros</th>
                                <th class="text-right">Remuneración</th>
                                <th class="text-right">Adelantos</th>
                                <th class="text-right">Neto</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($data as $item)
                                @php
                                    $clavePeriodo = $item->anio . '-' . $item->mes;
                                    $estaAnulada = (int) $item->estado_planilla === 3;
                                    $existeOtraVigente = isset($periodosOcupados[$clavePeriodo]);
                                    $puedeRegenerar = $estaAnulada && !$existeOtraVigente;

                                    $claseEstado = match ((int) $item->estado_planilla ) {
                                        1 => 'estado-generada',
                                        2 => 'estado-pagada',
                                        3 => 'estado-anulada',
                                        default => 'estado-generada',
                                    };

                                    $textoEstado = match ((int) $item->estado_planilla) {
                                        1 => 'Generada',
                                        2 => 'Pagada',
                                        3 => 'Anulada',
                                        default => 'Desconocido',
                                    };
                                @endphp

                                <tr>
                                    <td>
                                        <span class="planilla-numero">
                                            N.º
                                            {{ str_pad(
                                                $item->numero,
                                                5,
                                                '0',
                                                STR_PAD_LEFT
                                            ) }}
                                            /
                                            {{ $item->anio }}
                                        </span>
                                    </td>

                                    <td>
                                        <span class="planilla-periodo">
                                            {{ $meses[$item->mes]
                                                ?? 'Mes desconocido' }}
                                            {{ $item->anio }}
                                        </span>
                                    </td>

                                    <td>
                                        {{ $item->fecha_generacion
                                            ? \Carbon\Carbon::parse(
                                                $item->fecha_generacion
                                            )->format('d/m/Y')
                                            : 'N/A' }}
                                    </td>

                                    <td class="text-center">
                                        {{ number_format(
                                            $item->cantidad,
                                            0,
                                            ',',
                                            '.'
                                        ) }}
                                    </td>

                                    <td class="text-right planilla-monto">
                                        G.
                                        {{ number_format(
                                            $item->total_remuneracion,
                                            0,
                                            ',',
                                            '.'
                                        ) }}
                                    </td>

                                    <td class="text-right planilla-monto">
                                        G.
                                        {{ number_format(
                                            $item->total_adelanto,
                                            0,
                                            ',',
                                            '.'
                                        ) }}
                                    </td>

                                    <td class="text-right planilla-monto">
                                        G.
                                        {{ number_format(
                                            $item->total_neto,
                                            0,
                                            ',',
                                            '.'
                                        ) }}
                                    </td>

                                    <td class="text-center">
                                        <span class="estado-planilla {{ $claseEstado }}">
                                            {{ $textoEstado }}
                                        </span>
                                    </td>

                                    <td class="text-center">
                                        @php
                                            $generada = (int) $item->estado_planilla === 1;
                                            $pagada = (int) $item->estado_planilla === 2;
                                            $anulada = (int) $item->estado_planilla === 3;
                                            $tieneOrden =  !empty($item->orden_pago_id);
                                            $puedePagar = $generada && !$tieneOrden;
                                            $puedeAnular = $generada && !$tieneOrden;
                                            $puedeRegenerar = $anulada && !$existeOtraVigente;
                                        @endphp

                                        <div class="d-flex flex-wrap justify-content-center">

                                            {{-- IMPRIMIR --}}
                                            <a href="{{ route('miembros.planillas.imprimir',$item) }}"
                                                target="_blank"
                                                class="btn btn-dark btn-sm mr-1 mb-1"
                                                title="Imprimir planilla"
                                            >
                                                <i class="fa fa-print"></i>
                                            </a>

                                            {{-- GENERAR ORDEN DE PAGO --}}
                                            @if ($puedePagar)
                                                <form action="{{ route('miembros.planillas.pagar',$item) }}"
                                                    method="POST"
                                                    class="d-inline"
                                                    onsubmit="return confirm(
                                                        '¿Desea generar la orden de pago de esta planilla?'
                                                    );"
                                                >
                                                    @csrf

                                                    <button
                                                        type="submit"
                                                        class="btn btn-success btn-sm mr-1 mb-1"
                                                        title="Generar orden de pago"
                                                    >
                                                        <i class="fa fa-money mr-1"></i>
                                                        Pagar
                                                    </button>
                                                </form>
                                            @endif

                                            {{-- VER ORDEN GENERADA --}}
                                            @if ($tieneOrden)
                                                <a href="{{ route('orden.show',$item->orden_pago_id) }}" class="btn btn-info btn-sm mr-1 mb-1" title="Ver orden de pago">
                                                    <i class="fa fa-eye mr-1"></i>
                                                    Orden
                                                </a>
                                            @endif

                                            {{-- ANULAR --}}
                                            @if ($puedeAnular)
                                                <button type="button" class="btn btn-danger btn-sm mr-1 mb-1" data-toggle="modal"
                                                    data-target="#anular_planilla_{{ $item->id }}" title="Anular planilla"
                                                >
                                                    <i class="fa fa-ban"></i>
                                                    Anular
                                                </button>
                                                @include('miembro.modal_anular')
                                            @endif

                                            {{-- REGENERAR --}}
                                            @if ($anulada)
                                                @if ($puedeRegenerar)
                                                    <a  href="{{ route('miembros.planillas.regenerar',$item->id) }}" class="btn btn-warning btn-sm mr-1 mb-1">
                                                        <i class="fa fa-refresh"></i>
                                                        Regenerar
                                                    </a>
                                                @else
                                                    <button type="button" class="btn btn-secondary btn-sm mb-1" disabled title="Ya existe otra planilla vigente para este periodo">
                                                        <i class="fa fa-ban mr-1"></i>
                                                        No disponible
                                                    </button>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">
                                        <i class="fa fa-info-circle mr-1"></i>
                                        No existen planillas generadas.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($data->hasPages())
                    <div class="mt-3">
                        {{ $data->links() }}
                    </div>
                @endif

            </div>
        </div>
    </div>
@endsection

@section('js')
@endsection
