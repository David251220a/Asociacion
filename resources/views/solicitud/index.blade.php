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
                        <h3 class="mb-0">Solicitudes</h3>
                    </div>

                </div>

                @include('varios.mensaje')

                <form action="{{ route('solicitud.index') }}" method="GET">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <div class="form-row mb-2">
                            <div class="form-group col-md-3">
                                <label for="estado">Estado</label>
                                <select name="estado" id="estado" class="form-control">
                                    <option value="0" {{ request('estado') == 0 ? 'selected' : '' }}>PENDIENTE</option>
                                    <option value="1" {{ request('estado') == 1 ? 'selected' : '' }}>APROBADO</option>
                                    <option value="2" {{ request('estado') == 2 ? 'selected' : '' }}>RECHAZADO</option>
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
                                        <th class="">Celular</th>
                                        <th class="">Email</th>
                                        <th class="">Institución</th>
                                        <th class="">Estado</th>
                                        <th class="text-center">Accion</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($data as $item)
                                        <tr>
                                            <td class="">
                                                {{ str_pad($item->numero_solicitud, 7, '0', STR_PAD_LEFT) }}/{{$item->anio}}
                                            </td>
                                            <td class="">
                                                {{$item->documento}}
                                            </td>
                                            <td>
                                                {{$item->nombre}} {{$item->apellido}}
                                            </td>
                                            <td>{{$item->tipo_asociado->descripcion}}</td>
                                            <td>{{$item->celular}}</td>
                                            <td>
                                                {{$item->email}}
                                            </td>
                                            <td>
                                                {{$item->institucion->descripcion}}
                                            </td>
                                            <td>
                                                @if ($item->aprobado == 0)
                                                    PENDIENTE
                                                @endif

                                                @if ($item->aprobado == 1)
                                                    APROBADO
                                                @endif

                                                @if ($item->aprobado == 2)
                                                    RECHAZADO
                                                @endif
                                            </td>
                                            <td class="">
                                                {{-- @can('solicitud.show') --}}
                                                   <a href="{{route('solicitud.show', $item)}}" class="ml-3" style="font-size: 15px">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                {{-- @endcan --}}

                                                <a href="{{route('solicitud.imprimir', $item)}}" target="__blank" class="ml-3" style="font-size: 15px">
                                                    <i class="fas fa-file-pdf"></i>
                                                </a>

                                               {{-- @can('ficha_medica.create')
                                                   <a href="{{route('ficha_medica.create', $item)}}" class="ml-3">
                                                        <svg
                                                            xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                            class="feather feather-file-text"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline>
                                                            <line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline>
                                                        </svg>
                                                    </a>
                                               @endcan --}}

                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <th>
                                        <td colspan="8"></td>
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
