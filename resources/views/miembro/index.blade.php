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

                <div class="d-flex align-items-center mb-3">
                    <h4 class="mb-0">Miembros</h4>

                    <button type="button" class="btn btn-primary ms-3 mx-4" data-toggle="modal" data-target="#crear">
                        <i class="fa fa-plus"></i> Nuevo
                    </button>
                </div>

                @foreach ($data as $item)
                    <div class="row mb-3 align-items-end">
                        <div class="col-md-8 mb-2">
                            <label>{{ $tipos[$item->tipo] }}</label>
                            <input
                                type="text"
                                class="form-control"
                                value="{{ $item->nombre . ' ' . $item->apellido }}"
                                readonly style="font-weight: bold; color: black">
                        </div>
                        <div class="col-md-2 mb-2">
                            <label for="">Presente</label>
                            <a href="{{route('miembros.cambiarPresente', $item->id)}}" class="btn {{$item->presente == 0 ? 'btn-danger' : 'btn-success'}} btn-sm">
                                {{$item->presente == 0 ? 'NO' : 'SI'}}
                            </a>
                        </div>
                        <div class="col-md-2 mb-2">
                            <button type="button" class="btn btn-warning btn-block" data-toggle="modal" data-target="#editar_{{$item->id}}">
                                <i class="fa fa-edit"></i> Modificar
                            </button>
                        </div>
                    </div>
                    @include('miembro.editar')
                @endforeach

            </div>
        </div>
    </div>

    @include('miembro.crear')
@endsection


@section('js')

@endsection
