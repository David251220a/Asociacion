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
                        <h3 class="mb-0">Recibos</h3>
                    </div>
                </div>

                @include('varios.mensaje')

                <form action="{{ route('recibo.index') }}" method="GET">
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
                                <label for="fecha_hasta">Tipo Recibo</label>
                                <select name="tipo_recibo_id" id="tipo_recibo_id" class="form-control">
                                    <option value="0" {{ request('tipo_recibo_id') == 0 ? 'selected' : '' }}>TODOS</option>
                                    @foreach ($tipoRecibos as $item)
                                        <option value="{{ $item->id }}" {{ request('tipo_recibo_id') == $item->id ? 'selected' : '' }}>{{ $item->descripcion }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-md-3">
                                <label for="estado">Estado</label>
                                <div class="input-group">
                                    <select name="estado" id="estado" class="form-control">
                                        <option value="0" {{ request('estado') == 0 ? 'selected' : '' }}>TODOS</option>
                                        <option value="1" {{ request('estado') == 1 ? 'selected' : '' }}>ACTIVO</option>
                                        <option value="2" {{ request('estado') == 2 ? 'selected' : '' }}>ANULADO</option>
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
                                        <th class="">Recibo</th>
                                        <th class="">Fecha</th>
                                        <th class="">Tipo Recibo</th>
                                        <th class="">Persona/Institución</th>
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
                                                {{ $item->sucursal }}-{{ $item->general }}-{{ str_pad($item->numero, 7, '0', STR_PAD_LEFT) }}
                                            </td>
                                            <td class="">
                                                {{ $item->created_at->format('d/m/Y H:i') }}
                                            </td>
                                            <td>
                                                {{ $item->tipo_recibo->descripcion ?? '' }}
                                            </td>
                                            <td class="">
                                                {{$item->persona->nombre}} {{$item->persona->apellido}}
                                            </td>
                                            <td width="30%" class="">
                                                {{$item->concepto}}
                                            </td>
                                            <td class="text-right">
                                                {{number_format($item->monto_total, 0, ',', '.')}}
                                            </td>
                                            <td class="text-center">
                                                @if($item->estado_id == 1)
                                                    <span class="badge badge-success">APROBADO</span>
                                                @else
                                                    <span class="badge badge-danger">ANULADO</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                {{-- @can('recibo.show') --}}
                                                    <a href="{{route('recibo.show', $item)}}" class="mr-3">
                                                        <svg
                                                            xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                            stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye">
                                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>
                                                        </svg>
                                                    </a>
                                                {{-- @endcan --}}

                                                {{-- @can('recibo.anular') --}}
                                                    @if ($item->anulado == 0)
                                                        <button type="button" class="btn btn-danger btn-sm mr-3" data-toggle="modal" data-target="#exampleModalCenter_{{ $item->id }}">
                                                            <svg
                                                                xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline>
                                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17">
                                                                </line><line x1="14" y1="11" x2="14" y2="17"></line>
                                                            </svg>
                                                        </button>

                                                        <div class="modal fade"
                                                            id="exampleModalCenter_{{ $item->id }}"
                                                            tabindex="-1"
                                                            role="dialog"
                                                            data-backdrop="static"
                                                            data-keyboard="false"
                                                            aria-labelledby="modalTitle_{{ $item->id }}"
                                                            aria-hidden="true">

                                                            <div class="modal-dialog modal-dialog-centered" role="document">
                                                                <div class="modal-content">

                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title" id="modalTitle_{{ $item->id }}">
                                                                            Eliminar Cobro de Planilla
                                                                        </h5>
                                                                    </div>

                                                                    <div class="modal-body">
                                                                        ¿Está seguro que desea eliminar este cobro de planilla?
                                                                    </div>

                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-primary" data-dismiss="modal">
                                                                            Cancelar
                                                                        </button>
                                                                        <form action="{{route('recibo.anular', $item)}}" method="POST">
                                                                            @csrf
                                                                            <button type="submit"  class="btn btn-danger">
                                                                                Eliminar
                                                                            </button>
                                                                        </form>

                                                                    </div>

                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                {{-- @endcan --}}

                                            </td>
                                        </tr>
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
