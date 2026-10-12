@extends('layouts.admin')

@section('styles')
    <link
        rel="stylesheet"
        type="text/css"
        href="{{ asset('assets/css/elements/alert.css') }}"
    >

    <style>
        .filtros-prestamos {
            padding: 18px;
            margin-bottom: 20px;
            border: 1px solid #e0e6ed;
            border-radius: 6px;
            background-color: #f8f9fa;
        }

        .tabla-prestamos th {
            white-space: nowrap;
            vertical-align: middle !important;
        }

        .tabla-prestamos td {
            vertical-align: middle !important;
        }

        .numero-prestamo {
            color: #1b55e2;
            font-weight: bold;
            white-space: nowrap;
        }

        .nombre-asociado {
            color: #1b2e4b;
            font-weight: bold;
            text-transform: uppercase;
        }

        .documento-asociado {
            color: #6c757d;
            font-size: 12px;
        }

        .monto {
            font-weight: bold;
            text-align: right;
            white-space: nowrap;
        }

        .badge-estado {
            display: inline-block;
            min-width: 90px;
            padding: 6px 9px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
        }

        .estado-pendiente {
            color: #856404;
            background-color: #fff3cd;
        }

        .estado-activo {
            color: #155724;
            background-color: #d4edda;
        }

        .estado-pagado {
            color: #004085;
            background-color: #cce5ff;
        }

        .estado-anulado {
            color: #721c24;
            background-color: #f8d7da;
        }

        .estado-default {
            color: #383d41;
            background-color: #e2e3e5;
        }

        .acciones-prestamo {
            white-space: nowrap;
        }

        .resumen-listado {
            color: #6c757d;
            font-size: 12px;
        }

        .sin-resultados {
            padding: 30px !important;
            color: #6c757d;
            text-align: center;
        }
    </style>
@endsection

