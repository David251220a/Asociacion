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

    <div  class="col-lg-12 layout-spacing">
        <div class="statbox widget box box-shadow">
            <div class="widget-content widget-content-area">
                <div class="row align-items-center mb-3">
                    <div class="col-md-6">
                        <h3 class="mb-1">Solicitudes de Ayuda Social</h3>
                    </div>

                </div>

                @include('varios.mensaje')

                <form action="{{ route('solicitud.index_ayuda_social') }}" method="GET">
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
                                        <th class="">Monto Aprobado</th>
                                        <th class="">Orden de Pago</th>
                                        <th class="">Estado Orden</th>
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
                                            <td class="monto-solicitud">
                                                @if ($item->monto_aprobado > 0)
                                                    G. {{ number_format($item->monto_aprobado,0,',','.') }}
                                                @else
                                                    <span class="text-muted">
                                                        Pendiente
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if (!$item->orden_pago)
                                                    <span class="badge badge-secondary">
                                                        Sin orden de pago
                                                    </span>
                                                @else
                                                    {{ str_pad($item->numero, 7, '0', STR_PAD_LEFT) }}/{{ $item->anio }}
                                                @endif
                                            </td>
                                            <td>
                                                <span>
                                                    @if (!$item->orden_pago)
                                                        <span class="badge badge-secondary">
                                                            Sin orden de pago
                                                        </span>
                                                    @elseif ((int) $item->orden_pago->estado_pago === 0)
                                                        <span class="badge badge-warning">
                                                            Pendiente de pago
                                                        </span>
                                                    @else
                                                        <span class="badge badge-success">
                                                            Pagada
                                                        </span>
                                                    @endif
                                                </span>
                                            </td>
                                            <td class="">
                                                @can('solicitud.show_ayuda_social')
                                                   <a href="{{route('solicitud.show_ayuda_social', $item)}}" class="mr-3" style="font-size: 15px" title="Aprobacion de Solicitud">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                @endcan

                                                @can('orden.pago')
                                                    @if ($item->orden_pago->pagos->sum('monto') <> $item->orden_pago->total)
                                                        <a href="{{route('orden.pago', $item->orden_pago)}}" class="mr-3" title="Registrar pago">
                                                            <i class="fas fa-receipt" style="font-size: 20px"></i>
                                                        </a>
                                                    @endif
                                                @endcan

                                                <a href="#" target="__blank" class="mr-3" style="font-size: 15px" title="Imprimir Solicitud">
                                                    <i class="fas fa-file-pdf"></i>
                                                </a>

                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <th>
                                        <td colspan="9"></td>
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
