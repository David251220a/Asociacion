@extends('layouts.admin')

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/elements/alert.css')}}">
    <link href="{{asset('assets/css/elements/infobox.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('assets/css/tables/table-basic.css')}}" rel="stylesheet" type="text/css" />
@endsection

@section('content')

    <div  class="col-lg-12 layout-spacing">
        <div class="statbox widget box box-shadow">
            <div class="widget-content widget-content-area">
                <div class="row align-items-center mb-3">
                    <div class="col-md-6">
                        <h3 class="mb-0">Orden de Pago</h3>
                    </div>

                    <div class="col-md-6 text-end">
                        @can('orden.create')
                            <a href="{{ route('orden.create') }}" class="btn btn-primary">
                                <i class="fa fa-plus"></i> Agregar
                            </a>
                        @endcan
                    </div>
                </div>

                @include('varios.mensaje')

                <form action="{{ route('orden.index') }}" method="GET">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <div class="form-row mb-2">
                            <div class="form-group col-md-3">
                                <label for="fecha_desde">Fecha Desde</label>
                                <input type="date" name="fecha_desde" value="{{ $fecha_desde }}" class="form-control">
                            </div>

                            <div class="form-group col-md-3">
                                <label for="fecha_hasta">Fecha Hasta</label>
                                <input type="date" name="fecha_hasta" value="{{ $fecha_hasta }}" class="form-control">
                            </div>

                            <div class="form-group col-md-3">
                                <label for="tipo_egrego_id">Tipo Egreso</label>
                                <select name="tipo_egrego_id" id="tipo_egrego_id" class="form-control">
                                    <option value="0" {{ request('tipo_egrego_id') == 0 ? 'selected' : '' }}>TODOS</option>
                                    @foreach ($tipo_egresos as $item)
                                        <option value="{{ $item->id }}" {{ request('tipo_egrego_id') == $item->id ? 'selected' : '' }}>{{ $item->descripcion }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-md-3">
                                <label for="estado">Estado</label>
                                <div class="input-group">
                                    <select name="estado" id="estado" class="form-control">
                                        <option value="9" {{ $estado == 9 ? 'selected' : '' }}>TODOS</option>
                                        <option value="1" {{ $estado == 1 ? 'selected' : '' }}>PAGADO</option>
                                        <option value="2" {{ $estado == 2 ? 'selected' : '' }}>ANULADO</option>
                                        <option value="0" {{ $estado == 0 ? 'selected' : '' }}>PENDIENTE</option>
                                    </select>

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
                                        <th class="">Fecha</th>
                                        <th class="">Orden Pago</th>
                                        <th class="">Tipo Egreso</th>
                                        <th class="">Beneficiario</th>
                                        <th class="">Concepto</th>
                                        <th>Total</th>
                                        <th class="">Estado</th>
                                        <th class="text-center">Accion</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($data as $item)
                                        <tr>
                                            <td class="">
                                                {{ $item->created_at->format('d/m/Y H:i') }}
                                            </td>
                                            <td class="">
                                                {{ str_pad($item->numero, 7, '0', STR_PAD_LEFT) }}/{{ $item->anio }}
                                            </td>
                                            <td>
                                                {{ $item->tipo_egreso->descripcion ?? '' }}
                                            </td>
                                            <td class="">
                                                {{$item->persona->nombre}} {{$item->persona->apellido}}
                                            </td>
                                            <td width="30%" class="">
                                                {{$item->concepto}}
                                            </td>
                                            <td class="text-right">
                                                {{number_format($item->total, 0, ',', '.')}}
                                            </td>
                                            <td class="text-center">
                                                @if($item->estado_pago == 1)
                                                    <span class="badge badge-success">APROBADO</span>
                                                @elseif($item->estado_pago == 2)
                                                    <span class="badge badge-danger">ANULADO</span>
                                                @else
                                                    <span class="badge badge-warning">PENDIENTE</span>
                                                @endif
                                            </td>
                                            <td class="text-left">
                                                @can('orden.pago')
                                                    @if ($item->pagos->sum('monto') <> $item->total)
                                                        <a href="{{route('orden.pago', $item)}}" class="mr-3" title="Registrar pago">
                                                            <i class="fas fa-receipt" style="font-size: 20px"></i>
                                                        </a>
                                                    @endif
                                                @endcan

                                                @can('orden.show')
                                                    <a href="{{route('orden.show', $item)}}" class="mr-3" title="Ver Orden Pago">
                                                        <i class="fas fa-eye" class="mr-3" style="font-size: 20px"></i>
                                                    </a>
                                                @endcan

                                                @can('orden.anular')
                                                    <button type="button" class="btn btn-danger btn-sm mr-3" title="Anular Orden Pago" data-toggle="modal" data-target="#anular_{{$item->id}}">
                                                        <i class="fas fa-trash-alt" style="font-size: 10px"></i>
                                                    </button>
                                                @endcan

                                                <a href="{{route('pdf.orden', $item)}}" target="_blank">
                                                    <i class="fas fa-print" class="mr-3" style="font-size: 20px"></i>
                                                </a>

                                            </td>
                                        </tr>
                                        @include('orden.anular')
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="6">Total General</th>
                                        <th colspan="2" class="text-right">{{number_format($totalGeneral, 0, ',', '.')}}</th>
                                    </tr>
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
