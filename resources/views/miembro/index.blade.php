@extends('layouts.admin')

@section('styles')
    <link
        rel="stylesheet"
        type="text/css"
        href="{{ asset('assets/css/elements/alert.css') }}"
    >

    <link
        rel="stylesheet"
        type="text/css"
        href="{{ asset('assets/css/elements/infobox.css') }}"
    >

    <style>
        .miembros-encabezado {
            padding-bottom: 15px;
            border-bottom: 1px solid #e5e7eb;
        }

        .miembro-fila {
            padding: 16px;
            margin-bottom: 14px;
            border: 1px solid #e4e8ec;
            border-radius: 8px;
            background: #ffffff;
        }

        .miembro-fila:hover {
            border-color: #cbd5df;
            box-shadow: 0 3px 10px rgba(0, 0, 0, .05);
        }

        .miembro-tipo {
            display: block;
            margin-bottom: 6px;
            color: #6c757d;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .miembro-nombre {
            font-weight: 700;
            color: #20252a;
        }

        @media (max-width: 767.98px) {
            .miembros-acciones .btn {
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
                    class="miembros-encabezado d-flex flex-wrap
                           align-items-center justify-content-between mb-4"
                >
                    <div class="mb-2">
                        <h4 class="mb-1">Miembros</h4>

                        <small class="text-muted">
                            Administración de miembros y planillas.
                        </small>
                    </div>

                    <div class="miembros-acciones d-flex flex-wrap">
                        <button
                            type="button"
                            class="btn btn-primary mr-2 mb-2"
                            data-toggle="modal"
                            data-target="#crear"
                        >
                            <i class="fa fa-plus mr-1"></i>
                            Nuevo miembro
                        </button>

                        <a
                            href="{{ route('miembros.planillas.create') }}"
                            class="btn btn-success mr-2 mb-2"
                        >
                            <i class="fa fa-file-text-o mr-1"></i>
                            Generar planilla de miembros
                        </a>

                        <a
                            href="{{ route('miembros.planillas.index') }}"
                            class="btn btn-info mb-2"
                        >
                            <i class="fa fa-list-alt mr-1"></i>
                            Ver planillas generadas
                        </a>
                    </div>
                </div>

                @forelse ($data as $item)
                    <div class="miembro-fila">
                        <div class="row align-items-end">
                            <div class="col-md-8 mb-3 mb-md-0">
                                <span class="miembro-tipo">
                                    {{ $tipos[$item->tipo] ?? 'Sin tipo' }}
                                </span>

                                <input
                                    type="text"
                                    class="form-control miembro-nombre"
                                    value="{{ trim($item->nombre . ' ' . $item->apellido) }}"
                                    readonly
                                >
                            </div>

                            <div class="col-md-2 mb-3 mb-md-0">
                                <label class="d-block">
                                    Presente
                                </label>

                                <a
                                    href="{{ route('miembros.cambiarPresente', $item->id) }}"
                                    class="btn
                                        {{ (int) $item->presente === 0
                                            ? 'btn-danger'
                                            : 'btn-success' }}
                                        btn-sm btn-block"
                                >
                                    <i class="fa
                                        {{ (int) $item->presente === 0
                                            ? 'fa-times'
                                            : 'fa-check' }}">
                                    </i>

                                    {{ (int) $item->presente === 0
                                        ? 'NO'
                                        : 'SÍ' }}
                                </a>
                            </div>

                            <div class="col-md-2">
                                <button
                                    type="button"
                                    class="btn btn-warning btn-block"
                                    data-toggle="modal"
                                    data-target="#editar_{{ $item->id }}"
                                >
                                    <i class="fa fa-edit mr-1"></i>
                                    Modificar
                                </button>
                            </div>
                        </div>
                    </div>

                    @include('miembro.editar')
                @empty
                    <div class="alert alert-info mb-0">
                        <i class="fa fa-info-circle mr-1"></i>
                        No existen miembros registrados.
                    </div>
                @endforelse

            </div>
        </div>
    </div>

    @include('miembro.crear')
@endsection

@section('js')
@endsection
