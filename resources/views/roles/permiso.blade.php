@extends('layouts.admin')

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/elements/alert.css')}}">
    <link href="{{asset('assets/css/elements/infobox.css')}}" rel="stylesheet" type="text/css" />
@endsection

@section('content')

    <div class="col-lg-12 layout-spacing">

        <h2 class="mt-3">Crear Permiso</h2>
        @include('varios.mensaje')
        <div class="widget-content widget-content-area">
            <form action="{{route('role.permiso_crear_post')}}" method="post" onsubmit="
                    if (this.dataset.enviando === '1') return false;
                    this.dataset.enviando = '1';
                    document.getElementById('btnEnviar').disabled = true;
                    document.getElementById('btnEnviar').innerText = 'Enviando...';"
            >
                @csrf
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <div class="form-row mb-2">
                            <div class="form-group col-md-3">
                                <label for="">Permiso</label>
                                <input type="text" class="form-control" name="name" id="name" value="{{old('name')}}" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="">Descripcion</label>
                                <input type="text" class="form-control" name="descripcion" value="{{old('descripcion')}}" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <button id="btnEnviar" type="submit" class="btn btn-success">
                        Grabar
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection
