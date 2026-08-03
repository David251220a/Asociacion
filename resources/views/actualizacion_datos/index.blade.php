@extends('layouts.admin')

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/elements/alert.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/elements/infobox.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/tables/table-basic.css') }}">

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

    <div  class="col-lg-12 layout-spacing">
        <div class="statbox widget box box-shadow">
            <div class="widget-content widget-content-area">
                <div class="row align-items-center mb-3">
                    <div class="col-md-6">
                        <h3 class="mb-1">Solicitudes de Actualización de Datos</h3>
                    </div>

                </div>

                @include('varios.mensaje')

                <form action="{{ route('actu_datos.index') }}" method="GET">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <div class="form-row mb-2">
                            <div class="form-group col-md-3">
                                <label for="estado">Estado</label>
                                <select name="estado" id="estado" class="form-control">
                                    @foreach ($estados_solicitud as $item)
                                        <option value="{{$item->id}}" {{ request('estado') == $item->id ? 'selected' : '' }}>{{$item->descripcion}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-md-3">
                                <label for="desde">Fecha Desde</label>
                                <input type="date" name="desde" id="desde" value="{{ $desde }}" class="form-control" required>
                            </div>

                            <div class="form-group col-md-3">
                                <label for="hasta">Fecha Hasta</label>
                                <div class="input-group">
                                    <input type="date" name="hasta" id="hasta" value="{{ $hasta }}" class="form-control" required>
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-primary">
                                            🔍 Buscar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="row mt-1">
                    <div  class="col-xl-12 col-md-12 col-sm-12 col-12">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-striped table-checkable table-highlight-head mb-4">
                                <thead>
                                    <tr>
                                        <th class="">Solicitud</th>
                                        <th class="">Documento</th>
                                        <th class="">Nombre y Apellido</th>
                                        <th>Tipo Asociado</th>
                                        <th class="">Motivo</th>
                                        <th class="">Estado</th>
                                        <th class="text-center">Accion</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($data as $item)
                                        <tr>
                                            <td class="">
                                                {{ str_pad($item->numero, 5, '0', STR_PAD_LEFT) }}/{{$item->anio}}
                                            </td>
                                            <td class="">
                                                {{$item->persona->documento}}
                                            </td>
                                            <td>
                                                {{$item->persona->nombre}} {{$item->persona->apellido}}
                                            </td>
                                            <td>{{$item->persona->asociado->tipo_asociado->descripcion}}</td>
                                            <td>{{$item->motivo}}</td>
                                            <td>
                                                <span class="badge badge-{{ $item->estadoSolicitud->color ?? 'secondary' }}">
                                                    {{ $item->estadoSolicitud->descripcion ?? 'SIN ESTADO' }}
                                                </span>
                                            </td>
                                            <td class="">
                                                @can('actu_datos.show')
                                                   <a href="{{route('actu_datos.show', $item)}}" class="mr-3" style="font-size: 15px" title="Aprobacion de Solicitud">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <th>
                                        <td colspan="7"></td>
                                    </th>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="row">
                    {{ $data->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>

@endsection


@section('js')
@endsection