@section('content')

    <div class="col-lg-12 layout-spacing">
        <div class="statbox widget box box-shadow">
            <div class="widget-content widget-content-area">

                @include('varios.mensaje')

                <div class="d-flex align-items-center mb-4">
                    <div>
                        <h4 class="mb-1">
                            Préstamos aprobados
                        </h4>

                        <div class="resumen-listado">
                            Consulta general de préstamos generados
                        </div>
                    </div>
                </div>

                {{-- FILTROS --}}
                <form method="GET" action="{{ route('prestamos.index') }}" class="filtros-prestamos">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="documento">
                                Documento
                            </label>

                            <input
                                type="text"
                                name="documento"
                                id="documento"
                                class="form-control"
                                value="{{ request('documento') }}"
                                placeholder="Número de documento"
                                autocomplete="off"
                            >
                        </div>

                        <div class="col-md-2 mb-3">
                            <label for="numero">
                                N.º de préstamo
                            </label>

                            <input type="number" name="numero" id="numero" class="form-control" value="{{ request('numero') }}" min="1" placeholder="Número">
                        </div>

                        <div class="col-md-2 mb-3">
                            <label for="anio">
                                Año
                            </label>

                            <select name="anio" id="anio" class="form-control">
                                <option value="">
                                    Todos
                                </option>

                                @for ($anio = now()->year; $anio >= now()->year - 10; $anio--)
                                    <option value="{{ $anio }}" {{(string) request('anio') === (string) $anio ? 'selected' : ''}}>
                                        {{ $anio }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="tipo_prestamo_id">
                                Tipo de préstamo
                            </label>

                            <select name="tipo_prestamo_id" id="tipo_prestamo_id" class="form-control">
                                <option value="">
                                    Todos los tipos
                                </option>

                                @foreach ($tiposPrestamos as $tipoPrestamo)
                                    <option value="{{ $tipoPrestamo->id }}" {{(string) request('tipo_prestamo_id') === (string) $tipoPrestamo->id ? 'selected' : ''}}>
                                        {{$tipoPrestamo->descripcion ?? $tipoPrestamo->nombre}}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2 mb-3">
                            <label for="estado_prestamo_id">
                                Estado
                            </label>

                            <select name="estado_prestamo_id" id="estado_prestamo_id" class="form-control">
                                <option value="">
                                    Todos
                                </option>

                                @foreach ($estadosPrestamos as $estadoPrestamo)
                                    <option value="{{ $estadoPrestamo->id }}" {{(string) request('estado_prestamo_id') === (string) $estadoPrestamo->id ? 'selected' : ''}}>
                                        {{$estadoPrestamo->descripcion ?? $estadoPrestamo->nombre}}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="text-right">
                        <a href="{{ route('prestamos.index') }}" class="btn btn-secondary">
                            <i class="fa fa-eraser"></i>
                            Limpiar
                        </a>

                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-search"></i>
                            Buscar
                        </button>
                    </div>
                </form>

                {{-- CANTIDAD DE RESULTADOS --}}
                <div class="d-flex justify-content-between mb-3">
                    <div class="resumen-listado">
                        Se encontraron

                        <strong>
                            {{ number_format($data->total(), 0, ',', '.') }}
                        </strong>

                        préstamo(s).
                    </div>
                </div>

                {{-- LISTADO --}}
                <div class="table-responsive">
                    <table class="table table-bordered table-hover tabla-prestamos">
                        <thead>
                            <tr>
                                <th class="text-center">
                                    Préstamo
                                </th>

                                <th>
                                    Asociado
                                </th>

                                <th>
                                    Tipo
                                </th>

                                <th class="text-center">
                                    Cuotas
                                </th>

                                <th class="text-right">
                                    Capital
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
                                    Desembolso
                                </th>

                                <th class="text-center">
                                    Estado
                                </th>

                                <th class="text-center">
                                    Acciones
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($data as $item)
                                @php
                                    $persona =$item->persona ?? optional($item->solicitud)->persona;
                                    $tipoPrestamo = $item->tipoPrestamo ?? optional($item->solicitud)->tipoPrestamo;
                                    $numeroPrestamo = $item->numero_prestamo ?? $item->numero;
                                    $montoCapital = (int) ($item->monto_capital ?? 0);
                                    $montoTotal = (int) ( $item->monto_total ?? $item->monto_capital ?? 0);
                                    $montoPagado = (int) ($item->monto_pagado ?? 0);
                                    $saldo = max($montoTotal - $montoPagado,0);
                                    $estadoId = (int) ($item->estado_prestamo_id);

                                    $claseEstado = match ($estadoId) {
                                        1 => 'estado-pendiente',
                                        2 => 'estado-activo',
                                        3 => 'estado-pagado',
                                        4 => 'estado-anulado',
                                        default => 'estado-default',
                                    };

                                    $estadoDescripcion = optional($item->estadoPrestamo)->descripcion
                                    ?? optional( $item->estadoPrestamo)->nombre
                                    ?? match ($estadoId) {
                                        1 => 'Pendiente',
                                        2 => 'Activo',
                                        3 => 'Pagado',
                                        4 => 'Anulado',
                                        default => 'Sin estado',
                                    };
                                @endphp

                                <tr>
                                    <td class="text-center">
                                        <span class="numero-prestamo">
                                            {{str_pad($numeroPrestamo,5,'0',STR_PAD_LEFT)}}/{{ $item->anio }}
                                        </span>
                                    </td>

                                    <td>
                                        <div class="nombre-asociado">
                                            {{trim(optional($persona)->nombre . ' ' . optional($persona)->apellido)  ?: 'SIN DATOS'}}
                                        </div>

                                        <div class="documento-asociado">
                                            Documento:
                                            {{optional($persona)->documento?? 'N/A'}}
                                        </div>
                                    </td>

                                    <td>
                                        {{optional($tipoPrestamo)->descripcion ?? optional($tipoPrestamo)->nombre?? 'SIN TIPO'}}
                                    </td>

                                    <td class="text-center">
                                        {{$item->cantidad_cuotas ?? optional($item->solicitud)->cantidad_cuotas ?? 0}}
                                    </td>

                                    <td class="monto">
                                        {{number_format($montoCapital,0,',','.')}}
                                    </td>

                                    <td class="monto">
                                        {{number_format($montoTotal,0, ',', '.')}}
                                    </td>

                                    <td class="monto">
                                        {{number_format( $montoPagado,0,',','.')}}
                                    </td>

                                    <td class="monto">
                                        {{number_format($saldo,0,',','.' )}}
                                    </td>

                                    <td class="text-center">
                                        @if ($item->fecha_desembolso)
                                            {{\Carbon\Carbon::parse($item->fecha_desembolso)->format('d/m/Y')}}
                                        @else
                                            <span class="text-muted">
                                                Pendiente
                                            </span>
                                        @endif
                                    </td>

                                    <td class="text-center">
                                        <span class="badge-estado {{ $claseEstado }}">
                                            {{ $estadoDescripcion }}
                                        </span>
                                    </td>

                                    <td class="text-center acciones-prestamo">
                                        @can('prestamos.show')
                                            <a href="{{ route('prestamos.show', $item->id) }}" class="btn btn-info btn-sm" title="Ver préstamo">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                        @endcan

                                        @if (Route::has('prestamos.imprimir'))
                                            <a href="{{ route('prestamos.imprimir', $item->id) }}" target="_blank" class="btn btn-secondary btn-sm" title="Imprimir préstamo">
                                                <i class="fa fa-print"></i>
                                            </a>
                                        @endif

                                        @can('prestamos.contrato')
                                            <a href="{{ route('prestamos.contrato', $item->id) }}" target="_blank" class="btn btn-dark">
                                                <i class="fa fa-file-text-o"></i>
                                                Contrato
                                            </a>
                                        @endcan

                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="sin-resultados">
                                        <i class="fa fa-search" style="font-size: 25px;"></i>
                                        <div class="mt-2">
                                            No se encontraron préstamos con
                                            los filtros seleccionados.
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- PAGINACIÓN --}}
                @if ($data->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $data->onEachSide(1)->links() }}
                    </div>
                @endif

            </div>
        </div>
    </div>

@endsection

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const documento = document.getElementById('documento');

            if (documento) {
                documento.focus();
            }
        });
    </script>
@endsection
