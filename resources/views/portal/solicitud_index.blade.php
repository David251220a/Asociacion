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
                                <th class="text-right">Monto</th>
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
                                        <span class="badge badge-light">
                                            AYUDA SOCIAL
                                        </span>
                                    </td>

                                    <td class="monto-solicitud">
                                        G. {{ number_format($item->monto_solicitado, 0, ',', '.') }}
                                    </td>

                                    <td>
                                        <span class="badge badge-info">
                                            {{ $item->estadoSolicitud->descripcion ?? 'SIN ESTADO' }}
                                        </span>
                                    </td>

                                    <td class="text-center">
                                        <a href="#" class="btn-ver-solicitud" title="Ver solicitud">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                width="22"
                                                height="22"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                class="feather feather-eye"
                                            >
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"></path>
                                                <circle cx="12" cy="12" r="3"></circle>
                                            </svg>
                                        </a>
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
