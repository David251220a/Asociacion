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
                        <h3 class="mb-0">Usuarios</h3>
                    </div>
                    @can('usuario.create')
                        <div class="col-md-6 text-end">
                            <a href="{{ route('user.create') }}" class="btn btn-primary">
                                <i class="fa fa-plus"></i> Agregar
                            </a>
                        </div>
                    @endcan
                    
                </div>
                
                @include('varios.mensaje')

                <form action="{{ route('user.index') }}" method="GET">
                    <div class="col-lg-4 col-md-6 col-xs-12">
                        <div class="form-group">
                            <label for="search">Búsqueda</label>

                            <div class="input-group">
                                <input type="text"
                                    name="search"
                                    id="search"
                                    class="form-control"
                                    placeholder="Nombre, apellido o documento..."
                                    value="{{ $search }}">

                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-info">
                                        <i class="fa fa-search"></i>
                                    </button>
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
                                        <th class="">Usuario</th>
                                        <th class="">Documento</th>
                                        <th class="">Nombre</th>
                                        <th>Email</th>
                                        <th class="">Celular</th>
                                        <th class="text-center">Accion</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($data as $item)
                                        <tr>
                                            <td class="">
                                                {{$item->username}}
                                            </td>
                                            <td>
                                                {{$item->documento}}
                                            </td>
                                            <td>
                                                {{$item->name}} {{$item->lastname}}
                                            </td>
                                            <td>{{$item->email}}</td>
                                            <td>{{$item->celular}}</td>
                                            <td class="text-center">
                                                @can('usuario.edit')
                                                    <a href="{{route('user.edit', $item)}}" class="ml-3">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" 
                                                            class="feather feather-edit"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                                        </svg>
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
