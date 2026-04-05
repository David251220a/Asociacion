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
                                                {{ $item->anio }}/{{$item->mes}}
                                            </td>
                                            <td>
                                                {{$item->tipo_asociado_id}}
                                            </td>
                                            <td>
                                                {{$item->cantidad}}
                                            </td>
                                            <td>{{number_format($item->total, 0, ',', '.')}}</td>
                                            <td>{{$item->pagado}}</td>
                                            <td>
                                                {{number_format($item->monto_pagado, 0, ',', '.')}}
                                            </td>
                                            <td class="text-center">
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
