@extends('layouts.admin')

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{asset('assets/css/elements/alert.css')}}">
    <link href="{{asset('assets/css/elements/infobox.css')}}" rel="stylesheet" type="text/css" />
@endsection

@section('content')

    <div  class="col-lg-12 layout-spacing">
        <div class="statbox widget box box-shadow">
            <div class="widget-content widget-content-area">
                <div class="row align-items-center mb-3">
                    <div class="col-md-6">
                        <h3 class="mb-0">Agregar Familiar</h3>
                    </div>
                </div>
                @include('varios.mensaje')
                <form id="form_general" action="{{route('familiar.update', $data)}}" method="post" enctype="multipart/form-data" 
                        onsubmit="
                        if (this.dataset.enviando === '1') return false;
                        this.dataset.enviando = '1';
                        document.getElementById('btnEnviar').disabled = true;
                        document.getElementById('btnEnviar').innerText = 'Enviando...';
                    ">
                    @csrf
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <div class="form-row mb-2">

                                <div class="form-group col-md-3">
                                    <label for="documento">Documento</label>
                                    <input type="text" name="documento" class="form-control" value="{{old('documento', $data->documento)}}" required>
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="nombre">Nombre</label>
                                    <input type="text" name="nombre" class="form-control" value="{{(old('nombre', $data->nombre))}}" required>
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="apellido">Apellido</label>
                                    <input type="text" name="apellido" class="form-control" value="{{old('apellido', $data->apellido)}}" required>
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="celular">Celular</label>
                                    <input type="text" name="celular" class="form-control" value="{{old('celular', $data->celular)}}">
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="tipo">Tipo</label>
                                    <select name="tipo_familiar" id="tipo_familiar" class="form-control">
                                        @foreach ($tipo as $item)
                                            <option {{ (old('tipo_familiar', $data->tipo_familiar_id) == $item->id ? 'selected' : '' ) }} value="{{$item->id}}">{{$item->descripcion}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="estado_id">Estado</label>
                                    <select name="estado_id" id="estado_id" class="form-control">
                                        @foreach ($estado as $item)
                                            <option {{ (old('tipo_familiar', $data->estado_id) == $item->id ? 'selected' : '' ) }} value="{{$item->id}}">{{$item->descripcion}}</option>
                                        @endforeach
                                    </select>
                                </div>

                            </div>

                            <div class="form-row">
                                <button id="btnEnviar" type="submit" class="btn btn-success"
                                    onclick="
                                        if (this.dataset.clicked === '1') return false;
                                        this.dataset.clicked = '1';
                                        this.disabled = true;
                                        this.innerText = 'Enviando...';
                                        this.form.requestSubmit();
                                        return false;
                                    ">
                                    Actualizar
                                </button>
                            </div>

                        </div>
                    </div>

                </form>

            </div>
        </div>
    </div>

@endsection


@section('js')
    <script src="{{asset('plugins/select2/select2.min.js')}}"></script>
    <script src="{{asset('plugins/select2/custom-select2.js')}}"></script>
    <script src="{{asset('js/asociado.js')}}"></script>
@endsection
