@extends('layouts.admin')

@section('styles')
    <link rel="stylesheet" type="text/css"
          href="{{ asset('assets/css/elements/alert.css') }}">

    <link rel="stylesheet" type="text/css"
          href="{{ asset('assets/css/elements/infobox.css') }}">

    <link rel="stylesheet" type="text/css"
          href="{{ asset('assets/css/tables/table-basic.css') }}">

    <style>
        .titulo-solicitudes {
            color: #1b3a5b;
            font-weight: 700;
        }

        .numero-solicitud {
            color: #1b6fc2;
            font-weight: 700;
        }

        .monto-solicitud {
            font-weight: 600;
            text-align: right;
            white-space: nowrap;
        }

        .tabla-vacia {
            padding: 45px 15px !important;
            color: #8795a3;
            text-align: center;
        }

        .tabla-vacia i {
            display: block;
            margin-bottom: 12px;
            color: #b5c0ca;
            font-size: 38px;
        }

        .btn-ver-solicitud {
            color: #1b6fc2;
        }

        .btn-ver-solicitud:hover {
            color: #124f8c;
        }
    </style>
@endsection

@section('content')

    <div class="col-lg-12 layout-spacing">
        <div class="statbox widget box box-shadow">

            <div class="widget-content widget-content-area">

                <div class="row align-items-center mb-4">

                    <div class="col-md-7">
                        <h3 class="titulo-solicitudes mb-1">
                            Mis solicitudes
                        </h3>

                        <p class="text-muted mb-0">
                            Consultá las solicitudes realizadas durante el año
                            {{ now()->year }}.
                        </p>
                    </div>

                    <div class="col-md-5 text-md-right mt-3 mt-md-0">
                        <a
                            href="{{ route('nueva_solicitud') }}"
                            class="btn btn-primary"
                        >
                            <i class="fa fa-plus mr-2"></i>
                            Nueva solicitud
                        </a>
                    </div>

                </div>

                @include('varios.mensaje')

                <div class="table-responsive">

                    <table class="table table-bordered table-hover table-striped table-checkable table-highlight-head mb-4">

                        <thead>
                            <tr>
                                <th>Número</th>
                                <th>Fecha</th>
                                <th>Tipo de solicitud</th>
                                <th class="text-right">Monto Aprobado</th>
                                <th>Estado</th>
                                <th class="text-center">Acción</th>
                            </tr>
                        </thead>

                        <tbody>

                            @forelse ($data as $item)

                                <tr>
                                    <td>
                                        <span class="numero-solicitud">
                                            {{ str_pad($item->numero, 5, '0', STR_PAD_LEFT) }}/{{ $item->anio }}
                                        </span>
                                    </td>

                                    <td>
                                        {{ \Carbon\Carbon::parse($item->fecha_solicitud)->format('d/m/Y') }}
                                    </td>

                                    <td>
                                        @if ($item->tipo_codigo === 'AYUDA_SOCIAL')
                                            <span class="badge badge-success">
                                                {{ $item->tipo_solicitud }}
                                            </span>
                                        @elseif ($item->tipo_codigo === 'ACTUALIZACION_DATOS')
                                            <span class="badge badge-primary">
                                                {{ $item->tipo_solicitud }}
                                            </span>
                                        @elseif ($item->tipo_codigo === 'PRESTAMO_EMERGENCIA')
                                            <span class="badge badge-info">
                                                {{ $item->tipo_solicitud }}
                                            </span>
                                        @endif
                                    </td>

                                    <td class="monto-solicitud">
                                        @if ($item->tipo_codigo === 'AYUDA_SOCIAL')
                                            @if ($item->monto > 0)
                                                G. {{ number_format($item->monto,0,',','.') }}
                                            @else
                                                <span class="text-muted">
                                                    Pendiente
                                                </span>
                                            @endif
                                        @else
                                            <span class="text-muted">
                                                No aplica
                                            </span>
                                        @endif
                                    </td>

                                    <td>
                                        <span class="badge badge-{{ $item->estado_color ?? 'secondary' }}">
                                            {{ $item->estado_descripcion ?? 'SIN ESTADO' }}
                                        </span>
                                    </td>

                                    <td class="text-center">

                                        @if ($item->tipo_codigo === 'AYUDA_SOCIAL')
                                            <a href="{{ route('ayuda_social_show', $item->id) }}" class="btn btn-sm btn-outline-primary" title="Ver solicitud de ayuda social">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                        @elseif ($item->tipo_codigo === 'ACTUALIZACION_DATOS')
                                            <a href="{{ route('actualizacion_datos_show', $item->id) }}" class="btn btn-sm btn-outline-primary" title="Ver solicitud de actualización de datos">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                        @elseif ($item->tipo_codigo === 'PRESTAMO_EMERGENCIA')
                                            <a href="{{ route('prestamos_emergencia_show', $item->id) }}" class="btn btn-sm btn-outline-primary" title="Ver solicitud de préstamo de emergencia">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                        @endif

                                    </td>
                                </tr>

                            @empty

                                <tr>
                                    <td colspan="6" class="tabla-vacia">
                                        <i class="fas far fa-file-alt"></i>
                                        Todavía no realizaste ninguna solicitud
                                        durante el año {{ now()->year }}.
                                    </td>
                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

                @if ($data->hasPages())
                    <div class="d-flex justify-content-center mt-3">
                        {{ $data->links() }}
                    </div>
                @endif

            </div>
        </div>
    </div>

@endsection

@section('js')
@endsection
