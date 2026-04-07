@extends('layouts.admin')

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/elements/alert.css')}}">
    <link href="{{asset('assets/css/elements/infobox.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('assets/css/tables/table-basic.css')}}" rel="stylesheet" type="text/css" />
@endsection

@section('content')

    @php
        $meses = [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre',
        ];
    @endphp
    <div  class="col-lg-12 layout-spacing">
        <div class="statbox widget box box-shadow">
            <div class="widget-content widget-content-area">
                <div class="row align-items-center mb-3">
                    <div class="col-md-6">
                        <h3 class="mb-0">Planilla Generada</h3>
                    </div>

                    <div class="col-md-6 text-end">
                        <a href="{{ route('planilla.create') }}" class="btn btn-primary">
                            <i class="fa fa-plus"></i> Agregar
                        </a>
                    </div>
                </div>
                
                @include('varios.mensaje')

                <form action="{{ route('planilla.index') }}" method="GET">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <div class="form-row mb-2">
                            <div class="form-group col-md-3">
                                <label for="tipo_asociado">Tipo Asociado</label>
                                <select name="tipo_asociado_id" id="tipo_asociado_id" class="form-control">
                                    <option value="0" {{ request('tipo_asociado_id') == 0 ? 'selected' : '' }}> -- TODOS --</option>
                                    <option value="3" {{ request('tipo_asociado_id') == 3 ? 'selected' : '' }}> APORTANTES </option>
                                    <option value="1" {{ request('tipo_asociado_id') == 1 ? 'selected' : '' }}> JUBILADOS/AS </option>
                                </select>
                            </div>

                            <div class="form-group col-md-3">
                                <label for="mes">Mes</label>
                                <select name="mes" id="mes" class="form-control">
                                    <option value="0"> -- TODOS --</option>
                                    @foreach ($meses as $key => $nombre)
                                        <option value="{{ $key }}" {{ request('mes') == $key ? 'selected' : '' }}>{{ $nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-md-3">
                                <label for="anio">Año</label>
                                <div class="input-group">
                                    <select name="anio" id="anio" class="form-control">
                                        @php
                                            $anioActual = now()->year;
                                            $anioSeleccionado = request('anio', $anioActual);
                                        @endphp

                                        @for ($i = 0; $i <= 2; $i++)
                                            @php $anio = $anioActual - $i; @endphp
                                            <option value="{{ $anio }}" {{ $anioSeleccionado == $anio ? 'selected' : '' }}>
                                                {{ $anio }}
                                            </option>
                                        @endfor
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
                                        <th class="">IdPLanilla</th>
                                        <th class="">Periodo</th>
                                        <th class="">Tipo Asociado</th>
                                        <th class="">Cantidad</th>
                                        <th>Monto a Cobrar</th>
                                        <th class="">Pagado</th>
                                        <th class="">Monto Pagado</th>
                                        <th class="text-center">Accion</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($data as $item)
                                        <tr>
                                            <td class="">
                                                {{ $item->planilla_anio }}/{{ str_pad($item->planilla_numero, 5, '0', STR_PAD_LEFT) }}
                                            </td>
                                            <td class="">
                                                {{ $item->anio }}/{{$item->mes}}
                                            </td>
                                            <td>
                                                {{ $item->tipoAsociado->descripcion ?? '' }}
                                            </td>
                                            <td class="text-right">
                                                {{$item->cantidad}}
                                            </td>
                                            <td class="text-right">{{number_format($item->total, 0, ',', '.')}}</td>
                                            <td class="text-right">{{$item->pagado}}</td>
                                            <td class="text-right">
                                                {{number_format($item->monto_pagado, 0, ',', '.')}}
                                            </td>
                                            <td class="text-center">
                                                <a href="{{route('planilla.exportarDetalle', $item)}}" class="mr-3">
                                                    <svg 
                                                        xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" 
                                                        stroke-linejoin="round" class="feather feather-download"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                                        <polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line>
                                                    </svg>
                                                </a>
                                                
                                                @if ($item->pagado == 0)
                                                    <a href="{{route('planilla.cobrar', $item)}}" class="mr-3">
                                                        <svg 
                                                            xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" 
                                                            stroke-linecap="round" stroke-linejoin="round" class="feather feather-dollar-sign"><line x1="12" y1="1" x2="12" y2="23"></line>
                                                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                                                        </svg>
                                                    </a>
                                                @endif
                                            
                                                @php
                                                    $puedeAnular = isset($ultimasPlanillas[$item->tipo_asociado_id]) && $ultimasPlanillas[$item->tipo_asociado_id] == $item->id;
                                                @endphp
                                                
                                                @if($puedeAnular)
                                                    <button type="button" class="btn btn-danger mr-3" data-toggle="modal" data-target="#exampleModalCenter_{{ $item->id }}">
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
                                                                        Eliminar Planilla
                                                                    </h5>
                                                                </div>

                                                                <div class="modal-body">
                                                                    ¿Está seguro que desea eliminar esta planilla?
                                                                </div>

                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-primary" data-dismiss="modal">
                                                                        Cancelar
                                                                    </button>
                                                                    <form action="{{route('planilla.anular', $item)}}" method="POST">
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
