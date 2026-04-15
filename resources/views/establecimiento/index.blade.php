@extends('layouts.admin')

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/elements/alert.css')}}">
    <link href="{{asset('assets/css/elements/infobox.css')}}" rel="stylesheet" type="text/css" />
@endsection

@section('content')

    <div class="col-lg-12 layout-spacing">
        <div class="statbox widget box box-shadow">
            <div class="widget-content widget-content-area">

                @include('varios.mensaje')

                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 filtered-list-search mx-auto">
                        <div class="alert alert-icon-left alert-light-success mb-4" role="alert">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-alert-triangle">
                            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                            <line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12" y2="17"></line></svg>
                            Establecimiento
                        </div>
                    </div>
                </div>
                @can('establecimiento.create')
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <a href="{{route('establecimiento.create')}}" class="btn btn-info">Agregar</a>
                        </div>
                    </div>
                @endcan
                
                <div class="row mt-3">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-striped table-checkable table-highlight-head mb-4">
                                <thead>
                                    <tr>
                                        <th class="">ID#</th>
                                        <th class="">Punto</th>
                                        <th class="">Establecimiento</th>
                                        <th class="">Direccion</th>
                                        <th class="">Telefono</th>
                                        <th class="">Factura Sucursal</th>
                                        <th class="">Factura General</th>
                                        <th class=""></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($data as $item)
                                        <tr>
                                            <td>
                                                {{$item->id}}
                                            </td>
                                            <td class="">
                                                {{$item->punto}}
                                            </td>
                                            <td>
                                                {{$item->descripcion}}
                                            </td>
                                            <td>
                                                {{$item->direccion}}
                                            </td>
                                            <td>
                                                {{$item->telefono}}
                                            </td>
                                            <td>
                                                {{$item->sucursal}}
                                            </td>
                                            <td>
                                                {{$item->general}}
                                            </td>
                                            <td>
                                                @can('establecimiento.edit')
                                                    <a class="ml-2" href="{{route('establecimiento.edit', $item)}}" data-toggle="tooltip" data-placement="top" title="Editar">
                                                        <svg xmlns="#" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" 
                                                        stroke-linejoin="round" class="feather feather-edit-3"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                                                    </a>
                                                @endcan
                                                
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="8"></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

@endsection


@section('js')
    <script src="{{asset('js/factura.js')}}"></script>
@endsection